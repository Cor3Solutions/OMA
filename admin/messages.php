<?php
$page_title = "Contact Messages";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $admin_notes = sanitize($_POST['admin_notes']);
    
    $stmt = $conn->prepare("UPDATE contact_messages SET status=?, admin_notes=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $admin_notes, $id);
    
    if ($stmt->execute()) {
        $success = 'Message status updated!';
    } else {
        $error = 'Failed to update status';
    }
    $stmt->close();
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $id = (int)$_POST['id'];
    
    if ($conn->query("DELETE FROM contact_messages WHERE id = $id")) {
        $success = 'Message deleted!';
    } else {
        $error = 'Failed to delete message';
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = $filter !== 'all' ? "WHERE status = '$filter'" : '';

$messages = $conn->query("SELECT * FROM contact_messages $where ORDER BY created_at DESC");

// Get counts
$counts = [
    'all' => $conn->query("SELECT COUNT(*) as c FROM contact_messages")->fetch_assoc()['c'],
    'new' => $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE status='new'")->fetch_assoc()['c'],
    'read' => $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE status='read'")->fetch_assoc()['c'],
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
                <tr style="<?php echo $msg['status'] === 'new' ? 'background: #e3f2fd;' : ''; ?>">
                    <td><?php echo formatDateTime($msg['created_at']); ?></td>
                    <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                    <td>
                        <small><?php echo htmlspecialchars($msg['email']); ?></small><br>
                        <?php if ($msg['phone']): ?>
                            <small><?php echo htmlspecialchars($msg['phone']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                    <td><span class="badge badge-<?php echo $msg['status']; ?>"><?php echo ucfirst($msg['status']); ?></span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary" onclick="viewMessage(<?php echo htmlspecialchars(json_encode($msg)); ?>)">View</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this message?');">
                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" name="delete_message" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Message Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('viewModal').style.display='none'">&times;</span>
        <h2>Message Details</h2>
        <div id="messageDetails"></div>
        <form method="POST" style="margin-top: 2rem;">
            <input type="hidden" name="id" id="msg_id">
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" id="msg_status" class="form-select">
                    <option value="new">New</option>
                    <option value="read">Read</option>
                    <option value="replied">Replied</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Admin Notes</label>
                <textarea name="admin_notes" id="msg_notes" class="form-textarea"></textarea>
            </div>
            <div class="action-buttons">
                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('viewModal').style.display='none'">Close</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
.modal-content { background-color: #fefefe; margin: 5% auto; padding: 2rem; border-radius: 8px; width: 90%; max-width: 800px; max-height: 85vh; overflow-y: auto; }
.modal-close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
.modal-close:hover { color: #000; }
</style>

<script>
function viewMessage(msg) {
    document.getElementById('msg_id').value = msg.id;
    document.getElementById('msg_status').value = msg.status;
    document.getElementById('msg_notes').value = msg.admin_notes || '';
    
    const details = `
        <div style="background: #f5f5f5; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <p><strong>From:</strong> ${msg.name}</p>
            <p><strong>Email:</strong> <a href="mailto:${msg.email}">${msg.email}</a></p>
            ${msg.phone ? `<p><strong>Phone:</strong> ${msg.phone}</p>` : ''}
            <p><strong>Subject:</strong> ${msg.subject}</p>
            <p><strong>Date:</strong> ${msg.created_at}</p>
            <hr style="margin: 1rem 0;">
            <p><strong>Message:</strong></p>
            <p style="white-space: pre-wrap;">${msg.message}</p>
        </div>
    `;
    
    document.getElementById('messageDetails').innerHTML = details;
    document.getElementById('viewModal').style.display = 'block';
}
</script>

<?php include 'includes/admin_footer.php'; ?>
