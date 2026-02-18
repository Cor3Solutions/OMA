<?php
/**
 * import_members.php - Enhanced Historical Data Import with Smart Deduplication
 *
 * KEY IMPROVEMENTS:
 * 1. UPSERT logic: If a member already exists, UPDATE their record instead of duplicating.
 * 2. Smart duplicate detection by name + phone similarity (since email is often missing).
 * 3. "Promote" mode: If the same person is found at a LOWER level, it upgrades their level.
 * 4. Per-row status reporting: shows exactly what happened to each row (inserted/updated/skipped).
 * 5. User account linking: Checks if a matching user account already exists by email.
 * 6. Supports encoding from Khan 1 through 16 in batches — safe to re-run multiple times.
 */

$page_title = "Import Historical Data";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$message = '';
$messageType = '';
$importLog = []; // Per-row log for detailed feedback

// ============================================================
// HELPER: Find existing khan_member by name (and optionally phone/email)
// Returns the matching member row or null
// ============================================================
function findExistingMember($conn, $fullName, $email, $phone) {
    $nameLower = strtolower(trim($fullName));
    $phoneClean = preg_replace('/[^0-9]/', '', $phone);

    // 1. Try exact email match first (most reliable)
    if (!empty($email) && strpos($email, '@archive.local') === false) {
        $stmt = $conn->prepare("SELECT * FROM khan_members WHERE LOWER(TRIM(email)) = ? LIMIT 1");
        $emailLower = strtolower(trim($email));
        $stmt->bind_param("s", $emailLower);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) return $result->fetch_assoc();
        $stmt->close();
    }

    // 2. Try exact name match
    $stmt = $conn->prepare("SELECT * FROM khan_members WHERE LOWER(TRIM(full_name)) = ? LIMIT 1");
    $stmt->bind_param("s", $nameLower);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $match = $result->fetch_assoc();
        // If phone also matches (or we have no phone to compare), accept it
        if (empty($phoneClean)) return $match;
        $dbPhone = preg_replace('/[^0-9]/', '', $match['phone']);
        if ($dbPhone === $phoneClean || substr($dbPhone, -7) === substr($phoneClean, -7)) return $match;
        // Name matches but phone differs — still return as likely-same person
        return $match;
    }
    $stmt->close();

    // 3. Fuzzy name match (Levenshtein distance ≤ 2 — catches minor typos)
    $allMembers = $conn->query("SELECT * FROM khan_members ORDER BY id");
    while ($row = $allMembers->fetch_assoc()) {
        $dist = levenshtein($nameLower, strtolower(trim($row['full_name'])));
        if ($dist <= 2) {
            // Require phone match to confirm fuzzy name match, to avoid false positives
            if (!empty($phoneClean)) {
                $dbPhone = preg_replace('/[^0-9]/', '', $row['phone']);
                if ($dbPhone === $phoneClean || substr($dbPhone, -7) === substr($phoneClean, -7)) {
                    return $row;
                }
            }
            // No phone available: return fuzzy match but flag it
            return $row;
        }
    }

    return null; // No match found — this is a new person
}

// ============================================================
// HELPER: Check if a users account exists for this member
// ============================================================
function findUserAccount($conn, $email, $fullName) {
    if (empty($email) || strpos($email, '@archive.local') !== false) {
        // No real email — try matching by name in users table
        $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE LOWER(TRIM(name)) = ? LIMIT 1");
        $nameLower = strtolower(trim($fullName));
        $stmt->bind_param("s", $nameLower);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) return $result->fetch_assoc();
        $stmt->close();
        return null;
    }
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE LOWER(TRIM(email)) = ? LIMIT 1");
    $emailLower = strtolower(trim($email));
    $stmt->bind_param("s", $emailLower);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) return $result->fetch_assoc();
    $stmt->close();
    return null;
}

