<?php
$page_title = "Admin Accounts";
require_once '../config/database.php';
requireAdmin();
require_once 'includes/activity_helper.php';

$conn = getDbConnection();
$success = '';
$error   = '';

// ── HELPERS ───────────────────────────────────────────────────────────────────
function nextAdminSerial($conn) {
    // Admins get serial OMA-ADM-001, OMA-ADM-002, etc.
    $res  = $conn->query("SELECT serial_number FROM users WHERE serial_number LIKE 'OMA-ADM-%' ORDER BY serial_number DESC LIMIT 1");
    $last = $res ? $res->fetch_assoc() : null;
    $next = 1;
    if ($last && preg_match('/OMA-ADM-0*(\d+)/', $last['serial_number'], $m)) {
        $next = (int)$m[1] + 1;
    }
    return 'OMA-ADM-' . str_pad($next, 3, '0', STR_PAD_LEFT);
}

// ── HANDLE FORM SUBMISSIONS ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── ADD ADMIN ──
    if (isset($_POST['add_admin'])) {
        $name     = sanitize($_POST['name']);
        $email    = sanitize($_POST['email']);
        $phone    = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $status   = $_POST['status'] ?? 'active';

        if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $exists = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "' LIMIT 1");
            if ($exists && $exists->num_rows > 0) {
                $error = 'An account with that email already exists.';
            } else {
                $serial  = nextAdminSerial($conn);
                $hashed  = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (serial_number, name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, 'admin', ?)");
                $stmt->bind_param("ssssss", $serial, $name, $email, $phone, $hashed, $status);
                if ($stmt->execute()) {
                    $new_id  = $conn->insert_id;
                    $success = "Admin account created. Serial: $serial";
                    logActivity($conn, 'create', 'users', $new_id, $name,
                        "Admin account created. Serial: $serial | Email: $email | Status: $status");
                } else {
                    $error = 'Failed to create account: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }

    // ── EDIT ADMIN ──
    elseif (isset($_POST['edit_admin'])) {
        $id       = (int)$_POST['id'];
        $name     = sanitize($_POST['name']);
        $email    = sanitize($_POST['email']);
        $phone    = sanitize($_POST['phone'] ?? '');
        $status   = $_POST['status'] ?? 'active';
        $password = trim($_POST['password'] ?? '');

        $before = $conn->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();

        // Check email conflict
        $echeck = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "' AND id != $id LIMIT 1");
        if ($echeck && $echeck->num_rows > 0) {
            $error = 'That email is already used by another account.';
        } else {
            if (!empty($password)) {
                if (strlen($password) < 6) { $error = 'Password must be at least 6 characters.'; goto done; }
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, password=?, status=? WHERE id=? AND role='admin'");
                $stmt->bind_param("sssssi", $name, $email, $phone, $hashed, $status, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, status=? WHERE id=? AND role='admin'");
                $stmt->bind_param("ssssi", $name, $email, $phone, $status, $id);
            }
            if ($stmt->execute()) {
                $success = 'Admin account updated.';
                $changes = [];
                foreach (['name','email','phone','status'] as $f) {
                    if (($before[$f]??'') !== $$f) $changes[] = "$f: [" . ($before[$f]??'') . " -> " . $$f . "]";
                }
                if (!empty($password)) $changes[] = 'password: [changed]';
                logActivity($conn, 'edit', 'users', $id, $name,
                    empty($changes) ? 'No changes.' : implode(' | ', $changes));
            } else {
                $error = 'Failed to update: ' . $stmt->error;
            }
            $stmt->close();
        }
        done:;
    }

    // ── DELETE ADMIN ──
    elseif (isset($_POST['delete_admin'])) {
        $id = (int)$_POST['id'];
        if ($id === (int)$_SESSION['user_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            $row = $conn->query("SELECT * FROM users WHERE id = $id AND role = 'admin'")->fetch_assoc();
            if ($row) {
                archiveRecord($conn, 'users', $id, $row['name'] . ' [admin]', $row);
                logActivity($conn, 'delete', 'users', $id, $row['name'],
                    "Admin account deleted. Serial: " . $row['serial_number'] . " | Email: " . $row['email']);
                $conn->query("DELETE FROM users WHERE id = $id AND role = 'admin'");
                $success = 'Admin account deleted.';
            } else {
                $error = 'Account not found.';
            }
        }
    }
}

// ── FETCH ADMIN USERS ─────────────────────────────────────────────────────────
$admins = $conn->query("
    SELECT * FROM users
    WHERE role = 'admin'
    ORDER BY serial_number ASC
");

include 'includes/admin_header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="admin-section">
    <div class="section-header">
        <h2><i class="fas fa-user-shield"></i> Admin Accounts</h2>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">
            <i class="fas fa-plus-circle"></i> Add Admin
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Serial</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($admin = $admins->fetch_assoc()): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($admin['serial_number']); ?></code></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:0.6rem;">
                            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#D32F2F,#b71c1c);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.9rem;flex-shrink:0;">
                                <?php echo strtoupper(mb_substr($admin['name'],0,1)); ?>
                            </div>
                            <strong><?php echo htmlspecialchars($admin['name']); ?></strong>
                            <?php if ($admin['id'] == $_SESSION['user_id']): ?>
                                <span style="font-size:0.72rem;background:#e3f2fd;color:#1976d2;padding:2px 8px;border-radius:10px;font-weight:600;">You</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                    <td><?php echo htmlspecialchars($admin['phone'] ?: '—'); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $admin['status']; ?>"
                              style="padding:0.3rem 0.6rem;border-radius:4px;font-size:0.82rem;">
                            <?php echo ucfirst($admin['status']); ?>
                        </span>
                    </td>
                    <td><small><?php echo isset($admin['created_at']) ? date('M d, Y', strtotime($admin['created_at'])) : '—'; ?></small></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary"
                                    onclick='openEdit(<?php echo json_encode($admin); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($admin['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline;"
                                  onsubmit="return confirm('Delete admin account for <?php echo addslashes($admin['name']); ?>?');">
                                <input type="hidden" name="id" value="<?php echo $admin['id']; ?>">
                                <button type="submit" name="delete_admin" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
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

<!-- ADD MODAL -->
<div id="addModal" class="modal">
    <div class="modal-content" style="max-width:560px;">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-user-plus"></i> Add Admin Account</h2>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" id="add_name" class="form-input" required
                       oninput="genPw(this.value)">
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" class="form-input" placeholder="09XX XXX XXXX">
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <div style="position:relative;flex:1;">
                        <input type="password" name="password" id="add_pw" class="form-input"
                               required minlength="6" style="padding-right:2.5rem;">
                        <button type="button" onclick="toggleVis('add_pw','add_pw_icon')"
                                style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#888;">
                            <i class="fas fa-eye" id="add_pw_icon"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="copyPw('add_pw')" title="Copy">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <small id="add_pw_hint" style="color:#888;display:block;margin-top:0.3rem;"></small>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="action-buttons">
                <button type="submit" name="add_admin" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Account
                </button>
                <button type="button" class="btn btn-outline"
                        onclick="document.getElementById('addModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
    <div class="modal-content" style="max-width:560px;">
        <span class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-user-edit"></i> Edit Admin Account</h2>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" id="edit_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" id="edit_email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" id="edit_phone" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">New Password <span style="font-weight:400;color:#999;">(leave blank to keep current)</span></label>
                <div style="position:relative;">
                    <input type="password" name="password" id="edit_pw" class="form-input"
                           minlength="6" placeholder="Enter new password" style="padding-right:2.5rem;">
                    <button type="button" onclick="toggleVis('edit_pw','edit_pw_icon')"
                            style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#888;">
                        <i class="fas fa-eye" id="edit_pw_icon"></i>
                    </button>
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
                <button type="submit" name="edit_admin" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Account
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
.modal { display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background:rgba(0,0,0,0.5);animation:fadeIn 0.3s; }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.modal-content { background:#fefefe;margin:5% auto;padding:2rem;border-radius:8px;width:92%;max-height:90vh;overflow-y:auto;box-shadow:0 4px 20px rgba(0,0,0,0.3);animation:slideDown 0.3s; }
@keyframes slideDown { from{transform:translateY(-40px);opacity:0} to{transform:translateY(0);opacity:1} }
.modal-close { color:#aaa;float:right;font-size:28px;font-weight:bold;cursor:pointer; }
.modal-close:hover { color:#000; }
.badge-active   { background:#4caf50;color:#fff; }
.badge-inactive { background:#757575;color:#fff; }
</style>

<script>
function openEdit(admin) {
    document.getElementById('edit_id').value     = admin.id;
    document.getElementById('edit_name').value   = admin.name;
    document.getElementById('edit_email').value  = admin.email;
    document.getElementById('edit_phone').value  = admin.phone || '';
    document.getElementById('edit_status').value = admin.status;
    document.getElementById('edit_pw').value     = '';
    document.getElementById('editModal').style.display = 'block';
}

function toggleVis(inputId, iconId) {
    const inp  = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.replace('fa-eye','fa-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.replace('fa-eye-slash','fa-eye');
    }
}

function copyPw(inputId) {
    const val = document.getElementById(inputId).value;
    if (!val) return;
    navigator.clipboard.writeText(val).then(() => {
        const icon = document.querySelector(`#${inputId} ~ button i, button[onclick*="${inputId}"] i`);
    });
}

function genPw(name) {
    const parts = name.trim().toLowerCase().split(/\s+/).filter(Boolean);
    const first = parts[0] || '';
    const last  = parts.length > 1 ? parts[parts.length - 1] : '';
    const pw    = 'oma' + first + last;
    document.getElementById('add_pw').value = pw;
    document.getElementById('add_pw_hint').textContent = pw ? 'Suggested: ' + pw : '';
}

window.onclick = e => {
    if (e.target === document.getElementById('addModal'))  document.getElementById('addModal').style.display  = 'none';
    if (e.target === document.getElementById('editModal')) document.getElementById('editModal').style.display = 'none';
};
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('addModal').style.display  = 'none';
        document.getElementById('editModal').style.display = 'none';
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>