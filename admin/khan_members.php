<?php
$page_title = "Manage Khan Members";
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


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_member'])) {
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
        $current_khan_level = (int) $_POST['current_khan_level'];

        // Get khan color from database based on level
        $color_result = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $current_khan_level");
        $khan_color = '';
        if ($color_result && $color_row = $color_result->fetch_assoc()) {
            $khan_color = $color_row['color_name'];
        }

        $date_joined = $_POST['date_joined'];
        $date_promoted = !empty($_POST['date_promoted']) ? $_POST['date_promoted'] : null;
        $instructor_id = !empty($_POST['instructor_id']) ? (int) $_POST['instructor_id'] : null;
        $training_location = isset($_POST['training_location']) ? sanitize($_POST['training_location']) : '';
        $status = $_POST['status'];
        $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';

        // ── Auto-create user account ──────────────────────────────────
        // If a user_id was explicitly chosen in the dropdown, use it.
        // Otherwise, check if this email already has an account.
        // If not, create one automatically.
        $user_id = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : null;

        if (!$user_id && !empty($email)) {
            // Check if a user with this email already exists
            $ucheck = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "' LIMIT 1");
            if ($ucheck && $ucheck->num_rows > 0) {
                $user_id = (int)$ucheck->fetch_assoc()['id'];
            } else {
                // Generate next serial number
                $sres = $conn->query("SELECT serial_number FROM users WHERE serial_number LIKE 'OMA-%' ORDER BY serial_number DESC LIMIT 1");
                $slast = $sres ? $sres->fetch_assoc() : null;
                $snext = 1;
                if ($slast && preg_match('/OMA-0*(\d+)/', $slast['serial_number'], $sm)) {
                    $snext = (int)$sm[1] + 1;
                }
                $serial = 'OMA-' . str_pad($snext, 3, '0', STR_PAD_LEFT);

                // Default password: oma + firstname + lastname (all lowercase)
                $parts = array_values(array_filter(explode(' ', strtolower(trim($full_name)))));
                $first = $parts[0] ?? 'member';
                $last  = count($parts) > 1 ? $parts[count($parts) - 1] : '';
                $default_password = password_hash('oma' . $first . $last, PASSWORD_DEFAULT);

                $ustmt = $conn->prepare("INSERT INTO users (serial_number, name, email, phone, password, role, status, khan_level) VALUES (?, ?, ?, ?, ?, 'member', ?, ?)");
                $khan_level_label = 'Khan ' . $current_khan_level;
                $ustmt->bind_param("sssssss", $serial, $full_name, $email, $phone, $default_password, $status, $khan_level_label);
                if ($ustmt->execute()) {
                    $user_id = $conn->insert_id;
                }
                $ustmt->close();
            }
        }
        // ─────────────────────────────────────────────────────────────

        $stmt = $conn->prepare("INSERT INTO khan_members (user_id, full_name, email, phone, current_khan_level, khan_color, date_joined, date_promoted, instructor_id, training_location, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssississss", $user_id, $full_name, $email, $phone, $current_khan_level, $khan_color, $date_joined, $date_promoted, $instructor_id, $training_location, $status, $notes);

        if ($stmt->execute()) {
            $success = 'Khan member added successfully! A user account was automatically created.';
        } else {
            $error = 'Failed to add khan member: ' . $conn->error;
        }
        $stmt->close();
    } elseif (isset($_POST['edit_member'])) {
        $id = (int) $_POST['id'];
        $user_id = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : null;
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
        $current_khan_level = (int) $_POST['current_khan_level'];
        
        // Get khan color from database based on level
        $color_result = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $current_khan_level");
        $khan_color = '';
        if ($color_result && $color_row = $color_result->fetch_assoc()) {
            $khan_color = $color_row['color_name'];
        }
        
        $date_joined = $_POST['date_joined'];
        $date_promoted = !empty($_POST['date_promoted']) ? $_POST['date_promoted'] : null;
        $instructor_id = !empty($_POST['instructor_id']) ? (int) $_POST['instructor_id'] : null;
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
    } elseif (isset($_POST['delete_member'])) {
        $id = (int) $_POST['id'];

        if ($conn->query("DELETE FROM khan_members WHERE id = $id")) {
            $success = 'Khan member deleted successfully!';
        } else {
            $error = 'Failed to delete khan member';
        }
    }
}

// Get all khan members — PAGINATED
$_per_page  = isset($_GET['per_page']) ? max(10, min(100, (int)$_GET['per_page'])) : 10;
$_cur_page  = isset($_GET['page'])     ? max(1, (int)$_GET['page']) : 1;
$_offset    = ($_cur_page - 1) * $_per_page;
$_total_members = $conn->query("SELECT COUNT(*) as c FROM khan_members")->fetch_assoc()['c'];
if ($_cur_page > max(1, ceil($_total_members / $_per_page))) { $_cur_page = max(1, ceil($_total_members / $_per_page)); $_offset = ($_cur_page-1)*$_per_page; }

