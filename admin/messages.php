<?php
$page_title = "Messages & Applications";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error   = '';

if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

// ══════════════════════════════════════════════════════════════
// CONTACT MESSAGES — POST HANDLERS
// ══════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_mark_read'])) {
    $id = (int)$_POST['id'];
    $conn->query("UPDATE contact_messages SET status='read' WHERE id=$id AND status='new'");
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id          = (int)$_POST['id'];
    $status      = $conn->real_escape_string($_POST['status']);
    $admin_notes = sanitize($_POST['admin_notes']);
    $stmt = $conn->prepare("UPDATE contact_messages SET status=?, admin_notes=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $admin_notes, $id);
    if ($stmt->execute()) {
        $success = 'Message status updated successfully!';
        require_once 'includes/activity_helper.php';
        logActivity($conn, 'edit', 'contact_messages', $id, 'Message #'.$id,
            'Status changed to: '.$status.($admin_notes ? ' | Note: '.$admin_notes : ''));
    } else {
        $error = 'Failed to update status';
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $id = (int)$_POST['id'];
    require_once 'includes/activity_helper.php';
    $fullRow = $conn->query("SELECT * FROM contact_messages WHERE id=$id")->fetch_assoc();
    if ($fullRow) {
        archiveRecord($conn, 'contact_messages', $id, $fullRow['name'].' — '.$fullRow['subject'], $fullRow);
        logActivity($conn, 'delete', 'contact_messages', $id, $fullRow['name'], 'Subject: '.$fullRow['subject']);
    }
    if ($conn->query("DELETE FROM contact_messages WHERE id=$id")) {
        $success = 'Message deleted permanently.';
    } else {
        $error = 'Failed to delete message';
    }
}

// ══════════════════════════════════════════════════════════════
// ENROLLMENT APPLICATIONS — POST HANDLERS
// ══════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_mark_reviewed'])) {
    $id = (int)$_POST['id'];
    $conn->query("UPDATE enrollment_applications SET status='reviewed' WHERE id=$id AND status='pending'");
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_enrollment_status'])) {
    $id          = (int)$_POST['id'];
    $status      = $conn->real_escape_string($_POST['enroll_status']);
    $admin_notes = sanitize($_POST['enroll_notes']);
    $stmt = $conn->prepare("UPDATE enrollment_applications SET status=?, admin_notes=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $admin_notes, $id);
    $success = $stmt->execute() ? 'Enrollment application updated!' : 'Failed to update';
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_enrollment'])) {
    $id = (int)$_POST['id'];
    if ($conn->query("DELETE FROM enrollment_applications WHERE id=$id")) {
        $success = 'Enrollment application deleted.';
    } else {
        $error = 'Failed to delete application';
    }
}

// ══════════════════════════════════════════════════════════════
// FETCH DATA
// ══════════════════════════════════════════════════════════════

$active_tab = (isset($_GET['tab']) && $_GET['tab'] === 'enrollment') ? 'enrollment' : 'inquiry';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$allowed_filters = ['new', 'read', 'replied', 'archived'];
$where = in_array($filter, $allowed_filters) ? "WHERE status='$filter'" : '';
$messages = $conn->query("SELECT * FROM contact_messages $where ORDER BY created_at DESC");
$msg_counts = [
    'all'     => $conn->query("SELECT COUNT(*) AS c FROM contact_messages")->fetch_assoc()['c'],
    'new'     => $conn->query("SELECT COUNT(*) AS c FROM contact_messages WHERE status='new'")->fetch_assoc()['c'],
    'read'    => $conn->query("SELECT COUNT(*) AS c FROM contact_messages WHERE status='read'")->fetch_assoc()['c'],
    'replied' => $conn->query("SELECT COUNT(*) AS c FROM contact_messages WHERE status='replied'")->fetch_assoc()['c'],
];

$enroll_filter = isset($_GET['enroll_filter']) ? $_GET['enroll_filter'] : 'all';
$allowed_ef = ['pending','reviewed','enrolled','rejected'];
$ewhere = in_array($enroll_filter, $allowed_ef) ? "WHERE status='$enroll_filter'" : '';
$enrollments = $conn->query("SELECT * FROM enrollment_applications $ewhere ORDER BY created_at DESC");
$enroll_counts = [
    'all'      => $conn->query("SELECT COUNT(*) AS c FROM enrollment_applications")->fetch_assoc()['c'],
    'pending'  => $conn->query("SELECT COUNT(*) AS c FROM enrollment_applications WHERE status='pending'")->fetch_assoc()['c'],
    'reviewed' => $conn->query("SELECT COUNT(*) AS c FROM enrollment_applications WHERE status='reviewed'")->fetch_assoc()['c'],
    'enrolled' => $conn->query("SELECT COUNT(*) AS c FROM enrollment_applications WHERE status='enrolled'")->fetch_assoc()['c'],
    'rejected' => $conn->query("SELECT COUNT(*) AS c FROM enrollment_applications WHERE status='rejected'")->fetch_assoc()['c'],
];

include 'includes/admin_header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

<div class="admin-section">
    <div class="section-header">
        <h2>Messages &amp; Applications</h2>
    </div>

    <!-- ── Top-level Tab Nav ── -->
    <div style="display:flex; gap:0; border-bottom:2px solid #cc0000; margin-bottom:1.5rem;">
        <a href="?tab=inquiry"
           style="padding:.7rem 1.6rem; text-decoration:none; font-weight:600; font-size:.95rem;
                  color:<?php echo $active_tab==='inquiry' ? '#cc0000' : '#555'; ?>;
                  background:<?php echo $active_tab==='inquiry' ? '#fff' : '#f5f5f5'; ?>;
                  border:2px solid <?php echo $active_tab==='inquiry' ? '#cc0000' : '#ddd'; ?>;
                  border-bottom:<?php echo $active_tab==='inquiry' ? '2px solid #fff' : '2px solid #ddd'; ?>;
                  border-radius:6px 6px 0 0; position:relative; top:2px;">
            ✉️ Inquiries
            <?php if ($msg_counts['new'] > 0): ?>
                <span style="background:#cc0000;color:#fff;border-radius:12px;padding:1px 7px;font-size:.75rem;margin-left:6px;">
                    <?php echo $msg_counts['new']; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="?tab=enrollment"
           style="padding:.7rem 1.6rem; text-decoration:none; font-weight:600; font-size:.95rem;
                  color:<?php echo $active_tab==='enrollment' ? '#cc0000' : '#555'; ?>;
                  background:<?php echo $active_tab==='enrollment' ? '#fff' : '#f5f5f5'; ?>;
                  border:2px solid <?php echo $active_tab==='enrollment' ? '#cc0000' : '#ddd'; ?>;
                  border-bottom:<?php echo $active_tab==='enrollment' ? '2px solid #fff' : '2px solid #ddd'; ?>;
                  border-radius:6px 6px 0 0; position:relative; top:2px; margin-left:4px;">
            📋 Enrollments
            <?php if ($enroll_counts['pending'] > 0): ?>
                <span style="background:#cc0000;color:#fff;border-radius:12px;padding:1px 7px;font-size:.75rem;margin-left:6px;">
                    <?php echo $enroll_counts['pending']; ?>
                </span>
            <?php endif; ?>
        </a>
    </div>

<?php if ($active_tab === 'inquiry'): ?>
<!-- ════════════════════════════════════════ INQUIRY TAB ════ -->

    <div style="margin-bottom:1.5rem; display:flex; gap:.75rem; flex-wrap:wrap;">
        <?php
        $filter_labels = ['all'=>'All','new'=>'New','read'=>'Read','replied'=>'Replied'];
        foreach ($filter_labels as $f => $label):
            $count = $msg_counts[$f] ?? 0;
            $active_class = ($filter === $f) ? 'btn-primary' : 'btn-outline';
        ?>
        <a href="?tab=inquiry&filter=<?php echo $f; ?>" class="btn <?php echo $active_class; ?>">
            <?php echo $label; ?> (<?php echo $count; ?>)
        </a>
        <?php endforeach; ?>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>Date</th><th>Name</th><th>Contact</th><th>Subject</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($msg = $messages->fetch_assoc()): ?>
                <tr id="row-msg-<?php echo $msg['id']; ?>"
                    style="<?php echo $msg['status'] === 'new' ? 'background:#f0f9ff; font-weight:600;' : ''; ?>">
                    <td style="white-space:nowrap; font-size:.9em; color:#666;">
                        <?php echo date('M d, Y', strtotime($msg['created_at'])); ?>
                    </td>
                    <td>
                        <?php if ($msg['status'] === 'new'): ?>
                            <span style="display:inline-block;width:8px;height:8px;background:#cc0000;border-radius:50%;margin-right:5px;"></span>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($msg['name']); ?>
                    </td>
                    <td style="font-size:.9em;">
                        <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color:#007bff;text-decoration:none;">
                            <?php echo htmlspecialchars($msg['email']); ?>
                        </a>
                        <?php if ($msg['phone']): ?><br><span style="color:#666;"><?php echo htmlspecialchars($msg['phone']); ?></span><?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                    <td>
                        <?php $bc = ($msg['status']==='new') ? 'primary' : (($msg['status']==='replied') ? 'success' : 'secondary'); ?>
                        <span class="badge badge-<?php echo $bc; ?>" id="badge-msg-<?php echo $msg['id']; ?>">
                            <?php echo ucfirst($msg['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary" onclick='viewMessage(<?php echo json_encode($msg); ?>)'>View</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message permanently?');">
                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" name="delete_message" class="btn btn-sm btn-danger">&times;</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
<!-- ════════════════════════════════════════ ENROLLMENT TAB ════ -->

    <div style="margin-bottom:1.5rem; display:flex; gap:.75rem; flex-wrap:wrap;">
        <?php
        $ef_labels = ['all'=>'All','pending'=>'Pending','reviewed'=>'Reviewed','enrolled'=>'Enrolled','rejected'=>'Rejected'];
        foreach ($ef_labels as $ef => $label):
            $count = $enroll_counts[$ef] ?? 0;
            $active_class = ($enroll_filter === $ef) ? 'btn-primary' : 'btn-outline';
        ?>
        <a href="?tab=enrollment&enroll_filter=<?php echo $ef; ?>" class="btn <?php echo $active_class; ?>">
            <?php echo $label; ?> (<?php echo $count; ?>)
        </a>
        <?php endforeach; ?>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>Date</th><th>Name</th><th>Contact</th><th>Goals</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($app = $enrollments->fetch_assoc()): ?>
                <tr id="row-enroll-<?php echo $app['id']; ?>"
                    style="<?php echo $app['status'] === 'pending' ? 'background:#fffbf0; font-weight:600;' : ''; ?>">
                    <td style="white-space:nowrap; font-size:.9em; color:#666;">
                        <?php echo date('M d, Y', strtotime($app['created_at'])); ?>
                    </td>
                    <td>
                        <?php if ($app['status'] === 'pending'): ?>
                            <span style="display:inline-block;width:8px;height:8px;background:#e08000;border-radius:50%;margin-right:5px;"></span>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($app['last_name'].', '.$app['first_name'].($app['middle_name'] ? ' '.$app['middle_name'] : '')); ?>
                    </td>
                    <td style="font-size:.9em;">
                        <a href="mailto:<?php echo htmlspecialchars($app['email']); ?>" style="color:#007bff;text-decoration:none;">
                            <?php echo htmlspecialchars($app['email']); ?>
                        </a>
                        <br><span style="color:#666;"><?php echo htmlspecialchars($app['cellphone']); ?></span>
                    </td>
                    <td style="font-size:.85em; color:#555; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?php echo htmlspecialchars(mb_strimwidth($app['goals'] ?? '—', 0, 60, '…')); ?>
                    </td>
                    <td>
                        <?php $ebc = ['pending'=>'primary','reviewed'=>'secondary','enrolled'=>'success','rejected'=>'danger']; ?>
                        <span class="badge badge-<?php echo $ebc[$app['status']] ?? 'secondary'; ?>" id="badge-enroll-<?php echo $app['id']; ?>">
                            <?php echo ucfirst($app['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary" onclick='viewEnrollment(<?php echo json_encode($app); ?>)'>View</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this application permanently?');">
                                <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                <button type="submit" name="delete_enrollment" class="btn btn-sm btn-danger">&times;</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</div>

<!-- ══════════════════════════════════════ MODAL: Inquiry ══ -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('viewModal')">&times;</span>
        <h2 style="margin-top:0;">Message Details</h2>
        <div id="messageDetails"></div>
        <form method="POST" style="margin-top:2rem; border-top:1px solid #eee; padding-top:1.5rem;">
            <input type="hidden" name="id" id="msg_id">
            <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                <div style="flex:1; min-width:280px;">
                    <div class="form-group">
                        <label class="form-label">Internal Status</label>
                        <select name="status" id="msg_status" class="form-select">
                            <option value="new">New</option>
                            <option value="read">Read</option>
                            <option value="replied">Replied</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Admin Notes (Private)</label>
                        <textarea name="admin_notes" id="msg_notes" class="form-textarea" placeholder="Internal notes..."></textarea>
                    </div>
                </div>
                <div style="flex:1; min-width:280px; border-left:1px solid #eee; padding-left:2rem;">
                    <label class="form-label">Reply to Customer</label>
                    <a id="gmailReplyBtn" href="#" target="_blank" onclick="markAsReplied()"
                       class="btn btn-danger btn-lg"
                       style="text-decoration:none; display:flex; align-items:center; justify-content:center;
                              gap:10px; width:100%; background:#ea4335; border-color:#ea4335; color:#fff;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        Reply via Gmail
                    </a>
                    <p style="font-size:.85rem; color:#666; margin-top:.8rem; text-align:center;">
                        Opens Gmail with subject &amp; message pre-filled.
                    </p>
                </div>
            </div>
            <div class="action-buttons" style="margin-top:1.5rem; text-align:right; border-top:1px solid #eee; padding-top:1rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('viewModal')">Close</button>
                <button type="submit" name="update_status" class="btn btn-success">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════ MODAL: Enrollment ══ -->
<div id="enrollModal" class="modal">
    <div class="modal-content" style="max-width:980px;">
        <span class="modal-close" onclick="closeModal('enrollModal')">&times;</span>

        <!-- Modal header with action buttons -->
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem;">
            <h2 style="margin:0; color:#cc0000;">Enrollment Application</h2>
            <!-- ★ Action buttons: Gmail, PDF, XLSX -->
            <div style="display:flex; gap:.6rem; flex-wrap:wrap; align-items:center;">
                <a id="enrollGmailBtn" href="#" target="_blank"
                   style="display:inline-flex; align-items:center; gap:7px; padding:.5rem 1rem;
                          background:#ea4335; color:#fff; text-decoration:none; border-radius:5px;
                          font-size:.85rem; font-weight:600; transition:opacity .2s;"
                   onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    Reply via Gmail
                </a>
                <button onclick="downloadEnrollPDF()"
                        style="display:inline-flex; align-items:center; gap:7px; padding:.5rem 1rem;
                               background:#c0392b; color:#fff; border:none; border-radius:5px;
                               font-size:.85rem; font-weight:600; cursor:pointer; transition:opacity .2s;"
                        onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/></svg>
                    Download PDF
                </button>
                <button onclick="downloadEnrollXLSX()"
                        style="display:inline-flex; align-items:center; gap:7px; padding:.5rem 1rem;
                               background:#1d6f42; color:#fff; border:none; border-radius:5px;
                               font-size:.85rem; font-weight:600; cursor:pointer; transition:opacity .2s;"
                        onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
                    Download Excel
                </button>
            </div>
        </div>

        <div id="enrollDetails"></div>

        <form method="POST" style="margin-top:1.5rem; border-top:1px solid #eee; padding-top:1.5rem;">
            <input type="hidden" name="id" id="enroll_id">
            <div style="display:flex; gap:1.5rem; flex-wrap:wrap; align-items:flex-start;">
                <div class="form-group" style="flex:1; min-width:220px;">
                    <label class="form-label">Application Status</label>
                    <select name="enroll_status" id="enroll_status" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="enrolled">Enrolled</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="form-group" style="flex:2; min-width:280px;">
                    <label class="form-label">Admin Notes (Private)</label>
                    <textarea name="enroll_notes" id="enroll_notes" class="form-textarea" placeholder="Internal notes about this applicant..."></textarea>
                </div>
            </div>
            <div class="action-buttons" style="margin-top:1.5rem; text-align:right; border-top:1px solid #eee; padding-top:1rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('enrollModal')">Close</button>
                <button type="submit" name="update_enrollment_status" class="btn btn-success">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%;
         overflow:auto; background:rgba(0,0,0,.5); backdrop-filter:blur(2px); }
.modal-content { background:#fff; margin:4vh auto; padding:2rem; border-radius:8px;
                 width:92%; max-width:900px; box-shadow:0 4px 25px rgba(0,0,0,.15);
                 animation:slideDown .3s ease-out; }
@keyframes slideDown { from{transform:translateY(-20px);opacity:0} to{transform:translateY(0);opacity:1} }
.modal-close { color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer; }
.modal-close:hover { color:#333; }
.form-group { margin-bottom:1rem; }
.form-label { display:block; margin-bottom:.4rem; font-weight:600; color:#444; font-size:.9rem; }
.form-select,.form-textarea { width:100%; padding:.6rem; border:1px solid #ddd; border-radius:4px; font-family:inherit; font-size:.95rem; }
.form-textarea { min-height:80px; resize:vertical; }
.btn-lg { padding:.8rem 1.5rem; font-size:1.05rem; font-weight:500; }
.msg-box { background:#f8f9fa; padding:1.5rem; border-radius:6px; border-left:4px solid #ea4335; margin-bottom:1rem; }
.msg-meta { color:#555; font-size:.95rem; line-height:1.7; }
.msg-body { margin-top:1rem; white-space:pre-wrap; color:#333; font-size:1rem; line-height:1.6; }
.enroll-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:.6rem 1.2rem; }
.enroll-field { font-size:.88rem; padding:.3rem 0; }
.enroll-field span.lbl { font-weight:600; color:#555; display:block; font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; margin-bottom:2px; }
.enroll-section { font-weight:700; font-size:.75rem; text-transform:uppercase; letter-spacing:.06em;
                  color:#cc0000; border-bottom:2px solid #f0e0e0; padding-bottom:.4rem;
                  margin:1.1rem 0 .6rem; grid-column:1/-1; }
@media (max-width:768px) {
    .modal-content { margin:5% auto; width:95%; padding:1rem; }
}
</style>

<!-- SheetJS for XLSX export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
// ── Shared state ──────────────────────────────────────────────
let _currentEnrollment = null;

// ── Helpers ───────────────────────────────────────────────────
function escHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function f(v) { return v ? escHtml(v) : '<em style="color:#bbb">—</em>'; }
function yn(v) { return v == 1 ? 'Yes' : 'No'; }

function closeModal(id) { document.getElementById(id).style.display = 'none'; }
window.onclick = function(e) {
    ['viewModal','enrollModal'].forEach(id => {
        if (e.target === document.getElementById(id)) closeModal(id);
    });
};

// ── Inquiry modal ─────────────────────────────────────────────
function viewMessage(msg) {
    if (msg.status === 'new') {
        fetch('', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'ajax_mark_read=1&id='+msg.id
        }).then(() => {
            const row = document.getElementById('row-msg-'+msg.id);
            const badge = document.getElementById('badge-msg-'+msg.id);
            if (row)   { row.style.background=''; row.style.fontWeight=''; }
            if (badge) { badge.textContent='Read'; badge.className='badge badge-secondary'; }
            const dot = row ? row.querySelector('span[style*="border-radius:50%"]') : null;
            if (dot) dot.remove();
        });
        msg.status = 'read';
    }

    document.getElementById('msg_id').value     = msg.id;
    document.getElementById('msg_status').value = msg.status;
    document.getElementById('msg_notes').value  = msg.admin_notes || '';

    const subject = encodeURIComponent('Re: '+msg.subject);
    const body    = encodeURIComponent('\n\n\n--------------------------------\nOn '+msg.created_at+', '+msg.name+' wrote:\n\n'+msg.message);
    document.getElementById('gmailReplyBtn').href =
        'https://mail.google.com/mail/?view=cm&fs=1&to='+encodeURIComponent(msg.email)+'&su='+subject+'&body='+body;

    document.getElementById('messageDetails').innerHTML = `
        <div class="msg-box">
            <div class="msg-meta">
                <strong>From:</strong> ${escHtml(msg.name)} &lt;${escHtml(msg.email)}&gt;<br>
                ${msg.phone ? '<strong>Phone:</strong> '+escHtml(msg.phone)+'<br>' : ''}
                <strong>Received:</strong> ${escHtml(msg.created_at)}<br>
                <strong>Subject:</strong> ${escHtml(msg.subject)}
            </div>
            <hr style="margin:15px 0;border:0;border-top:1px solid #e0e0e0;">
            <div class="msg-body">${escHtml(msg.message)}</div>
        </div>`;

    document.getElementById('viewModal').style.display = 'block';
}

function markAsReplied() {
    const s = document.getElementById('msg_status');
    if (s.value === 'new' || s.value === 'read') s.value = 'replied';
}

// ── Enrollment modal ──────────────────────────────────────────
function viewEnrollment(app) {
    _currentEnrollment = app; // store for PDF/XLSX export

    if (app.status === 'pending') {
        fetch('', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'ajax_mark_reviewed=1&id='+app.id
        }).then(() => {
            const row = document.getElementById('row-enroll-'+app.id);
            const badge = document.getElementById('badge-enroll-'+app.id);
            if (row)   { row.style.background=''; row.style.fontWeight=''; }
            if (badge) { badge.textContent='Reviewed'; badge.className='badge badge-secondary'; }
            const dot = row ? row.querySelector('span[style*="border-radius:50%"]') : null;
            if (dot) dot.remove();
        });
        app.status = 'reviewed';
    }

    document.getElementById('enroll_id').value     = app.id;
    document.getElementById('enroll_status').value = app.status;
    document.getElementById('enroll_notes').value  = app.admin_notes || '';

    // ── Gmail reply button ────────────────────────────────────
    const fullName = [app.first_name, app.middle_name, app.last_name].filter(Boolean).join(' ');
    const gmailSubject = encodeURIComponent('Re: Your Enrollment Application — Oriental Muayboran Academy');
    const gmailBody    = encodeURIComponent(
        'Dear ' + fullName + ',\n\n' +
        'Thank you for submitting your enrollment application to Oriental Muayboran Academy.\n\n' +
        '[Write your message here]\n\n' +
        'Best regards,\nOriental Muayboran Academy\norientalmuayboranacademy@gmail.com'
    );
    document.getElementById('enrollGmailBtn').href =
        'https://mail.google.com/mail/?view=cm&fs=1&to=' + encodeURIComponent(app.email) +
        '&su=' + gmailSubject + '&body=' + gmailBody;

    // ── Render details grid ───────────────────────────────────
    document.getElementById('enrollDetails').innerHTML = `
        <div class="enroll-grid">
            <div class="enroll-section">Personal Information</div>
            <div class="enroll-field"><span class="lbl">Last Name</span>${f(app.last_name)}</div>
            <div class="enroll-field"><span class="lbl">First Name</span>${f(app.first_name)}</div>
            <div class="enroll-field"><span class="lbl">Middle Name</span>${f(app.middle_name)}</div>
            <div class="enroll-field"><span class="lbl">Date of Birth</span>${f(app.date_of_birth)}</div>
            <div class="enroll-field"><span class="lbl">Place of Birth</span>${f(app.place_of_birth)}</div>
            <div class="enroll-field"><span class="lbl">Sex</span>${f(app.sex)}</div>
            <div class="enroll-field"><span class="lbl">Civil Status</span>${f(app.civil_status)}</div>
            <div class="enroll-field"><span class="lbl">Religion</span>${f(app.religion)}</div>
            <div class="enroll-field"><span class="lbl">Citizenship</span>${f(app.citizenship)}</div>
            <div class="enroll-field"><span class="lbl">Occupation</span>${f(app.occupation)}</div>
            <div class="enroll-field" style="grid-column:span 2"><span class="lbl">Address</span>${f(app.address)}</div>

            <div class="enroll-section">Contact &amp; Health</div>
            <div class="enroll-field"><span class="lbl">Cellphone</span>${f(app.cellphone)}</div>
            <div class="enroll-field"><span class="lbl">Email</span>${f(app.email)}</div>
            <div class="enroll-field"><span class="lbl">Blood Type</span>${f(app.blood_type)}</div>
            <div class="enroll-field"><span class="lbl">Weight (kg)</span>${f(app.weight_kg)}</div>
            <div class="enroll-field"><span class="lbl">Height (cm)</span>${f(app.height_cm)}</div>
            <div class="enroll-field" style="grid-column:span 3"><span class="lbl">Injury / Medical Condition</span>${f(app.injury_or_condition)}</div>

            <div class="enroll-section">Emergency Contact</div>
            <div class="enroll-field"><span class="lbl">Name</span>${f(app.emergency_name)}</div>
            <div class="enroll-field"><span class="lbl">Relationship</span>${f(app.emergency_relation)}</div>
            <div class="enroll-field"><span class="lbl">Phone</span>${f(app.emergency_phone)}</div>
            <div class="enroll-field" style="grid-column:span 2"><span class="lbl">Address</span>${f(app.emergency_address)}</div>

            <div class="enroll-section">Training Background</div>
            <div class="enroll-field" style="grid-column:span 2"><span class="lbl">Goals</span>${f(app.goals)}</div>
            <div class="enroll-field"><span class="lbl">How did they know</span>${f(app.how_did_you_know)}</div>
            <div class="enroll-field"><span class="lbl">Previous Khan</span>${yn(app.previous_khan)}</div>
            ${app.previous_khan == 1 ? `
            <div class="enroll-field"><span class="lbl">Khan Level</span>${f(app.khan_level)}</div>
            <div class="enroll-field"><span class="lbl">Kru</span>${f(app.kru_name)}</div>` : ''}

            <div class="enroll-section">Waiver &amp; Application</div>
            <div class="enroll-field"><span class="lbl">Agreed to Waiver</span>${yn(app.waiver_agreed)}</div>
            <div class="enroll-field"><span class="lbl">Current Status</span>${f(app.status)}</div>
            <div class="enroll-field"><span class="lbl">Submitted</span>${f(app.created_at)}</div>
        </div>`;

    document.getElementById('enrollModal').style.display = 'block';
}

// ── PDF Export ────────────────────────────────────────────────
function downloadEnrollPDF() {
    const app = _currentEnrollment;
    if (!app) return;

    const fullName = [app.first_name, app.middle_name, app.last_name].filter(Boolean).join(' ');
    const yn2 = v => v == 1 ? 'Yes' : 'No';
    const val  = v => (v && String(v).trim()) ? String(v).trim() : '—';

    const html = `<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Enrollment — ${fullName}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 11px; color: #222; padding: 28px 32px; }
  .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #cc0000; padding-bottom: 12px; }
  .header h1 { font-size: 15px; color: #cc0000; letter-spacing: 1px; text-transform: uppercase; }
  .header h2 { font-size: 12px; font-weight: normal; color: #555; margin-top: 3px; }
  .header .meta { font-size: 10px; color: #888; margin-top: 4px; }
  .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em;
                   color: #cc0000; border-bottom: 1px solid #f0d0d0; padding-bottom: 3px;
                   margin: 14px 0 7px; }
  .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px 14px; }
  .field { }
  .field .lbl { font-size: 9px; font-weight: bold; color: #666; text-transform: uppercase;
                letter-spacing: .04em; display: block; }
  .field .val { font-size: 11px; color: #222; margin-top: 1px; }
  .full { grid-column: 1 / -1; }
  .status-badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 10px;
                  font-weight: bold; text-transform: uppercase; background: #eee; color: #333; }
  .status-pending  { background: #fff3cd; color: #856404; }
  .status-reviewed { background: #e2e3e5; color: #383d41; }
  .status-enrolled { background: #d4edda; color: #155724; }
  .status-rejected { background: #f8d7da; color: #721c24; }
  .waiver-box { margin-top: 16px; border: 1px solid #ddd; border-left: 3px solid #cc0000;
                padding: 8px 10px; font-size: 9.5px; color: #555; line-height: 1.5; }
  .footer { margin-top: 20px; border-top: 1px solid #eee; padding-top: 8px;
            font-size: 9px; color: #aaa; text-align: center; }
</style>
</head>
<body>
<div class="header">
  <h1>Oriental Muayboran Academy</h1>
  <h2>Enrollment Application Form</h2>
  <div class="meta">
    Application ID: #${val(app.id)} &nbsp;|&nbsp;
    Submitted: ${val(app.created_at)} &nbsp;|&nbsp;
    Status: <span class="status-badge status-${val(app.status)}">${val(app.status)}</span>
  </div>
</div>

<div class="section-title">Personal Information</div>
<div class="grid">
  <div class="field"><span class="lbl">Last Name</span><span class="val">${val(app.last_name)}</span></div>
  <div class="field"><span class="lbl">First Name</span><span class="val">${val(app.first_name)}</span></div>
  <div class="field"><span class="lbl">Middle Name</span><span class="val">${val(app.middle_name)}</span></div>
  <div class="field"><span class="lbl">Date of Birth</span><span class="val">${val(app.date_of_birth)}</span></div>
  <div class="field"><span class="lbl">Place of Birth</span><span class="val">${val(app.place_of_birth)}</span></div>
  <div class="field"><span class="lbl">Sex</span><span class="val">${val(app.sex)}</span></div>
  <div class="field"><span class="lbl">Civil Status</span><span class="val">${val(app.civil_status)}</span></div>
  <div class="field"><span class="lbl">Religion</span><span class="val">${val(app.religion)}</span></div>
  <div class="field"><span class="lbl">Citizenship</span><span class="val">${val(app.citizenship)}</span></div>
  <div class="field"><span class="lbl">Occupation</span><span class="val">${val(app.occupation)}</span></div>
  <div class="field full"><span class="lbl">Address</span><span class="val">${val(app.address)}</span></div>
</div>

<div class="section-title">Contact &amp; Health Information</div>
<div class="grid">
  <div class="field"><span class="lbl">Cellphone</span><span class="val">${val(app.cellphone)}</span></div>
  <div class="field"><span class="lbl">Email</span><span class="val">${val(app.email)}</span></div>
  <div class="field"><span class="lbl">Blood Type</span><span class="val">${val(app.blood_type)}</span></div>
  <div class="field"><span class="lbl">Weight (kg)</span><span class="val">${val(app.weight_kg)}</span></div>
  <div class="field"><span class="lbl">Height (cm)</span><span class="val">${val(app.height_cm)}</span></div>
  <div class="field full"><span class="lbl">Injury / Medical Condition</span><span class="val">${val(app.injury_or_condition)}</span></div>
</div>

<div class="section-title">Emergency Contact</div>
<div class="grid">
  <div class="field"><span class="lbl">Name</span><span class="val">${val(app.emergency_name)}</span></div>
  <div class="field"><span class="lbl">Relationship</span><span class="val">${val(app.emergency_relation)}</span></div>
  <div class="field"><span class="lbl">Cellphone</span><span class="val">${val(app.emergency_phone)}</span></div>
  <div class="field full"><span class="lbl">Address</span><span class="val">${val(app.emergency_address)}</span></div>
</div>

<div class="section-title">Training Background</div>
<div class="grid">
  <div class="field full"><span class="lbl">Goals in Joining</span><span class="val">${val(app.goals)}</span></div>
  <div class="field"><span class="lbl">How did they hear about OMA</span><span class="val">${val(app.how_did_you_know)}</span></div>
  <div class="field"><span class="lbl">Previous Khan</span><span class="val">${yn2(app.previous_khan)}</span></div>
  ${app.previous_khan == 1 ? `
  <div class="field"><span class="lbl">Khan Level</span><span class="val">${val(app.khan_level)}</span></div>
  <div class="field"><span class="lbl">Kru / Instructor</span><span class="val">${val(app.kru_name)}</span></div>` : ''}
</div>

<div class="section-title">Waiver &amp; Release</div>
<div class="waiver-box">
  I, <strong>${fullName}</strong>, hereby release the Oriental Muayboran Academy (OMA) — its officials, instructors, staffs, and members — from any liability. I understand that by signing this Waiver and Release, I expressly and willingly agree to take part in trainings, tournaments, and other activities entirely at my own risk. I waive all claims for damages, injuries, and/or death. I am fully aware that the activities are inherently dangerous and take full responsibility.
  <br><br>
  <strong>Agreed to Waiver:</strong> ${yn2(app.waiver_agreed)}
</div>

${app.admin_notes ? `
<div class="section-title">Admin Notes</div>
<div style="background:#f9f9f9; border:1px solid #eee; padding:8px 10px; font-size:10.5px; color:#444; line-height:1.5;">${val(app.admin_notes)}</div>` : ''}

<div class="footer">
  Generated by Oriental Muayboran Academy Admin Portal &nbsp;|&nbsp; ${new Date().toLocaleDateString('en-PH', {year:'numeric', month:'long', day:'numeric'})}
</div>
</body>
</html>`;

    // Open in new tab and trigger print-to-PDF
    const win = window.open('', '_blank');
    win.document.write(html);
    win.document.close();
    win.addEventListener('load', () => {
        setTimeout(() => { win.focus(); win.print(); }, 300);
    });
}

// ── XLSX Export ───────────────────────────────────────────────
function downloadEnrollXLSX() {
    const app = _currentEnrollment;
    if (!app) return;

    const fullName = [app.first_name, app.middle_name, app.last_name].filter(Boolean).join(' ');
    const yn2 = v => v == 1 ? 'Yes' : 'No';
    const val  = v => (v && String(v).trim()) ? String(v).trim() : '';

    // Build rows as [Label, Value] pairs — clean two-column layout
    const rows = [
        ['ORIENTAL MUAYBORAN ACADEMY', ''],
        ['Enrollment Application', ''],
        ['', ''],
        ['Application ID', '#' + val(app.id)],
        ['Submitted', val(app.created_at)],
        ['Status', val(app.status)],
        ['', ''],
        ['── PERSONAL INFORMATION ──', ''],
        ['Last Name',    val(app.last_name)],
        ['First Name',   val(app.first_name)],
        ['Middle Name',  val(app.middle_name)],
        ['Date of Birth',  val(app.date_of_birth)],
        ['Place of Birth', val(app.place_of_birth)],
        ['Sex',          val(app.sex)],
        ['Civil Status', val(app.civil_status)],
        ['Religion',     val(app.religion)],
        ['Citizenship',  val(app.citizenship)],
        ['Occupation',   val(app.occupation)],
        ['Address',      val(app.address)],
        ['', ''],
        ['── CONTACT & HEALTH ──', ''],
        ['Cellphone',    val(app.cellphone)],
        ['Email',        val(app.email)],
        ['Blood Type',   val(app.blood_type)],
        ['Weight (kg)',  val(app.weight_kg)],
        ['Height (cm)',  val(app.height_cm)],
        ['Injury / Medical Condition', val(app.injury_or_condition)],
        ['', ''],
        ['── EMERGENCY CONTACT ──', ''],
        ['Name',         val(app.emergency_name)],
        ['Relationship', val(app.emergency_relation)],
        ['Phone',        val(app.emergency_phone)],
        ['Address',      val(app.emergency_address)],
        ['', ''],
        ['── TRAINING BACKGROUND ──', ''],
        ['Goals',                val(app.goals)],
        ['How did they know',    val(app.how_did_you_know)],
        ['Previous Khan',        yn2(app.previous_khan)],
    ];

    if (app.previous_khan == 1) {
        rows.push(['Khan Level', val(app.khan_level)]);
        rows.push(['Kru / Instructor', val(app.kru_name)]);
    }

    rows.push(['', '']);
    rows.push(['── WAIVER ──', '']);
    rows.push(['Agreed to Waiver', yn2(app.waiver_agreed)]);

    if (app.admin_notes) {
        rows.push(['', '']);
        rows.push(['── ADMIN NOTES ──', '']);
        rows.push(['Notes', val(app.admin_notes)]);
    }

    // Create workbook
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(rows);

    // Column widths
    ws['!cols'] = [{ wch: 30 }, { wch: 50 }];

    // Style the header rows (SheetJS CE supports limited styling)
    // Bold the title
    if (ws['A1']) ws['A1'].s = { font: { bold: true, sz: 14 }, fill: { fgColor: { rgb: 'CC0000' } }, font: { color: { rgb: 'FFFFFF' }, bold: true, sz: 14 } };
    if (ws['A2']) ws['A2'].s = { font: { bold: true, sz: 11 } };

    XLSX.utils.book_append_sheet(wb, ws, 'Enrollment');

    const safeFileName = fullName.replace(/[^a-z0-9]/gi, '_').toLowerCase();
    XLSX.writeFile(wb, `OMA_Enrollment_${safeFileName}_${app.id}.xlsx`);
}
</script>

<?php include 'includes/admin_footer.php'; ?>