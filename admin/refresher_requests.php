<?php
$page_title = "Refresher Requests";
require_once '../config/database.php';
requireAdmin();
require_once 'includes/activity_helper.php';

$conn    = getDbConnection();
$success = '';
$error   = '';

// ── HANDLE APPROVE / REJECT ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $req_id    = (int)$_POST['request_id'];
    $action    = $_POST['action'] ?? '';          // 'approve' or 'reject'
    $adm_notes = sanitize($_POST['admin_notes'] ?? '');
    $admin_name = $_SESSION['user_name'] ?? 'Admin';

    if (!in_array($action, ['approve','reject'])) {
        $error = 'Invalid action.';
    } else {
        // Load request + member info
        $req = $conn->query("
            SELECT rr.*, km.full_name, km.id as member_id, km.current_khan_level
            FROM refresher_requests rr
            JOIN khan_members km ON rr.member_id = km.id
            WHERE rr.id = $req_id AND rr.status = 'pending'
        ")->fetch_assoc();

        if (!$req) {
            $error = 'Request not found or already reviewed.';
        } else {
            $new_status  = $action === 'approve' ? 'approved' : 'rejected';
            $adm_esc     = $conn->real_escape_string($adm_notes);
            $admin_esc   = $conn->real_escape_string($admin_name);

            // Update the request record
            $conn->query("
                UPDATE refresher_requests
                SET status       = '$new_status',
                    reviewed_by  = '$admin_esc',
                    reviewed_at  = NOW(),
                    admin_notes  = '$adm_esc'
                WHERE id = $req_id
            ");

            if ($action === 'approve') {
                // Restore member to active + record a training history entry
                $mid = (int)$req['member_id'];
                $conn->query("UPDATE khan_members SET status = 'active' WHERE id = $mid");

                // Insert a refresher training record so the 6-month clock resets
                $conn->query("
                    INSERT INTO khan_training_history
                        (member_id, khan_level, training_date, certified_date, status, notes)
                    VALUES
                        ($mid, {$req['current_khan_level']}, CURDATE(), CURDATE(),
                         'certified',
                         'Refresher validated by admin. Member restored to active status.')
                ");

                $success = htmlspecialchars($req['full_name']) . ' has been approved and restored to Active.';
                logActivity($conn, 'refresher', 'khan_members', $mid, $req['full_name'],
                    'Refresher request approved. Member restored to active. Admin: ' . $admin_name .
                    ($adm_notes ? ' | Note: ' . $adm_notes : ''));
            } else {
                $success = 'Request for ' . htmlspecialchars($req['full_name']) . ' was rejected.';
                logActivity($conn, 'refresher', 'khan_members', (int)$req['member_id'], $req['full_name'],
                    'Refresher request rejected. Admin: ' . $admin_name .
                    ($adm_notes ? ' | Note: ' . $adm_notes : ''));
            }
        }
    }
}

// ── FETCH DATA ────────────────────────────────────────────────────────────────
$filter = $_GET['filter'] ?? 'pending';
$allowed = ['pending','approved','rejected','all'];
$where   = in_array($filter, $allowed) && $filter !== 'all'
           ? "WHERE rr.status = '$filter'"
           : "WHERE 1=1";

$requests = $conn->query("
    SELECT rr.*,
           km.full_name, km.current_khan_level, km.khan_color, km.training_location,
           i.name as instructor_name,
           u.serial_number, u.email
    FROM refresher_requests rr
    JOIN khan_members km ON rr.member_id = km.id
    LEFT JOIN instructors i ON km.instructor_id = i.id
    LEFT JOIN users u ON rr.user_id = u.id
    $where
    ORDER BY rr.submitted_at DESC
");

$counts = [
    'pending'  => $conn->query("SELECT COUNT(*) c FROM refresher_requests WHERE status='pending'")->fetch_assoc()['c'],
    'approved' => $conn->query("SELECT COUNT(*) c FROM refresher_requests WHERE status='approved'")->fetch_assoc()['c'],
    'rejected' => $conn->query("SELECT COUNT(*) c FROM refresher_requests WHERE status='rejected'")->fetch_assoc()['c'],
    'all'      => $conn->query("SELECT COUNT(*) c FROM refresher_requests")->fetch_assoc()['c'],
];

include 'includes/admin_header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="admin-section">
    <div class="section-header">
        <h2><i class="fas fa-sync-alt"></i> Refresher Validation Requests</h2>
    </div>

    <!-- Filter tabs -->
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.5rem;">
        <?php
        $tabs = ['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','all'=>'All'];
        $tab_colors = ['pending'=>'btn-warning','approved'=>'btn-success','rejected'=>'btn-danger','all'=>'btn-outline'];
        foreach ($tabs as $key => $label):
            $active = $filter === $key ? '' : 'btn-outline';
            $cls = $filter === $key ? $tab_colors[$key] : 'btn-outline';
        ?>
        <a href="?filter=<?php echo $key; ?>"
           class="btn btn-sm <?php echo $cls; ?>"
           style="<?php echo $filter===$key?'font-weight:700;':'opacity:0.7;'; ?>">
            <?php echo $label; ?>
            <?php if ($counts[$key] > 0): ?>
                <span style="background:rgba(0,0,0,0.15);color:inherit;padding:1px 7px;border-radius:999px;font-size:0.72rem;margin-left:4px;font-weight:700;">
                    <?php echo $counts[$key]; ?>
                </span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Submitted</th>
                    <th>Member</th>
                    <th>Khan Level</th>
                    <th>Instructor</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($requests->num_rows === 0): ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:3rem;color:#999;">
                        <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:0.75rem;opacity:0.3;"></i>
                        No <?php echo $filter !== 'all' ? $filter : ''; ?> requests found.
                    </td>
                </tr>
            <?php endif; ?>
            <?php while ($req = $requests->fetch_assoc()): ?>
                <?php
                $row_bg = $req['status']==='pending' ? 'background:#fffbf0;' : '';
                $badge_map = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'];
                $badge = $badge_map[$req['status']] ?? 'secondary';
                ?>
                <tr style="<?php echo $row_bg; ?>">
                    <td style="white-space:nowrap;font-size:0.82rem;color:#666;">
                        <?php echo date('M d, Y', strtotime($req['submitted_at'])); ?><br>
                        <span style="font-size:0.75rem;"><?php echo date('g:i A', strtotime($req['submitted_at'])); ?></span>
                    </td>
                    <td>
                        <div style="font-weight:600;"><?php echo htmlspecialchars($req['full_name']); ?></div>
                        <div style="font-size:0.8rem;color:#888;"><?php echo htmlspecialchars($req['serial_number']); ?></div>
                        <div style="font-size:0.8rem;color:#888;"><?php echo htmlspecialchars($req['email']); ?></div>
                    </td>
                    <td>
                        <strong style="color:#388e3c;">Khan <?php echo $req['current_khan_level']; ?></strong><br>
                        <small style="color:#888;"><?php echo htmlspecialchars($req['khan_color']); ?></small>
                    </td>
                    <td style="font-size:0.88rem;"><?php echo htmlspecialchars($req['instructor_name'] ?: '—'); ?></td>
                    <td style="max-width:220px;">
                        <span style="font-size:0.85rem;color:#444;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer;"
                              title="<?php echo htmlspecialchars($req['message']); ?>"
                              onclick="this.style.whiteSpace=this.style.whiteSpace==='normal'?'nowrap':'normal'">
                            <?php echo htmlspecialchars($req['message']); ?>
                        </span>
                        <?php if ($req['admin_notes']): ?>
                        <span style="font-size:0.78rem;color:#888;margin-top:3px;display:block;">
                            <i class="fas fa-reply"></i> <?php echo htmlspecialchars($req['admin_notes']); ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $badge; ?>"
                              style="padding:0.3rem 0.7rem;border-radius:4px;font-size:0.82rem;">
                            <?php echo ucfirst($req['status']); ?>
                        </span>
                        <?php if ($req['reviewed_by']): ?>
                        <div style="font-size:0.75rem;color:#888;margin-top:3px;">
                            by <?php echo htmlspecialchars($req['reviewed_by']); ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($req['status'] === 'pending'): ?>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-success"
                                    onclick="openReview(<?php echo $req['id']; ?>, '<?php echo addslashes($req['full_name']); ?>', 'approve')">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn-sm btn-danger"
                                    onclick="openReview(<?php echo $req['id']; ?>, '<?php echo addslashes($req['full_name']); ?>', 'reject')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </div>
                        <?php else: ?>
                            <span style="font-size:0.8rem;color:#aaa;">Reviewed</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:480px;">
        <span class="modal-close" onclick="document.getElementById('reviewModal').style.display='none'">&times;</span>
        <h2 id="reviewTitle" style="margin-top:0;font-size:1.15rem;"></h2>
        <form method="POST" id="reviewForm">
            <input type="hidden" name="request_id" id="review_request_id">
            <input type="hidden" name="action"     id="review_action">

            <div class="form-group">
                <label class="form-label">Admin Notes / Response <span style="font-weight:400;color:#999;">(optional)</span></label>
                <textarea name="admin_notes" id="review_notes" class="form-textarea" rows="4"
                          placeholder="Add a message for the member (e.g. reason for rejection, next steps)..."></textarea>
            </div>

            <div id="approveWarning" style="display:none;background:#e8f5e9;border-left:4px solid #4caf50;padding:0.9rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:0.88rem;color:#2e7d32;">
                <i class="fas fa-check-circle"></i>
                This will set the member's status back to <strong>Active</strong> and log a training record so their 6-month clock resets.
            </div>
            <div id="rejectWarning" style="display:none;background:#ffebee;border-left:4px solid #f44336;padding:0.9rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:0.88rem;color:#c62828;">
                <i class="fas fa-exclamation-circle"></i>
                The member will remain in <strong>Needs Refresher</strong> status and can submit another request later.
            </div>

            <div class="action-buttons">
                <button type="submit" id="reviewSubmitBtn" class="btn btn-primary">
                    <i class="fas fa-check"></i> Confirm
                </button>
                <button type="button" class="btn btn-outline"
                        onclick="document.getElementById('reviewModal').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal { display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background:rgba(0,0,0,0.5);animation:fadeIn 0.25s; }
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal-content { background:#fff;margin:6% auto;padding:2rem;border-radius:8px;width:92%;max-width:500px;box-shadow:0 4px 24px rgba(0,0,0,0.18);animation:slideDown 0.25s; }
@keyframes slideDown{from{transform:translateY(-20px);opacity:0}to{transform:translateY(0);opacity:1}}
.modal-close { color:#aaa;float:right;font-size:26px;font-weight:bold;cursor:pointer; }
.modal-close:hover { color:#333; }
.form-group { margin-bottom:1.2rem; }
.form-label { display:block;font-weight:600;margin-bottom:0.5rem;color:#444; }
.form-textarea { width:100%;padding:0.75rem;border:1px solid #ddd;border-radius:6px;font-family:inherit;font-size:0.95rem;resize:vertical; }
.btn-warning  { background:#ff9800;color:#fff;border-color:#ff9800; }
.btn-warning:hover { background:#e65100; }
</style>

<script>
function openReview(id, name, action) {
    document.getElementById('review_request_id').value = id;
    document.getElementById('review_action').value     = action;
    document.getElementById('review_notes').value      = '';

    const isApprove = action === 'approve';
    document.getElementById('reviewTitle').textContent =
        (isApprove ? '✅ Approve' : '❌ Reject') + ' request for ' + name;

    document.getElementById('approveWarning').style.display = isApprove ? 'block' : 'none';
    document.getElementById('rejectWarning').style.display  = isApprove ? 'none'  : 'block';

    const btn = document.getElementById('reviewSubmitBtn');
    btn.className = 'btn ' + (isApprove ? 'btn-success' : 'btn-danger');
    btn.innerHTML = isApprove
        ? '<i class="fas fa-check"></i> Approve & Restore Active'
        : '<i class="fas fa-times"></i> Reject Request';

    document.getElementById('reviewModal').style.display = 'block';
}

window.onclick = e => {
    if (e.target === document.getElementById('reviewModal'))
        document.getElementById('reviewModal').style.display = 'none';
};
</script>

<?php include 'includes/admin_footer.php'; ?>