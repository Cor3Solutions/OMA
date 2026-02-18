<?php
/**
 * manual_encode.php — Manual Khan Level Encoder
 *
 * Two modes:
 *  1. BATCH MODE  — Set a Khan level, then type names one-by-one rapidly.
 *                   Each name is checked: new = inserted, exists at lower level = promoted.
 *  2. SEARCH MODE — Type any name, see live results, add or promote in one click.
 *
 * Matching logic (name-only, since that's all that's available):
 *  - Exact match (case-insensitive)
 *  - Levenshtein distance ≤ 2 (catches typos, swapped words)
 */

$page_title = "Manual Encode Members";
require_once '../config/database.php';
requireAdmin();
$conn = getDbConnection();

// ─────────────────────────────────────────────
// AJAX: Live name search
// ─────────────────────────────────────────────
if (isset($_GET['ajax']) && $_GET['ajax'] === 'search') {
    header('Content-Type: application/json');
    $q = strtolower(trim($conn->real_escape_string($_GET['q'] ?? '')));
    if (strlen($q) < 2) { echo json_encode([]); exit; }


    $results = [];
    $res = $conn->query("
        SELECT km.id, km.full_name, km.current_khan_level, km.khan_color, km.status,
               i.name AS instructor_name
        FROM khan_members km
        LEFT JOIN instructors i ON km.instructor_id = i.id
        ORDER BY km.full_name ASC
    ");
    while ($row = $res->fetch_assoc()) {
        $nameLower = strtolower(trim($row['full_name']));
        $exact  = ($nameLower === $q);
        $dist   = levenshtein($nameLower, $q);
        $starts = strpos($nameLower, $q) !== false;
        if ($exact || $starts || $dist <= 3) {
            $row['match_type'] = $exact ? 'exact' : ($starts ? 'contains' : 'fuzzy');
            $results[] = $row;
        }
    }
    // Sort: exact first, then contains, then fuzzy
    usort($results, function($a, $b) {
        $order = ['exact' => 0, 'contains' => 1, 'fuzzy' => 2];
        return $order[$a['match_type']] - $order[$b['match_type']];
    });
    echo json_encode(array_slice($results, 0, 8));
    exit;
}

// ─────────────────────────────────────────────
// HELPER: Normalize name to Title Case
// ─────────────────────────────────────────────
function toTitleCase($name) {
    $particles = ['de', 'la', 'las', 'los', 'del', 'ng', 'ni', 'van', 'von', 'bin', 'binti', 'al', 'el'];
    $words = explode(' ', strtolower(trim($name)));
    $result = [];
    foreach ($words as $i => $word) {
        if (empty($word)) continue;
        $result[] = ($i === 0 || !in_array($word, $particles)) ? ucfirst($word) : $word;
    }
    return implode(' ', $result);
}

// ─────────────────────────────────────────────
// HELPER: Parse a full name into first / middle / last parts
// Returns ['first' => '', 'middle' => '', 'last' => '']
// Strategy: first word = first name, last word = last name, rest = middle
// ─────────────────────────────────────────────
function parseName($fullName) {
    // Strip particles that aren't standalone name parts
    $nameLower = strtolower(trim($fullName));
    $words = array_values(array_filter(explode(' ', $nameLower)));
    $count = count($words);

    if ($count === 0) return ['first' => '', 'middle' => '', 'last' => ''];
    if ($count === 1) return ['first' => $words[0], 'middle' => '', 'last' => ''];
    if ($count === 2) return ['first' => $words[0], 'middle' => '', 'last' => $words[1]];

    // 3+ words: first = words[0], last = words[last], middle = everything in between
    $first  = $words[0];
    $last   = $words[$count - 1];
    $middle = implode(' ', array_slice($words, 1, $count - 2));
    return ['first' => $first, 'middle' => $middle, 'last' => $last];
}

// ─────────────────────────────────────────────
// HELPER: Smart name matching engine
//
// Returns array of candidates with confidence levels:
//   'certain'   — safe to auto-match (first + last exact, middle absent or matching)
//   'probable'  — first + last match, middle differs (user decides)
//   'possible'  — first name only matches, last name close (user decides)
//
// Location is deliberately NOT a factor — people change training locations.
// ─────────────────────────────────────────────
function findNameMatches($conn, $inputName) {
    $input   = parseName($inputName);
    $results = [];

    $all = $conn->query("
        SELECT km.*, i.name AS instructor_name
        FROM khan_members km
        LEFT JOIN instructors i ON km.instructor_id = i.id
    ");

    while ($row = $all->fetch_assoc()) {
        $db = parseName($row['full_name']);

        // First name must match (exact or very close typo tolerance ≤1)
        $firstDist = levenshtein($input['first'], $db['first']);
        if ($firstDist > 1) continue;

        // Last name comparison
        $lastDist = levenshtein($input['last'], $db['last']);

        // Middle name comparison (either side can be empty — that's the missing middle name case)
        $middleMatch = true;
        $middleNote  = '';
        if (!empty($input['middle']) && !empty($db['middle'])) {
            // Both have middle — check if they match or if input middle initial matches db middle
            $inputInitial = substr($input['middle'], 0, 1);
            $dbInitial    = substr($db['middle'], 0, 1);
            if (levenshtein($input['middle'], $db['middle']) > 2 && $inputInitial !== $dbInitial) {
                $middleMatch = false;
            }
        } elseif (!empty($input['middle']) && empty($db['middle'])) {
            // Input has middle but DB doesn't — possible same person, db record was incomplete
            $middleNote = 'DB record has no middle name';
        } elseif (empty($input['middle']) && !empty($db['middle'])) {
            // Input has no middle (your Khan 5 list case) — DB has full name
            $middleNote = 'Input has no middle name';
        }
        // Both empty → fine, no note

        // ── Determine confidence ──
        if ($firstDist === 0 && $lastDist === 0 && $middleMatch) {
            // First + last exact, middle absent on one side OR matching → CERTAIN
            $confidence = 'certain';
        } elseif ($firstDist <= 1 && $lastDist <= 1 && $middleMatch) {
            // First + last very close (typo), middle ok → CERTAIN (typo corrected)
            $confidence = 'certain';
        } elseif ($firstDist <= 1 && $lastDist <= 2) {
            // First close, last slightly off, or middle conflict → PROBABLE
            $confidence = 'probable';
        } elseif ($firstDist === 0 && $lastDist <= 4) {
            // Same first, last is somewhat similar → POSSIBLE
            $confidence = 'possible';
        } else {
            continue; // Not similar enough to surface
        }

        $results[] = [
            'member'     => $row,
            'confidence' => $confidence,
            'note'       => $middleNote,
            // parsed input vs db for display
            'input_parsed' => $input,
            'db_parsed'    => $db,
        ];
    }

    // Sort: certain first, then probable, then possible
    $order = ['certain' => 0, 'probable' => 1, 'possible' => 2];
    usort($results, fn($a, $b) => $order[$a['confidence']] - $order[$b['confidence']]);

    return $results;
}

// ─────────────────────────────────────────────
// HELPER: Write a khan_training_history record
// Called whenever a member is inserted or promoted
// ─────────────────────────────────────────────
function writeHistoryRecord($conn, $memberId, $khanLevel, $datePromo, $instrId, $location) {
    // Don't duplicate — skip if a record for this member+level already exists
    $check = $conn->prepare("SELECT id FROM khan_training_history WHERE member_id = ? AND khan_level = ? LIMIT 1");
    $check->bind_param("ii", $memberId, $khanLevel);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) { $check->close(); return; }
    $check->close();

    $stmt = $conn->prepare("
        INSERT INTO khan_training_history
            (member_id, khan_level, training_date, certified_date, instructor_id, location, status, notes)
        VALUES (?, ?, ?, ?, ?, ?, 'certified', 'Manually encoded from masterlist')
    ");
    $stmt->bind_param("iissis", $memberId, $khanLevel, $datePromo, $datePromo, $instrId, $location);
    $stmt->execute();
    $stmt->close();
}

// ─────────────────────────────────────────────
// HELPER: Execute a promote/insert for a confirmed member
// ─────────────────────────────────────────────
function doPromote($conn, $existingId, $existingLevel, $khanLevel, $khanColor, $datePromo, $instrId, $location) {
    $stmt = $conn->prepare("
        UPDATE khan_members
        SET current_khan_level = ?,
            khan_color         = ?,
            date_promoted      = ?,
            instructor_id      = COALESCE(?, instructor_id),
            training_location  = COALESCE(NULLIF(?, ''), training_location),
            status             = 'active'
        WHERE id = ?
    ");
    $stmt->bind_param("issssi", $khanLevel, $khanColor, $datePromo, $instrId, $location, $existingId);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        // Write history for the newly promoted level
        writeHistoryRecord($conn, $existingId, $khanLevel, $datePromo, $instrId, $location);
    }
    return $ok;
}

function generateSerialNumber($conn) {
    $res  = $conn->query("SELECT serial_number FROM users WHERE serial_number LIKE 'OMA-%' ORDER BY serial_number DESC LIMIT 1");
    $last = $res ? $res->fetch_assoc() : null;
    $next = 1;
    if ($last && preg_match('/OMA-0*(\d+)/', $last['serial_number'], $m)) {
        $next = (int)$m[1] + 1;
    }
    return 'OMA-' . str_pad($next, 3, '0', STR_PAD_LEFT);
}

function generateUniqueEmail($conn, $fullName) {
    $slug  = preg_replace('/[^a-z0-9]+/', '.', strtolower(trim($fullName)));
    $slug  = trim($slug, '.');
    $base  = $slug . '@oma.local';
    $email = $base;
    $i     = 2;
    while ($conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "' LIMIT 1")->num_rows > 0) {
        $email = $slug . $i . '@oma.local';
        $i++;
    }
    return $email;
}

function createUserForMember($conn, $fullName, $email, $phone, $khanLevel, $status) {
    // Don't create duplicate if email already exists
    $check = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "' LIMIT 1");
    if ($check && $check->num_rows > 0) {
        return (int)$check->fetch_assoc()['id'];
    }
    $serial = generateSerialNumber($conn);
    $parts  = array_values(array_filter(explode(' ', strtolower(trim($fullName)))));
    $first  = $parts[0] ?? 'member';
    $last   = count($parts) > 1 ? $parts[count($parts) - 1] : '';
    $password = password_hash('oma' . $first . $last, PASSWORD_DEFAULT);
    $levelLabel = 'Khan ' . $khanLevel;
    $stmt = $conn->prepare("INSERT INTO users (serial_number, name, email, phone, password, role, status, khan_level) VALUES (?, ?, ?, ?, ?, 'member', ?, ?)");
    $stmt->bind_param("sssssss", $serial, $fullName, $email, $phone, $password, $status, $levelLabel);
    $ok  = $stmt->execute();
    $uid = $conn->insert_id;
    $stmt->close();
    return $ok ? $uid : null;
}