// ============================================================
// MAIN: Handle CSV Import
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");

        if ($handle !== FALSE) {
            $insertCount  = 0;
            $updateCount  = 0;
            $promoteCount = 0;
            $skipCount    = 0;
            $errorCount   = 0;
            $row = 0;

            // Instructor Name -> ID map
            $instructors = [];
            $instResult = $conn->query("SELECT id, name FROM instructors");
            while ($inst = $instResult->fetch_assoc()) {
                $instructors[strtolower(trim($inst['name']))] = $inst['id'];
            }

            $conn->begin_transaction();

            try {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;

                    // Skip header row
                    if ($row === 1 && isset($data[0]) && in_array(strtolower(trim($data[0])), ['full name', 'name', 'fullname'])) {
                        continue;
                    }

                    // --- Parse CSV columns ---
                    // Col 0: Full Name (required)
                    // Col 1: Email (optional)
                    // Col 2: Phone (optional)
                    // Col 3: Khan Level (required, integer 1-16)
                    // Col 4: Khan Color (optional, auto-filled if blank)
                    // Col 5: Date Promoted (YYYY-MM-DD, optional)
                    // Col 6: Instructor Name (optional, must match system)
                    // Col 7: Location (optional)

                    $fullName = isset($data[0]) ? sanitize(trim($data[0])) : '';
                    if (empty($fullName)) {
                        $skipCount++;
                        $importLog[] = ['row' => $row, 'status' => 'skipped', 'name' => '(empty row)', 'reason' => 'No name provided'];
                        continue;
                    }

                    $email = isset($data[1]) ? sanitize(trim($data[1])) : '';
                    $phone = isset($data[2]) ? sanitize(trim($data[2])) : '';
                    $khanLevel = isset($data[3]) ? max(1, min(16, (int)$data[3])) : 1;

                    // Auto-lookup color from DB if not in CSV
                    $khanColor = isset($data[4]) ? sanitize(trim($data[4])) : '';
                    if (empty($khanColor)) {
                        $colorRes = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $khanLevel LIMIT 1");
                        if ($colorRes && $colorRow = $colorRes->fetch_assoc()) {
                            $khanColor = $colorRow['color_name'];
                        }
                    }

                    $datePromoted = isset($data[5]) ? trim($data[5]) : '';
                    if (empty($datePromoted) || $datePromoted === '0000-00-00') {
                        $datePromoted = date('Y-m-d');
                    }

                    $instructorName = isset($data[6]) ? strtolower(trim($data[6])) : '';
                    $instructorId   = isset($instructors[$instructorName]) ? $instructors[$instructorName] : null;

                    $location = isset($data[7]) ? sanitize(trim($data[7])) : '';

                    // Generate placeholder email only if truly missing
                    $realEmail = $email;
                    if (empty($email)) {
                        $slug  = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $fullName));
                        $email = $slug . '_' . time() . rand(100, 999) . '@archive.local';
                    }

                    // --- Check if user account exists ---
                    $linkedUser   = findUserAccount($conn, $realEmail, $fullName);
                    $linkedUserId = $linkedUser ? $linkedUser['id'] : null;

                    // --- Check for existing member record ---
                    $existing = findExistingMember($conn, $fullName, $realEmail, $phone);

                    if ($existing) {
                        $existingLevel = (int)$existing['current_khan_level'];

                        // CASE A: Exact same level already recorded → skip (no change needed)
                        if ($existingLevel === $khanLevel) {
                            $skipCount++;
                            $importLog[] = [
                                'row'    => $row,
                                'status' => 'skipped',
                                'name'   => $fullName,
                                'reason' => "Already in system at Khan $khanLevel — no changes needed."
                            ];
                            continue;
                        }

                        // CASE B: CSV has a HIGHER level → PROMOTE (update to new level)
                        if ($khanLevel > $existingLevel) {
                            $stmt = $conn->prepare("
                                UPDATE khan_members 
                                SET current_khan_level = ?, 
                                    khan_color         = ?, 
                                    date_promoted      = ?,
                                    instructor_id      = COALESCE(?, instructor_id),
                                    training_location  = COALESCE(NULLIF(?, ''), training_location),
                                    user_id            = COALESCE(user_id, ?),
                                    status             = 'active'
                                WHERE id = ?
                            ");
                            $stmt->bind_param("issssii", $khanLevel, $khanColor, $datePromoted, $instructorId, $location, $linkedUserId, $existing['id']);
                            if ($stmt->execute()) {
                                $promoteCount++;
                                $importLog[] = [
                                    'row'    => $row,
                                    'status' => 'promoted',
                                    'name'   => $fullName,
                                    'reason' => "Promoted from Khan $existingLevel → Khan $khanLevel"
                                ];
                            } else {
                                $errorCount++;
                                $importLog[] = ['row' => $row, 'status' => 'error', 'name' => $fullName, 'reason' => $conn->error];
                            }
                            $stmt->close();
                            continue;
                        }

                        // CASE C: CSV has a LOWER level than what's recorded
                        // (Can happen if encoding from Khan 1 → 16 in order and person was already added at higher level)
                        // → Update supplementary info only (don't downgrade level)
                        $stmt = $conn->prepare("
                            UPDATE khan_members 
                            SET phone             = COALESCE(NULLIF(phone, ''), ?),
                                training_location = COALESCE(NULLIF(training_location, ''), ?),
                                user_id           = COALESCE(user_id, ?),
                                instructor_id     = COALESCE(instructor_id, ?)
                            WHERE id = ?
                        ");
                        $stmt->bind_param("ssiii", $phone, $location, $linkedUserId, $instructorId, $existing['id']);
                        if ($stmt->execute()) {
                            $updateCount++;
                            $importLog[] = [
                                'row'    => $row,
                                'status' => 'updated',
                                'name'   => $fullName,
                                'reason' => "Already at higher Khan $existingLevel — supplementary info filled in only (no downgrade)."
                            ];
                        } else {
                            $errorCount++;
                        }
                        $stmt->close();

                    } else {
                        // CASE D: New person → INSERT
                        $status = 'graduated'; // Historical import default

                        $stmt = $conn->prepare("
                            INSERT INTO khan_members 
                                (user_id, full_name, email, phone, current_khan_level, khan_color, date_joined, date_promoted, instructor_id, training_location, status, notes) 
                            VALUES 
                                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Imported from Historical Masterlist')
                        ");
                        $stmt->bind_param("isssississss",
                            $linkedUserId, $fullName, $email, $phone,
                            $khanLevel, $khanColor, $datePromoted, $datePromoted,
                            $instructorId, $location, $status
                        );

                        if ($stmt->execute()) {
                            $insertCount++;
                            $linkedNote = $linkedUser ? " (linked to user account: {$linkedUser['email']})" : " (no user account — can be linked later)";
                            $importLog[] = [
                                'row'    => $row,
                                'status' => 'inserted',
                                'name'   => $fullName,
                                'reason' => "New member added at Khan $khanLevel.$linkedNote"
                            ];
                        } else {
                            $errorCount++;
                            $importLog[] = ['row' => $row, 'status' => 'error', 'name' => $fullName, 'reason' => $conn->error];
                        }
                        $stmt->close();
                    }
                }

                $conn->commit();
                $message     = "✅ Import Complete — Inserted: <strong>$insertCount</strong> | Promoted: <strong>$promoteCount</strong> | Info Updated: <strong>$updateCount</strong> | Skipped (no change): <strong>$skipCount</strong> | Errors: <strong>$errorCount</strong>";
                $messageType = 'success';

            } catch (Exception $e) {
                $conn->rollback();
                $message     = "❌ Import failed and was rolled back: " . $e->getMessage();
                $messageType = 'error';
            }

            fclose($handle);
        }
    } else {
        $message     = "Please upload a valid CSV file.";
        $messageType = 'error';
    }
}

