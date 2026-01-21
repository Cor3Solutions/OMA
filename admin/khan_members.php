<?php
$page_title = "Manage Khan Members";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_member'])) {
        $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
        $current_khan_level = (int)$_POST['current_khan_level'];
        $khan_color = isset($_POST['khan_color']) ? sanitize($_POST['khan_color']) : '';
        $date_joined = $_POST['date_joined'];
        $date_promoted = !empty($_POST['date_promoted']) ? $_POST['date_promoted'] : null;
        $instructor_id = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
        $training_location = isset($_POST['training_location']) ? sanitize($_POST['training_location']) : '';
        $status = $_POST['status'];
        $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';
        
        $stmt = $conn->prepare("INSERT INTO khan_members (user_id, full_name, email, phone, current_khan_level, khan_color, date_joined, date_promoted, instructor_id, training_location, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssississss", $user_id, $full_name, $email, $phone, $current_khan_level, $khan_color, $date_joined, $date_promoted, $instructor_id, $training_location, $status, $notes);
        
        if ($stmt->execute()) {
            $success = 'Khan member added successfully!';
        } else {
            $error = 'Failed to add khan member';
        }
        $stmt->close();
    }
    
    elseif (isset($_POST['edit_member'])) {
        $id = (int)$_POST['id'];
        $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
        $current_khan_level = (int)$_POST['current_khan_level'];
        $khan_color = isset($_POST['khan_color']) ? sanitize($_POST['khan_color']) : '';
        $date_joined = $_POST['date_joined'];
        $date_promoted = !empty($_POST['date_promoted']) ? $_POST['date_promoted'] : null;
        $instructor_id = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
        $training_location = isset($_POST['training_location']) ? sanitize($_POST['training_location']) : '';
        $status = $_POST['status'];
        $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';
        
        $stmt = $conn->prepare("UPDATE khan_members SET user_id=?, full_name=?, email=?, phone=?, current_khan_level=?, khan_color=?, date_joined=?, date_promoted=?, instructor_id=?, training_location=?, status=?, notes=? WHERE id=?");
        $stmt->bind_param("isssississssi", $user_id, $full_name, $email, $phone, $current_khan_level, $khan_color, $date_joined, $date_promoted, $instructor_id, $training_location, $status, $notes, $id);
        
        if ($stmt->execute()) {
            $success = 'Khan member updated successfully!';
        } else {
            $error = 'Failed to update khan member';
        }
        $stmt->close();
    }
    
    elseif (isset($_POST['delete_member'])) {
        $id = (int)$_POST['id'];
        
        if ($conn->query("DELETE FROM khan_members WHERE id = $id")) {
            $success = 'Khan member deleted successfully!';
        } else {
            $error = 'Failed to delete khan member';
        }
    }
}