function doInsert($conn, $fullName, $khanLevel, $khanColor, $datePromo, $instrId, $location) {
    $email  = generateUniqueEmail($conn, $fullName);
    $userId = createUserForMember($conn, $fullName, $email, '', $khanLevel, 'active');

    $stmt = $conn->prepare("
        INSERT INTO khan_members
            (user_id, full_name, email, phone, current_khan_level, khan_color,
             date_joined, date_promoted, instructor_id, training_location, status, notes)
        VALUES (?, ?, ?, '', ?, ?, ?, ?, ?, ?, 'active', 'Manually encoded')
    ");
    $stmt->bind_param("issiissss", $userId, $fullName, $email, $khanLevel, $khanColor, $datePromo, $datePromo, $instrId, $location);
    $ok = $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close();

    if ($ok && $id) {
        writeHistoryRecord($conn, $id, $khanLevel, $datePromo, $instrId, $location);
    }
    return $ok ? $id : false;
}

// ─────────────────────────────────────────────
// AJAX: Single encode (batch or search promote)
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_encode'])) {
    header('Content-Type: application/json');

    $fullName  = toTitleCase(sanitize(trim($_POST['full_name'] ?? '')));
    $khanLevel = max(1, min(16, (int)($_POST['khan_level'] ?? 1)));
    $datePromo = !empty($_POST['date_promoted']) ? $_POST['date_promoted'] : date('Y-m-d');
    $instrId   = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
    $location  = sanitize(trim($_POST['location'] ?? ''));
    $forceId   = !empty($_POST['force_member_id']) ? (int)$_POST['force_member_id'] : null;  // user confirmed a match
    $forceNew  = isset($_POST['force_new']) && $_POST['force_new'] === '1';                   // user said "no, add as new"

    if (empty($fullName)) {
        echo json_encode(['status' => 'error', 'message' => 'Name is required.']);
        exit;
    }

    // Get color for this level
    $colorRes  = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $khanLevel LIMIT 1");
    $khanColor = ($colorRes && $cr = $colorRes->fetch_assoc()) ? $cr['color_name'] : '';

    // ── CASE: User explicitly chose a candidate from the ambiguous list ──
    if ($forceId) {
        $stmt = $conn->prepare("SELECT * FROM khan_members WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $forceId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$existing) {
            echo json_encode(['status' => 'error', 'message' => 'Member not found.']);
            exit;
        }

        $existingLevel = (int)$existing['current_khan_level'];
        if ($khanLevel > $existingLevel) {
            doPromote($conn, $existing['id'], $existingLevel, $khanLevel, $khanColor, $datePromo, $instrId, $location);
            echo json_encode([
                'status'  => 'promoted',
                'message' => "✦ {$existing['full_name']} promoted: Khan $existingLevel → Khan $khanLevel",
            ]);
        } elseif ($khanLevel === $existingLevel) {
            echo json_encode([
                'status'  => 'skipped',
                'message' => "{$existing['full_name']} is already at Khan $khanLevel. No changes made.",
            ]);
        } else {
            echo json_encode([
                'status'  => 'higher_exists',
                'message' => "{$existing['full_name']} is already at Khan $existingLevel (higher). No downgrade applied.",
            ]);
        }
        exit;
    }

    // ── CASE: User said "No, add as brand new person" ──
    if ($forceNew) {
        $newId = doInsert($conn, $fullName, $khanLevel, $khanColor, $datePromo, $instrId, $location);
        echo json_encode([
            'status'  => 'inserted',
            'message' => "✔ {$fullName} added as new member at Khan $khanLevel.",
        ]);
        exit;
    }

    // ── AUTO-MATCH: Run the smart matching engine ──
    $matches = findNameMatches($conn, $fullName);

    if (empty($matches)) {
        // No candidates at all → safe to insert
        $newId = doInsert($conn, $fullName, $khanLevel, $khanColor, $datePromo, $instrId, $location);
        echo json_encode([
            'status'  => 'inserted',
            'message' => "✔ {$fullName} added at Khan $khanLevel.",
        ]);
        exit;
    }

    $topMatch  = $matches[0];
    $topMember = $topMatch['member'];

    if ($topMatch['confidence'] === 'certain' && count($matches) === 1) {
        // One clear match → auto-process, no question asked
        $existingLevel = (int)$topMember['current_khan_level'];
        if ($khanLevel > $existingLevel) {
            doPromote($conn, $topMember['id'], $existingLevel, $khanLevel, $khanColor, $datePromo, $instrId, $location);
            $note = !empty($topMatch['note']) ? " ({$topMatch['note']})" : '';
            echo json_encode([
                'status'  => 'promoted',
                'message' => "✦ {$topMember['full_name']} promoted: Khan $existingLevel → Khan $khanLevel{$note}",
            ]);
        } elseif ($khanLevel === $existingLevel) {
            echo json_encode([
                'status'  => 'skipped',
                'message' => "{$topMember['full_name']} is already at Khan $khanLevel. No changes made.",
            ]);
        } else {
            echo json_encode([
                'status'  => 'higher_exists',
                'message' => "{$topMember['full_name']} is already at Khan $existingLevel (higher). No downgrade.",
            ]);
        }
        exit;
    }

    // Multiple certain matches OR any probable/possible → ask user to decide
    echo json_encode([
        'status'    => 'ambiguous',
        'message'   => "Found " . count($matches) . " possible match(es) for \"{$fullName}\". Please confirm which one is correct.",
        'input_name'=> $fullName,
        'candidates'=> array_map(fn($m) => [
            'id'            => $m['member']['id'],
            'full_name'     => $m['member']['full_name'],
            'current_khan_level' => $m['member']['current_khan_level'],
            'khan_color'    => $m['member']['khan_color'],
            'training_location' => $m['member']['training_location'],
            'instructor_name'   => $m['member']['instructor_name'],
            'status'        => $m['member']['status'],
            'confidence'    => $m['confidence'],
            'note'          => $m['note'],
        ], $matches),
    ]);
    exit;
}