$members = $conn->query("
    SELECT km.*, i.name as instructor_name, u.serial_number, u.email as user_email
    FROM khan_members km 
    LEFT JOIN instructors i ON km.instructor_id = i.id 
    LEFT JOIN users u ON km.user_id = u.id
    ORDER BY km.current_khan_level DESC, km.full_name ASC
    LIMIT $_per_page OFFSET $_offset
");

// Get instructors for dropdown
$instructors = $conn->query("SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name");

// Get users for dropdown
$available_users = $conn->query("SELECT id, name, email FROM users WHERE role = 'member' ORDER BY name");

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
        <h2><i class="fas fa-user-graduate"></i> Khan Members Management</h2>
        <a href="manual_encode.php" class="btn btn-primary">
            <i class="fas fa-keyboard"></i> Encode Members
        </a>
    </div>

    <div class="filters-row"
        style="display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: center; flex-wrap: wrap;">
        <div class="search-box" style="flex: 1; min-width: 250px;">
            <input type="text" placeholder=" Search members..." id="searchInput" style="width: 100%;">
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
            <option value="refresher">Needs Refresher</option>
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
                    <tr data-level="<?php echo $member['current_khan_level']; ?>"
                        data-status="<?php echo $member['status']; ?>">
                        <td><?php echo $member['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($member['full_name']); ?></strong></td>
                        <td>
                            <?php if ($member['user_id']): ?>
                                <span
                                    style="display: inline-block; background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($member['serial_number']); ?>
                                </span><br>
                                <small style="color: #666;"><?php echo htmlspecialchars($member['user_email']); ?></small>
                            <?php else: ?>
                                <span style="color: #999; font-style: italic;">No account</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><i class="fas fa-envelope"></i>
                                <?php echo htmlspecialchars($member['email']); ?></small><br>
                            <small><i class="fas fa-phone"></i>
                                <?php echo htmlspecialchars($member['phone'] ?: 'N/A'); ?></small>
                        </td>
                        <td><strong style="color: #388e3c;">Khan <?php echo $member['current_khan_level']; ?></strong></td>
                        <td>
                            <?php if ($member['khan_color']): ?>
                                <span
                                    style="display: inline-block; background: #f5f5f5; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
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
                            <span class="badge badge-<?php echo $member['status']; ?>"
                                style="padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.85rem;">
                                <?php echo ucfirst($member['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-primary"
                                    onclick="editMember(<?php echo htmlspecialchars(json_encode($member), ENT_QUOTES, 'UTF-8'); ?>)"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this member?');">
                                    <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                    <button type="submit" name="delete_member" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <a href="member_training_history.php?member_id=<?php echo $member['id']; ?>"
                                    class="btn btn-sm btn-success" title="View Training History">
                                    <i class="fas fa-history"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php echo buildPaginationBar($_total_members, $_per_page, $_cur_page); ?>
</div>

<!-- Add Member Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-user-plus"></i> Quick Add Khan Member</h2>

        <div class="alert"
            style="background: #e3f2fd; border-left: 4px solid #1976d2; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
            <strong><i class="fas fa-lightbulb"></i> Tip:</strong> For better duplicate detection, use the
            <a href="add_khan_member.php" style="color: #1976d2; font-weight: bold;">Add Member (with Duplicate Check)</a> page.
            To create a member with a user account, use
            <a href="manage_users_centralized.php" style="color: #1976d2; font-weight: bold;">Centralized User Management</a>.
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
                    <select name="current_khan_level" id="add_current_khan_level" class="form-select" required onchange="updateKhanColor('add')">
                        <?php for ($i = 1; $i <= 16; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i == 1 ? 'selected' : ''; ?>>Khan <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid">
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
                        Color is automatically assigned based on Khan Level
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Date Joined *</label>
                    <input type="date" name="date_joined" class="form-input" value="<?php echo date('Y-m-d'); ?>"
                        required>
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
                        <option value="refresher">Needs Refresher</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-textarea" rows="3"
                    placeholder="Additional notes about the member..."></textarea>
            </div>

            <div class="action-buttons">
                <button type="submit" name="add_member" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Member
                </button>
                <button type="button" class="btn btn-outline"
                    onclick="document.getElementById('addModal').style.display='none'">
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
                    <select name="current_khan_level" id="edit_current_khan_level" class="form-select" required onchange="updateKhanColor('edit')">
                        <?php for ($i = 1; $i <= 16; $i++): ?>
                            <option value="<?php echo $i; ?>">Khan <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Khan Color/Band (Auto-filled)</label>
                    <div style="position: relative;">
                        <input type="text" 
                               name="khan_color" 
                               id="edit_khan_color_display"
                               class="form-input"
                               readonly
                               style="padding-left: 45px; background-color: #f5f5f5; cursor: not-allowed;">
                        <div id="edit_color_indicator" 
                             style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 25px; height: 25px; border-radius: 50%; border: 2px solid #999; background-color: #FFFFFF;">
                        </div>
                    </div>
                    <small style="color: #666; display: block; margin-top: 0.25rem;">
                        Color is automatically assigned based on Khan Level
                    </small>
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
                        <option value="refresher">Needs Refresher</option>
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

    .badge-refresher {
        background: #ff9800;
        color: white;
    }
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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateKhanColor('add');
    });

    function editMember(member) {
        document.getElementById('edit_id').value = member.id;
        document.getElementById('edit_user_id').value = member.user_id || '';
        document.getElementById('edit_full_name').value = member.full_name;
        document.getElementById('edit_email').value = member.email;
        document.getElementById('edit_phone').value = member.phone || '';
        document.getElementById('edit_current_khan_level').value = member.current_khan_level;
        document.getElementById('edit_date_joined').value = member.date_joined;
        document.getElementById('edit_date_promoted').value = member.date_promoted || '';
        document.getElementById('edit_instructor_id').value = member.instructor_id || '';
        document.getElementById('edit_training_location').value = member.training_location || '';
        document.getElementById('edit_status').value = member.status;
        document.getElementById('edit_notes').value = member.notes || '';

        // Update the color display based on current level
        updateKhanColor('edit');

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

        rows.forEach(function (row) {
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
    window.onclick = function (event) {
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
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('editModal').style.display = 'none';
        }
    });
</script>

<?php include 'includes/admin_footer.php'; ?>