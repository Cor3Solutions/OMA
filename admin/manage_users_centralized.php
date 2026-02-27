<?php
$page_title = "Centralized User Management";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

// ── PAGINATION HELPER ─────────────────────────────────────────────────
function buildPaginationBar($total, $per_page, $current_page, $extra_params = []) {
    $total_pages = max(1, ceil($total / $per_page));
    $makeUrl = function($p) use ($per_page, $extra_params) {
        $params = array_merge($extra_params, ['page' => $p]);
        if ($per_page !== 10) $params['per_page'] = $per_page;
        return '?' . http_build_query($params);
    };
    $btnBase   = 'display:inline-block;padding:.35rem .7rem;border-radius:5px;border:1px solid #ddd;font-size:.85rem;text-decoration:none;color:#333;background:#fff;';
    $btnActive  = 'background:#007bff;color:#fff;border-color:#007bff;font-weight:600;';
    $btnDis    = 'opacity:.45;pointer-events:none;';
    ob_start(); ?>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-top:1rem;padding:.8rem 1rem;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;">
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <span style="color:#666;font-size:.88rem;">
                Showing <strong><?= min(($current_page-1)*$per_page+1,$total) ?>–<?= min($current_page*$per_page,$total) ?></strong>
                of <strong><?= $total ?></strong>
            </span>
            <form method="GET" style="display:flex;align-items:center;gap:.4rem;">
                <?php foreach($extra_params as $k=>$v): ?><input type="hidden" name="<?=$k?>" value="<?=htmlspecialchars($v)?>"><?php endforeach; ?>
                <input type="hidden" name="page" value="1">
                <label style="font-size:.85rem;color:#666;">Rows:</label>
                <select name="per_page" onchange="this.form.submit()" style="padding:.3rem .5rem;border:1px solid #ddd;border-radius:5px;font-size:.85rem;cursor:pointer;">
                    <?php foreach([10,25,50,100] as $opt): ?>
                        <option value="<?=$opt?>" <?=$per_page==$opt?'selected':''?>><?=$opt?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php if($total_pages>1): ?>
        <div style="display:flex;gap:.3rem;align-items:center;">
            <?php
            $pd=$current_page<=1?$btnDis:'';
            echo "<a href='{$makeUrl($current_page-1)}' style='{$btnBase}{$pd}'>&laquo;</a>";
            $rng=2;$sp=max(1,$current_page-$rng);$ep=min($total_pages,$current_page+$rng);
            if($ep-$sp<$rng*2){$sp=max(1,$ep-$rng*2);$ep=min($total_pages,$sp+$rng*2);}
            if($sp>1){echo "<a href='{$makeUrl(1)}' style='{$btnBase}'>1</a>";if($sp>2)echo "<span style='padding:.35rem .5rem;color:#999;font-size:.85rem;'>…</span>";}
            for($p=$sp;$p<=$ep;$p++){$a=$p===$current_page?$btnActive:'';echo "<a href='{$makeUrl($p)}' style='{$btnBase}{$a}'>{$p}</a>";}
            if($ep<$total_pages){if($ep<$total_pages-1)echo "<span style='padding:.35rem .5rem;color:#999;font-size:.85rem;'>…</span>";echo "<a href='{$makeUrl($total_pages)}' style='{$btnBase}'>{$total_pages}</a>";}
            $nd=$current_page>=$total_pages?$btnDis:'';
            echo "<a href='{$makeUrl($current_page+1)}' style='{$btnBase}{$nd}'>&raquo;</a>";
            ?>
        </div>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}
// ─────────────────────────────────────────────────────────────────────


