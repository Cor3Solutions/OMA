<?php
$page_title = "Manage Instructors";
require_once '../config/database.php';
require_once 'includes/activity_helper.php';
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


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── RESET INSTRUCTOR PASSWORD ──────────────────────────────────────
    if (isset($_POST['reset_password'])) {
        $inst_id      = (int)($_POST['inst_id'] ?? 0);
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_pwd  = trim($_POST['confirm_password'] ?? '');

        $inst_row = $conn->query("SELECT name, user_id FROM instructors WHERE id = $inst_id")->fetch_assoc();

        if (!$inst_row) {
            $error = 'Instructor not found.';
        } elseif (empty($inst_row['user_id'])) {
            $error = 'This instructor has no linked user account. Link one first before resetting the password.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new_password !== $confirm_pwd) {
            $error = 'Passwords do not match.';
        } else {
            $hashed  = password_hash($new_password, PASSWORD_DEFAULT);
            $user_id = (int)$inst_row['user_id'];
            $stmt    = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $user_id);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $success = 'Password for <strong>' . htmlspecialchars($inst_row['name']) . '</strong> has been reset successfully.';
                logActivity($conn, 'edit', 'users', $user_id, $inst_row['name'], 'Admin reset instructor password.');
            } else {
                $error = 'Failed to reset password. Make sure the instructor has a linked user account.';
            }
            $stmt->close();
        }

    // ── UPDATE INSTRUCTOR BIO / HISTORY ───────────────────────────────
    } elseif (isset($_POST['update_bio'])) {
        $id             = (int)$_POST['bio_inst_id'];
        $bio            = sanitize($_POST['bio'] ?? '');
        $specialization = sanitize($_POST['specialization'] ?? '');
        $title          = sanitize($_POST['title'] ?? '');
        $location       = sanitize($_POST['location'] ?? '');
        $facebook_url   = sanitize($_POST['facebook_url'] ?? '');

        $before = $conn->query("SELECT name, bio, specialization, title, location, facebook_url FROM instructors WHERE id = $id")->fetch_assoc();

        $stmt = $conn->prepare("UPDATE instructors SET bio=?, specialization=?, title=?, location=?, facebook_url=? WHERE id=?");
        $stmt->bind_param("sssssi", $bio, $specialization, $title, $location, $facebook_url, $id);

        if ($stmt->execute()) {
            $success = 'Profile &amp; history for <strong>' . htmlspecialchars($before['name']) . '</strong> updated successfully.';
            $ch = [];
            foreach (['title','location','specialization','bio','facebook_url'] as $ff) {
                $ov = $before[$ff] ?? '';
                $nv = $$ff ?? '';
                if (trim((string)$ov) !== trim((string)$nv))
                    $ch[] = "$ff changed";
            }
            logActivity($conn, 'edit', 'instructors', $id, $before['name'],
                empty($ch) ? 'Bio/history: no changes.' : 'Bio/history updated: ' . implode(', ', $ch));
        } else {
            $error = 'Failed to update profile &amp; history.';
        }
        $stmt->close();

    } elseif (isset($_POST['add_instructor'])) {
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
                logActivity($conn, 'create', 'instructors', $conn->insert_id, $name,
                    'New instructor added. Title: ' . $title . ' | Location: ' . ($location??'N/A') .
                    ' | Email: ' . ($email??'N/A') . ' | Phone: ' . ($phone??'N/A') .
                    ' | Status: ' . $status . ' | Specialization: ' . ($specialization??'N/A'));
            } else {
                $error = 'Failed to add instructor';
            }
            $stmt->close();
        }
    } elseif (isset($_POST['edit_instructor'])) {
        $id = (int) $_POST['id'];
        $before_inst = $conn->query("SELECT * FROM instructors WHERE id = $id")->fetch_assoc();
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
                $ch = [];
                $wf = ['name','title','location','email','phone','specialization','status'];
                foreach ($wf as $ff) {
                    $ov = $before_inst[$ff] ?? ''; $nv = $$ff ?? '';
                    if ((string)$ov !== (string)$nv) $ch[] = "$ff: \"$ov\" → \"$nv\"";
                }
                logActivity($conn, 'edit', 'instructors', $id, $name,
                    empty($ch) ? 'No changes.' : implode(' | ', $ch));
            } else {
                $error = 'Failed to update instructor';
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_instructor'])) {
        $id = (int) $_POST['id'];

        // Archive before delete 
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

// Get all instructors — PAGINATED
$_per_page  = isset($_GET['per_page']) ? max(10, min(100, (int)$_GET['per_page'])) : 10;
$_cur_page  = isset($_GET['page'])     ? max(1, (int)$_GET['page']) : 1;
$_offset    = ($_cur_page - 1) * $_per_page;
$_total_inst = $conn->query("SELECT COUNT(*) as c FROM instructors")->fetch_assoc()['c'];
if ($_cur_page > max(1, ceil($_total_inst / $_per_page))) { $_cur_page = max(1, ceil($_total_inst / $_per_page)); $_offset = ($_cur_page-1)*$_per_page; }

$instructors = $conn->query("
    SELECT i.*, u.serial_number, u.email as user_email 
    FROM instructors i 
    LEFT JOIN users u ON i.user_id = u.id 
    ORDER BY i.display_order ASC, i.name ASC
    LIMIT $_per_page OFFSET $_offset
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
        <input type="text" placeholder=" Search instructors..." id="searchInput">
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
                                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%); display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; color: white; font-size: 1.5rem;">
                                    <?php echo strtoupper(substr($instructor['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($instructor['name']); ?></strong></td>
                        <td>
                            <?php if ($instructor['user_id']): ?>
                                <span style="display: inline-block; background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($instructor['serial_number']); ?>
                                </span><br>
                                <small style="color: #666;"><?php echo htmlspecialchars($instructor['user_email']); ?></small>
                            <?php else: ?>
                                <span style="color: #999; font-style: italic;">No account</span>
                            <?php endif; ?>
                        </td>
                        <td><strong style="color: #f57c00;"><?php echo htmlspecialchars($instructor['khan_level']); ?></strong></td>
                        <td><?php echo htmlspecialchars($instructor['title'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($instructor['location'] ?: 'N/A'); ?></td>
                        <td>
                            <?php if ($instructor['email']): ?>
                                <small><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($instructor['email']); ?></small><br>
                            <?php endif; ?>
                            <?php if ($instructor['phone']): ?>
                                <small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($instructor['phone']); ?></small>
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
                                <!-- Edit -->
                                <button class="btn btn-sm btn-primary"
                                    onclick="editInstructor(<?php echo htmlspecialchars(json_encode($instructor), ENT_QUOTES, 'UTF-8'); ?>)"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <!-- Edit History / Bio -->
                                <a href="instructor_khan_history.php?id=<?= (int)$instructor['id'] ?>"
                                   class="btn btn-sm"
                                   title="View &amp; Edit Khan Journey"
                                   style="background:#8b0000;border-color:#6d0000;color:#fff;text-decoration:none;">
                                    <i class="fas fa-route"></i>
                                </a>

                                <!-- Reset Password -->
                                <?php if ($instructor['user_id']): ?>
                                    <button class="btn btn-sm btn-warning"
                                        onclick="openResetPwd(<?php echo (int)$instructor['id']; ?>, '<?php echo htmlspecialchars(addslashes($instructor['name'])); ?>')"
                                        title="Reset Password"
                                        style="background:#f59e0b;border-color:#d97706;color:#fff;">
                                        <i class="fas fa-key"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm" disabled
                                        title="No user account linked — cannot reset password"
                                        style="background:#f3f4f6;color:#d1d5db;border-color:#e5e7eb;cursor:not-allowed;">
                                        <i class="fas fa-key"></i>
                                    </button>
                                <?php endif; ?>

                                <!-- Delete -->
                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this instructor?');">
                                    <input type="hidden" name="id" value="<?php echo $instructor['id']; ?>">
                                    <button type="submit" name="delete_instructor" class="btn btn-sm btn-danger" title="Delete">
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
    <?php echo buildPaginationBar($_total_inst, $_per_page, $_cur_page); ?>
</div>


<!-- ═══════════════════════════════════════════════
     RESET PASSWORD MODAL
═══════════════════════════════════════════════ -->
<div id="resetPwdModal" class="modal">
    <div class="modal-content" style="max-width: 480px;">
        <span class="modal-close" onclick="closeResetPwd()">&times;</span>
        <h2 style="display:flex;align-items:center;gap:.6rem;margin-bottom:.25rem;">
            <i class="fas fa-key" style="color:#f59e0b;"></i> Reset Instructor Password
        </h2>
        <p id="resetPwdSubtitle" style="color:#64748b;font-size:.9rem;margin-bottom:1.5rem;"></p>

        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:.875rem 1.125rem;margin-bottom:1.5rem;display:flex;gap:.75rem;align-items:flex-start;">
            <i class="fas fa-exclamation-triangle" style="color:#d97706;margin-top:2px;flex-shrink:0;"></i>
            <span style="font-size:.85rem;color:#92400e;">
                This will immediately overwrite the instructor's current password.
                Share the new credentials with them securely.
            </span>
        </div>

        <form method="POST" id="resetPwdForm">
            <input type="hidden" name="inst_id" id="reset_inst_id">

            <div class="form-group">
                <label class="form-label">New Password *</label>
                <div style="position:relative;">
                    <input type="password" name="new_password" id="reset_new_pwd" class="form-input"
                           required minlength="6" placeholder="Minimum 6 characters"
                           style="padding-right:3rem;" oninput="resetStrength(this.value)">
                    <button type="button" onclick="togglePwd('reset_new_pwd', this)"
                            style="position:absolute;right:.875rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;font-size:.95rem;padding:4px;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <!-- Strength bar -->
                <div style="margin-top:6px;height:4px;background:#e2e8f0;border-radius:2px;overflow:hidden;">
                    <div id="resetStrengthBar" style="height:100%;width:0%;background:#ef4444;border-radius:2px;transition:all .3s;"></div>
                </div>
                <small id="resetStrengthLabel" style="color:#94a3b8;">Enter a password</small>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Confirm New Password *</label>
                <div style="position:relative;">
                    <input type="password" name="confirm_password" id="reset_confirm_pwd" class="form-input"
                           required minlength="6" placeholder="Re-enter password"
                           style="padding-right:3rem;" oninput="resetMatch()">
                    <button type="button" onclick="togglePwd('reset_confirm_pwd', this)"
                            style="position:absolute;right:.875rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;font-size:.95rem;padding:4px;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small id="resetMatchLabel" style="font-size:.8rem;margin-top:4px;display:block;color:transparent;">–</small>
            </div>

            <div style="display:flex;gap:.75rem;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid #f1f5f9;">
                <button type="submit" name="reset_password" class="btn btn-primary" style="flex:1;">
                    <i class="fas fa-save"></i> Set New Password
                </button>
                <button type="button" class="btn btn-outline" onclick="closeResetPwd()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>


<!-- Add Instructor Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-user-plus"></i> Add New Instructor</h2>

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
                <textarea name="specialization" class="form-textarea" rows="2" placeholder="Areas of expertise..."></textarea>
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
        from { opacity: 0; }
        to   { opacity: 1; }
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
        from { transform: translateY(-50px); opacity: 0; }
        to   { transform: translateY(0);     opacity: 1; }
    }

    .modal-close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s;
    }

    .modal-close:hover { color: #000; }

    .badge-active   { background: #4caf50; color: white; }
    .badge-inactive { background: #757575; color: white; }
</style>

<script>
    // ── Reset Password Modal ──────────────────────────────────────────
    function openResetPwd(instId, instName) {
        document.getElementById('reset_inst_id').value = instId;
        document.getElementById('resetPwdSubtitle').textContent = 'Resetting password for: ' + instName;
        document.getElementById('reset_new_pwd').value     = '';
        document.getElementById('reset_confirm_pwd').value = '';
        document.getElementById('resetStrengthBar').style.width    = '0%';
        document.getElementById('resetStrengthLabel').textContent  = 'Enter a password';
        document.getElementById('resetStrengthLabel').style.color  = '#94a3b8';
        document.getElementById('resetMatchLabel').textContent     = '–';
        document.getElementById('resetMatchLabel').style.color     = 'transparent';
        document.getElementById('resetPwdModal').style.display     = 'block';
    }

    function closeResetPwd() {
        document.getElementById('resetPwdModal').style.display = 'none';
    }

    function togglePwd(id, btn) {
        const inp = document.getElementById(id);
        const ic  = btn.querySelector('i');
        inp.type  = inp.type === 'password' ? 'text' : 'password';
        ic.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    function resetStrength(pw) {
        let s = 0;
        if (pw.length >= 6)  s++;
        if (pw.length >= 10) s++;
        if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) s++;
        if (/\d/.test(pw))   s++;
        if (/[^a-zA-Z\d]/.test(pw)) s++;
        const bar   = document.getElementById('resetStrengthBar');
        const label = document.getElementById('resetStrengthLabel');
        const pcts  = [0, 20, 40, 65, 85, 100];
        const cols  = ['#ef4444','#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
        const labs  = ['Enter a password','Weak','Fair','Moderate','Strong','Very Strong'];
        const lcols = ['#94a3b8','#ef4444','#f97316','#ca8a04','#16a34a','#15803d'];
        bar.style.width      = pcts[s] + '%';
        bar.style.background = cols[s];
        label.textContent    = labs[s];
        label.style.color    = lcols[s];
        resetMatch();
    }

    function resetMatch() {
        const np  = document.getElementById('reset_new_pwd').value;
        const cp  = document.getElementById('reset_confirm_pwd').value;
        const lbl = document.getElementById('resetMatchLabel');
        if (!cp) { lbl.style.color = 'transparent'; lbl.textContent = '–'; return; }
        if (np === cp) {
            lbl.textContent = '✓ Passwords match';
            lbl.style.color = '#16a34a';
        } else {
            lbl.textContent = '✗ Passwords do not match';
            lbl.style.color = '#ef4444';
        }
    }

    document.getElementById('resetPwdForm').addEventListener('submit', function (e) {
        const np = document.getElementById('reset_new_pwd').value;
        const cp = document.getElementById('reset_confirm_pwd').value;
        if (np.length < 6) { e.preventDefault(); alert('Password must be at least 6 characters.'); return; }
        if (np !== cp)     { e.preventDefault(); alert('Passwords do not match.'); return; }
    });

    // ── Edit Instructor Modal ─────────────────────────────────────────
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

    // ── Search ────────────────────────────────────────────────────────
    document.getElementById('searchInput').addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.data-table tbody tr');
        rows.forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
        });
    });

    // ── Close modals on outside click / Escape ────────────────────────
    window.onclick = function (event) {
        ['addModal', 'editModal', 'resetPwdModal', 'bioModal'].forEach(function (id) {
            const m = document.getElementById(id);
            if (event.target === m) m.style.display = 'none';
        });
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            ['addModal', 'editModal', 'resetPwdModal', 'bioModal'].forEach(function (id) {
                document.getElementById(id).style.display = 'none';
            });
        }
    });
</script>

<!-- ═══════════════════════════════════════════════
     EDIT PROFILE & HISTORY MODAL
═══════════════════════════════════════════════ -->
<div id="bioModal" class="modal">
    <div class="modal-content" style="max-width:640px;">
        <span class="modal-close" onclick="closeBioModal()">&times;</span>
        <h2 style="display:flex;align-items:center;gap:.6rem;margin-bottom:.25rem;">
            <i class="fas fa-scroll" style="color:#0ea5e9;"></i> Edit Profile &amp; History
        </h2>
        <p id="bioModalSubtitle" style="color:#64748b;font-size:.88rem;margin-bottom:1.5rem;"></p>

        <form method="POST" id="bioForm">
            <input type="hidden" name="bio_inst_id" id="bio_inst_id">

            <!-- Quick fields -->
            <div class="form-grid" style="margin-bottom:0;">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-id-badge" style="color:#94a3b8;margin-right:.3rem;"></i> Title / Position</label>
                    <input type="text" name="title" id="bio_title" class="form-input" placeholder="e.g., Founder, Head Instructor">
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-map-marker-alt" style="color:#94a3b8;margin-right:.3rem;"></i> Location</label>
                    <input type="text" name="location" id="bio_location" class="form-input" placeholder="e.g., Quezon City">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fas fa-star" style="color:#94a3b8;margin-right:.3rem;"></i> Specialization</label>
                <textarea name="specialization" id="bio_specialization" class="form-textarea" rows="2"
                    placeholder="Areas of expertise, techniques, styles..."></textarea>
                <small style="color:#94a3b8;">Shown on the public instructors / lineage page</small>
            </div>

            <div class="form-group">
                <label class="form-label" style="display:flex;align-items:center;justify-content:space-between;">
                    <span><i class="fas fa-book-open" style="color:#94a3b8;margin-right:.3rem;"></i> Biography &amp; History</span>
                    <span id="bioCharCount" style="font-size:.75rem;color:#94a3b8;font-weight:400;">0 chars</span>
                </label>
                <textarea name="bio" id="bio_bio" class="form-textarea" rows="8"
                    placeholder="Write the instructor's background, martial arts journey, achievements, lineage, notable events..."
                    oninput="document.getElementById('bioCharCount').textContent=this.value.length+' chars'"></textarea>
                <small style="color:#94a3b8;">Markdown-style paragraphs are fine. This appears on the public profile.</small>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fab fa-facebook" style="color:#1877f2;margin-right:.3rem;"></i> Facebook URL</label>
                <input type="url" name="facebook_url" id="bio_facebook_url" class="form-input" placeholder="https://facebook.com/...">
            </div>

            <div style="display:flex;gap:.75rem;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid #f1f5f9;">
                <button type="submit" name="update_bio" class="btn btn-primary" style="flex:1;">
                    <i class="fas fa-save"></i> Save Profile &amp; History
                </button>
                <button type="button" class="btn btn-outline" onclick="closeBioModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // ── Bio / History Modal ───────────────────────────────────────────
    function openBioModal(instructor) {
        document.getElementById('bio_inst_id').value        = instructor.id;
        document.getElementById('bio_title').value          = instructor.title        || '';
        document.getElementById('bio_location').value       = instructor.location     || '';
        document.getElementById('bio_specialization').value = instructor.specialization || '';
        document.getElementById('bio_bio').value            = instructor.bio          || '';
        document.getElementById('bio_facebook_url').value   = instructor.facebook_url || '';
        document.getElementById('bioModalSubtitle').textContent = 'Editing profile for: ' + instructor.name;
        document.getElementById('bioCharCount').textContent = (instructor.bio || '').length + ' chars';
        document.getElementById('bioModal').style.display   = 'block';
    }

    function closeBioModal() {
        document.getElementById('bioModal').style.display = 'none';
    }
</script>

<?php include 'includes/admin_footer.php'; ?>