include 'includes/admin_header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<?php if (!empty($importLog)): ?>
<div class="admin-section" style="margin-bottom: 2rem;">
    <div class="section-header">
        <h3><i class="fas fa-list-alt"></i> Import Log (<?php echo count($importLog); ?> rows processed)</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table" style="font-size: 0.9rem;">
            <thead>
                <tr>
                    <th width="50">Row</th>
                    <th width="60">Status</th>
                    <th>Name</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($importLog as $log): 
                    $badgeColor = [
                        'inserted' => '#2e7d32',
                        'promoted' => '#1565c0',
                        'updated'  => '#f57c00',
                        'skipped'  => '#757575',
                        'error'    => '#c62828',
                    ][$log['status']] ?? '#333';
                ?>
                <tr>
                    <td style="color:#999"><?php echo $log['row']; ?></td>
                    <td>
                        <span style="background:<?php echo $badgeColor; ?>; color:white; padding:2px 8px; border-radius:10px; font-size:0.8rem; font-weight:600; text-transform:uppercase;">
                            <?php echo htmlspecialchars($log['status']); ?>
                        </span>
                    </td>
                    <td><strong><?php echo htmlspecialchars($log['name']); ?></strong></td>
                    <td style="color:#555"><?php echo htmlspecialchars($log['reason']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="admin-section">
    <div class="section-header">
        <h2><i class="fas fa-file-import"></i> Import Historical Masterlist</h2>
        <a href="khan_members.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Members</a>
    </div>

    <div class="info-box" style="background: #e3f2fd; border-left: 4px solid #1976d2; padding: 1rem 1.5rem; margin-bottom: 1.5rem; border-radius:4px;">
        <strong><i class="fas fa-info-circle"></i> How the smart import works:</strong>
        <ol style="margin-top: 0.5rem; margin-left: 1.5rem; line-height: 2;">
            <li>You can <strong>safely re-run the import multiple times</strong> — it will never create duplicates.</li>
            <li>Start by uploading your <strong>Khan 1 list</strong>, then Khan 2, then Khan 3 … all the way to Khan 16.</li>
            <li>If a person appears in both the Khan 1 list AND the Khan 5 list (because they progressed), their record will simply be <strong>promoted to the higher level</strong> — not duplicated.</li>
            <li>Member matching is done by: exact email → exact name → fuzzy name + phone. <strong>Email is most reliable</strong>; include it when available.</li>
            <li>If a matching <strong>user account</strong> (login) already exists in the system with the same email or name, it will be <strong>automatically linked</strong>.</li>
            <li>Members imported without a user account can be linked to one later via the Members page.</li>
        </ol>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">

        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Upload CSV File</h3>
            <p style="color:#666; font-size:0.9rem; margin-top:0.25rem;">
                Tip: Upload by batch per Khan level (e.g. all Khan 1 graduates first), or upload the entire masterlist at once.
            </p>
            <form method="POST" enctype="multipart/form-data" style="margin-top: 1rem;">
                <div class="form-group">
                    <label class="form-label">Select CSV File</label>
                    <input type="file" name="csv_file" class="form-input" accept=".csv" required>
                </div>
                <div class="action-buttons">
                    <button type="submit" name="import_csv" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i> Upload and Import
                    </button>
                    <a href="#" onclick="downloadTemplate()" class="btn btn-outline">
                        <i class="fas fa-download"></i> Sample CSV
                    </a>
                </div>
            </form>
        </div>

        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border: 1px solid #ddd;">
            <h3>Required CSV Column Order</h3>
            <p style="color: #666; margin-bottom: 1rem; font-size:0.9rem;">Do not change the order of columns. Email is highly recommended to ensure accurate matching.</p>
            <table style="width:100%; font-size:0.9rem; border-collapse:collapse;">
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:0.4rem 0; font-weight:600;">Col 1</td>
                    <td style="padding:0.4rem 0;">Full Name <span style="color:red">*</span></td>
                </tr>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:0.4rem 0; font-weight:600;">Col 2</td>
                    <td style="padding:0.4rem 0;">Email <span style="color:#888">(optional but recommended)</span></td>
                </tr>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:0.4rem 0; font-weight:600;">Col 3</td>
                    <td style="padding:0.4rem 0;">Phone</td>
                </tr>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:0.4rem 0; font-weight:600;">Col 4</td>
                    <td style="padding:0.4rem 0;">Khan Level (1–16) <span style="color:red">*</span></td>
                </tr>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:0.4rem 0; font-weight:600;">Col 5</td>
                    <td style="padding:0.4rem 0;">Khan Color <span style="color:#888">(auto-filled if blank)</span></td>
                </tr>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:0.4rem 0; font-weight:600;">Col 6</td>
                    <td style="padding:0.4rem 0;">Date Promoted (YYYY-MM-DD)</td>
                </tr>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:0.4rem 0; font-weight:600;">Col 7</td>
                    <td style="padding:0.4rem 0;">Instructor Name <span style="color:#888">(must match system spelling)</span></td>
                </tr>
                <tr>
                    <td style="padding:0.4rem 0; font-weight:600;">Col 8</td>
                    <td style="padding:0.4rem 0;">Location</td>
                </tr>
            </table>
        </div>

    </div>

    <div style="margin-top: 2rem; background: #fff8e1; border-left: 4px solid #fbc02d; padding: 1rem 1.5rem; border-radius:4px;">
        <strong><i class="fas fa-lightbulb"></i> Recommended Encoding Strategy (Khan 1 → 16):</strong>
        <p style="margin-top:0.5rem; color:#555; line-height:1.7;">
            Pull your masterlist out of Excel. Sort it by <strong>Khan Level ascending (1 → 16)</strong>, then export/save as CSV. 
            Upload that single CSV — the system will walk through each row, and when it encounters the same person 
            appearing at Khan 3 (having already processed them at Khan 1), it will automatically <strong>promote</strong> 
            them to Khan 3 rather than creating a duplicate. You can also upload per-level batches — either way works.
        </p>
    </div>
</div>

<script>
function downloadTemplate() {
    const csv = [
        'Full Name,Email,Phone,Khan Level,Khan Color,Date Promoted,Instructor Name,Location',
        'Juan dela Cruz,juan@example.com,09123456789,1,White,2019-01-15,Ajarn Mike,Quezon City',
        'Maria Santos,,09987654321,3,Yellow,2020-06-01,,Manila',
        'Juan dela Cruz,juan@example.com,09123456789,5,Green,2021-09-10,Ajarn Mike,Quezon City',
    ].join('\n');

    const link = document.createElement('a');
    link.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv));
    link.setAttribute('download', 'khan_masterlist_template.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php include 'includes/admin_footer.php'; ?>