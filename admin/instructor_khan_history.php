<?php
$page_title = "Instructor Khan Journey";
require_once '../config/database.php';
requireAdmin();
require_once 'includes/activity_helper.php';

$conn      = getDbConnection();
$inst_id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success   = '';
$error     = '';

if ($inst_id <= 0) {
    header("Location: instructors.php");
    exit;
}

// ── Fetch instructor + linked khan_member ──────────────────────────────────
$inst = $conn->query("
    SELECT i.*,
           km.id          AS member_id,
           km.full_name   AS km_full_name,
           km.current_khan_level,
           km.date_joined,
           km.date_promoted,
           km.status      AS member_status
    FROM instructors i
    LEFT JOIN users u        ON i.user_id = u.id
    LEFT JOIN khan_members km ON km.user_id = u.id
    WHERE i.id = $inst_id
    LIMIT 1
")->fetch_assoc();

if (!$inst) {
    header("Location: instructors.php");
    exit;
}

$member_id = $inst['member_id'] ? (int)$inst['member_id'] : 0;

// ── Khan color map ────────────────────────────────────────────────────────
$khan_colors = [
    1  => ['color'=>'#FFFFFF',                                          'text'=>'#000','name'=>'White Khan'],
    2  => ['color'=>'#FFEB3B',                                          'text'=>'#000','name'=>'Yellow Khan'],
    3  => ['color'=>'linear-gradient(135deg,#FFEB3B 50%,#FFFFFF 50%)', 'text'=>'#000','name'=>'Yellow-White'],
    4  => ['color'=>'#4CAF50',                                          'text'=>'#fff','name'=>'Green Khan'],
    5  => ['color'=>'linear-gradient(135deg,#4CAF50 50%,#FFFFFF 50%)', 'text'=>'#000','name'=>'Green-White'],
    6  => ['color'=>'#2196F3',                                          'text'=>'#fff','name'=>'Blue Khan'],
    7  => ['color'=>'linear-gradient(135deg,#2196F3 50%,#FFFFFF 50%)', 'text'=>'#000','name'=>'Blue-White'],
    8  => ['color'=>'#795548',                                          'text'=>'#fff','name'=>'Brown Khan'],
    9  => ['color'=>'linear-gradient(135deg,#795548 50%,#FFFFFF 50%)', 'text'=>'#000','name'=>'Brown-White'],
    10 => ['color'=>'#D32F2F',                                          'text'=>'#fff','name'=>'Red Khan'],
    11 => ['color'=>'linear-gradient(135deg,#D32F2F 50%,#FFFFFF 50%)', 'text'=>'#000','name'=>'Red-White'],
    12 => ['color'=>'linear-gradient(135deg,#D32F2F 50%,#FFEB3B 50%)', 'text'=>'#000','name'=>'Red-Yellow'],
    13 => ['color'=>'linear-gradient(135deg,#D32F2F 50%,#C0C0C0 50%)', 'text'=>'#000','name'=>'Red-Silver'],
    14 => ['color'=>'linear-gradient(135deg,#C0C0C0,#E8E8E8,#C0C0C0)', 'text'=>'#000','name'=>'Silver Khan'],
    15 => ['color'=>'linear-gradient(135deg,#C0C0C0 50%,#FFD700 50%)', 'text'=>'#000','name'=>'Silver-Gold'],
    16 => ['color'=>'linear-gradient(135deg,#FFD700,#FFF9C4,#FFD700)',  'text'=>'#000','name'=>'Gold Khan'],
];

// ── Handle POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($member_id <= 0) {
        $error = 'This instructor has no linked Khan member record. Create a Khan member account for them first.';
        goto render;
    }

    // ADD
    if (isset($_POST['add_entry'])) {
        $khan_level         = (int)$_POST['khan_level'];
        $training_date      = sanitize($_POST['training_date'] ?? '');
        $certified_date     = !empty($_POST['certified_date']) ? sanitize($_POST['certified_date']) : null;
        $location           = sanitize($_POST['location'] ?? '');
        $notes              = sanitize($_POST['notes'] ?? '');
        $certificate_number = sanitize($_POST['certificate_number'] ?? '');
        $status             = sanitize($_POST['status'] ?? 'completed');
        $instructor_id_ref  = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;

        $stmt = $conn->prepare("INSERT INTO khan_training_history
            (member_id, khan_level, training_date, certified_date, instructor_id, location, notes, certificate_number, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissiisss",
            $member_id, $khan_level, $training_date, $certified_date,
            $instructor_id_ref, $location, $notes, $certificate_number, $status);

        if ($stmt->execute()) {
            // Optionally update current khan level if this is the highest certified entry
            if ($status === 'certified') {
                $conn->query("UPDATE khan_members
                    SET current_khan_level = $khan_level,
                        date_promoted = " . ($certified_date ? "'$certified_date'" : "date_promoted") . "
                    WHERE id = $member_id AND current_khan_level < $khan_level");
            }
            $success = 'Khan journey entry added successfully.';
            logActivity($conn, 'create', 'khan_training_history', $conn->insert_id, $inst['name'],
                "Khan $khan_level entry added. Status: $status | Date: $training_date");
        } else {
            $error = 'Failed to add entry: ' . $conn->error;
        }
        $stmt->close();

    // EDIT
    } elseif (isset($_POST['edit_entry'])) {
        $history_id         = (int)$_POST['history_id'];
        $khan_level         = (int)$_POST['khan_level'];
        $training_date      = sanitize($_POST['training_date'] ?? '');
        $certified_date     = !empty($_POST['certified_date']) ? sanitize($_POST['certified_date']) : null;
        $location           = sanitize($_POST['location'] ?? '');
        $notes              = sanitize($_POST['notes'] ?? '');
        $certificate_number = sanitize($_POST['certificate_number'] ?? '');
        $status             = sanitize($_POST['status'] ?? 'completed');
        $instructor_id_ref  = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;

        $stmt = $conn->prepare("UPDATE khan_training_history
            SET khan_level=?, training_date=?, certified_date=?, instructor_id=?,
                location=?, notes=?, certificate_number=?, status=?
            WHERE id=? AND member_id=?");
        $stmt->bind_param("isssisssii",
            $khan_level, $training_date, $certified_date, $instructor_id_ref,
            $location, $notes, $certificate_number, $status, $history_id, $member_id);

        if ($stmt->execute()) {
            $success = 'Entry updated successfully.';
            logActivity($conn, 'edit', 'khan_training_history', $history_id, $inst['name'],
                "Khan $khan_level updated. Status: $status");
        } else {
            $error = 'Failed to update entry: ' . $conn->error;
        }
        $stmt->close();

    // DELETE
    } elseif (isset($_POST['delete_entry'])) {
        $history_id = (int)$_POST['history_id'];
        if ($conn->query("DELETE FROM khan_training_history WHERE id=$history_id AND member_id=$member_id")) {
            $success = 'Entry deleted.';
            logActivity($conn, 'delete', 'khan_training_history', $history_id, $inst['name'], 'Journey entry deleted.');
        } else {
            $error = 'Failed to delete entry.';
        }
    }
}

render:

// ── Fetch history ──────────────────────────────────────────────────────────
$history = $member_id ? $conn->query("
    SELECT kth.*, i.name as ref_instructor_name
    FROM khan_training_history kth
    LEFT JOIN instructors i ON kth.instructor_id = i.id
    WHERE kth.member_id = $member_id
    ORDER BY kth.khan_level ASC, kth.training_date ASC
") : null;

// ── All instructors for dropdown ───────────────────────────────────────────
$all_instructors = $conn->query("SELECT id, name FROM instructors WHERE status='active' ORDER BY name");

include 'includes/admin_header.php';
?>

<!-- ── Breadcrumb ── -->
<div style="margin-bottom:1.5rem;padding:.875rem 1rem;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
    <a href="instructors.php" style="color:#1976d2;text-decoration:none;font-size:.9rem;">
        <i class="fas fa-arrow-left"></i> Back to Instructors
    </a>
    <span style="color:#888;font-size:.85rem;">
        <i class="fas fa-route"></i> Khan Journey Management
    </span>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
<?php endif; ?>

<!-- ── Instructor Profile Card ── -->
<div class="admin-section" style="margin-bottom:1.75rem;">
    <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
        <!-- Avatar -->
        <div style="flex-shrink:0;">
            <?php if (!empty($inst['photo_path'])): ?>
                <img src="<?= SITE_URL . '/' . htmlspecialchars($inst['photo_path']) ?>" alt=""
                     style="width:80px;height:80px;border-radius:50%;object-fit:cover;box-shadow:0 2px 10px rgba(0,0,0,0.15);">
            <?php else: ?>
                <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#8b0000,#c9a84c);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:#fff;">
                    <?= strtoupper(substr($inst['name'],0,1)) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div style="flex:1;min-width:200px;">
            <h2 style="margin:0 0 .25rem;font-size:1.4rem;color:#1a1a1a;"><?= htmlspecialchars($inst['name']) ?></h2>
            <?php if (!empty($inst['title'])): ?>
                <div style="color:#8b0000;font-size:.85rem;font-weight:600;margin-bottom:.5rem;"><?= htmlspecialchars($inst['title']) ?></div>
            <?php endif; ?>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <span style="background:#e3f2fd;color:#1976d2;padding:.2rem .6rem;border-radius:4px;font-size:.78rem;font-weight:600;">
                    <i class="fas fa-layer-group"></i> <?= htmlspecialchars($inst['khan_level'] ?: 'N/A') ?>
                </span>
                <?php if ($member_id): ?>
                <span style="background:#e8f5e9;color:#2e7d32;padding:.2rem .6rem;border-radius:4px;font-size:.78rem;font-weight:600;">
                    <i class="fas fa-id-card"></i> Member ID #<?= $member_id ?>
                </span>
                <?php if (!empty($inst['current_khan_level'])): ?>
                <span style="background:#fff3e0;color:#e65100;padding:.2rem .6rem;border-radius:4px;font-size:.78rem;font-weight:600;">
                    <i class="fas fa-star"></i> Current KL <?= (int)$inst['current_khan_level'] ?>
                </span>
                <?php endif; ?>
                <?php else: ?>
                <span style="background:#fce4ec;color:#c62828;padding:.2rem .6rem;border-radius:4px;font-size:.78rem;">
                    <i class="fas fa-exclamation-triangle"></i> No Khan member record linked
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add entry button -->
        <?php if ($member_id): ?>
        <div>
            <button class="btn btn-primary" onclick="document.getElementById('addEntryModal').style.display='block'">
                <i class="fas fa-plus-circle"></i> Add Journey Entry
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!$member_id): ?>
<!-- No member record notice -->
<div class="admin-section" style="text-align:center;padding:3rem;color:#999;">
    <i class="fas fa-unlink" style="font-size:3rem;margin-bottom:1rem;display:block;color:#ddd;"></i>
    <h3 style="color:#aaa;margin-bottom:.5rem;">No Khan Member Record Found</h3>
    <p style="font-size:.9rem;">This instructor does not have a linked user account with a <code>khan_members</code> row.<br>
    To track their Khan journey, first create a Khan member entry for them via
    <a href="khan_members.php" style="color:#1976d2;">Manage Khan Members</a> and link it to their user account.</p>
</div>

<?php else: ?>

<!-- ── JOURNEY TIMELINE ── -->
<div class="admin-section">
    <div class="section-header" style="margin-bottom:1.5rem;">
        <h2><i class="fas fa-route" style="color:#8b0000;"></i> Khan Journey Timeline</h2>
        <span style="color:#888;font-size:.85rem;">
            <?php
            $count = $history ? $history->num_rows : 0;
            echo $count . ' entr' . ($count === 1 ? 'y' : 'ies');
            ?>
        </span>
    </div>

    <?php if (!$history || $history->num_rows === 0): ?>
    <div style="text-align:center;padding:3rem;color:#bbb;">
        <i class="fas fa-scroll" style="font-size:2.5rem;margin-bottom:1rem;display:block;"></i>
        <p>No journey entries yet. Click <strong>Add Journey Entry</strong> to start logging.</p>
    </div>
    <?php else: ?>

    <!-- Timeline -->
    <div style="position:relative;padding-left:2rem;">
        <!-- vertical line -->
        <div style="position:absolute;left:.65rem;top:.5rem;bottom:.5rem;width:2px;background:linear-gradient(to bottom,#8b0000,#c9a84c);border-radius:1px;opacity:.3;"></div>

        <?php
        $history->data_seek(0);
        while ($entry = $history->fetch_assoc()):
            $kl    = (int)$entry['khan_level'];
            $ci    = $khan_colors[$kl] ?? ['color'=>'#999','text'=>'#fff','name'=>'Khan '.$kl];
            $is_grad = $entry['status'] === 'certified';
            $dot_color = $is_grad ? '#c9a84c' : ($entry['status'] === 'in_progress' ? '#f59e0b' : '#6b7280');
        ?>
        <div style="position:relative;margin-bottom:1.25rem;padding-left:1.25rem;" id="entry-<?= $entry['id'] ?>">
            <!-- dot -->
            <div style="position:absolute;left:-1.4rem;top:.9rem;width:13px;height:13px;border-radius:50%;background:<?= $dot_color ?>;border:2.5px solid #fff;box-shadow:0 0 0 3px <?= $dot_color ?>33;"></div>

            <!-- card -->
            <div style="background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:1.1rem 1.25rem;box-shadow:0 1px 4px rgba(0,0,0,.05);transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow='0 1px 4px rgba(0,0,0,.05)'">

                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                    <!-- Left: level swatch + info -->
                    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;flex:1;">
                        <!-- level circle -->
                        <div style="width:52px;height:52px;border-radius:50%;background:<?= $ci['color'] ?>;border:3px solid rgba(0,0,0,.1);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;color:<?= $ci['text'] ?>;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.15);">
                            <?= $kl ?>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:1rem;color:#1a1a1a;margin-bottom:2px;">
                                Khan <?= $kl ?> &mdash; <?= htmlspecialchars($ci['name']) ?>
                            </div>
                            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
                                <!-- Status badge -->
                                <?php
                                $sbg = ['certified'=>'#e8f5e9','completed'=>'#e3f2fd','in_progress'=>'#fff3e0'];
                                $sco = ['certified'=>'#2e7d32','completed'=>'#1565c0','in_progress'=>'#e65100'];
                                $slb = ['certified'=>'Certified ✓','completed'=>'Completed','in_progress'=>'In Progress'];
                                $s = $entry['status'];
                                ?>
                                <span style="background:<?= $sbg[$s]??'#f3f4f6' ?>;color:<?= $sco[$s]??'#374151' ?>;font-size:.7rem;font-weight:700;padding:.2rem .6rem;border-radius:50px;text-transform:uppercase;letter-spacing:.04em;">
                                    <?= $slb[$s] ?? ucfirst($s) ?>
                                </span>
                                <!-- Training date -->
                                <?php if (!empty($entry['training_date']) && $entry['training_date'] !== '0000-00-00'): ?>
                                <span style="color:#888;font-size:.78rem;">
                                    <i class="fas fa-calendar-alt" style="margin-right:.2rem;"></i>
                                    Training: <?= date('M j, Y', strtotime($entry['training_date'])) ?>
                                </span>
                                <?php endif; ?>
                                <!-- Certified date -->
                                <?php if (!empty($entry['certified_date']) && $entry['certified_date'] !== '0000-00-00'): ?>
                                <span style="color:#2e7d32;font-size:.78rem;font-weight:600;">
                                    <i class="fas fa-award" style="margin-right:.2rem;"></i>
                                    Certified: <?= date('M j, Y', strtotime($entry['certified_date'])) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right: actions -->
                    <div style="display:flex;gap:.4rem;flex-shrink:0;">
                        <button class="btn btn-sm btn-primary"
                            onclick="openEditEntry(<?= htmlspecialchars(json_encode($entry), ENT_QUOTES) ?>)"
                            title="Edit this entry">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this Khan journey entry?');">
                            <input type="hidden" name="history_id" value="<?= $entry['id'] ?>">
                            <button type="submit" name="delete_entry" class="btn btn-sm btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Detail rows -->
                <?php if (!empty($entry['location']) || !empty($entry['ref_instructor_name']) || !empty($entry['certificate_number']) || !empty($entry['notes'])): ?>
                <div style="margin-top:.875rem;padding-top:.875rem;border-top:1px solid #f1f5f9;display:flex;flex-wrap:wrap;gap:.5rem 1.5rem;">
                    <?php if (!empty($entry['ref_instructor_name'])): ?>
                    <span style="font-size:.8rem;color:#555;"><i class="fas fa-chalkboard-teacher" style="color:#ccc;margin-right:.3rem;"></i><?= htmlspecialchars($entry['ref_instructor_name']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($entry['location'])): ?>
                    <span style="font-size:.8rem;color:#555;"><i class="fas fa-map-marker-alt" style="color:#ccc;margin-right:.3rem;"></i><?= htmlspecialchars($entry['location']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($entry['certificate_number'])): ?>
                    <span style="font-size:.8rem;color:#555;"><i class="fas fa-certificate" style="color:#c9a84c;margin-right:.3rem;"></i>Cert #<?= htmlspecialchars($entry['certificate_number']) ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($entry['notes'])): ?>
                <div style="margin-top:.625rem;font-size:.82rem;color:#666;background:#f8fafc;border-left:3px solid #e2e8f0;padding:.5rem .75rem;border-radius:0 4px 4px 0;">
                    <?= nl2br(htmlspecialchars($entry['notes'])) ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php endif; ?>
</div>

<?php endif; // end member_id check ?>


<!-- ══════════════════════════════════════════
     ADD ENTRY MODAL
══════════════════════════════════════════ -->
<div id="addEntryModal" class="modal">
    <div class="modal-content" style="max-width:580px;">
        <span class="modal-close" onclick="document.getElementById('addEntryModal').style.display='none'">&times;</span>
        <h2 style="display:flex;align-items:center;gap:.6rem;margin-bottom:1.5rem;">
            <i class="fas fa-plus-circle" style="color:#8b0000;"></i> Add Khan Journey Entry
        </h2>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Khan Level *</label>
                    <select name="khan_level" class="form-select" required>
                        <option value="">— Select Level —</option>
                        <?php foreach ($khan_colors as $lv => $ci): ?>
                        <option value="<?= $lv ?>">Khan <?= $lv ?> — <?= $ci['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="certified" selected>Certified</option>
                    </select>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Training Date *</label>
                    <input type="date" name="training_date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Certified Date</label>
                    <input type="date" name="certified_date" class="form-input">
                    <small style="color:#94a3b8;">Leave blank if not yet certified</small>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Presiding Instructor</label>
                    <select name="instructor_id" class="form-select">
                        <option value="">— None / Self —</option>
                        <?php
                        $all_instructors->data_seek(0);
                        while ($ir = $all_instructors->fetch_assoc()):
                        ?>
                        <option value="<?= $ir['id'] ?>"><?= htmlspecialchars($ir['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Certificate Number</label>
                    <input type="text" name="certificate_number" class="form-input" placeholder="e.g. OMA-2019-011">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Training Location</label>
                <input type="text" name="location" class="form-input" placeholder="e.g. Quezon City">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Notable achievements, observations, context..."></textarea>
            </div>
            <div style="display:flex;gap:.75rem;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid #f1f5f9;">
                <button type="submit" name="add_entry" class="btn btn-primary" style="flex:1;">
                    <i class="fas fa-save"></i> Add Entry
                </button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addEntryModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════════════════════════════════════
     EDIT ENTRY MODAL
══════════════════════════════════════════ -->
<div id="editEntryModal" class="modal">
    <div class="modal-content" style="max-width:580px;">
        <span class="modal-close" onclick="document.getElementById('editEntryModal').style.display='none'">&times;</span>
        <h2 style="display:flex;align-items:center;gap:.6rem;margin-bottom:1.5rem;">
            <i class="fas fa-edit" style="color:#1976d2;"></i> Edit Khan Journey Entry
        </h2>
        <form method="POST" id="editEntryForm">
            <input type="hidden" name="history_id" id="ee_history_id">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Khan Level *</label>
                    <select name="khan_level" id="ee_khan_level" class="form-select" required>
                        <option value="">— Select Level —</option>
                        <?php foreach ($khan_colors as $lv => $ci): ?>
                        <option value="<?= $lv ?>">Khan <?= $lv ?> — <?= $ci['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="ee_status" class="form-select">
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="certified">Certified</option>
                    </select>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Training Date *</label>
                    <input type="date" name="training_date" id="ee_training_date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Certified Date</label>
                    <input type="date" name="certified_date" id="ee_certified_date" class="form-input">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Presiding Instructor</label>
                    <select name="instructor_id" id="ee_instructor_id" class="form-select">
                        <option value="">— None / Self —</option>
                        <?php
                        $all_instructors->data_seek(0);
                        while ($ir = $all_instructors->fetch_assoc()):
                        ?>
                        <option value="<?= $ir['id'] ?>"><?= htmlspecialchars($ir['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Certificate Number</label>
                    <input type="text" name="certificate_number" id="ee_cert_num" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Training Location</label>
                <input type="text" name="location" id="ee_location" class="form-input">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Notes</label>
                <textarea name="notes" id="ee_notes" class="form-textarea" rows="3"></textarea>
            </div>
            <div style="display:flex;gap:.75rem;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid #f1f5f9;">
                <button type="submit" name="edit_entry" class="btn btn-primary" style="flex:1;">
                    <i class="fas fa-save"></i> Update Entry
                </button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editEntryModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>


<style>
.modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background:rgba(0,0,0,0.5); animation:fadeIn .3s; }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.modal-content { background:#fefefe; margin:3% auto; padding:2rem; border-radius:10px; width:90%; max-width:900px; max-height:90vh; overflow-y:auto; box-shadow:0 4px 20px rgba(0,0,0,.3); animation:slideDown .3s; }
@keyframes slideDown { from{transform:translateY(-40px);opacity:0} to{transform:translateY(0);opacity:1} }
.modal-close { color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer; transition:color .3s; }
.modal-close:hover { color:#000; }
</style>

<script>
function openEditEntry(entry) {
    document.getElementById('ee_history_id').value    = entry.id;
    document.getElementById('ee_khan_level').value    = entry.khan_level;
    document.getElementById('ee_status').value        = entry.status;
    document.getElementById('ee_training_date').value = entry.training_date || '';
    document.getElementById('ee_certified_date').value= (entry.certified_date && entry.certified_date !== '0000-00-00') ? entry.certified_date : '';
    document.getElementById('ee_instructor_id').value = entry.instructor_id || '';
    document.getElementById('ee_cert_num').value      = entry.certificate_number || '';
    document.getElementById('ee_location').value      = entry.location || '';
    document.getElementById('ee_notes').value         = entry.notes || '';
    document.getElementById('editEntryModal').style.display = 'block';
}

window.onclick = function(e) {
    ['addEntryModal','editEntryModal'].forEach(id => {
        const m = document.getElementById(id);
        if (e.target === m) m.style.display = 'none';
    });
};
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        ['addEntryModal','editEntryModal'].forEach(id => {
            document.getElementById(id).style.display = 'none';
        });
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>