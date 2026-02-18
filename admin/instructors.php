<?php
$page_title = "Manage Instructors";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_instructor'])) {
        $user_id = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : null;
        $name = sanitize($_POST['name']);
        $khan_level = sanitize($_POST['khan_level']);
        $title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
        $location = isset($_POST['location']) ? sanitize($_POST['location']) : '';
        $specialization = isset($_POST['specialization']) ? sanitize($_POST['specialization']) : '';
        $bio = isset($_POST['bio']) ? sanitize($_POST['bio']) : '';
        $facebook_url = isset($_POST['facebook_url']) ? sanitize($_POST['facebook_url']) : '';
        $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
        $display_order = (int) $_POST['display_order'];
        $status = $_POST['status'];

        // Handle photo upload
        $photo_path = '';
        if (!empty($_FILES['photo']['name'])) {
            $upload = uploadFile($_FILES['photo'], UPLOAD_DIR . 'instructors/', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
            if ($upload['success']) {
                $photo_path = 'assets/uploads/instructors/' . $upload['filename'];
            } else {
                $error = $upload['message'];
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO instructors (user_id, name, photo_path, khan_level, title, location, specialization, bio, facebook_url, email, phone, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssssssis", $user_id, $name, $photo_path, $khan_level, $title, $location, $specialization, $bio, $facebook_url, $email, $phone, $display_order, $status);

            if ($stmt->execute()) {
                $success = 'Instructor added successfully!';
            } else {
                $error = 'Failed to add instructor';
            }
            $stmt->close();
        }
    } elseif (isset($_POST['edit_instructor'])) {
        $id = (int) $_POST['id'];
        $user_id = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : null;
        $name = sanitize($_POST['name']);
        $khan_level = sanitize($_POST['khan_level']);
        $title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
        $location = isset($_POST['location']) ? sanitize($_POST['location']) : '';
        $specialization = isset($_POST['specialization']) ? sanitize($_POST['specialization']) : '';
        $bio = isset($_POST['bio']) ? sanitize($_POST['bio']) : '';
        $facebook_url = isset($_POST['facebook_url']) ? sanitize($_POST['facebook_url']) : '';
        $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
        $display_order = (int) $_POST['display_order'];
        $status = $_POST['status'];

        // Get current photo
        $current = $conn->query("SELECT photo_path FROM instructors WHERE id = $id")->fetch_assoc();
        $photo_path = $current['photo_path'];

        // Handle new photo upload
        if (!empty($_FILES['photo']['name'])) {
            // Delete old photo
            if (!empty($photo_path) && file_exists($photo_path)) {
                deleteFile($photo_path);
            }

            $upload = uploadFile($_FILES['photo'], UPLOAD_DIR . 'instructors/', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
            if ($upload['success']) {
                $photo_path = 'assets/uploads/instructors/' . $upload['filename'];
            } else {
                $error = $upload['message'];
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE instructors SET user_id=?, name=?, photo_path=?, khan_level=?, title=?, location=?, specialization=?, bio=?, facebook_url=?, email=?, phone=?, display_order=?, status=? WHERE id=?");
            $stmt->bind_param("issssssssssisi", $user_id, $name, $photo_path, $khan_level, $title, $location, $specialization, $bio, $facebook_url, $email, $phone, $display_order, $status, $id);

            if ($stmt->execute()) {
                $success = 'Instructor updated successfully!';
            } else {
                $error = 'Failed to update instructor';
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_instructor'])) {
        $id = (int) $_POST['id'];

        // Archive before delete
        require_once 'includes/activity_helper.php';
        $fullRow = $conn->query("SELECT * FROM instructors WHERE id = $id")->fetch_assoc();
        if ($fullRow) {
            archiveRecord($conn, 'instructors', $id, $fullRow['name'], $fullRow);
            logActivity($conn, 'delete', 'instructors', $id, $fullRow['name'], $fullRow['title'].' | '.$fullRow['location']);
        }

        // Get photo path before deleting
        $result = $conn->query("SELECT photo_path FROM instructors WHERE id = $id");
        if ($instructor = $result->fetch_assoc()) {
            // Delete photo file
            if (!empty($instructor['photo_path']) && file_exists($instructor['photo_path'])) {
                deleteFile($instructor['photo_path']);
            }

            // Delete record
            if ($conn->query("DELETE FROM instructors WHERE id = $id")) {
                $success = 'Instructor deleted successfully!';
            } else {
                $error = 'Failed to delete instructor';
            }
        }
    }
}

// Get all instructors with user info
$instructors = $conn->query("
    SELECT i.*, u.serial_number, u.email as user_email 
    FROM instructors i 
    LEFT JOIN users u ON i.user_id = u.id 
    ORDER BY i.display_order ASC, i.name ASC
");

// Get users for dropdown
$available_users = $conn->query("SELECT id, name, email FROM users WHERE role IN ('instructor', 'admin') ORDER BY name");

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
        <h2><i class="fas fa-chalkboard-teacher"></i> Kru Management</h2>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">
            <i class="fas fa-plus-circle"></i> Add New Instructor
        </button>
    </div>

    <div class="search-box">
        <input type="text" placeholder="🔍 Search instructors..." id="searchInput">
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>User Account</th>
                    <th>Khan Level</th>
                    <th>Title/Position</th>
                    <th>Location</th>
                    <th>Contact</th> 
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($instructor = $instructors->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (!empty($instructor['photo_path'])): ?>
                                <img src="<?php echo SITE_URL . '/' . $instructor['photo_path']; ?>" alt="Photo"
                                    style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                            <?php else: ?>
                                <div
                                    style="width: 60px; height: 60px; background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%); display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; color: white; font-size: 1.5rem;">
                                    <?php echo strtoupper(substr($instructor['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($instructor['name']); ?></strong></td>
                        <td>
                            <?php if ($instructor['user_id']): ?>
                                <span
                                    style="display: inline-block; background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($instructor['serial_number']); ?>
                                </span><br>
                                <small style="color: #666;"><?php echo htmlspecialchars($instructor['user_email']); ?></small>
                            <?php else: ?>
                                <span style="color: #999; font-style: italic;">No account</span>
                            <?php endif; ?>
                        </td>
                        <td><strong
                                style="color: #f57c00;"><?php echo htmlspecialchars($instructor['khan_level']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($instructor['title'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($instructor['location'] ?: 'N/A'); ?></td>
                        <td>
                            <?php if ($instructor['email']): ?>
                                <small><i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($instructor['email']); ?></small><br>
                            <?php endif; ?>
                            <?php if ($instructor['phone']): ?>
                                <small><i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($instructor['phone']); ?></small>
                            <?php endif; ?>
                        </td>
                         <td>
                            <span class="badge badge-<?php echo $instructor['status']; ?>"
                                style="padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.85rem;">
                                <?php echo ucfirst($instructor['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-primary"
                                    onclick="editInstructor(<?php echo htmlspecialchars(json_encode($instructor), ENT_QUOTES, 'UTF-8'); ?>)"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this instructor?');">
                                    <input type="hidden" name="id" value="<?php echo $instructor['id']; ?>">
                                    <button type="submit" name="delete_instructor" class="btn btn-sm btn-danger"
                                        title="Delete">
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

<!-- Add Instructor Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-user-plus"></i> Add New Instructor</h2>

        <div class="alert"
            style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
            <strong><i class="fas fa-lightbulb"></i> Tip:</strong> To create an instructor with a user account, use the
            <a href="manage_users_centralized.php" style="color: #1976d2; font-weight: bold;">Centralized User
                Management</a> page instead.
        </div>

        <form method="POST" enctype="multipart/form-data">
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
                    <input type="text" name="name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Khan Level *</label>
                    <input type="text" name="khan_level" class="form-input" placeholder="e.g., Khan 11 (Kru)" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Photo</label>
                <input type="file" name="photo" class="form-input" accept="image/*">
                <small style="color: #666;">Recommended: Square image, min 200x200px</small>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Title/Position</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g., Founder, Head Instructor">
                </div>

                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-input" placeholder="e.g., Quezon City">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Specialization</label>
                <textarea name="specialization" class="form-textarea" rows="2"
                    placeholder="Areas of expertise..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Biography</label>
                <textarea name="bio" class="form-textarea" rows="3" placeholder="Brief biography..."></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-input">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" class="form-input" placeholder="https://facebook.com/...">
                </div>

                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-input" value="0">
                    <small style="color: #666;">Lower numbers appear first</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="action-buttons">
                <button type="submit" name="add_instructor" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Instructor
                </button>
                <button type="button" class="btn btn-outline"
                    onclick="document.getElementById('addModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Instructor Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-user-edit"></i> Edit Instructor</h2>
        <form method="POST" enctype="multipart/form-data" id="editForm">
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
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Khan Level *</label>
                    <input type="text" name="khan_level" id="edit_khan_level" class="form-input" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Photo (leave empty to keep current)</label>
                <input type="file" name="photo" class="form-input" accept="image/*">
                <div id="current_photo" style="margin-top: 10px;"></div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Title/Position</label>
                    <input type="text" name="title" id="edit_title" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" id="edit_location" class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Specialization</label>
                <textarea name="specialization" id="edit_specialization" class="form-textarea" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Biography</label>
                <textarea name="bio" id="edit_bio" class="form-textarea" rows="3"></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="edit_email" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" id="edit_phone" class="form-input">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" id="edit_facebook_url" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" id="edit_display_order" class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" id="edit_status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="action-buttons">
                <button type="submit" name="edit_instructor" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Instructor
                </button>
                <button type="button" class="btn btn-outline"
                    onclick="document.getElementById('editModal').style.display='none'">
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
        background-color: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
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
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
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

    .badge-active {
        background: #4caf50;
        color: white;
    }

    .badge-inactive {
        background: #757575;
        color: white;
    }
</style>

<script>
    function editInstructor(instructor) {
        document.getElementById('edit_id').value = instructor.id;
        document.getElementById('edit_user_id').value = instructor.user_id || '';
        document.getElementById('edit_name').value = instructor.name;
        document.getElementById('edit_khan_level').value = instructor.khan_level;
        document.getElementById('edit_title').value = instructor.title || '';
        document.getElementById('edit_location').value = instructor.location || '';
        document.getElementById('edit_specialization').value = instructor.specialization || '';
        document.getElementById('edit_bio').value = instructor.bio || '';
        document.getElementById('edit_email').value = instructor.email || '';
        document.getElementById('edit_phone').value = instructor.phone || '';
        document.getElementById('edit_facebook_url').value = instructor.facebook_url || '';
        document.getElementById('edit_display_order').value = instructor.display_order;
        document.getElementById('edit_status').value = instructor.status;

        const currentPhoto = document.getElementById('current_photo');
        if (instructor.photo_path) {
            currentPhoto.innerHTML = '<strong>Current photo:</strong><br><img src="<?php echo SITE_URL; ?>/' + instructor.photo_path + '" style="max-width: 150px; margin-top: 10px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">';
        } else {
            currentPhoto.innerHTML = '';
        }

        document.getElementById('editModal').style.display = 'block';
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.data-table tbody tr');

        rows.forEach(function (row) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Close modal when clicking outside 
    window.onclick = function (event) {
        const addModal = document.getElementById('addModal');
        const editModal = document.getElementById('editModal'); // Make sure this var is defined
        if (event.target == addModal) {
            addModal.style.display = 'none';
        }
        if (event.target == editModal) { // Typo fixed
            editModal.style.display = 'none';
        }
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('editModal').style.display = 'none';
        }
    });
</script>

<?php include 'includes/admin_footer.php'; ?>