<?php
$page_title = "Contact Messages";
// Adjust these paths if your folder structure is different
require_once '../config/database.php';
requireAdmin(); // Assuming this function exists in your system

$conn = getDbConnection();
$success = '';
$error = '';

// Helper function for security
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

// --- 1. HANDLE FORM SUBMISSIONS ---

// Update Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $admin_notes = sanitize($_POST['admin_notes']);
    
    $stmt = $conn->prepare("UPDATE contact_messages SET status=?, admin_notes=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $admin_notes, $id);
    
    if ($stmt->execute()) {
        $success = 'Message status updated successfully!';
    } else {
        $error = 'Failed to update status';
    }
    $stmt->close();
}

// Delete Message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $id = (int)$_POST['id'];
    // Archive before delete
    require_once 'includes/activity_helper.php';
    $fullRow = $conn->query("SELECT * FROM contact_messages WHERE id = $id")->fetch_assoc();
    if ($fullRow) {
        archiveRecord($conn, 'contact_messages', $id, $fullRow['name'].' — '.$fullRow['subject'], $fullRow);
        logActivity($conn, 'delete', 'contact_messages', $id, $fullRow['name'], 'Subject: '.$fullRow['subject']);
    }
    if ($conn->query("DELETE FROM contact_messages WHERE id = $id")) {
        $success = 'Message deleted permanently.';
    } else {
        $error = 'Failed to delete message';
    }
}

// --- 2. FETCH DATA ---

// Filters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$allowed_filters = ['new', 'read', 'replied'];
$where = in_array($filter, $allowed_filters) ? "WHERE status = '$filter'" : '';

// Main Query
$messages = $conn->query("SELECT * FROM contact_messages $where ORDER BY created_at DESC");

// Counts for Dashboard
$counts = [
    'all'     => $conn->query("SELECT COUNT(*) as c FROM contact_messages")->fetch_assoc()['c'],
    'new'     => $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE status='new'")->fetch_assoc()['c'],
    'read'    => $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE status='read'")->fetch_assoc()['c'],
    'replied' => $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE status='replied'")->fetch_assoc()['c'],
];

