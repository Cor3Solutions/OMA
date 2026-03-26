<?php
$page_title = "My Profile";
require_once '../config/database.php';
requireLogin();

$conn      = getDbConnection();
$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($user_role === 'admin') { header('Location: ' . SITE_URL . '/admin/index.php'); exit; }

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);

    $check = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "' AND id != $user_id");
    if ($check->num_rows > 0) {
        $error = 'Email already in use by another account.';
    } else {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
            if (!$stmt->execute()) throw new Exception('Failed to update profile.');
            $stmt->close();

            if ($user_role === 'member') {
                $training_location = sanitize($_POST['training_location']);
                $notes             = sanitize($_POST['notes']);
                $stmt = $conn->prepare("UPDATE khan_members SET full_name=?, email=?, phone=?, training_location=?, notes=? WHERE user_id=?");
                $stmt->bind_param("sssssi", $name, $email, $phone, $training_location, $notes, $user_id);
                if (!$stmt->execute()) throw new Exception('Failed to update member profile.');
                $stmt->close();

            } elseif ($user_role === 'instructor') {
                $location       = sanitize($_POST['location']);
                $specialization = sanitize($_POST['specialization']);
                $bio            = sanitize($_POST['bio']);
                $facebook_url   = sanitize($_POST['facebook_url']);

                $current    = $conn->query("SELECT photo_path FROM instructors WHERE user_id = $user_id");
                $photo_path = $current->num_rows > 0 ? $current->fetch_assoc()['photo_path'] : '';

                if (!empty($_FILES['photo']['name'])) {
                    if (!empty($photo_path) && file_exists($photo_path)) deleteFile($photo_path);
                    $upload = uploadFile($_FILES['photo'], UPLOAD_DIR . 'instructors/', ['image/jpeg','image/png','image/gif','image/webp']);
                    if ($upload['success']) {
                        $photo_path = 'assets/uploads/instructors/' . $upload['filename'];
                    } else { throw new Exception($upload['message']); }
                }
                $stmt = $conn->prepare("UPDATE instructors SET name=?, photo_path=?, location=?, specialization=?, bio=?, facebook_url=?, email=?, phone=? WHERE user_id=?");
                $stmt->bind_param("ssssssssi", $name, $photo_path, $location, $specialization, $bio, $facebook_url, $email, $phone, $user_id);
                if (!$stmt->execute()) throw new Exception('Failed to update instructor profile.');
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

$user     = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$role_data = null;
if ($user_role === 'member') {
    $role_data = $conn->query("SELECT km.*, i.name as instructor_name FROM khan_members km LEFT JOIN instructors i ON km.instructor_id = i.id WHERE km.user_id = $user_id")->fetch_assoc();
} elseif ($user_role === 'instructor') {
    $role_data = $conn->query("SELECT * FROM instructors WHERE user_id = $user_id")->fetch_assoc();
}

include 'includes/user_header.php';
?>

<div class="dashboard-container" style="max-width:900px;margin:0 auto;">

    <?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Profile Hero -->
    <div style="background:linear-gradient(135deg,var(--crimson-dark) 0%,#1a0000 100%);border-radius:var(--radius-lg);padding:2.25rem 2.5rem;display:flex;align-items:center;gap:2rem;flex-wrap:wrap;box-shadow:var(--shadow-lg);border:1px solid rgba(201,168,76,0.18);position:relative;overflow:hidden;">
        <div style="position:absolute;top:-50%;right:-5%;width:300px;height:300px;background:radial-gradient(circle,rgba(201,168,76,0.1) 0%,transparent 65%);border-radius:50%;pointer-events:none;"></div>

        <?php if ($user_role === 'instructor' && $role_data && !empty($role_data['photo_path'])): ?>
            <img src="<?php echo SITE_URL.'/'.$role_data['photo_path']; ?>"
                 style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--gold);box-shadow:var(--glow-gold);position:relative;z-index:1;flex-shrink:0;">
        <?php else: ?>
            <div style="width:96px;height:96px;border-radius:50%;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;font-family:'Cinzel',serif;font-size:2.5rem;font-weight:700;color:var(--gold);border:3px solid var(--gold);box-shadow:var(--glow-gold);position:relative;z-index:1;flex-shrink:0;">
                <?php echo strtoupper(substr($user['name'],0,1)); ?>
            </div>
        <?php endif; ?>

        <div style="position:relative;z-index:1;flex:1;">
            <h1 style="color:white;font-size:1.75rem;margin-bottom:0.3rem;"><?php echo htmlspecialchars($user['name']); ?></h1>
            <div style="color:rgba(255,255,255,0.55);font-size:0.9rem;margin-bottom:0.75rem;"><?php echo htmlspecialchars($user['serial_number']); ?></div>
            <span class="badge badge-<?php echo $user_role; ?>"><?php echo ucfirst($user_role); ?></span>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:1.5rem;">

        <!-- Basic Info -->
        <div class="form-section">
            <div class="form-section-header">
                <h2><i class="fas fa-user" style="color:var(--crimson);"></i> Basic Information</h2>
            </div>
            <div class="form-grid">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="09XX XXX XXXX">
                </div>
            </div>
        </div>

        <?php if ($user_role === 'member' && $role_data): ?>
        <!-- Member Info (read-only fields) -->
        <div class="form-section">
            <div class="form-section-header">
                <h2><i class="fas fa-trophy" style="color:var(--crimson);"></i> Academic Information</h2>
                <p class="section-note">Managed by your instructor — read only</p>
            </div>
            <div class="form-grid" style="margin-bottom:1.5rem;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Current Khan Level</label>
                    <div class="readonly-field">Khan <?php echo $role_data['current_khan_level']; ?></div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Khan Color</label>
                    <div class="readonly-field"><?php echo htmlspecialchars($role_data['khan_color'] ?: 'Not assigned'); ?></div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Date Joined</label>
                    <div class="readonly-field"><?php echo formatDate($role_data['date_joined']); ?></div>
                </div>
                <?php if ($role_data['instructor_name']): ?>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Instructor</label>
                    <div class="readonly-field"><?php echo htmlspecialchars($role_data['instructor_name']); ?></div>
                </div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label">Training Location</label>
                <input type="text" name="training_location" class="form-input" value="<?php echo htmlspecialchars($role_data['training_location'] ?? ''); ?>">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Personal Notes</label>
                <textarea name="notes" class="form-textarea" rows="3"><?php echo htmlspecialchars($role_data['notes'] ?? ''); ?></textarea>
            </div>
        </div>

        <?php elseif ($user_role === 'instructor' && $role_data): ?>
        <!-- Instructor Info -->
        <div class="form-section">
            <div class="form-section-header">
                <h2><i class="fas fa-chalkboard-teacher" style="color:var(--crimson);"></i> Instructor Information</h2>
            </div>
            <div class="form-group">
                <label class="form-label">Profile Photo</label>
                <input type="file" name="photo" class="form-input" accept="image/*">
                <small class="form-hint">Leave empty to keep current photo. JPG, PNG, WebP accepted.</small>
            </div>
            <div class="form-grid" style="margin-bottom:1.5rem;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Khan Level</label>
                    <div class="readonly-field"><?php echo htmlspecialchars($role_data['khan_level']); ?></div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Title / Position</label>
                    <div class="readonly-field"><?php echo htmlspecialchars($role_data['title'] ?: 'Not assigned'); ?></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-input" value="<?php echo htmlspecialchars($role_data['location'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Specialization</label>
                <textarea name="specialization" class="form-textarea" rows="2"><?php echo htmlspecialchars($role_data['specialization'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Biography</label>
                <textarea name="bio" class="form-textarea" rows="4"><?php echo htmlspecialchars($role_data['bio'] ?? ''); ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Facebook URL</label>
                <input type="url" name="facebook_url" class="form-input" value="<?php echo htmlspecialchars($role_data['facebook_url'] ?? ''); ?>">
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            <a href="dashboard.php" class="btn btn-outline"><i class="fas fa-times"></i> Cancel</a>
        </div>
    </form>

</div>

<?php include 'includes/user_footer.php'; ?>