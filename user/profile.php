<?php
$page_title = "My Profile";
require_once '../config/database.php';
requireLogin();

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Redirect admins
if ($user_role === 'admin') {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    
    // Check if email exists for other users
    $check = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "' AND id != $user_id");
    if ($check->num_rows > 0) {
        $error = 'Email already exists';
    } else {
        $conn->begin_transaction();
        
        try {
            // Update user
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update profile');
            }
            $stmt->close();
            
            // Update role-specific data
            if ($user_role === 'member') {
                $training_location = sanitize($_POST['training_location']);
                $notes = sanitize($_POST['notes']);
                
                $stmt = $conn->prepare("UPDATE khan_members SET full_name=?, email=?, phone=?, training_location=?, notes=? WHERE user_id=?");
                $stmt->bind_param("sssssi", $name, $email, $phone, $training_location, $notes, $user_id);
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to update member profile');
                }
                $stmt->close();
                
            } elseif ($user_role === 'instructor') {
                $location = sanitize($_POST['location']);
                $specialization = sanitize($_POST['specialization']);
                $bio = sanitize($_POST['bio']);
                $facebook_url = sanitize($_POST['facebook_url']);
                
                // Get current photo
                $current = $conn->query("SELECT photo_path FROM instructors WHERE user_id = $user_id");
                $photo_path = $current->num_rows > 0 ? $current->fetch_assoc()['photo_path'] : '';
                
                // Handle photo upload
                if (!empty($_FILES['photo']['name'])) {
                    // Delete old photo
                    if (!empty($photo_path) && file_exists($photo_path)) {
                        deleteFile($photo_path);
                    }
                    
                    $upload = uploadFile($_FILES['photo'], UPLOAD_DIR . 'instructors/', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                    if ($upload['success']) {
                        $photo_path = 'assets/uploads/instructors/' . $upload['filename'];
                    } else {
                        throw new Exception($upload['message']);
                    }
                }
                
                $stmt = $conn->prepare("UPDATE instructors SET name=?, photo_path=?, location=?, specialization=?, bio=?, facebook_url=?, email=?, phone=? WHERE user_id=?");
                $stmt->bind_param("ssssssssi", $name, $photo_path, $location, $specialization, $bio, $facebook_url, $email, $phone, $user_id);
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to update instructor profile');
                }
                $stmt->close();
            }
            
            $conn->commit();
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

// Get user data
$user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();

