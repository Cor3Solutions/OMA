<?php
$page_title = "Khan Training History";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

// Get member ID from URL
$member_id = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;

if ($member_id <= 0) {
    header("Location: khan_members.php");
    exit;
}

// Get member details
$member_query = $conn->query("
    SELECT km.*, i.name as instructor_name, u.serial_number
    FROM khan_members km
    LEFT JOIN instructors i ON km.instructor_id = i.id
    LEFT JOIN users u ON km.user_id = u.id
    WHERE km.id = $member_id
");

if (!$member_query || $member_query->num_rows === 0) {
    header("Location: khan_members.php");
    exit;
}

$member = $member_query->fetch_assoc();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_history'])) {
        $khan_level = (int)$_POST['khan_level'];
        $training_date = $_POST['training_date'];
        $certified_date = !empty($_POST['certified_date']) ? $_POST['certified_date'] : null;
        $instructor_id = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
        $location = sanitize($_POST['location']);
        $notes = sanitize($_POST['notes']);
        $certificate_number = sanitize($_POST['certificate_number']);
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("INSERT INTO khan_training_history (member_id, khan_level, training_date, certified_date, instructor_id, location, notes, certificate_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissiisss", $member_id, $khan_level, $training_date, $certified_date, $instructor_id, $location, $notes, $certificate_number, $status);
        
        if ($stmt->execute()) {
            // Update member's current khan level if this is higher
            if ($status === 'certified' && $khan_level > $member['current_khan_level']) {
                $conn->query("UPDATE khan_members SET current_khan_level = $khan_level, date_promoted = '$certified_date' WHERE id = $member_id");
            }
            $success = 'Training history added successfully!';
        } else {
            $error = 'Failed to add training history';
        }
        $stmt->close();
    }
    
    elseif (isset($_POST['edit_history'])) {
        $history_id = (int)$_POST['history_id'];
        $khan_level = (int)$_POST['khan_level'];
        $training_date = $_POST['training_date'];
        $certified_date = !empty($_POST['certified_date']) ? $_POST['certified_date'] : null;
        $instructor_id = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
        $location = sanitize($_POST['location']);
        $notes = sanitize($_POST['notes']);
        $certificate_number = sanitize($_POST['certificate_number']);
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE khan_training_history SET khan_level=?, training_date=?, certified_date=?, instructor_id=?, location=?, notes=?, certificate_number=?, status=? WHERE id=? AND member_id=?");
        $stmt->bind_param("isssisssii", $khan_level, $training_date, $certified_date, $instructor_id, $location, $notes, $certificate_number, $status, $history_id, $member_id);
        
        if ($stmt->execute()) {
            $success = 'Training history updated successfully!';
        } else {
            $error = 'Failed to update training history';
        }
        $stmt->close();
    }
    
    elseif (isset($_POST['delete_history'])) {
        $history_id = (int)$_POST['history_id'];
        
        if ($conn->query("DELETE FROM khan_training_history WHERE id = $history_id AND member_id = $member_id")) {
            $success = 'Training history deleted successfully!';
        } else {
            $error = 'Failed to delete training history';
        }
    }
}

