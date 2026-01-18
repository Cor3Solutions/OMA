<?php
$page_title = "Manage Users";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        $status = $_POST['status'];
        
        // Check if email exists
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $error = 'Email already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $phone, $hashed_password, $role, $status);
            
            if ($stmt->execute()) {
                $success = 'User added successfully!';
            } else {
                $error = 'Failed to add user';
            }
            $stmt->close();
        }
    }
    
    elseif (isset($_POST['edit_user'])) {
        $id = (int)$_POST['id'];
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $role = $_POST['role'];
        $status = $_POST['status'];
        $password = $_POST['password'];
        
        // Check if email exists for other users
        $check = $conn->query("SELECT id FROM users WHERE email = '$email' AND id != $id");
        if ($check->num_rows > 0) {
            $error = 'Email already exists';
        } else {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, password=?, role=?, status=? WHERE id=?");
                $stmt->bind_param("ssssssi", $name, $email, $phone, $hashed_password, $role, $status, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, role=?, status=? WHERE id=?");
                $stmt->bind_param("sssssi", $name, $email, $phone, $role, $status, $id);
            }
            
            if ($stmt->execute()) {
                $success = 'User updated successfully!';
            } else {
                $error = 'Failed to update user';
            }
            $stmt->close();
        }
    }
    
    elseif (isset($_POST['delete_user'])) {
        $id = (int)$_POST['id'];
        
        // Prevent deleting yourself
        if ($id == $_SESSION['user_id']) {
            $error = 'You cannot delete your own account';
        } else {
            if ($conn->query("DELETE FROM users WHERE id = $id")) {
                $success = 'User deleted successfully!';
            } else {
                $error = 'Failed to delete user';
            }
        }
    }
}

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

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
        <h2>User Management</h2>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">
            Add New User
        </button>
    </div>
    
    <div class="search-box">
        <input type="text" placeholder="Search users..." id="searchInput">
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                    <td>
                        <span class="badge" style="background: 
                            <?php echo $user['role'] === 'admin' ? '#1976d2' : ($user['role'] === 'instructor' ? '#f57c00' : '#388e3c'); ?>; color: white;">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td><span class="badge badge-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                    <td><?php echo formatDate($user['created_at']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">Edit</button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2>Add New User</h2>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-input" required minlength="6">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select name="role" class="form-select" required>
                        <option value="member">Member</option>
                        <option value="instructor">Instructor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h2>Edit User</h2>
        <form method="POST" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" id="edit_email" class="form-input" required>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" id="edit_phone" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">New Password (leave empty to keep current)</label>
                    <input type="password" name="password" class="form-input" minlength="6">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select name="role" id="edit_role" class="form-select" required>
                        <option value="member">Member</option>
                        <option value="instructor">Instructor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" id="edit_status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
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
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
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
function editUser(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_status').value = user.status;
    
    document.getElementById('editModal').style.display = 'block';
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.data-table tbody tr');
    
    rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php include 'includes/admin_footer.php'; ?>