include 'includes/admin_header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="admin-section">
    <div class="section-header">
        <h2>Contact Messages</h2>
    </div>
    
    <div style="margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">
            All (<?php echo $counts['all']; ?>)
        </a>
        <a href="?filter=new" class="btn <?php echo $filter === 'new' ? 'btn-primary' : 'btn-outline'; ?>">
            New (<?php echo $counts['new']; ?>)
        </a>
        <a href="?filter=read" class="btn <?php echo $filter === 'read' ? 'btn-primary' : 'btn-outline'; ?>">
            Read (<?php echo $counts['read']; ?>)
        </a>
        <a href="?filter=replied" class="btn <?php echo $filter === 'replied' ? 'btn-primary' : 'btn-outline'; ?>">
            Replied (<?php echo $counts['replied']; ?>)
        </a>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($msg = $messages->fetch_assoc()): ?>
                <tr style="<?php echo $msg['status'] === 'new' ? 'background: #f0f9ff;' : ''; ?>">
                    <td style="white-space: nowrap; font-size: 0.9em; color: #666;">
                        <?php echo date('M d, Y', strtotime($msg['created_at'])); ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                    <td>
                        <div style="font-size: 0.9em;">
                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="text-decoration:none; color:#007bff;">
                                <?php echo htmlspecialchars($msg['email']); ?>
                            </a>
                            <?php if ($msg['phone']): ?>
                                <br><span style="color: #666;"><?php echo htmlspecialchars($msg['phone']); ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                    <td>
                        <?php 
                            $badgeClass = 'secondary';
                            if($msg['status'] == 'new') $badgeClass = 'primary';
                            if($msg['status'] == 'replied') $badgeClass = 'success';
                        ?>
                        <span class="badge badge-<?php echo $badgeClass; ?>"><?php echo ucfirst($msg['status']); ?></span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary" onclick='viewMessage(<?php echo json_encode($msg); ?>)'>
                                View
                            </button>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" name="delete_message" class="btn btn-sm btn-danger" title="Delete">
                                    &times;
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2 style="margin-top: 0;">Message Details</h2>
        
        <div id="messageDetails"></div>

        <form method="POST" style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1.5rem;">
            <input type="hidden" name="id" id="msg_id">
            
            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                
                <div style="flex: 1; min-width: 300px;">
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
                        <textarea name="admin_notes" id="msg_notes" class="form-textarea" placeholder="Internal notes about this conversation..."></textarea>
                    </div>
                </div>

                <div style="flex: 1; min-width: 300px; border-left: 1px solid #eee; padding-left: 2rem;">
                    <label class="form-label">Reply to Customer</label>
                    
                    <a id="gmailReplyBtn" href="#" target="_blank" onclick="markAsReplied()" 
                       class="btn btn-danger btn-lg" 
                       style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; background-color: #ea4335; border-color: #ea4335; color: white;">
                        
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        
                        Reply via Gmail
                    </a>
                    
                    <p style="font-size: 0.85rem; color: #666; margin-top: 0.8rem; text-align: center;">
                        Opens a new tab in Gmail with subject & message pre-filled.
                    </p>
                </div>
            </div>

            <div class="action-buttons" style="margin-top: 1.5rem; text-align: right; border-top: 1px solid #eee; padding-top: 1rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Close</button>
                <button type="submit" name="update_status" class="btn btn-success">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Modal Base */
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px); }
.modal-content { background-color: #fff; margin: 4vh auto; padding: 2rem; border-radius: 8px; width: 92%; max-width: 900px; box-shadow: 0 4px 25px rgba(0,0,0,0.15); animation: slideDown 0.3s ease-out; }

/* Animations */
@keyframes slideDown { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }

.modal-close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; transition: 0.2s; }
.modal-close:hover { color: #333; }

/* Form Elements */
.form-group { margin-bottom: 1.2rem; }
.form-label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #444; font-size: 0.9rem; }
.form-select, .form-textarea { width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; font-size: 0.95rem; }
.form-textarea { min-height: 80px; resize: vertical; }
.btn-lg { padding: 0.8rem 1.5rem; font-size: 1.05rem; font-weight: 500; }

/* Message View Box */
.msg-box { background: #f8f9fa; padding: 1.5rem; border-radius: 6px; border-left: 4px solid #ea4335; margin-bottom: 1rem; }
.msg-meta { color: #555; font-size: 0.95rem; line-height: 1.6; }
.msg-body { margin-top: 1rem; white-space: pre-wrap; color: #333; font-size: 1rem; line-height: 1.6; }

/* Responsive adjustments for modal */
@media (max-width: 768px) {
    .modal-content { margin: 10% auto; width: 95%; padding: 1rem; }
    div[style*="flex: 1"] { border-left: none !important; padding-left: 0 !important; border-top: 1px solid #eee; padding-top: 1.5rem; }
}
</style>

<script>
function viewMessage(msg) {
    // 1. Populate Hidden Inputs
    document.getElementById('msg_id').value = msg.id;
    document.getElementById('msg_status').value = msg.status;
    document.getElementById('msg_notes').value = msg.admin_notes || '';
    
    // 2. Generate Gmail Link
    const subject = encodeURIComponent("Re: " + msg.subject);
    const bodyRaw = "\n\n\n--------------------------------\n" +
                    "On " + msg.created_at + ", " + msg.name + " wrote:\n\n" + 
                    msg.message;
    const body = encodeURIComponent(bodyRaw);
    
    // Construct the Gmail Compose URL
    const gmailLink = `https://mail.google.com/mail/?view=cm&fs=1&to=${msg.email}&su=${subject}&body=${body}`;
    
    // Apply link to button
    document.getElementById('gmailReplyBtn').href = gmailLink;

    // 3. Render Message Details
    const details = `
        <div class="msg-box">
            <div class="msg-meta">
                <strong>From:</strong> ${msg.name} <span style="color:#777">&lt;${msg.email}&gt;</span><br>
                ${msg.phone ? `<strong>Phone:</strong> ${msg.phone}<br>` : ''}
                <strong>Received:</strong> ${msg.created_at}<br>
                <strong>Subject:</strong> ${msg.subject}
            </div>
            <hr style="margin: 15px 0; border: 0; border-top: 1px solid #e0e0e0;">
            <div class="msg-body">${msg.message}</div>
        </div>
    `;
    
    document.getElementById('messageDetails').innerHTML = details;
    document.getElementById('viewModal').style.display = 'block';
}

// Helper: Auto-change status to 'replied' when the button is clicked
function markAsReplied() {
    const statusSelect = document.getElementById('msg_status');
    // Only change if it's currently 'new' or 'read'
    if(statusSelect.value === 'new' || statusSelect.value === 'read') {
        statusSelect.value = 'replied';
    }
}

function closeModal() {
    document.getElementById('viewModal').style.display = 'none';
}

// Close modal if clicking on the dark background
window.onclick = function(event) {
    if (event.target == document.getElementById('viewModal')) {
        closeModal();
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>