// Get training history
$history = $conn->query("
    SELECT kth.*, i.name as instructor_name
    FROM khan_training_history kth
    LEFT JOIN instructors i ON kth.instructor_id = i.id
    WHERE kth.member_id = $member_id
    ORDER BY kth.khan_level ASC, kth.training_date ASC
");

// Get instructors for dropdown
$instructors = $conn->query("SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name");

include 'includes/admin_header.php';
?>

<div class="breadcrumb" style="margin-bottom: 1.5rem; padding: 1rem; background: #f5f5f5; border-radius: 4px;">
    <a href="khan_members.php" style="color: #1976d2; text-decoration: none;">← Back to Khan Members</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Member Profile Card -->
<div class="admin-section" style="margin-bottom: 2rem;">
    <div style="display: flex; align-items: center; gap: 2rem;">
        <div style="flex: 1;">
            <h2 style="margin: 0 0 0.5rem 0; color: #1976d2;">
                <i class="fas fa-user-graduate"></i> <?php echo htmlspecialchars($member['full_name']); ?>
            </h2>
            <div style="display: flex; gap: 2rem; flex-wrap: wrap; margin-top: 1rem;">
                <div>
                    <strong>Current Khan Level:</strong>
                    <span style="display: inline-block; background: #388e3c; color: white; padding: 0.25rem 0.75rem; border-radius: 4px; margin-left: 0.5rem;">
                        Khan <?php echo $member['current_khan_level']; ?>
                    </span>
                </div>
                <div>
                    <strong>Khan Color:</strong>
                    <span style="color: #666;"><?php echo htmlspecialchars($member['khan_color'] ?: 'N/A'); ?></span>
                </div>
                <div>
                    <strong>Date Joined:</strong>
                    <span style="color: #666;"><?php echo formatDate($member['date_joined']); ?></span>
                </div>
                <div>
                    <strong>Instructor:</strong>
                    <span style="color: #666;"><?php echo htmlspecialchars($member['instructor_name'] ?: 'Not assigned'); ?></span>
                </div>
                <div>
                    <strong>Serial Number:</strong>
                    <span style="color: #666;"><?php echo htmlspecialchars($member['serial_number'] ?: 'N/A'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Training History Section -->
<div class="admin-section">
    <div class="section-header">
        <h2><i class="fas fa-history"></i> Khan Training History Timeline</h2>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">
            <i class="fas fa-plus-circle"></i> Add Training Record
        </button>
    </div>
    
    <div style="margin-bottom: 2rem; padding: 1rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
        <strong><i class="fas fa-info-circle"></i> Training Progress:</strong>
        Track each Khan level training session with dates, instructors, and certification status.
    </div>
    
    <!-- Khan Level Progress Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <?php for ($level = 1; $level <= 16; $level++): ?>
            <?php
            $level_history = $conn->query("SELECT * FROM khan_training_history WHERE member_id = $member_id AND khan_level = $level ORDER BY training_date DESC LIMIT 1");
            $has_level = $level_history && $level_history->num_rows > 0;
            $level_data = $has_level ? $level_history->fetch_assoc() : null;
            $is_current = $level == $member['current_khan_level'];
            $is_completed = $level_data && $level_data['status'] === 'certified';
            ?>
            <div style="padding: 1rem; border-radius: 8px; text-align: center; 
                background: <?php echo $is_completed ? '#e8f5e9' : ($is_current ? '#fff3e0' : '#f5f5f5'); ?>; 
                border: 2px solid <?php echo $is_completed ? '#4caf50' : ($is_current ? '#ff9800' : '#e0e0e0'); ?>;">
                <div style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.5rem; 
                    color: <?php echo $is_completed ? '#2e7d32' : ($is_current ? '#f57c00' : '#999'); ?>;">
                    <?php echo $level; ?>
                </div>
                <div style="font-size: 0.75rem; color: #666;">Khan Level</div>
                <?php if ($is_completed): ?>
                    <div style="margin-top: 0.5rem;">
                        <i class="fas fa-check-circle" style="color: #4caf50; font-size: 1.5rem;"></i>
                    </div>
                    <div style="font-size: 0.7rem; color: #666; margin-top: 0.25rem;">
                        <?php echo date('M d, Y', strtotime($level_data['certified_date'])); ?>
                    </div>
                <?php elseif ($is_current): ?>
                    <div style="margin-top: 0.5rem;">
                        <i class="fas fa-dot-circle" style="color: #ff9800; font-size: 1.5rem;"></i>
                    </div>
                    <div style="font-size: 0.7rem; color: #666; margin-top: 0.25rem;">Current</div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
    
    <!-- Training History Table -->
    <h3 style="margin: 2rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #1976d2;">
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
                    <?php while ($record = $history->fetch_assoc()): ?>
                    <tr>
                        <td><strong style="color: #1976d2;">Khan <?php echo $record['khan_level']; ?></strong></td>
                        <td><?php echo formatDate($record['training_date']); ?></td>
                        <td><?php echo $record['certified_date'] ? formatDate($record['certified_date']) : '<span style="color: #999;">Pending</span>'; ?></td>
                        <td><?php echo htmlspecialchars($record['instructor_name'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($record['location'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($record['certificate_number'] ?: '-'); ?></td>
                        <td>
                            <span class="badge" style="padding: 0.3rem 0.6rem; border-radius: 4px; color: white;
                                background: <?php echo $record['status'] === 'certified' ? '#4caf50' : ($record['status'] === 'completed' ? '#2196f3' : '#ff9800'); ?>;">
                                <?php echo ucfirst(str_replace('_', ' ', $record['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($record['notes']): ?>
                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                                     title="<?php echo htmlspecialchars($record['notes']); ?>">
                                    <?php echo htmlspecialchars(substr($record['notes'], 0, 50)); ?>...
                                </div>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-primary" onclick="editHistory(<?php echo htmlspecialchars(json_encode($record)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this training record?');">
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
        <div style="text-align: center; padding: 3rem; background: #f5f5f5; border-radius: 8px;">
            <i class="fas fa-history" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
            <h3 style="color: #999; margin: 0 0 0.5rem 0;">No Training History Yet</h3>
            <p style="color: #999; margin: 0;">Click "Add Training Record" to start tracking this member's Khan progression.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Add Training History Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-plus-circle"></i> Add Training Record</h2>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Khan Level *</label>
                    <select name="khan_level" class="form-select" required>
                        <?php for ($i = 1; $i <= 16; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i == ($member['current_khan_level'] + 1) ? 'selected' : ''; ?>>
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
                        <option value="certified">Certified</option>
                    </select>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Training Start Date *</label>
                    <input type="date" name="training_date" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Certified Date (Optional)</label>
                    <input type="date" name="certified_date" class="form-input">
                    <small style="color: #666;">Leave empty if not yet certified</small>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Instructor/Kru</label>
                    <select name="instructor_id" class="form-select">
                        <option value="">-- Select Instructor --</option>
                        <?php 
                        $instructors->data_seek(0);
                        while ($instructor = $instructors->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $instructor['id']; ?>" <?php echo $instructor['id'] == $member['instructor_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($instructor['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Training Location</label>
                    <input type="text" name="location" class="form-input" value="<?php echo htmlspecialchars($member['training_location']); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Certificate Number</label>
                <input type="text" name="certificate_number" class="form-input" placeholder="e.g., OMA-KHAN-2024-001">
            </div>
            
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Training details, achievements, areas for improvement..."></textarea>
            </div>
            
            <div class="action-buttons" style="margin-top: 1.5rem;">
                <button type="submit" name="add_history" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Record
                </button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Training History Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
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
                    <label class="form-label">Training Start Date *</label>
                    <input type="date" name="training_date" id="edit_training_date" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Certified Date</label>
                    <input type="date" name="certified_date" id="edit_certified_date" class="form-input">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Instructor/Kru</label>
                    <select name="instructor_id" id="edit_instructor_id" class="form-select">
                        <option value="">-- Select Instructor --</option>
                        <?php 
                        $instructors->data_seek(0);
                        while ($instructor = $instructors->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $instructor['id']; ?>">
                                <?php echo htmlspecialchars($instructor['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Training Location</label>
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
            
            <div class="action-buttons" style="margin-top: 1.5rem;">
                <button type="submit" name="edit_history" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Record
                </button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 3% auto;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 900px;
    max-height: 85vh;
    overflow-y: auto;
}

.modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.modal-close:hover {
    color: #000;
}
</style>

<script>
function editHistory(record) {
    document.getElementById('edit_id').value = record.id;
    document.getElementById('edit_khan_level').value = record.khan_level;
    document.getElementById('edit_training_date').value = record.training_date;
    document.getElementById('edit_certified_date').value = record.certified_date || '';
    document.getElementById('edit_instructor_id').value = record.instructor_id || '';
    document.getElementById('edit_location').value = record.location || '';
    document.getElementById('edit_certificate_number').value = record.certificate_number || '';
    document.getElementById('edit_status').value = record.status;
    document.getElementById('edit_notes').value = record.notes || '';
    
    document.getElementById('editModal').style.display = 'block';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    if (event.target == addModal) {
        addModal.style.display = 'none';
    }
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('addModal').style.display = 'none';
        document.getElementById('editModal').style.display = 'none';
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>