// Get all khan members with instructor names and user info
$members = $conn->query("
    SELECT km.*, i.name as instructor_name, u.serial_number, u.email as user_email
    FROM khan_members km 
    LEFT JOIN instructors i ON km.instructor_id = i.id 
    LEFT JOIN users u ON km.user_id = u.id
    ORDER BY km.current_khan_level DESC, km.full_name ASC
");

// Get instructors for dropdown
$instructors = $conn->query("SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name");

// Get users for dropdown
$available_users = $conn->query("SELECT id, name, email FROM users WHERE role = 'member' ORDER BY name");

include 'includes/admin_header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="admin-section">
    <!-- Notice about centralized management -->
    <div class="info-notice" style="background: linear-gradient(135deg, #43a047 0%, #66bb6a 100%); color: white; padding: 1.5rem; margin-bottom: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="font-size: 3rem;">
                <i class="fas fa-info-circle"></i>
            </div>
            <div style="flex: 1;">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.3rem;">
                    <i class="fas fa-sparkles"></i> New! Centralized User Management
                </h3>
                <p style="margin: 0; opacity: 0.95; line-height: 1.6;">
                    You can now create members with their user accounts in one place! 
                    The <strong>Centralized User Management</strong> page lets you create a user and their member profile simultaneously.
                </p>
                <a href="manage_users_centralized.php" class="btn" style="background: white; color: #43a047; margin-top: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                    <i class="fas fa-arrow-right"></i> Go to Centralized Management
                </a>
            </div>
        </div>
    </div>

    <div class="section-header">
        <h2><i class="fas fa-user-graduate"></i> Khan Members Management</h2>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">
            <i class="fas fa-plus-circle"></i> Add New Member
        </button>
    </div>
    
    <div class="filters-row" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: center; flex-wrap: wrap;">
        <div class="search-box" style="flex: 1; min-width: 250px;">
            <input type="text" placeholder="🔍 Search members..." id="searchInput" style="width: 100%;">
        </div>
        <select id="levelFilter" class="form-select" style="width: 180px;">
            <option value="">All Levels</option>
            <?php for ($i = 1; $i <= 16; $i++): ?>
                <option value="<?php echo $i; ?>">Khan <?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        <select id="statusFilter" class="form-select" style="width: 180px;">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="graduated">Graduated</option>
        </select>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>User Account</th>
                    <th>Email/Phone</th>
                    <th>Khan Level</th>
                    <th>Color</th>
                    <th>Instructor</th>
                    <th>Location</th>
                    <th>Date Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($member = $members->fetch_assoc()): ?>
                <tr data-level="<?php echo $member['current_khan_level']; ?>" data-status="<?php echo $member['status']; ?>">
                    <td><?php echo $member['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($member['full_name']); ?></strong></td>
                    <td>
                        <?php if ($member['user_id']): ?>
                            <span style="display: inline-block; background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($member['serial_number']); ?>
                            </span><br>
                            <small style="color: #666;"><?php echo htmlspecialchars($member['user_email']); ?></small>
                        <?php else: ?>
                            <span style="color: #999; font-style: italic;">No account</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <small><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($member['email']); ?></small><br>
                        <small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($member['phone'] ?: 'N/A'); ?></small>
                    </td>
                    <td><strong style="color: #388e3c;">Khan <?php echo $member['current_khan_level']; ?></strong></td>
                    <td>
                        <?php if ($member['khan_color']): ?>
                            <span style="display: inline-block; background: #f5f5f5; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                <?php echo htmlspecialchars($member['khan_color']); ?>
                            </span>
                        <?php else: ?>
                            <span style="color: #999;">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($member['instructor_name'] ?: 'Not assigned'); ?></td>
                    <td><?php echo htmlspecialchars($member['training_location'] ?: 'N/A'); ?></td>
                    <td><small><?php echo formatDate($member['date_joined']); ?></small></td>
                    <td>
                        <span class="badge badge-<?php echo $member['status']; ?>" style="padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.85rem;">
                            <?php echo ucfirst($member['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary" onclick="editMember(<?php echo htmlspecialchars(json_encode($member)); ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this member?');">
                                <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                <button type="submit" name="delete_member" class="btn btn-sm btn-danger" title="Delete">
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
</div>

<!-- Add Member Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-user-plus"></i> Add New Khan Member</h2>
        
        <div class="alert" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
            <strong><i class="fas fa-lightbulb"></i> Tip:</strong> To create a member with a user account, use the 
            <a href="manage_users_centralized.php" style="color: #1976d2; font-weight: bold;">Centralized User Management</a> page instead.
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Link to User Account (Optional)</label>
                <select name="user_id" class="form-select">
                    <option value="">-- No User Account --</option>
                    <?php 
                    $available_users->data_seek(0);
                    while ($user = $available_users->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-input" placeholder="09XX XXX XXXX">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Current Khan Level *</label>
                    <input type="number" name="current_khan_level" class="form-input" min="1" max="16" value="1" required>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Khan Color/Band</label>
                    <input type="text" name="khan_color" class="form-input" placeholder="e.g., White, Yellow, Green">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date Joined *</label>
                    <input type="date" name="date_joined" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Date Promoted (Optional)</label>
                    <input type="date" name="date_promoted" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Instructor/Kru</label>
                    <select name="instructor_id" class="form-select">
                        <option value="">-- No Instructor --</option>
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
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Training Location</label>
                    <input type="text" name="training_location" class="form-input" placeholder="e.g., Quezon City">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="graduated">Graduated</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Additional notes about the member..."></textarea>
            </div>
            
            <div class="action-buttons">
                <button type="submit" name="add_member" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Member
                </button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Member Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-user-edit"></i> Edit Khan Member</h2>
        <form method="POST" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label class="form-label">Link to User Account (Optional)</label>
                <select name="user_id" id="edit_user_id" class="form-select">
                    <option value="">-- No User Account --</option>
                    <?php 
                    $available_users->data_seek(0);
                    while ($user = $available_users->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" id="edit_full_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" id="edit_email" class="form-input" required>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" id="edit_phone" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Current Khan Level *</label>
                    <input type="number" name="current_khan_level" id="edit_current_khan_level" class="form-input" min="1" max="16" required>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Khan Color/Band</label>
                    <input type="text" name="khan_color" id="edit_khan_color" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date Joined *</label>
                    <input type="date" name="date_joined" id="edit_date_joined" class="form-input" required>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Date Promoted (Optional)</label>
                    <input type="date" name="date_promoted" id="edit_date_promoted" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Instructor/Kru</label>
                    <select name="instructor_id" id="edit_instructor_id" class="form-select">
                        <option value="">-- No Instructor --</option>
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
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Training Location</label>
                    <input type="text" name="training_location" id="edit_training_location" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" id="edit_status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="graduated">Graduated</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" id="edit_notes" class="form-textarea" rows="3"></textarea>
            </div>
            
            <div class="action-buttons">
                <button type="submit" name="edit_member" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Member
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
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background-color: #fefefe;
    margin: 3% auto;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    animation: slideDown 0.3s;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s;
}

.modal-close:hover {
    color: #000;
}

.filters-row select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.badge-active {
    background: #4caf50;
    color: white;
}

.badge-inactive {
    background: #757575;
    color: white;
}

.badge-graduated {
    background: #2196f3;
    color: white;
}
</style>

<script>
function editMember(member) {
    document.getElementById('edit_id').value = member.id;
    document.getElementById('edit_user_id').value = member.user_id || '';
    document.getElementById('edit_full_name').value = member.full_name;
    document.getElementById('edit_email').value = member.email;
    document.getElementById('edit_phone').value = member.phone || '';
    document.getElementById('edit_current_khan_level').value = member.current_khan_level;
    document.getElementById('edit_khan_color').value = member.khan_color || '';
    document.getElementById('edit_date_joined').value = member.date_joined;
    document.getElementById('edit_date_promoted').value = member.date_promoted || '';
    document.getElementById('edit_instructor_id').value = member.instructor_id || '';
    document.getElementById('edit_training_location').value = member.training_location || '';
    document.getElementById('edit_status').value = member.status;
    document.getElementById('edit_notes').value = member.notes || '';
    
    document.getElementById('editModal').style.display = 'block';
}

// Search and filter functionality
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('levelFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const levelFilter = document.getElementById('levelFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.data-table tbody tr');
    
    rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        const rowLevel = row.getAttribute('data-level');
        const rowStatus = row.getAttribute('data-status');
        
        const matchesSearch = text.includes(searchTerm);
        const matchesLevel = !levelFilter || rowLevel === levelFilter;
        const matchesStatus = !statusFilter || rowStatus === statusFilter;
        
        row.style.display = (matchesSearch && matchesLevel && matchesStatus) ? '' : 'none';
    });
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