// ─────────────────────────────────────────────
// Page data
// ─────────────────────────────────────────────
$instructors = $conn->query("SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name");
$khanColors  = [];
$cr = $conn->query("SELECT khan_level, color_name, hex_color FROM khan_colors ORDER BY khan_level");
while ($r = $cr->fetch_assoc()) $khanColors[$r['khan_level']] = $r;

include 'includes/admin_header.php';
?>

<style>
/* ── Page Layout ── */
.encode-page { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start; }
@media (max-width: 900px) { .encode-page { grid-template-columns: 1fr; } }

/* ── Panels ── */
.encode-panel {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
}
.panel-header {
    padding: 1rem 1.25rem;
    border-bottom: 2px solid #f0f0f0;
    display: flex; align-items: center; gap: 0.6rem;
}
.panel-header h3 { margin: 0; font-size: 1rem; font-weight: 700; }
.panel-header .mode-tag {
    font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
    padding: 2px 8px; border-radius: 20px; color: white;
}
.panel-body { padding: 1.25rem; }

/* ── Level Selector ── */
.level-grid {
    display: grid; grid-template-columns: repeat(8, 1fr); gap: 6px; margin-bottom: 1.25rem;
}
.level-btn {
    aspect-ratio: 1; border-radius: 6px; border: 2px solid #e0e0e0;
    background: #f8f8f8; font-weight: 700; font-size: 0.8rem;
    cursor: pointer; transition: all 0.15s; line-height: 1;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;
}
.level-btn:hover { transform: translateY(-2px); box-shadow: 0 3px 8px rgba(0,0,0,0.15); }
.level-btn.active { border-color: currentColor; box-shadow: 0 0 0 3px rgba(0,0,0,0.1); transform: scale(1.05); }
.level-btn .dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,0.15); }
.level-btn .num { font-size: 0.75rem; color: #555; }

/* ── Name Input (Batch) ── */
.name-input-wrap { position: relative; }
.name-input-big {
    width: 100%; padding: 0.9rem 1rem; font-size: 1.1rem;
    border: 2px solid #e0e0e0; border-radius: 8px;
    font-family: inherit; transition: border-color 0.2s;
    box-sizing: border-box;
}
.name-input-big:focus { outline: none; border-color: #1976d2; }
.encode-submit-btn {
    width: 100%; margin-top: 0.75rem; padding: 0.85rem;
    background: #1976d2; color: white; border: none; border-radius: 8px;
    font-size: 1rem; font-weight: 700; cursor: pointer; transition: background 0.2s;
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
}
.encode-submit-btn:hover { background: #1565c0; }
.encode-submit-btn:active { background: #0d47a1; }

/* ── Optional fields (collapsible) ── */
.optional-toggle {
    color: #888; font-size: 0.85rem; cursor: pointer; margin: 0.75rem 0 0;
    display: inline-flex; align-items: center; gap: 4px; user-select: none;
}
.optional-toggle:hover { color: #555; }
.optional-fields { display: none; margin-top: 0.75rem; }
.optional-fields.open { display: block; }
.opt-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem; }
.opt-label { display: block; font-size: 0.8rem; font-weight: 600; color: #555; margin-bottom: 3px; }
.opt-input, .opt-select {
    width: 100%; padding: 0.5rem 0.7rem; font-size: 0.9rem;
    border: 1px solid #ddd; border-radius: 6px; font-family: inherit;
    box-sizing: border-box;
}

/* ── Activity Log ── */
.activity-log {
    max-height: 340px; overflow-y: auto;
    border: 1px solid #eee; border-radius: 8px;
    background: #fafafa;
}
.log-item {
    padding: 0.7rem 1rem; border-bottom: 1px solid #eee;
    display: flex; align-items: flex-start; gap: 0.6rem;
    font-size: 0.88rem; animation: fadeIn 0.25s ease;
}
.log-item:last-child { border-bottom: none; }
@keyframes fadeIn { from { opacity:0; transform: translateY(-4px); } to { opacity:1; transform: none; } }
.log-icon { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }
.log-msg { flex: 1; line-height: 1.4; }
.log-time { font-size: 0.75rem; color: #aaa; flex-shrink: 0; }
.log-empty { padding: 2rem; text-align: center; color: #bbb; font-size: 0.9rem; }

.status-inserted    { color: #2e7d32; }
.status-promoted    { color: #1565c0; }
.status-skipped     { color: #757575; }
.status-higher_exists { color: #e65100; }
.status-error       { color: #c62828; }

/* ── Search Mode ── */
.search-input-wrap { position: relative; }
.search-input {
    width: 100%; padding: 0.85rem 1rem 0.85rem 2.8rem;
    font-size: 1rem; border: 2px solid #e0e0e0; border-radius: 8px;
    font-family: inherit; transition: border-color 0.2s; box-sizing: border-box;
}
.search-input:focus { outline: none; border-color: #1976d2; }
.search-icon { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: #aaa; pointer-events: none; }

.search-results { margin-top: 0.75rem; }
.search-result-item {
    border: 1px solid #e8e8e8; border-radius: 8px; padding: 0.75rem 1rem;
    margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;
    background: #fff; transition: box-shadow 0.15s;
}
.search-result-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.result-level-badge {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.85rem; flex-shrink: 0;
    border: 2px solid rgba(0,0,0,0.1);
}
.result-info { flex: 1; min-width: 0; }
.result-name { font-weight: 600; font-size: 0.95rem; }
.result-meta { font-size: 0.8rem; color: #888; }
.result-actions { display: flex; gap: 0.4rem; flex-shrink: 0; }
.btn-promote {
    padding: 0.35rem 0.85rem; background: #1565c0; color: white;
    border: none; border-radius: 5px; font-size: 0.82rem; font-weight: 600;
    cursor: pointer; transition: background 0.15s;
}
.btn-promote:hover { background: #0d47a1; }
.btn-promote:disabled { background: #aaa; cursor: not-allowed; }
.btn-promote.same-level { background: #757575; }
.btn-promote.lower-level { background: #e65100; }

.new-member-card {
    border: 2px dashed #1976d2; border-radius: 8px; padding: 1rem;
    background: #e3f2fd; text-align: center; margin-bottom: 0.5rem;
}
.new-member-card button {
    margin-top: 0.5rem; padding: 0.5rem 1.5rem;
    background: #1976d2; color: white; border: none; border-radius: 5px;
    font-size: 0.9rem; font-weight: 700; cursor: pointer;
}

/* ── Ambiguous Modal ── */
#ambModal {
    display: none; position: fixed; inset: 0; z-index: 2000;
    background: rgba(0,0,0,0.55); backdrop-filter: blur(3px);
    align-items: center; justify-content: center; padding: 1rem;
}
.amb-modal-box {
    background: #fff; border-radius: 12px; width: 100%; max-width: 680px;
    max-height: 88vh; overflow-y: auto;
    box-shadow: 0 8px 40px rgba(0,0,0,0.25);
    animation: slideDown 0.2s ease;
}
.amb-modal-header {
    padding: 1.25rem 1.5rem; border-bottom: 2px solid #f0f0f0;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; background: #fff; z-index: 1;
}
.amb-modal-header h3 { margin: 0; font-size: 1.05rem; }
.amb-modal-close { background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #aaa; line-height: 1; }
.amb-modal-close:hover { color: #333; }
.amb-modal-body { padding: 1.25rem 1.5rem; }
.amb-cards { display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1rem; }
.amb-card {
    border: 2px solid #e0e0e0; border-radius: 8px; padding: 1rem;
    transition: border-color 0.15s;
}
.amb-card:hover { border-color: #1976d2; }
.amb-card-header { display: flex; align-items: flex-start; gap: 0.75rem; }
.amb-confidence {
    font-size: 0.75rem; font-weight: 700; white-space: nowrap;
    padding: 3px 8px; border-radius: 10px; background: #f5f5f5;
}
.amb-note {
    font-size: 0.8rem; color: #1565c0; background: #e3f2fd;
    padding: 0.4rem 0.75rem; border-radius: 5px; margin-top: 0.5rem;
}
.amb-btn {
    padding: 0.5rem 1rem; border: none; border-radius: 6px;
    font-size: 0.88rem; font-weight: 700; cursor: pointer; transition: all 0.15s;
}
.amb-btn-confirm { background: #1976d2; color: white; }
.amb-btn-confirm:hover { background: #1565c0; }
.amb-btn-disabled { background: #e0e0e0; color: #999; cursor: not-allowed; }
.amb-btn-new { background: #f0f0f0; color: #333; margin-top: 0.5rem; display: block; width: 100%; }
.amb-btn-new:hover { background: #e0e0e0; }
.amb-new-option {
    border-top: 2px dashed #eee; padding-top: 1rem; margin-top: 0.5rem;
    font-size: 0.88rem; color: #555;
}
.status-ambiguous { color: #7b1fa2; }
.search-level-row {
    display: flex; align-items: center; gap: 0.75rem;
    margin-bottom: 1rem; flex-wrap: wrap;
}
.level-display {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.4rem 0.85rem; border-radius: 20px;
    font-weight: 700; font-size: 0.9rem; border: 2px solid rgba(0,0,0,0.1);
}
.level-dot { width: 14px; height: 14px; border-radius: 50%; border: 1px solid rgba(0,0,0,0.2); }

.empty-search { text-align: center; padding: 2rem 1rem; color: #bbb; }
</style>

<div class="admin-section">
    <div class="section-header" style="margin-bottom:1.25rem;">
        <h2><i class="fas fa-keyboard"></i> Manual Encode Members</h2>
        <a href="khan_members.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Members</a>
    </div>

    <div class="encode-page">

        <!-- ════════════════════════════════════════
             LEFT: BATCH MODE
        ════════════════════════════════════════ -->
        <div>
            <div class="encode-panel">
                <div class="panel-header">
                    <i class="fas fa-layer-group" style="color:#1976d2"></i>
                    <h3>Batch Encode by Level</h3>
                    <span class="mode-tag" style="background:#1976d2">Batch</span>
                </div>
                <div class="panel-body">

                    <p style="font-size:0.85rem; color:#777; margin:0 0 0.85rem;">
                        Select a Khan level, then type names one by one. Press <kbd>Enter</kbd> or click Encode.
                    </p>

                    <!-- Level Picker -->
                    <div class="level-grid" id="batchLevelGrid">
                        <?php for ($i = 1; $i <= 16; $i++):
                            $hex  = $khanColors[$i]['hex_color']  ?? '#ccc';
                            $name = $khanColors[$i]['color_name'] ?? '';
                        ?>
                        <button type="button"
                            class="level-btn <?php echo $i === 1 ? 'active' : ''; ?>"
                            data-level="<?php echo $i; ?>"
                            data-hex="<?php echo $hex; ?>"
                            data-name="<?php echo htmlspecialchars($name); ?>"
                            title="Khan <?php echo $i; ?> — <?php echo htmlspecialchars($name); ?>"
                            style="<?php echo $i === 1 ? "border-color:$hex; color:$hex;" : ''; ?>"
                            onclick="selectBatchLevel(<?php echo $i; ?>, '<?php echo $hex; ?>', '<?php echo addslashes($name); ?>')">
                            <span class="dot" style="background:<?php echo $hex; ?>"></span>
                            <span class="num"><?php echo $i; ?></span>
                        </button>
                        <?php endfor; ?>
                    </div>

                    <!-- Selected level label -->
                    <div style="margin-bottom:0.85rem; font-size:0.88rem; color:#555;">
                        Encoding as:
                        <span id="batchLevelLabel" style="font-weight:700; color:#1976d2">Khan 1</span>
                        <span id="batchColorLabel" style="color:#888; margin-left:4px;">
                            — <?php echo htmlspecialchars($khanColors[1]['color_name'] ?? ''); ?>
                        </span>
                    </div>
                    <input type="hidden" id="batchLevel" value="1">

                    <!-- Name input -->
                    <div class="name-input-wrap">
                        <input type="text" id="batchName" class="name-input-big"
                               placeholder="Type full name…" autocomplete="off"
                               onkeydown="if(event.key==='Enter'){event.preventDefault();submitBatch();}">
                    </div>

                    <!-- Optional fields -->
                    <span class="optional-toggle" onclick="toggleOptional('batch')">
                        <i class="fas fa-chevron-right" id="batchOptIcon" style="transition:transform 0.2s; font-size:0.7rem;"></i>
                        Optional fields (date, instructor, location)
                    </span>
                    <div class="optional-fields" id="batchOptional">
                        <div class="opt-row">
                            <div>
                                <label class="opt-label">Date Promoted</label>
                                <input type="date" id="batchDate" class="opt-input" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div>
                                <label class="opt-label">Location</label>
                                <input type="text" id="batchLocation" class="opt-input" placeholder="e.g. Manila">
                            </div>
                        </div>
                        <div>
                            <label class="opt-label">Instructor</label>
                            <select id="batchInstructor" class="opt-select">
                                <option value="">— None —</option>
                                <?php $instructors->data_seek(0); while ($ins = $instructors->fetch_assoc()): ?>
                                <option value="<?php echo $ins['id']; ?>"><?php echo htmlspecialchars($ins['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <button class="encode-submit-btn" onclick="submitBatch()" style="margin-top:1rem;">
                        <i class="fas fa-plus-circle"></i> Encode Member
                    </button>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="encode-panel" style="margin-top:1.25rem;">
                <div class="panel-header">
                    <i class="fas fa-history" style="color:#555"></i>
                    <h3>Activity Log</h3>
                    <button onclick="clearLog()" style="margin-left:auto; background:none; border:none; color:#bbb; cursor:pointer; font-size:0.8rem;">Clear</button>
                </div>
                <div class="panel-body" style="padding:0;">
                    <div class="activity-log" id="activityLog">
                        <div class="log-empty" id="logEmpty">No entries yet. Start encoding above.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════
             RIGHT: SEARCH MODE
        ════════════════════════════════════════ -->
        <div>
            <div class="encode-panel">
                <div class="panel-header">
                    <i class="fas fa-search" style="color:#7b1fa2"></i>
                    <h3>Search &amp; Add / Promote</h3>
                    <span class="mode-tag" style="background:#7b1fa2">Search</span>
                </div>
                <div class="panel-body">

                    <p style="font-size:0.85rem; color:#777; margin:0 0 0.85rem;">
                        Look up a name first. You'll see if they exist and at what level, then act accordingly.
                    </p>

                    <!-- Level selector for search mode -->
                    <div class="search-level-row">
                        <span style="font-size:0.85rem; color:#555; font-weight:600;">Target level:</span>
                        <div class="level-display" id="searchLevelDisplay"
                             style="background:<?php echo $khanColors[1]['hex_color'] ?? '#ccc'; ?>22;">
                            <span class="level-dot" id="searchLevelDot"
                                  style="background:<?php echo $khanColors[1]['hex_color'] ?? '#ccc'; ?>"></span>
                            <span id="searchLevelText">Khan 1</span>
                        </div>
                        <select id="searchLevelSelect" class="opt-select" style="flex:1; max-width:160px;"
                                onchange="updateSearchLevel()">
                            <?php for ($i = 1; $i <= 16; $i++):
                                $name = $khanColors[$i]['color_name'] ?? '';
                            ?>
                            <option value="<?php echo $i; ?>" data-hex="<?php echo $khanColors[$i]['hex_color'] ?? '#ccc'; ?>">
                                Khan <?php echo $i; ?> — <?php echo htmlspecialchars($name); ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Search input -->
                    <div class="search-input-wrap">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input"
                               placeholder="Type a name to search…" autocomplete="off">
                    </div>

                    <!-- Optional fields for search mode -->
                    <span class="optional-toggle" onclick="toggleOptional('search')" style="margin-top:0.6rem;">
                        <i class="fas fa-chevron-right" id="searchOptIcon" style="transition:transform 0.2s; font-size:0.7rem;"></i>
                        Optional: date, instructor, location
                    </span>
                    <div class="optional-fields" id="searchOptional">
                        <div class="opt-row" style="margin-top:0.6rem;">
                            <div>
                                <label class="opt-label">Date Promoted</label>
                                <input type="date" id="searchDate" class="opt-input" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div>
                                <label class="opt-label">Location</label>
                                <input type="text" id="searchLocation" class="opt-input" placeholder="e.g. Manila">
                            </div>
                        </div>
                        <div>
                            <label class="opt-label">Instructor</label>
                            <select id="searchInstructor" class="opt-select">
                                <option value="">— None —</option>
                                <?php $instructors->data_seek(0); while ($ins = $instructors->fetch_assoc()): ?>
                                <option value="<?php echo $ins['id']; ?>"><?php echo htmlspecialchars($ins['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Results -->
                    <div class="search-results" id="searchResults">
                        <div class="empty-search">
                            <i class="fas fa-user-search" style="font-size:2rem; margin-bottom:0.5rem; display:block;"></i>
                            Start typing to search existing members.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- end .encode-page -->
</div>

<!-- ════════════════════════════════════════
     AMBIGUOUS MATCH MODAL
════════════════════════════════════════ -->
<div id="ambModal">
    <div class="amb-modal-box">
        <div class="amb-modal-header">
            <h3 id="ambModalTitle">Who is this person?</h3>
            <button class="amb-modal-close" onclick="closeAmbiguousModal()">×</button>
        </div>
        <div class="amb-modal-body" id="ambModalBody"></div>
    </div>
</div>

<script>
// ── Khan color map (PHP → JS) ──
const khanColors = <?php
    $map = [];
    foreach ($khanColors as $level => $c) {
        $map[$level] = ['name' => $c['color_name'], 'hex' => $c['hex_color']];
    }
    echo json_encode($map);
?>;

// ══════════════════════════════════════
// BATCH MODE
// ══════════════════════════════════════
function selectBatchLevel(level, hex, name) {
    document.querySelectorAll('#batchLevelGrid .level-btn').forEach(btn => {
        const isActive = parseInt(btn.dataset.level) === level;
        btn.classList.toggle('active', isActive);
        btn.style.borderColor = isActive ? hex : '';
        btn.style.color       = isActive ? hex : '';
    });
    document.getElementById('batchLevel').value         = level;
    document.getElementById('batchLevelLabel').textContent = 'Khan ' + level;
    document.getElementById('batchLevelLabel').style.color  = hex;
    document.getElementById('batchColorLabel').textContent  = '— ' + name;
    document.getElementById('batchName').focus();
}

function toggleOptional(mode) {
    const el   = document.getElementById(mode + 'Optional');
    const icon = document.getElementById(mode + 'OptIcon');
    const open = el.classList.toggle('open');
    icon.style.transform = open ? 'rotate(90deg)' : 'rotate(0deg)';
}

async function submitBatch() {
    const name = document.getElementById('batchName').value.trim();
    if (!name) { document.getElementById('batchName').focus(); return; }

    const btn = document.querySelector('.encode-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Encoding…';

    const body = new FormData();
    body.append('ajax_encode', '1');
    body.append('full_name',    name);
    body.append('khan_level',   document.getElementById('batchLevel').value);
    body.append('date_promoted', document.getElementById('batchDate').value);
    body.append('instructor_id', document.getElementById('batchInstructor').value);
    body.append('location',      document.getElementById('batchLocation').value);

    try {
        const res  = await fetch(window.location.href, { method: 'POST', body });
        const data = await res.json();

        if (data.status === 'ambiguous') {
            // Pause and show the disambiguation modal — don't clear the name yet
            addLog('ambiguous', `⚡ "${name}" needs your input — ${data.candidates.length} possible match(es) found.`);
            showAmbiguousModal(data, body);
        } else {
            addLog(data.status, data.message);
            if (data.status !== 'error') {
                document.getElementById('batchName').value = '';
            }
        }
    } catch(e) {
        addLog('error', 'Network error. Please try again.');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-plus-circle"></i> Encode Member';
    document.getElementById('batchName').focus();
}

// ══════════════════════════════════════
// AMBIGUOUS MATCH MODAL
// Shows candidates side-by-side for the user to pick
// ══════════════════════════════════════
function showAmbiguousModal(data, originalFormData) {
    const khanLevelTarget = originalFormData.get('khan_level');

    const confidenceLabel = { certain: '🟢 Strong match', probable: '🟡 Likely match', possible: '🔵 Possible match' };
    const confidenceDesc  = {
        certain:  'First and last name match. Middle name was missing from one side.',
        probable: 'First and last name are close but not identical (possible typo or nickname).',
        possible: 'First name matches; last name is similar but different.',
    };

    let cardsHtml = data.candidates.map(c => {
        const hex = (khanColors[c.current_khan_level] && khanColors[c.current_khan_level].hex) || '#ccc';
        const isHigher = c.current_khan_level >= parseInt(khanLevelTarget);
        const actionBtn = isHigher
            ? `<button class="amb-btn amb-btn-disabled" disabled title="Already at Khan ${c.current_khan_level}">
                   Already Khan ${c.current_khan_level}
               </button>`
            : `<button class="amb-btn amb-btn-confirm"
                   onclick="confirmAmbiguous(${c.id})">
                   ✔ This is them — Promote to Khan ${khanLevelTarget}
               </button>`;

        return `
        <div class="amb-card">
            <div class="amb-card-header">
                <div class="result-level-badge" style="background:${hex}22; color:${hex}; border-color:${hex}55; width:36px; height:36px; font-size:0.9rem;">
                    ${c.current_khan_level}
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700; font-size:1rem;">${escHtml(c.full_name)}</div>
                    <div style="font-size:0.8rem; color:#888;">
                        Khan ${c.current_khan_level} · ${escHtml(c.khan_color || '')}
                        ${c.training_location ? ' · ' + escHtml(c.training_location) : ''}
                        ${c.instructor_name ? ' · ' + escHtml(c.instructor_name) : ''}
                    </div>
                </div>
                <span class="amb-confidence" title="${confidenceDesc[c.confidence]}">${confidenceLabel[c.confidence]}</span>
            </div>
            ${c.note ? `<div class="amb-note"><i class="fas fa-info-circle"></i> ${escHtml(c.note)}</div>` : ''}
            <div style="margin-top:0.75rem;">${actionBtn}</div>
        </div>`;
    }).join('');

    document.getElementById('ambModalTitle').textContent = `Who is "${data.input_name}"?`;
    document.getElementById('ambModalBody').innerHTML = `
        <p style="color:#555; margin:0 0 1rem; font-size:0.9rem;">
            The name <strong>${escHtml(data.input_name)}</strong> (targeting <strong>Khan ${khanLevelTarget}</strong>)
            partially matches existing records. Pick the correct one below, or add as a brand new person.
        </p>
        <div class="amb-cards">${cardsHtml}</div>
        <div class="amb-new-option">
            <strong>None of these?</strong> — This is a different person with a similar name.
            <button class="amb-btn amb-btn-new" onclick="confirmAmbiguousNew()">
                <i class="fas fa-user-plus"></i> Add "${escHtml(data.input_name)}" as a New Member
            </button>
        </div>
    `;

    // Store form data on modal for use by confirm buttons
    document.getElementById('ambModal')._formData = originalFormData;
    document.getElementById('ambModal').style.display = 'flex';
}

async function confirmAmbiguous(memberId) {
    const fd = document.getElementById('ambModal')._formData;
    const body = new FormData();
    body.append('ajax_encode',    '1');
    body.append('full_name',       fd.get('full_name'));
    body.append('khan_level',      fd.get('khan_level'));
    body.append('date_promoted',   fd.get('date_promoted'));
    body.append('instructor_id',   fd.get('instructor_id'));
    body.append('location',        fd.get('location'));
    body.append('force_member_id', memberId);

    closeAmbiguousModal();
    const res  = await fetch(window.location.href, { method: 'POST', body });
    const data = await res.json();
    addLog(data.status, data.message);
    document.getElementById('batchName').value = '';
    document.getElementById('batchName').focus();
}

async function confirmAmbiguousNew() {
    const fd = document.getElementById('ambModal')._formData;
    const body = new FormData();
    body.append('ajax_encode',   '1');
    body.append('full_name',     fd.get('full_name'));
    body.append('khan_level',    fd.get('khan_level'));
    body.append('date_promoted', fd.get('date_promoted'));
    body.append('instructor_id', fd.get('instructor_id'));
    body.append('location',      fd.get('location'));
    body.append('force_new',     '1');

    closeAmbiguousModal();
    const res  = await fetch(window.location.href, { method: 'POST', body });
    const data = await res.json();
    addLog(data.status, data.message);
    document.getElementById('batchName').value = '';
    document.getElementById('batchName').focus();
}

function closeAmbiguousModal() {
    document.getElementById('ambModal').style.display = 'none';
}

// Close on backdrop click
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('ambModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeAmbiguousModal();
    });
});

// ══════════════════════════════════════
// ACTIVITY LOG
// ══════════════════════════════════════
const icons = {
    inserted:     { icon: '✔', cls: 'status-inserted' },
    promoted:     { icon: '▲', cls: 'status-promoted' },
    skipped:      { icon: '–', cls: 'status-skipped' },
    higher_exists:{ icon: '⚠', cls: 'status-higher_exists' },
    ambiguous:    { icon: '?', cls: 'status-ambiguous' },
    error:        { icon: '✖', cls: 'status-error' },
};

function addLog(status, message) {
    const log   = document.getElementById('activityLog');
    const empty = document.getElementById('logEmpty');
    if (empty) empty.remove();

    const now  = new Date().toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    const meta = icons[status] || { icon: '?', cls: '' };

    const item = document.createElement('div');
    item.className = 'log-item';
    item.innerHTML = `
        <span class="log-icon ${meta.cls}">${meta.icon}</span>
        <span class="log-msg ${meta.cls}">${message}</span>
        <span class="log-time">${now}</span>
    `;
    log.insertBefore(item, log.firstChild);
}

function clearLog() {
    const log = document.getElementById('activityLog');
    log.innerHTML = '<div class="log-empty" id="logEmpty">No entries yet. Start encoding above.</div>';
}

// ══════════════════════════════════════
// SEARCH MODE
// ══════════════════════════════════════
let searchTimeout;

function updateSearchLevel() {
    const sel  = document.getElementById('searchLevelSelect');
    const opt  = sel.options[sel.selectedIndex];
    const hex  = opt.dataset.hex || '#ccc';
    const text = opt.text;
    document.getElementById('searchLevelDot').style.background  = hex;
    document.getElementById('searchLevelDisplay').style.background = hex + '22';
    document.getElementById('searchLevelText').textContent = 'Khan ' + sel.value;
    // Re-render existing results with new level context
    const current = document.getElementById('searchInput').value;
    if (current.length >= 2) liveSearch(current);
}

function liveSearch(q) {
    clearTimeout(searchTimeout);
    const resultsEl = document.getElementById('searchResults');

    if (q.length < 2) {
        resultsEl.innerHTML = `<div class="empty-search">
            <i class="fas fa-user-search" style="font-size:2rem; margin-bottom:0.5rem; display:block;"></i>
            Start typing to search existing members.
        </div>`;
        return;
    }

    searchTimeout = setTimeout(async () => {
        resultsEl.innerHTML = '<div class="log-empty"><i class="fas fa-spinner fa-spin"></i> Searching…</div>';
        const res  = await fetch(`?ajax=search&q=${encodeURIComponent(q)}`);
        const data = await res.json();
        renderSearchResults(data, q);
    }, 300);
}

function renderSearchResults(members, q) {
    const resultsEl   = document.getElementById('searchResults');
    const targetLevel = parseInt(document.getElementById('searchLevelSelect').value);
    let html = '';

    if (members.length > 0) {
        members.forEach(m => {
            const mLevel = parseInt(m.current_khan_level);
            const hex    = (khanColors[mLevel] && khanColors[mLevel].hex) || '#ccc';
            const colorName = (khanColors[mLevel] && khanColors[mLevel].name) || '';

            let btnLabel = 'Promote to Khan ' + targetLevel;
            let btnClass = 'btn-promote';
            let btnDisabled = '';

            if (mLevel === targetLevel) {
                btnLabel   = 'Already Khan ' + targetLevel;
                btnClass  += ' same-level';
                btnDisabled = 'disabled';
            } else if (mLevel > targetLevel) {
                btnLabel   = 'Already Khan ' + mLevel + ' (higher)';
                btnClass  += ' lower-level';
                btnDisabled = 'disabled';
            }

            html += `
            <div class="search-result-item">
                <div class="result-level-badge" style="background:${hex}22; color:${hex}; border-color:${hex}55;">
                    ${mLevel}
                </div>
                <div class="result-info">
                    <div class="result-name">${escHtml(m.full_name)}</div>
                    <div class="result-meta">
                        Khan ${mLevel} — ${escHtml(colorName)}
                        ${m.instructor_name ? ' · ' + escHtml(m.instructor_name) : ''}
                        · <span style="text-transform:capitalize;">${m.status}</span>
                    </div>
                </div>
                <div class="result-actions">
                    <button class="${btnClass}" ${btnDisabled}
                        onclick="searchEncode(${m.id}, '${escHtml(m.full_name).replace(/'/g,"\\'")}', ${mLevel})">
                        ${btnLabel}
                    </button>
                </div>
            </div>`;
        });
    }

    // Always show "Add as new" option
    html += `
    <div class="new-member-card">
        <div style="font-weight:600; color:#1565c0;">
            <i class="fas fa-user-plus"></i> Add "<strong>${escHtml(q)}</strong>" as a NEW member at Khan ${targetLevel}
        </div>
        <div style="font-size:0.82rem; color:#555; margin-top:3px;">
            ${members.length > 0 ? 'None of the matches above?' : 'No existing match found.'} Create a fresh record.
        </div>
        <button onclick="searchEncodeNew('${q.replace(/'/g,"\\'")}')">Add New Member</button>
    </div>`;

    resultsEl.innerHTML = html;
}

async function searchEncode(memberId, memberName, currentLevel) {
    const targetLevel = parseInt(document.getElementById('searchLevelSelect').value);
    if (currentLevel >= targetLevel) return;

    const body = new FormData();
    body.append('ajax_encode',    '1');
    body.append('full_name',      memberName);
    body.append('khan_level',     targetLevel);
    body.append('force_member_id', memberId);
    body.append('date_promoted',  document.getElementById('searchDate').value);
    body.append('instructor_id',  document.getElementById('searchInstructor').value);
    body.append('location',       document.getElementById('searchLocation').value);

    const res  = await fetch(window.location.href, { method: 'POST', body });
    const data = await res.json();
    addLog(data.status, data.message);

    // Refresh search results
    liveSearch(document.getElementById('searchInput').value);
}

async function searchEncodeNew(name) {
    const targetLevel = parseInt(document.getElementById('searchLevelSelect').value);

    const body = new FormData();
    body.append('ajax_encode',   '1');
    body.append('full_name',     name);
    body.append('khan_level',    targetLevel);
    body.append('date_promoted', document.getElementById('searchDate').value);
    body.append('instructor_id', document.getElementById('searchInstructor').value);
    body.append('location',      document.getElementById('searchLocation').value);

    const res  = await fetch(window.location.href, { method: 'POST', body });
    const data = await res.json();
    addLog(data.status, data.message);

    // Clear search and refresh
    document.getElementById('searchInput').value = '';
    document.getElementById('searchResults').innerHTML = `<div class="empty-search">
        <i class="fas fa-user-search" style="font-size:2rem; margin-bottom:0.5rem; display:block;"></i>
        Start typing to search existing members.
    </div>`;
}

// ══════════════════════════════════════
// NAME NORMALIZATION (real-time visual)
// Converts ALL CAPS or all lowercase to Title Case as the user types
// ══════════════════════════════════════
const particles = new Set(['de','la','las','los','del','ng','ni','van','von','bin','binti','al','el']);

function toTitleCase(str) {
    return str
        .toLowerCase()
        .split(' ')
        .map((word, i) => {
            if (!word) return word;
            // Always capitalize first word; keep known particles lowercase mid-name
            if (i > 0 && particles.has(word)) return word;
            return word.charAt(0).toUpperCase() + word.slice(1);
        })
        .join(' ');
}

function applyTitleCase(input) {
    const pos = input.selectionStart; // preserve cursor position
    input.value = toTitleCase(input.value);
    input.setSelectionRange(pos, pos);
}

// Attach to both name inputs on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const batchName  = document.getElementById('batchName');
    const searchInp  = document.getElementById('searchInput');

    batchName.addEventListener('input', () => applyTitleCase(batchName));
    searchInp.addEventListener('input', () => {
        applyTitleCase(searchInp);
        liveSearch(searchInp.value);
    });

    // Remove the duplicate oninput on searchInput (we handle it here)
    searchInp.removeAttribute('oninput');
});

function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
}
</script>

<?php include 'includes/admin_footer.php'; ?>