// Helper to safely delete files from admin directory
function safeDeleteFile($path) {
    if (empty($path)) return;
    // Check if path is absolute or relative
    $target = $path;
    if (!file_exists($target) && file_exists('../' . $target)) {
        $target = '../' . $target;
    }
    if (file_exists($target)) {
        // Assuming deleteFile is in your config/functions, otherwise use unlink
        if (function_exists('deleteFile')) {
            deleteFile($target);
        } else {
            unlink($target);
        }
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
        $password = $_POST['password'];
        $role = $_POST['role'];
        $status = $_POST['status'];

        if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            // Check if email exists
            $check = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "'");
            if ($check->num_rows > 0) {
                $error = 'Email already exists';
            } else {
                // Start transaction
                $conn->begin_transaction();

                try {
                    // Insert user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $name, $email, $phone, $hashed_password, $role, $status);

                    if (!$stmt->execute()) {
                        throw new Exception('Failed to create user: ' . $stmt->error);
                    }

                    $user_id = $conn->insert_id;
                    $stmt->close();

                    // Handle role-specific data
                    if ($role === 'member') {
                        $current_khan_level = isset($_POST['current_khan_level']) ? (int) $_POST['current_khan_level'] : 1;
                        
                        // Get khan color from database based on level
                        $color_result = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $current_khan_level");
                        $khan_color = '';
                        if ($color_result && $color_row = $color_result->fetch_assoc()) {
                            $khan_color = $color_row['color_name'];
                        }
                        
                        $date_joined = !empty($_POST['date_joined']) ? $_POST['date_joined'] : date('Y-m-d');
                        $date_promoted = !empty($_POST['date_promoted']) ? $_POST['date_promoted'] : null;
                        $instructor_id = !empty($_POST['instructor_id']) ? (int) $_POST['instructor_id'] : null;
                        $training_location = isset($_POST['training_location']) ? sanitize($_POST['training_location']) : '';
                        $member_notes = isset($_POST['member_notes']) ? sanitize($_POST['member_notes']) : '';

                        $stmt = $conn->prepare("INSERT INTO khan_members (user_id, full_name, email, phone, current_khan_level, khan_color, date_joined, date_promoted, instructor_id, training_location, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("isssississss", $user_id, $name, $email, $phone, $current_khan_level, $khan_color, $date_joined, $date_promoted, $instructor_id, $training_location, $status, $member_notes);

                        if (!$stmt->execute()) {
                            throw new Exception('Failed to create khan member profile: ' . $stmt->error);
                        }
                        $stmt->close();
                    } elseif ($role === 'instructor') {
                        $khan_level = isset($_POST['khan_level']) ? sanitize($_POST['khan_level']) : '';
                        $title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
                        $location = isset($_POST['location']) ? sanitize($_POST['location']) : '';
                        $specialization = isset($_POST['specialization']) ? sanitize($_POST['specialization']) : '';
                        $bio = isset($_POST['bio']) ? sanitize($_POST['bio']) : '';
                        $facebook_url = isset($_POST['facebook_url']) ? sanitize($_POST['facebook_url']) : '';
                        $display_order = isset($_POST['display_order']) ? (int) $_POST['display_order'] : 0;

                        // Handle photo upload
                        $photo_path = '';
                        if (!empty($_FILES['photo']['name'])) {
                            // Ensure UPLOAD_DIR is correctly defined in config/database.php
                            $upload = uploadFile($_FILES['photo'], UPLOAD_DIR . 'instructors/', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                            if ($upload['success']) {
                                $photo_path = 'assets/uploads/instructors/' . $upload['filename'];
                            } else {
                                throw new Exception($upload['message']);
                            }
                        }

                        $stmt = $conn->prepare("INSERT INTO instructors (user_id, name, photo_path, khan_level, title, location, specialization, bio, facebook_url, email, phone, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("issssssssssis", $user_id, $name, $photo_path, $khan_level, $title, $location, $specialization, $bio, $facebook_url, $email, $phone, $display_order, $status);

                        if (!$stmt->execute()) {
                            throw new Exception('Failed to create instructor profile: ' . $stmt->error);
                        }
                        $stmt->close();
                    }

                    // Commit transaction
                    $conn->commit();
                    $success = 'User and profile created successfully!';

                } catch (Exception $e) {
                    // Rollback on error
                    $conn->rollback();
                    $error = $e->getMessage();
                }
            }
        }
    } elseif (isset($_POST['edit_user'])) {
        $id = (int) $_POST['id'];
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
        $role = $_POST['role'];
        $status = $_POST['status'];
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        // Get current user data to check if email is being changed
        $current_user = $conn->query("SELECT email FROM users WHERE id = $id");
        if (!$current_user || $current_user->num_rows === 0) {
            $error = 'User not found';
        } else {
            $current_email = $current_user->fetch_assoc()['email'];

            // Only check for email conflicts if email is being changed
            $email_conflict = false;
            if ($email !== $current_email) {
                $email_escaped = $conn->real_escape_string($email);
                $check = $conn->query("SELECT id FROM users WHERE email = '$email_escaped' AND id != $id");
                if ($check && $check->num_rows > 0) {
                    $email_conflict = true;
                    $error = 'Email already exists for another user';
                }
            }

            if (!$email_conflict) {
                $conn->begin_transaction();

                try {
                    // Update user - handle password separately
                    if (!empty($password)) {
                        if (strlen($password) < 6) throw new Exception("Password must be at least 6 characters.");
                        
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, password=?, role=?, status=? WHERE id=?");
                        $stmt->bind_param("ssssssi", $name, $email, $phone, $hashed_password, $role, $status, $id);
                    } else {
                        // Update without changing password
                        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, role=?, status=? WHERE id=?");
                        $stmt->bind_param("sssssi", $name, $email, $phone, $role, $status, $id);
                    }

                    if (!$stmt->execute()) {
                        throw new Exception('Failed to update user: ' . $stmt->error);
                    }
                    $stmt->close();

                    // Update role-specific data
                    if ($role === 'member') {
                        $current_khan_level = isset($_POST['current_khan_level']) ? (int) $_POST['current_khan_level'] : 1;
                        
                        // Get khan color from database based on level
                        $color_result = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $current_khan_level");
                        $khan_color = '';
                        if ($color_result && $color_row = $color_result->fetch_assoc()) {
                            $khan_color = $color_row['color_name'];
                        }
                        
                        $date_joined = !empty($_POST['date_joined']) ? $_POST['date_joined'] : date('Y-m-d');
                        $date_promoted = !empty($_POST['date_promoted']) ? $_POST['date_promoted'] : null;
                        $instructor_id = !empty($_POST['instructor_id']) ? (int) $_POST['instructor_id'] : null;
                        $training_location = isset($_POST['training_location']) ? sanitize($_POST['training_location']) : '';
                        $member_notes = isset($_POST['member_notes']) ? sanitize($_POST['member_notes']) : '';

                        // Check if khan_member exists
                        $check = $conn->query("SELECT id FROM khan_members WHERE user_id = $id");
                        if ($check && $check->num_rows > 0) {
                            // Update existing
                            $stmt = $conn->prepare("UPDATE khan_members SET full_name=?, email=?, phone=?, current_khan_level=?, khan_color=?, date_joined=?, date_promoted=?, instructor_id=?, training_location=?, status=?, notes=? WHERE user_id=?");
                            $stmt->bind_param("sssississssi", $name, $email, $phone, $current_khan_level, $khan_color, $date_joined, $date_promoted, $instructor_id, $training_location, $status, $member_notes, $id);
                        } else {
                            // Create new
                            $stmt = $conn->prepare("INSERT INTO khan_members (user_id, full_name, email, phone, current_khan_level, khan_color, date_joined, date_promoted, instructor_id, training_location, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("isssississss", $id, $name, $email, $phone, $current_khan_level, $khan_color, $date_joined, $date_promoted, $instructor_id, $training_location, $status, $member_notes);
                        }

                        if (!$stmt->execute()) {
                            throw new Exception('Failed to update khan member profile: ' . $stmt->error);
                        }
                        $stmt->close();

                        // Cleanup: Delete instructor profile if exists (role changed)
                        $result = $conn->query("SELECT photo_path FROM instructors WHERE user_id = $id");
                        if ($result && $instructor = $result->fetch_assoc()) {
                            safeDeleteFile($instructor['photo_path']);
                        }
                        $conn->query("DELETE FROM instructors WHERE user_id = $id");
                    } elseif ($role === 'instructor') {
                        $khan_level = isset($_POST['khan_level']) ? sanitize($_POST['khan_level']) : '';
                        $title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
                        $location = isset($_POST['location']) ? sanitize($_POST['location']) : '';
                        $specialization = isset($_POST['specialization']) ? sanitize($_POST['specialization']) : '';
                        $bio = isset($_POST['bio']) ? sanitize($_POST['bio']) : '';
                        $facebook_url = isset($_POST['facebook_url']) ? sanitize($_POST['facebook_url']) : '';
                        $display_order = isset($_POST['display_order']) ? (int) $_POST['display_order'] : 0;

                        // Get current photo
                        $current = $conn->query("SELECT photo_path FROM instructors WHERE user_id = $id");
                        $photo_path = ($current && $current->num_rows > 0) ? $current->fetch_assoc()['photo_path'] : '';

                        // Handle new photo upload
                        if (!empty($_FILES['photo']['name'])) {
                            // Delete old photo
                            safeDeleteFile($photo_path);

                            $upload = uploadFile($_FILES['photo'], UPLOAD_DIR . 'instructors/', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                            if ($upload['success']) {
                                $photo_path = 'assets/uploads/instructors/' . $upload['filename'];
                            } else {
                                throw new Exception($upload['message']);
                            }
                        }

                        // Check if instructor exists
                        $check = $conn->query("SELECT id FROM instructors WHERE user_id = $id");
                        if ($check && $check->num_rows > 0) {
                            // Update existing
                            $stmt = $conn->prepare("UPDATE instructors SET name=?, photo_path=?, khan_level=?, title=?, location=?, specialization=?, bio=?, facebook_url=?, email=?, phone=?, display_order=?, status=? WHERE user_id=?");
                            $stmt->bind_param("ssssssssssisi", $name, $photo_path, $khan_level, $title, $location, $specialization, $bio, $facebook_url, $email, $phone, $display_order, $status, $id);
                        } else {
                            // Create new
                            $stmt = $conn->prepare("INSERT INTO instructors (user_id, name, photo_path, khan_level, title, location, specialization, bio, facebook_url, email, phone, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("issssssssssis", $id, $name, $photo_path, $khan_level, $title, $location, $specialization, $bio, $facebook_url, $email, $phone, $display_order, $status);
                        }

                        if (!$stmt->execute()) {
                            throw new Exception('Failed to update instructor profile: ' . $stmt->error);
                        }
                        $stmt->close();

                        // Cleanup: Delete member profile if exists (role changed)
                        $conn->query("DELETE FROM khan_members WHERE user_id = $id");
                    } else {
                        // If role is admin, remove any instructor or member profiles
                        $result = $conn->query("SELECT photo_path FROM instructors WHERE user_id = $id");
                        if ($result && $instructor = $result->fetch_assoc()) {
                            safeDeleteFile($instructor['photo_path']);
                        }
                        $conn->query("DELETE FROM instructors WHERE user_id = $id");
                        $conn->query("DELETE FROM khan_members WHERE user_id = $id");
                    }

                    $conn->commit();
                    $success = 'User updated successfully!';

                } catch (Exception $e) {
                    $conn->rollback();
                    $error = $e->getMessage();
                }
            }
        }
    } elseif (isset($_POST['delete_user'])) {
        $id = (int) $_POST['id'];

        // Prevent deleting yourself
        if ($id == $_SESSION['user_id']) {
            $error = 'You cannot delete your own account';
        } else {
            // Archive before delete
            require_once 'includes/activity_helper.php';
            $fullRow = $conn->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();
            if ($fullRow) {
                archiveRecord($conn, 'users', $id, $fullRow['name'].' ('.$fullRow['role'].')', $fullRow);
                logActivity($conn, 'delete', 'users', $id, $fullRow['name'], 'Role: '.$fullRow['role'].' | Email: '.$fullRow['email']);
            }

            $conn->begin_transaction();

            try {
                // Delete instructor photo if exists
                $result = $conn->query("SELECT photo_path FROM instructors WHERE user_id = $id");
                if ($instructor = $result->fetch_assoc()) {
                    safeDeleteFile($instructor['photo_path']);
                }

                // Delete related records
                $conn->query("DELETE FROM instructors WHERE user_id = $id");
                $conn->query("DELETE FROM khan_members WHERE user_id = $id");

                // Delete user
                if (!$conn->query("DELETE FROM users WHERE id = $id")) {
                    throw new Exception('Failed to delete user');
                }

                $conn->commit();
                $success = 'User deleted successfully!';

            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
    }
}

// Get all users with their role-specific data — PAGINATED
$_per_page  = isset($_GET['per_page']) ? max(10, min(100, (int)$_GET['per_page'])) : 10;
$_cur_page  = isset($_GET['page'])     ? max(1, (int)$_GET['page']) : 1;
$_offset    = ($_cur_page - 1) * $_per_page;
$_total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
if ($_cur_page > max(1, ceil($_total_users / $_per_page))) { $_cur_page = max(1, ceil($_total_users / $_per_page)); $_offset = ($_cur_page-1)*$_per_page; }

$users = $conn->query("
    SELECT 
        u.*,
        i.khan_level as instructor_khan_level,
        i.title as instructor_title,
        i.photo_path as instructor_photo,
        km.current_khan_level,
        km.khan_color
    FROM users u
    LEFT JOIN instructors i ON u.id = i.user_id
    LEFT JOIN khan_members km ON u.id = km.user_id
    ORDER BY u.created_at DESC
    LIMIT $_per_page OFFSET $_offset
");

// Get instructors for dropdown
$instructors = $conn->query("SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name");

// Get khan colors for automatic mapping
$khan_colors = $conn->query("SELECT khan_level, color_name, hex_color FROM khan_colors ORDER BY khan_level ASC");

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
        <h2><i class="fas fa-users-cog"></i> Centralized User Management</h2>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus-circle"></i> Add New User
        </button>
    </div>

    <div class="info-box" style="background: #e3f2fd; border-left: 4px solid #1976d2; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
        <strong><i class="fas fa-info-circle"></i> Tip:</strong> Select a role when creating a user to automatically show the relevant form fields. Khan colors are automatically assigned based on level.
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Serial / Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Role Details</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users && $users->num_rows > 0): ?>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr data-role="<?php echo $user['role']; ?>" data-status="<?php echo $user['status']; ?>">
                            <td>
                                <?php if ($user['role'] === 'instructor' && !empty($user['instructor_photo'])): ?>
                                    <img src="<?php echo SITE_URL . '/' . $user['instructor_photo']; ?>" alt="Photo" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; color: white; font-size: 1.2rem;">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="display: block;"><?php echo htmlspecialchars($user['name']); ?></strong>
                                <small style="color: #666;"><?php echo htmlspecialchars($user['serial_number']); ?></small>
                            </td>
                            <td><small><?php echo htmlspecialchars($user['email']); ?></small></td>
                            <td><small><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></small></td>
                            <td>
                                <span class="badge" style="background: <?php echo $user['role'] === 'admin' ? '#1976d2' : ($user['role'] === 'instructor' ? '#f57c00' : '#388e3c'); ?>; color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.85rem;">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['role'] === 'instructor'): ?>
                                    <small><strong style="color: #f57c00;"><?php echo htmlspecialchars($user['instructor_khan_level'] ?? 'N/A'); ?></strong></small><br>
                                    <small style="color: #666;"><?php echo htmlspecialchars($user['instructor_title'] ?: 'No title'); ?></small>
                                <?php elseif ($user['role'] === 'member'): ?>
                                    <small><strong style="color: #388e3c;">Khan <?php echo $user['current_khan_level'] ?? '1'; ?></strong></small><br>
                                    <small style="color: #666;"><?php echo htmlspecialchars($user['khan_color'] ?: 'No color'); ?></small>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $user['status']; ?>" style="padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.85rem;">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td><small><?php echo formatDate($user['created_at']); ?></small></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-primary" onclick="editUser(<?php echo $user['id']; ?>)" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user and all associated data?');">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger" title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 3rem;">
                            <div style="color: #999;">
                                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                <h3 style="margin: 0 0 0.5rem 0; color: #666;">No Users Found</h3>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo buildPaginationBar($_total_users, $_per_page, $_cur_page); ?>
</div>

<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeAddModal()">&times;</span>
        <h2><i class="fas fa-user-plus"></i> Add New User</h2>
        <form method="POST" enctype="multipart/form-data" id="addForm">
            <div class="form-section">
                <h3 style="margin-bottom: 1rem; color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 0.5rem;">
                    <i class="fas fa-user-circle"></i> Basic Information
                </h3>
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
                        <input type="tel" name="phone" class="form-input" placeholder="09XX XXX XXXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div style="display:flex; gap:0.5rem; align-items:center;">
                            <input type="text" name="password" id="add_password" class="form-input" 
                                   placeholder="Auto-generated from name" readonly
                                   style="background:#f5f5f5; font-family:monospace; letter-spacing:1px;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="copyPassword()" title="Copy password">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <small style="color:#888;">Format: <strong>oma</strong> + first name + last name (e.g. <em>omajuancruz</em>). Member can change after login.</small>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role" id="add_role" class="form-select" required onchange="toggleRoleFields('add')">
                            <option value="">-- Select Role --</option>
                            <option value="admin">Admin</option>
                            <option value="instructor">Instructor</option>
                            <option value="member">Member</option>
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
            </div>

            <div id="add_instructor_fields" class="form-section" style="display: none;">
                <h3 style="margin: 1.5rem 0 1rem; color: #f57c00; border-bottom: 2px solid #f57c00; padding-bottom: 0.5rem;">
                    <i class="fas fa-chalkboard-teacher"></i> Instructor Information
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Khan Level</label>
                        <input type="text" name="khan_level" class="form-input" placeholder="e.g., Khan 11 (Kru)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Title/Position</label>
                        <input type="text" name="title" class="form-input" placeholder="e.g., Head Instructor, Founder">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Profile Photo</label>
                    <input type="file" name="photo" class="form-input" accept="image/*">
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-input" placeholder="e.g., Quezon City">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="display_order" class="form-input" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Specialization</label>
                    <textarea name="specialization" class="form-textarea" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Biography</label>
                    <textarea name="bio" class="form-textarea" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" class="form-input">
                </div>
            </div>

            <div id="add_member_fields" class="form-section" style="display: none;">
                <h3 style="margin: 1.5rem 0 1rem; color: #388e3c; border-bottom: 2px solid #388e3c; padding-bottom: 0.5rem;">
                    <i class="fas fa-user-graduate"></i> Member Information
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Current Khan Level *</label>
                        <select name="current_khan_level" id="add_current_khan_level" class="form-select" onchange="updateKhanColor('add')">
                            <?php for ($i = 1; $i <= 16; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == 1 ? 'selected' : ''; ?>>Khan <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Khan Color/Band (Auto-filled)</label>
                        <div style="position: relative;">
                            <input type="text" 
                                   name="khan_color" 
                                   id="add_khan_color_display"
                                   class="form-input"
                                   value="White"
                                   readonly
                                   style="padding-left: 45px; background-color: #f5f5f5; cursor: not-allowed;">
                            <div id="add_color_indicator" 
                                 style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 25px; height: 25px; border-radius: 50%; border: 2px solid #999; background-color: #FFFFFF;">
                            </div>
                        </div>
                        <small style="color: #666; display: block; margin-top: 0.25rem;">
                            Auto-assigned based on Khan Level
                        </small>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Date Joined *</label>
                        <input type="date" name="date_joined" class="form-input" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Promoted (Optional)</label>
                        <input type="date" name="date_promoted" class="form-input">
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Assigned Instructor/Kru</label>
                        <select name="instructor_id" class="form-select">
                            <option value="">-- No Instructor --</option>
                            <?php
                            if ($instructors) {
                                $instructors->data_seek(0);
                                while ($instructor = $instructors->fetch_assoc()):
                            ?>
                                <option value="<?php echo $instructor['id']; ?>">
                                    <?php echo htmlspecialchars($instructor['name']); ?>
                                </option>
                            <?php
                                endwhile;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Training Location</label>
                        <input type="text" name="training_location" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="member_notes" class="form-textarea" rows="3"></textarea>
                </div>
            </div>

            <div class="action-buttons" style="margin-top: 2rem; border-top: 1px solid #ddd; padding-top: 1rem;">
                <button type="submit" name="add_user" class="btn btn-primary"><i class="fas fa-save"></i> Create User</button>
                <button type="button" class="btn btn-outline" onclick="closeAddModal()"><i class="fas fa-times"></i> Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeEditModal()">&times;</span>
        <h2><i class="fas fa-user-edit"></i> Edit User</h2>
        <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            <div id="edit_loading" style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #1976d2;"></i>
                <p style="margin-top: 1rem;">Loading user data...</p>
            </div>
            <div id="edit_content" style="display: none;"></div>
        </form>
    </div>
</div>

<style>
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .modal-content { background-color: #fefefe; margin: 2% auto; padding: 2rem; border-radius: 8px; width: 95%; max-width: 1000px; max-height: 90vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.3); animation: slideDown 0.3s; }
    @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .modal-close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; transition: color 0.3s; }
    .modal-close:hover { color: #000; }
    .form-section { margin-bottom: 1.5rem; }
    .badge-active { background: #4caf50; color: white; }
    .badge-inactive { background: #757575; color: white; }
    .badge-suspended { background: #f44336; color: white; }
    .action-buttons .btn { margin-right: 0.3rem; }
</style>

<script>
    // Khan color mapping from database
    const khanColorMap = {
        <?php 
        $khan_colors->data_seek(0);
        $color_map = [];
        while($kc = $khan_colors->fetch_assoc()) {
            $color_map[] = $kc['khan_level'] . ": { name: '" . addslashes($kc['color_name']) . "', hex: '" . $kc['hex_color'] . "' }";
        }
        echo implode(",\n        ", $color_map);
        ?>
    };

    // Update khan color when level changes
    function updateKhanColor(prefix) {
        const level = document.getElementById(prefix + '_current_khan_level').value;
        const colorDisplay = document.getElementById(prefix + '_khan_color_display');
        const colorIndicator = document.getElementById(prefix + '_color_indicator');
        
        if (khanColorMap[level]) {
            colorDisplay.value = khanColorMap[level].name;
            colorIndicator.style.backgroundColor = khanColorMap[level].hex;
            
            // Add border for light colors for better visibility
            if (['#FFFFFF', '#FFFACD', '#90EE90', '#87CEEB', '#D2B48C', '#FFB6C1'].includes(khanColorMap[level].hex)) {
                colorIndicator.style.border = '2px solid #999';
            } else {
                colorIndicator.style.border = '2px solid #ddd';
            }
        }
    }

    function openAddModal() {
        document.getElementById('addModal').style.display = 'block';
        document.getElementById('addForm').reset();
        toggleRoleFields('add');
        // Initialize khan color on modal open
        setTimeout(() => updateKhanColor('add'), 100);
    }
    
    function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }
    function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }

    function toggleRoleFields(prefix) {
        const role = document.getElementById(prefix + '_role').value;
        const instructorFields = document.getElementById(prefix + '_instructor_fields');
        const memberFields = document.getElementById(prefix + '_member_fields');
        
        // Reset displays
        instructorFields.style.display = 'none';
        memberFields.style.display = 'none';
        
        // Remove all required attributes first
        instructorFields.querySelectorAll('[required]').forEach(el => el.removeAttribute('required'));
        memberFields.querySelectorAll('[required]').forEach(el => el.removeAttribute('required'));

        if (role === 'instructor') {
            instructorFields.style.display = 'block';
        } else if (role === 'member') {
            memberFields.style.display = 'block';
            // Update khan color when member role is selected
            if (prefix === 'add') {
                setTimeout(() => updateKhanColor('add'), 100);
            }
        }
    }

    function editUser(userId) {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('edit_id').value = userId;
        document.getElementById('edit_loading').style.display = 'block';
        document.getElementById('edit_content').style.display = 'none';

        fetch('ajax/get_user_data.php?id=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateEditForm(data.user, data.instructors_list);
                } else {
                    alert('Error loading user data: ' + (data.message || 'Unknown error'));
                    closeEditModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading user data');
                closeEditModal();
            });
    }

    function populateEditForm(user, instructorsList) {
        const content = document.getElementById('edit_content');
        
        const joinedDate = user.member_data?.date_joined ? user.member_data.date_joined.split(' ')[0] : '';
        const promotedDate = user.member_data?.date_promoted ? user.member_data.date_promoted.split(' ')[0] : '';

        const instructorOptions = instructorsList.map(inst => `
            <option value="${inst.id}" ${user.member_data?.instructor_id == inst.id ? 'selected' : ''}>
                ${escapeHtml(inst.name)}
            </option>
        `).join('');

        // Get current khan color info
        const currentLevel = user.member_data?.current_khan_level || 1;
        const currentColorInfo = khanColorMap[currentLevel] || { name: 'White', hex: '#FFFFFF' };

        let html = `
        <div class="form-section">
            <h3 style="margin-bottom: 1rem; color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 0.5rem;">
                <i class="fas fa-user-circle"></i> Basic Information
            </h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-input" value="${escapeHtml(user.name)}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-input" value="${escapeHtml(user.email)}" required>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-input" value="${escapeHtml(user.phone || '')}">
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-input" minlength="6">
                    <small style="color: #666;">Leave empty to keep current password. Default format: <strong>oma</strong> + firstname + lastname</small>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select name="role" id="edit_role" class="form-select" required onchange="toggleRoleFields('edit')">
                        <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                        <option value="instructor" ${user.role === 'instructor' ? 'selected' : ''}>Instructor</option>
                        <option value="member" ${user.role === 'member' ? 'selected' : ''}>Member</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="active" ${user.status === 'active' ? 'selected' : ''}>Active</option>
                        <option value="inactive" ${user.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                        <option value="suspended" ${user.status === 'suspended' ? 'selected' : ''}>Suspended</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div id="edit_instructor_fields" class="form-section" style="display: ${user.role === 'instructor' ? 'block' : 'none'};">
            <h3 style="margin: 1.5rem 0 1rem; color: #f57c00; border-bottom: 2px solid #f57c00; padding-bottom: 0.5rem;">
                <i class="fas fa-chalkboard-teacher"></i> Instructor Information
            </h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Khan Level</label>
                    <input type="text" name="khan_level" class="form-input" value="${escapeHtml(user.instructor_data?.khan_level || '')}">
                </div>
                <div class="form-group">
                    <label class="form-label">Title/Position</label>
                    <input type="text" name="title" class="form-input" value="${escapeHtml(user.instructor_data?.title || '')}">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Photo (leave empty to keep current)</label>
                <input type="file" name="photo" class="form-input" accept="image/*">
                ${user.instructor_data?.photo_path ? `
                    <div style="margin-top: 10px;">
                        <strong>Current photo:</strong><br>
                        <img src="<?php echo SITE_URL; ?>/${user.instructor_data.photo_path}" style="max-width: 150px; margin-top: 10px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    </div>
                ` : ''}
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-input" value="${escapeHtml(user.instructor_data?.location || '')}">
                </div>
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-input" value="${user.instructor_data?.display_order || 0}">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Specialization</label>
                <textarea name="specialization" class="form-textarea" rows="2">${escapeHtml(user.instructor_data?.specialization || '')}</textarea>
            </div>
             <div class="form-group">
                <label class="form-label">Biography</label>
                <textarea name="bio" class="form-textarea" rows="3">${escapeHtml(user.instructor_data?.bio || '')}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Facebook URL</label>
                <input type="url" name="facebook_url" class="form-input" value="${escapeHtml(user.instructor_data?.facebook_url || '')}">
            </div>
        </div>
        
        <div id="edit_member_fields" class="form-section" style="display: ${user.role === 'member' ? 'block' : 'none'};">
            <h3 style="margin: 1.5rem 0 1rem; color: #388e3c; border-bottom: 2px solid #388e3c; padding-bottom: 0.5rem;">
                <i class="fas fa-user-graduate"></i> Member Information
            </h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Current Khan Level *</label>
                    <select name="current_khan_level" id="edit_current_khan_level" class="form-select" onchange="updateKhanColor('edit')">
                        <?php for ($i = 1; $i <= 16; $i++): ?>
                            <option value="<?php echo $i; ?>" ${currentLevel == <?php echo $i; ?> ? 'selected' : ''}>Khan <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Khan Color/Band (Auto-filled)</label>
                    <div style="position: relative;">
                        <input type="text" 
                               name="khan_color" 
                               id="edit_khan_color_display"
                               class="form-input"
                               value="${currentColorInfo.name}"
                               readonly
                               style="padding-left: 45px; background-color: #f5f5f5; cursor: not-allowed;">
                        <div id="edit_color_indicator" 
                             style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 25px; height: 25px; border-radius: 50%; border: 2px solid #999; background-color: ${currentColorInfo.hex};">
                        </div>
                    </div>
                    <small style="color: #666; display: block; margin-top: 0.25rem;">
                        Auto-assigned based on Khan Level
                    </small>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Date Joined *</label>
                    <input type="date" name="date_joined" class="form-input" value="${joinedDate}">
                </div>
                <div class="form-group">
                    <label class="form-label">Date Promoted</label>
                    <input type="date" name="date_promoted" class="form-input" value="${promotedDate}">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Assigned Instructor/Kru</label>
                    <select name="instructor_id" class="form-select">
                        <option value="">-- No Instructor --</option>
                        ${instructorOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Training Location</label>
                    <input type="text" name="training_location" class="form-input" value="${escapeHtml(user.member_data?.training_location || '')}">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="member_notes" class="form-textarea" rows="3">${escapeHtml(user.member_data?.notes || '')}</textarea>
            </div>
        </div>
        
        <div class="action-buttons" style="margin-top: 2rem; border-top: 1px solid #ddd; padding-top: 1rem;">
            <button type="submit" name="edit_user" class="btn btn-primary"><i class="fas fa-save"></i> Update User</button>
            <button type="button" class="btn btn-outline" onclick="closeEditModal()"><i class="fas fa-times"></i> Cancel</button>
        </div>
    `;

        content.innerHTML = html;
        document.getElementById('edit_loading').style.display = 'none';
        content.style.display = 'block';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    window.onclick = function (event) {
        if (event.target == document.getElementById('addModal')) closeAddModal();
        if (event.target == document.getElementById('editModal')) closeEditModal();
    }
    
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });
</script>
<script>
// Auto-generate password when name is typed
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.querySelector('#addForm input[name="name"]');
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            generatePassword(this.value);
        });
    }
});

function generatePassword(fullName) {
    const parts = fullName.trim().toLowerCase().split(/\s+/).filter(Boolean);
    if (parts.length === 0) {
        document.getElementById('add_password').value = '';
        return;
    }
    const first = parts[0];
    const last  = parts.length > 1 ? parts[parts.length - 1] : '';
    document.getElementById('add_password').value = 'oma' + first + last;
}

function copyPassword() {
    const pw = document.getElementById('add_password');
    if (!pw.value) return;
    navigator.clipboard.writeText(pw.value).then(() => {
        const btn = pw.nextElementSibling;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => btn.innerHTML = '<i class="fas fa-copy"></i>', 1500);
    });
}
</script>

<?php include 'includes/admin_footer.php'; ?>