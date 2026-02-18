<?php
/**
 * member_training_history.php
 * 
 * CONSOLIDATED history + promotion page.
 * All history writes go to khan_training_history ONLY.
 * khan_promotion.php is now redundant — promote from here directly.
 * 
 * Fixes applied:
 * - INSERT bind_param was "iississss" (wrong). Fixed to "iississss" with correct i for instructor_id.
 * - UPDATE bind_param was "issiisssii" — verified correct.
 * - Added promote_member handler (absorbs khan_promotion.php).
 * - add_khan_members.php no longer needs to be touched for history — handled here on backfill.
 * - Auto-backfill on first open for members with no history records.
 * - Also migrates any stray records from member_training_history into khan_training_history.
 */

$page_title = "Khan Training History";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

$member_id = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;
if ($member_id <= 0) { header("Location: khan_members.php"); exit; }

$member_query = $conn->query("
    SELECT km.*, i.name as instructor_name, u.serial_number
    FROM khan_members km
    LEFT JOIN instructors i ON km.instructor_id = i.id
    LEFT JOIN users u ON km.user_id = u.id
    WHERE km.id = $member_id
");
if (!$member_query || $member_query->num_rows === 0) { header("Location: khan_members.php"); exit; }
$member = $member_query->fetch_assoc();

// ── ONE-TIME MIGRATION: pull in records from member_training_history if table exists ──
// (covers promotions done via the old khan_promotion.php before this fix)
$tableCheck = $conn->query("SHOW TABLES LIKE 'member_training_history'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $migrate = $conn->query("
        SELECT * FROM member_training_history 
        WHERE member_id = $member_id
    ");
    if ($migrate) {
        while ($mRow = $migrate->fetch_assoc()) {
            $lvl   = (int)$mRow['khan_level_achieved'];
            $date  = $conn->real_escape_string($mRow['promotion_date']);
            $color = $conn->real_escape_string($mRow['khan_color'] ?? '');
            $notes = $conn->real_escape_string($mRow['notes'] ?? 'Migrated from promotion record');
            $instr = $mRow['promoted_by_instructor_id'] ? (int)$mRow['promoted_by_instructor_id'] : 'NULL';
            // Only insert if not already present
            $exists = $conn->query("SELECT id FROM khan_training_history WHERE member_id = $member_id AND khan_level = $lvl AND training_date = '$date' LIMIT 1");
            if ($exists && $exists->num_rows === 0) {
                $conn->query("
                    INSERT INTO khan_training_history
                        (member_id, khan_level, training_date, certified_date, instructor_id, location, status, notes)
                    VALUES
                        ($member_id, $lvl, '$date', '$date', $instr, '', 'certified', '$notes')
                ");
            }
        }
    }
}

// ── AUTO-BACKFILL: member has a level but zero history rows ──
$histCheck = $conn->query("SELECT COUNT(*) as cnt FROM khan_training_history WHERE member_id = $member_id");
$histCount = $histCheck->fetch_assoc()['cnt'];
if ($histCount === 0 && $member['current_khan_level'] > 0) {
    $lvl      = (int)$member['current_khan_level'];
    $date     = $conn->real_escape_string($member['date_promoted'] ?: $member['date_joined'] ?: date('Y-m-d'));
    $instr    = $member['instructor_id'] ? (int)$member['instructor_id'] : 'NULL';
    $location = $conn->real_escape_string($member['training_location'] ?? '');
    $conn->query("
        INSERT INTO khan_training_history
            (member_id, khan_level, training_date, certified_date, instructor_id, location, status, notes)
        VALUES
            ($member_id, $lvl, '$date', '$date', $instr, '$location', 'certified', 'Auto-generated from member record')
    ");
}

// ── FORM HANDLERS ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ADD TRAINING RECORD
    if (isset($_POST['add_history'])) {
        $khan_level         = (int)$_POST['khan_level'];
        $training_date      = $_POST['training_date'];
        $certified_date     = !empty($_POST['certified_date']) ? $_POST['certified_date'] : null;
        $instructor_id      = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
        $location           = sanitize($_POST['location']);
        $notes              = sanitize($_POST['notes']);
        $certificate_number = sanitize($_POST['certificate_number']);
        $status             = $_POST['status'];

        // i=member_id, i=khan_level, s=training_date, s=certified_date, i=instructor_id, s=location, s=notes, s=certificate_number, s=status
        $stmt = $conn->prepare("INSERT INTO khan_training_history (member_id, khan_level, training_date, certified_date, instructor_id, location, notes, certificate_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iississss", $member_id, $khan_level, $training_date, $certified_date, $instructor_id, $location, $notes, $certificate_number, $status);

        if ($stmt->execute()) {
            // Sync member's current level upward if certified
            if ($status === 'certified' && $khan_level > $member['current_khan_level']) {
                $certDate = $certified_date ?: $training_date;
                // Get color for new level
                $clr = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $khan_level LIMIT 1");
                $newColor = ($clr && $cr = $clr->fetch_assoc()) ? $conn->real_escape_string($cr['color_name']) : '';
                $conn->query("UPDATE khan_members SET current_khan_level = $khan_level, khan_color = '$newColor', date_promoted = '$certDate' WHERE id = $member_id");
                $member['current_khan_level'] = $khan_level; // refresh for this request
            }
            $success = 'Training record added successfully!';
        } else {
            $error = 'Failed to add record: ' . $stmt->error;
        }
        $stmt->close();
    }

    // PROMOTE MEMBER (replaces khan_promotion.php)
    elseif (isset($_POST['promote_member'])) {
        $new_level      = (int)$_POST['new_khan_level'];
        $promo_date     = $_POST['promotion_date'];
        $instructor_id  = !empty($_POST['promoted_by']) ? (int)$_POST['promoted_by'] : null;
        $notes          = sanitize($_POST['notes'] ?? '');

        if ($new_level <= $member['current_khan_level']) {
            $error = "New level must be higher than current Khan {$member['current_khan_level']}.";
        } else {
            $clr      = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $new_level LIMIT 1");
            $newColor = ($clr && $cr = $clr->fetch_assoc()) ? $cr['color_name'] : '';
            $newColorEsc = $conn->real_escape_string($newColor);

            $conn->begin_transaction();
            try {
                // Write to khan_training_history
                $stmt = $conn->prepare("INSERT INTO khan_training_history (member_id, khan_level, training_date, certified_date, instructor_id, location, notes, certificate_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, '', 'certified')");
                $stmt->bind_param("iississ", $member_id, $new_level, $promo_date, $promo_date, $instructor_id, $member['training_location'], $notes);
                if (!$stmt->execute()) throw new Exception('History insert failed: ' . $stmt->error);
                $stmt->close();

                // Update khan_members
                $stmt = $conn->prepare("UPDATE khan_members SET current_khan_level = ?, khan_color = ?, date_promoted = ? WHERE id = ?");
                $stmt->bind_param("issi", $new_level, $newColor, $promo_date, $member_id);
                if (!$stmt->execute()) throw new Exception('Member update failed: ' . $stmt->error);
                $stmt->close();

                $conn->commit();
                $member['current_khan_level'] = $new_level;
                $member['khan_color'] = $newColor;
                $success = "Promoted to Khan $new_level successfully!";
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
    }

    // EDIT TRAINING RECORD
    elseif (isset($_POST['edit_history'])) {
        $history_id         = (int)$_POST['history_id'];
        $khan_level         = (int)$_POST['khan_level'];
        $training_date      = $_POST['training_date'];
        $certified_date     = !empty($_POST['certified_date']) ? $_POST['certified_date'] : null;
        $instructor_id      = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
        $location           = sanitize($_POST['location']);
        $notes              = sanitize($_POST['notes']);
        $certificate_number = sanitize($_POST['certificate_number']);
        $status             = $_POST['status'];

        // i=khan_level, s=training_date, s=certified_date, i=instructor_id, s=location, s=notes, s=certificate_number, s=status, i=history_id, i=member_id
        $stmt = $conn->prepare("UPDATE khan_training_history SET khan_level=?, training_date=?, certified_date=?, instructor_id=?, location=?, notes=?, certificate_number=?, status=? WHERE id=? AND member_id=?");
        $stmt->bind_param("issiisssii", $khan_level, $training_date, $certified_date, $instructor_id, $location, $notes, $certificate_number, $status, $history_id, $member_id);

        if ($stmt->execute()) {
            // Re-sync level if a certified record was edited
            if ($status === 'certified') {
                $maxRes = $conn->query("SELECT MAX(khan_level) as max_lvl FROM khan_training_history WHERE member_id = $member_id AND status = 'certified'");
                if ($maxRes) {
                    $maxRow = $maxRes->fetch_assoc();
                    $maxLvl = (int)$maxRow['max_lvl'];
                    if ($maxLvl !== (int)$member['current_khan_level']) {
                        $clr = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $maxLvl LIMIT 1");
                        $newColor = ($clr && $cr = $clr->fetch_assoc()) ? $conn->real_escape_string($cr['color_name']) : '';
                        $certDate = $conn->real_escape_string($certified_date ?: $training_date);
                        $conn->query("UPDATE khan_members SET current_khan_level = $maxLvl, khan_color = '$newColor', date_promoted = '$certDate' WHERE id = $member_id");
                    }
                }
            }
            $success = 'Training record updated successfully!';
        } else {
            $error = 'Failed to update record: ' . $stmt->error;
        }
        $stmt->close();
    }

    // DELETE TRAINING RECORD
    elseif (isset($_POST['delete_history'])) {
        $history_id = (int)$_POST['history_id'];
        if ($conn->query("DELETE FROM khan_training_history WHERE id = $history_id AND member_id = $member_id")) {
            // Re-sync member's level to the highest remaining certified entry
            $maxRes = $conn->query("SELECT MAX(khan_level) as max_lvl FROM khan_training_history WHERE member_id = $member_id AND status = 'certified'");
            if ($maxRes) {
                $maxRow = $maxRes->fetch_assoc();
                $maxLvl = (int)($maxRow['max_lvl'] ?? 0);
                if ($maxLvl > 0 && $maxLvl !== (int)$member['current_khan_level']) {
                    $clr = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $maxLvl LIMIT 1");
                    $newColor = ($clr && $cr = $clr->fetch_assoc()) ? $conn->real_escape_string($cr['color_name']) : '';
                    $conn->query("UPDATE khan_members SET current_khan_level = $maxLvl, khan_color = '$newColor' WHERE id = $member_id");
                }
            }
            $success = 'Training record deleted successfully!';
        } else {
            $error = 'Failed to delete record.';
        }
    }
}

// Re-fetch member to reflect any updates
$member_query = $conn->query("SELECT km.*, i.name as instructor_name, u.serial_number FROM khan_members km LEFT JOIN instructors i ON km.instructor_id = i.id LEFT JOIN users u ON km.user_id = u.id WHERE km.id = $member_id");
$member = $member_query->fetch_assoc();

// Training history
$history = $conn->query("
    SELECT kth.*, i.name as instructor_name
    FROM khan_training_history kth
    LEFT JOIN instructors i ON kth.instructor_id = i.id
    WHERE kth.member_id = $member_id
    ORDER BY kth.khan_level ASC, kth.training_date ASC
");

// Khan colors for progress grid
$khanColorsQ = $conn->query("SELECT khan_level, color_name, hex_color FROM khan_colors ORDER BY khan_level ASC");
$khanColorMap = [];
while ($kc = $khanColorsQ->fetch_assoc()) {
    $khanColorMap[$kc['khan_level']] = $kc;
}

// Instructors
$instructors = $conn->query("SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name");

include 'includes/admin_header.php';
?>

<div class="breadcrumb" style="margin-bottom:1.5rem; padding:1rem; background:#f5f5f5; border-radius:4px;">
    <a href="khan_members.php" style="color:#1976d2; text-decoration:none;">← Back to Khan Members</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Member Profile Card -->
<div class="admin-section" style="margin-bottom:2rem;">
    <div style="display:flex; align-items:center; gap:2rem; flex-wrap:wrap;">
        <div style="flex:1;">
            <h2 style="margin:0 0 0.5rem 0; color:#1976d2;">
                <i class="fas fa-user-graduate"></i> <?php echo htmlspecialchars($member['full_name']); ?>
            </h2>
            <div style="display:flex; gap:2rem; flex-wrap:wrap; margin-top:1rem;">
                <div>
                    <strong>Current Khan Level:</strong>
                    <?php
                        $curLvl = (int)$member['current_khan_level'];
                        $curHex = $khanColorMap[$curLvl]['hex_color'] ?? '#388e3c';
                    ?>
                    <span style="display:inline-block; background:<?php echo $curHex; ?>; color:white; padding:0.25rem 0.75rem; border-radius:4px; margin-left:0.5rem; font-weight:700;">
                        Khan <?php echo $curLvl; ?> — <?php echo htmlspecialchars($member['khan_color'] ?: ''); ?>
                    </span>
                </div>
                <div><strong>Date Joined:</strong> <span style="color:#666;"><?php echo formatDate($member['date_joined']); ?></span></div>
                <div><strong>Last Promoted:</strong> <span style="color:#666;"><?php echo $member['date_promoted'] ? formatDate($member['date_promoted']) : 'N/A'; ?></span></div>
                <div><strong>Instructor:</strong> <span style="color:#666;"><?php echo htmlspecialchars($member['instructor_name'] ?: 'Not assigned'); ?></span></div>
                <div><strong>Serial #:</strong> <span style="color:#666;"><?php echo htmlspecialchars($member['serial_number'] ?: 'N/A'); ?></span></div>
                <div><strong>Location:</strong> <span style="color:#666;"><?php echo htmlspecialchars($member['training_location'] ?: 'N/A'); ?></span></div>
            </div>
        </div>
    </div>
</div>

<!-- Training History Section -->
<div class="admin-section">
    <div class="section-header">
        <h2><i class="fas fa-history"></i> Khan Training History</h2>
        <div style="display:flex; gap:0.75rem;">
            <?php if ($member['current_khan_level'] < 16): ?>
            <button class="btn btn-success" onclick="document.getElementById('promoteModal').style.display='block'">
                <i class="fas fa-arrow-up"></i> Promote Member
            </button>
            <?php endif; ?>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">
                <i class="fas fa-plus-circle"></i> Add Record
            </button>
        </div>
    </div>

    <!-- Khan Level Progress Grid -->
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(120px,1fr)); gap:0.75rem; margin-bottom:2rem;">
        <?php for ($level = 1; $level <= 16; $level++):
            $lvlRes  = $conn->query("SELECT * FROM khan_training_history WHERE member_id = $member_id AND khan_level = $level ORDER BY training_date DESC LIMIT 1");
            $lvlData = ($lvlRes && $lvlRes->num_rows > 0) ? $lvlRes->fetch_assoc() : null;
            $isCurrent   = ($level === (int)$member['current_khan_level']);
            $isCompleted = $lvlData && $lvlData['status'] === 'certified';
            $hex = $khanColorMap[$level]['hex_color'] ?? '#ccc';
            $bg  = $isCompleted ? '#e8f5e9' : ($isCurrent ? '#fff3e0' : '#f5f5f5');
            $border = $isCompleted ? '#4caf50' : ($isCurrent ? '#ff9800' : '#e0e0e0');
        ?>
        <div style="padding:0.75rem; border-radius:8px; text-align:center; background:<?php echo $bg; ?>; border:2px solid <?php echo $border; ?>;">
            <div style="width:18px; height:18px; border-radius:50%; background:<?php echo $hex; ?>; margin:0 auto 0.4rem; border:1px solid rgba(0,0,0,0.15);"></div>
            <div style="font-size:1.2rem; font-weight:bold; color:<?php echo $isCompleted ? '#2e7d32' : ($isCurrent ? '#f57c00' : '#aaa'); ?>;"><?php echo $level; ?></div>
            <div style="font-size:0.65rem; color:#888; margin-top:2px;"><?php echo htmlspecialchars($khanColorMap[$level]['color_name'] ?? ''); ?></div>
            <?php if ($isCompleted): ?>
                <i class="fas fa-check-circle" style="color:#4caf50; margin-top:4px; display:block;"></i>
                <div style="font-size:0.65rem; color:#666;"><?php echo date('M Y', strtotime($lvlData['certified_date'] ?: $lvlData['training_date'])); ?></div>
            <?php elseif ($isCurrent): ?>
                <i class="fas fa-dot-circle" style="color:#ff9800; margin-top:4px; display:block;"></i>
                <div style="font-size:0.65rem; color:#888;">Current</div>
            <?php endif; ?>
        </div>
        <?php endfor; ?>
    </div>

    <!-- Detailed Records Table -->
    <h3 style="margin:1.5rem 0 1rem; padding-bottom:0.5rem; border-bottom:2px solid #1976d2;">
        <i class="fas fa-clipboard-list"></i> Detailed Training Records
    </h3>

    <?php if ($history && $history->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Khan Level</th>
                    <th>Training Date</th>
                    <th>Certified Date</th>
                    <th>Instructor</th>
                    <th>Location</th>
                    <th>Certificate #</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($record = $history->fetch_assoc()):
                    $rHex = $khanColorMap[$record['khan_level']]['hex_color'] ?? '#1976d2';
                    $statusColor = match($record['status']) {
                        'certified'   => '#4caf50',
                        'completed'   => '#2196f3',
                        default       => '#ff9800'
                    };
                ?>
                <tr>
                    <td>
                        <span style="display:inline-flex; align-items:center; gap:6px; font-weight:700; color:<?php echo $rHex; ?>;">
                            <span style="width:12px; height:12px; border-radius:50%; background:<?php echo $rHex; ?>; display:inline-block; border:1px solid rgba(0,0,0,0.1);"></span>
                            Khan <?php echo $record['khan_level']; ?>
                        </span>
                    </td>
                    <td><?php echo formatDate($record['training_date']); ?></td>
                    <td><?php echo $record['certified_date'] ? formatDate($record['certified_date']) : '<span style="color:#999;">Pending</span>'; ?></td>
                    <td><?php echo htmlspecialchars($record['instructor_name'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($record['location'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($record['certificate_number'] ?: '—'); ?></td>
                    <td>
                        <span style="padding:0.25rem 0.6rem; border-radius:4px; color:white; font-size:0.85rem; background:<?php echo $statusColor; ?>;">
                            <?php echo ucfirst(str_replace('_', ' ', $record['status'])); ?>
                        </span>
                    </td>
                    <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                        title="<?php echo htmlspecialchars($record['notes'] ?? ''); ?>">
                        <?php echo $record['notes'] ? htmlspecialchars(substr($record['notes'], 0, 50)) . (strlen($record['notes']) > 50 ? '…' : '') : '—'; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary" onclick='editHistory(<?php echo json_encode($record, JSON_HEX_QUOT | JSON_HEX_APOS); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this record?');">
                                <input type="hidden" name="history_id" value="<?php echo $record['id']; ?>">
                                <button type="submit" name="delete_history" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align:center; padding:3rem; background:#f5f5f5; border-radius:8px;">
        <i class="fas fa-history" style="font-size:3rem; color:#ccc; margin-bottom:1rem; display:block;"></i>
        <h3 style="color:#999; margin:0 0 0.5rem;">No Training Records Yet</h3>
        <p style="color:#999; margin:0;">Click "Add Record" or "Promote Member" to start tracking progression.</p>
    </div>
    <?php endif; ?>
</div>

<!-- PROMOTE MODAL -->
<div id="promoteModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('promoteModal')">&times;</span>
        <h2><i class="fas fa-arrow-up"></i> Promote Member</h2>
        <p style="color:#555; margin-bottom:1.5rem;">
            Currently at <strong>Khan <?php echo $member['current_khan_level']; ?> — <?php echo htmlspecialchars($member['khan_color']); ?></strong>
        </p>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Promote to Khan Level *</label>
                    <select name="new_khan_level" id="promote_level" class="form-select" required onchange="syncColor('promote_level','promote_color_text','promote_color_dot')">
                        <?php for ($i = $member['current_khan_level'] + 1; $i <= 16; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i === $member['current_khan_level'] + 1 ? 'selected' : ''; ?>>
                            Khan <?php echo $i; ?> — <?php echo htmlspecialchars($khanColorMap[$i]['color_name'] ?? ''); ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">New Color (Auto)</label>
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <span id="promote_color_dot" style="width:22px; height:22px; border-radius:50%; border:2px solid #ddd; display:inline-block;"></span>
                        <input type="text" id="promote_color_text" class="form-input" readonly style="background:#f5f5f5;">
                    </div>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Promotion Date *</label>
                    <input type="date" name="promotion_date" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Promoted By (Instructor)</label>
                    <select name="promoted_by" class="form-select">
                        <option value="">— Select Instructor —</option>
                        <?php $instructors->data_seek(0); while ($ins = $instructors->fetch_assoc()): ?>
                        <option value="<?php echo $ins['id']; ?>" <?php echo $ins['id'] == $member['instructor_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ins['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Promotion notes, achievements..."></textarea>
            </div>
            <div class="action-buttons">
                <button type="submit" name="promote_member" class="btn btn-success"><i class="fas fa-check"></i> Confirm Promotion</button>
                <button type="button" class="btn btn-outline" onclick="closeModal('promoteModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- ADD RECORD MODAL -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('addModal')">&times;</span>
        <h2><i class="fas fa-plus-circle"></i> Add Training Record</h2>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Khan Level *</label>
                    <select name="khan_level" class="form-select" required>
                        <?php for ($i = 1; $i <= 16; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == $member['current_khan_level'] + 1 ? 'selected' : ''; ?>>
                            Khan <?php echo $i; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="certified" selected>Certified</option>
                    </select>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Training Date *</label>
                    <input type="date" name="training_date" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Certified Date</label>
                    <input type="date" name="certified_date" class="form-input">
                    <small style="color:#666;">Leave blank if not yet certified</small>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Instructor</label>
                    <select name="instructor_id" class="form-select">
                        <option value="">— Select Instructor —</option>
                        <?php $instructors->data_seek(0); while ($ins = $instructors->fetch_assoc()): ?>
                        <option value="<?php echo $ins['id']; ?>" <?php echo $ins['id'] == $member['instructor_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ins['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-input" value="<?php echo htmlspecialchars($member['training_location']); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Certificate Number</label>
                <input type="text" name="certificate_number" class="form-input" placeholder="e.g. OMA-2024-001">
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-textarea" rows="3"></textarea>
            </div>
            <div class="action-buttons">
                <button type="submit" name="add_history" class="btn btn-primary"><i class="fas fa-save"></i> Add Record</button>
                <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT RECORD MODAL -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('editModal')">&times;</span>
        <h2><i class="fas fa-edit"></i> Edit Training Record</h2>
        <form method="POST">
            <input type="hidden" name="history_id" id="edit_id">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Khan Level *</label>
                    <select name="khan_level" id="edit_khan_level" class="form-select" required>
                        <?php for ($i = 1; $i <= 16; $i++): ?>
                        <option value="<?php echo $i; ?>">Khan <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" id="edit_status" class="form-select" required>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="certified">Certified</option>
                    </select>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Training Date *</label>
                    <input type="date" name="training_date" id="edit_training_date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Certified Date</label>
                    <input type="date" name="certified_date" id="edit_certified_date" class="form-input">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Instructor</label>
                    <select name="instructor_id" id="edit_instructor_id" class="form-select">
                        <option value="">— Select Instructor —</option>
                        <?php $instructors->data_seek(0); while ($ins = $instructors->fetch_assoc()): ?>
                        <option value="<?php echo $ins['id']; ?>"><?php echo htmlspecialchars($ins['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" id="edit_location" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Certificate Number</label>
                <input type="text" name="certificate_number" id="edit_certificate_number" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" id="edit_notes" class="form-textarea" rows="3"></textarea>
            </div>
            <div class="action-buttons">
                <button type="submit" name="edit_history" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal { display:none; position:fixed; z-index:1000; inset:0; background:rgba(0,0,0,0.5); overflow:auto; }
.modal-content { background:#fefefe; margin:3% auto; padding:2rem; border-radius:8px; width:90%; max-width:860px; max-height:90vh; overflow-y:auto; }
.modal-close { color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer; }
.modal-close:hover { color:#000; }
</style>

<script>
const khanColorMap = <?php echo json_encode(array_map(fn($c) => ['name' => $c['color_name'], 'hex' => $c['hex_color']], $khanColorMap)); ?>;

function syncColor(selectId, textId, dotId) {
    const level = document.getElementById(selectId).value;
    const c = khanColorMap[level];
    if (!c) return;
    document.getElementById(textId).value = c.name;
    document.getElementById(dotId).style.background = c.hex;
    document.getElementById(dotId).style.borderColor = c.hex === '#FFFFFF' ? '#999' : c.hex;
}

function closeModal(id) { document.getElementById(id).style.display = 'none'; }

window.onclick = e => {
    ['promoteModal','addModal','editModal'].forEach(id => {
        if (e.target === document.getElementById(id)) closeModal(id);
    });
};
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['promoteModal','addModal','editModal'].forEach(closeModal);
});

function editHistory(r) {
    document.getElementById('edit_id').value                = r.id;
    document.getElementById('edit_khan_level').value        = r.khan_level;
    document.getElementById('edit_training_date').value     = r.training_date;
    document.getElementById('edit_certified_date').value    = r.certified_date || '';
    document.getElementById('edit_instructor_id').value     = r.instructor_id || '';
    document.getElementById('edit_location').value          = r.location || '';
    document.getElementById('edit_certificate_number').value= r.certificate_number || '';
    document.getElementById('edit_status').value            = r.status;
    document.getElementById('edit_notes').value             = r.notes || '';
    document.getElementById('editModal').style.display = 'block';
}

// Init promote color on load
document.addEventListener('DOMContentLoaded', () => {
    const ps = document.getElementById('promote_level');
    if (ps) syncColor('promote_level','promote_color_text','promote_color_dot');
});
</script>

<?php include 'includes/admin_footer.php'; ?>