// Get role-specific data
$role_data = null;
if ($user_role === 'member') {
    $member_query = $conn->query("
        SELECT km.*, i.name as instructor_name 
        FROM khan_members km 
        LEFT JOIN instructors i ON km.instructor_id = i.id 
        WHERE km.user_id = $user_id
    ");
    $role_data = $member_query->fetch_assoc();
} elseif ($user_role === 'instructor') {
    $instructor_query = $conn->query("SELECT * FROM instructors WHERE user_id = $user_id");
    $role_data = $instructor_query->fetch_assoc();
}

include 'includes/user_header.php';
?>

<div class="profile-container">
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-avatar-section">
            <?php if ($user_role === 'instructor' && $role_data && !empty($role_data['photo_path'])): ?>
                <img src="<?php echo SITE_URL . '/' . $role_data['photo_path']; ?>" alt="Profile" class="profile-avatar-img">
            <?php else: ?>
                <div class="profile-avatar-placeholder">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            <div class="profile-header-info">
                <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                <p class="profile-serial"><?php echo htmlspecialchars($user['serial_number']); ?></p>
                <span class="role-badge <?php echo $user_role; ?>">
                    <?php echo ucfirst($user_role); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <form method="POST" enctype="multipart/form-data" class="profile-form">
        <!-- Basic Information -->
        <div class="form-section">
            <div class="form-section-header">
                <h2><i class="fas fa-user"></i> Basic Information</h2>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="09XX XXX XXXX">
                </div>
            </div>
        </div>

        <?php if ($user_role === 'member' && $role_data): ?>
            <!-- Member Information (Read-only) -->
            <div class="form-section">
                <div class="form-section-header">
                    <h2><i class="fas fa-trophy"></i> Academic Information</h2>
                    <p class="section-note">These fields are managed by your instructor</p>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Current Khan Level</label>
                        <div class="readonly-field">Khan <?php echo $role_data['current_khan_level']; ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Khan Color</label>
                        <div class="readonly-field"><?php echo htmlspecialchars($role_data['khan_color'] ?: 'Not assigned'); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Date Joined</label>
                        <div class="readonly-field"><?php echo formatDate($role_data['date_joined']); ?></div>
                    </div>
                    
                    <?php if ($role_data['instructor_name']): ?>
                    <div class="form-group">
                        <label class="form-label">Instructor</label>
                        <div class="readonly-field"><?php echo htmlspecialchars($role_data['instructor_name']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Training Location</label>
                    <input type="text" name="training_location" class="form-input" value="<?php echo htmlspecialchars($role_data['training_location']); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Personal Notes</label>
                    <textarea name="notes" class="form-textarea" rows="4"><?php echo htmlspecialchars($role_data['notes']); ?></textarea>
                </div>
            </div>
        <?php elseif ($user_role === 'instructor' && $role_data): ?>
            <!-- Instructor Information -->
            <div class="form-section">
                <div class="form-section-header">
                    <h2><i class="fas fa-chalkboard-teacher"></i> Instructor Information</h2>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Profile Photo</label>
                    <input type="file" name="photo" class="form-input" accept="image/*">
                    <small class="form-hint">Leave empty to keep current photo</small>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Khan Level</label>
                        <div class="readonly-field"><?php echo htmlspecialchars($role_data['khan_level']); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Title/Position</label>
                        <div class="readonly-field"><?php echo htmlspecialchars($role_data['title'] ?: 'Not assigned'); ?></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-input" value="<?php echo htmlspecialchars($role_data['location']); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Specialization</label>
                    <textarea name="specialization" class="form-textarea" rows="2"><?php echo htmlspecialchars($role_data['specialization']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Biography</label>
                    <textarea name="bio" class="form-textarea" rows="4"><?php echo htmlspecialchars($role_data['bio']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" class="form-input" value="<?php echo htmlspecialchars($role_data['facebook_url']); ?>">
                </div>
            </div>
        <?php endif; ?>

        <!-- Submit Buttons -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<style>
.profile-container {
    max-width: 900px;
    margin: 0 auto;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: var(--radius);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-success {
    background: #e8f5e9;
    color: #2e7d32;
    border-left: 4px solid #4caf50;
}

.alert-error {
    background: #ffebee;
    color: #c62828;
    border-left: 4px solid #f44336;
}

.profile-header {
    background: white;
    padding: 2rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
}

.profile-avatar-section {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.profile-avatar-img,
.profile-avatar-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.profile-avatar-placeholder {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 700;
}

.profile-header-info h1 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.profile-serial {
    color: var(--text-light);
    font-size: 1.125rem;
    margin-bottom: 0.75rem;
}

.role-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
}

.role-badge.member {
    background: #e3f2fd;
    color: #1976d2;
}

.role-badge.instructor {
    background: #fff3e0;
    color: #f57c00;
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.form-section {
    background: white;
    padding: 2rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.form-section-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border);
}

.form-section-header h2 {
    font-size: 1.5rem;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.25rem;
}

.section-note {
    color: var(--text-light);
    font-size: 0.875rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text);
}

.form-input,
.form-textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    transition: var(--transition);
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
}

.form-textarea {
    resize: vertical;
}

.form-hint {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-light);
}

.readonly-field {
    padding: 0.75rem 1rem;
    background: var(--light);
    border: 2px solid var(--border);
    border-radius: 8px;
    font-weight: 600;
    color: var(--text);
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding: 2rem;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.btn-outline {
    background: transparent;
    border: 2px solid var(--border);
    color: var(--text);
}

.btn-outline:hover {
    background: var(--light);
    border-color: var(--text-light);
}

@media (max-width: 768px) {
    .profile-avatar-section {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-header-info h1 {
        font-size: 1.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<?php include 'includes/user_footer.php'; ?>