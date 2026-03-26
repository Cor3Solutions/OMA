<?php
$page_title = "Change Password";
require_once '../config/database.php';
requireAdmin();
require_once 'includes/activity_helper.php';

$conn      = getDbConnection();
$admin_id  = $_SESSION['user_id'];
$success   = '';
$error     = '';

// ── HANDLE SUBMIT ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Fetch current hash
    $row = $conn->query("SELECT password, name, email FROM users WHERE id = $admin_id AND role = 'admin'")->fetch_assoc();

    if (!$row) {
        $error = 'Admin account not found.';
    } elseif (!password_verify($current_password, $row['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (password_verify($new_password, $row['password'])) {
        $error = 'New password must be different from the current password.';
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt   = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'admin'");
        $stmt->bind_param("si", $hashed, $admin_id);

        if ($stmt->execute()) {
            $success = 'Password changed successfully!';
            logActivity($conn, 'edit', 'users', $admin_id, $row['name'],
                'Admin changed their own password.');
            // Clear form
            $_POST = [];
        } else {
            $error = 'Failed to update password. Please try again.';
        }
        $stmt->close();
    }
}

// Fetch admin info for display
$admin = $conn->query("SELECT name, email, serial_number FROM users WHERE id = $admin_id")->fetch_assoc();

include 'includes/admin_header.php';
?>

<!-- ── Page ── -->
<div class="admin-section" style="max-width:680px;margin:0 auto;">

    <?php if ($success): ?>
    <div class="alert alert-success" style="display:flex;align-items:center;gap:.75rem;">
        <i class="fas fa-check-circle" style="font-size:1.25rem;"></i>
        <span><?php echo $success; ?></span>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-error" style="display:flex;align-items:center;gap:.75rem;">
        <i class="fas fa-exclamation-circle" style="font-size:1.25rem;"></i>
        <span><?php echo $error; ?></span>
    </div>
    <?php endif; ?>

    <!-- Identity Card -->
    <div style="background:linear-gradient(135deg,#1e293b,#0f172a);color:white;border-radius:12px;padding:1.75rem 2rem;margin-bottom:2rem;display:flex;align-items:center;gap:1.5rem;box-shadow:0 4px 20px rgba(0,0,0,0.2);">
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:700;color:white;border:2px solid rgba(255,255,255,0.2);flex-shrink:0;">
            <?php echo strtoupper(substr($admin['name'],0,1)); ?>
        </div>
        <div>
            <div style="font-weight:700;font-size:1.1rem;margin-bottom:2px;"><?php echo htmlspecialchars($admin['name']); ?></div>
            <div style="font-size:0.85rem;opacity:0.65;margin-bottom:4px;"><?php echo htmlspecialchars($admin['email']); ?></div>
            <div style="display:inline-block;background:rgba(255,255,255,0.12);padding:2px 10px;border-radius:50px;font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">
                Admin · <?php echo htmlspecialchars($admin['serial_number']); ?>
            </div>
        </div>
        <div style="margin-left:auto;opacity:0.25;font-size:3rem;"><i class="fas fa-shield-alt"></i></div>
    </div>

    <!-- Form Card -->
    <div style="background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid #e2e8f0;overflow:hidden;">
        <div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:1.25rem 1.75rem;display:flex;align-items:center;gap:.75rem;">
            <i class="fas fa-lock" style="color:#8b0000;font-size:1.1rem;"></i>
            <h2 style="margin:0;font-size:1.15rem;font-weight:700;color:#1e293b;">Change Password</h2>
        </div>

        <form method="POST" id="pwdForm" style="padding:1.75rem 2rem;">

            <!-- Current password -->
            <div class="form-group">
                <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                    <i class="fas fa-key" style="color:#64748b;font-size:.85rem;"></i> Current Password
                </label>
                <div style="position:relative;">
                    <input type="password" name="current_password" id="currentPwd" class="form-input" required
                           autocomplete="current-password" style="padding-right:3rem;">
                    <button type="button" onclick="togglePwd('currentPwd',this)"
                            style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;line-height:1;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <hr style="border:none;border-top:1px solid #f1f5f9;margin:1.25rem 0;">

            <!-- New password -->
            <div class="form-group">
                <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                    <i class="fas fa-lock-open" style="color:#64748b;font-size:.85rem;"></i> New Password
                </label>
                <div style="position:relative;">
                    <input type="password" name="new_password" id="newPwd" class="form-input" required minlength="6"
                           autocomplete="new-password" style="padding-right:3rem;" oninput="updateStrength(this.value)">
                    <button type="button" onclick="togglePwd('newPwd',this)"
                            style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;line-height:1;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <!-- Strength bar -->
                <div style="margin-top:8px;height:5px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                    <div id="strengthBar" style="height:100%;width:0%;background:#ef4444;border-radius:3px;transition:all .3s;"></div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:4px;">
                    <small id="strengthLabel" style="color:#94a3b8;">Minimum 6 characters</small>
                    <small id="strengthText" style="font-weight:600;"></small>
                </div>
            </div>

            <!-- Confirm password -->
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                    <i class="fas fa-check-double" style="color:#64748b;font-size:.85rem;"></i> Confirm New Password
                </label>
                <div style="position:relative;">
                    <input type="password" name="confirm_password" id="confirmPwd" class="form-input" required minlength="6"
                           autocomplete="new-password" style="padding-right:3rem;" oninput="checkMatch()">
                    <button type="button" onclick="togglePwd('confirmPwd',this)"
                            style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;line-height:1;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small id="matchLabel" style="color:transparent;font-size:0.8rem;margin-top:4px;display:block;">Passwords match</small>
            </div>

        </form>

        <!-- Tips -->
        <div style="margin:0 2rem;padding:1rem 1.25rem;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;margin-bottom:1.5rem;">
            <div style="font-size:0.8rem;font-weight:600;color:#92400e;margin-bottom:.5rem;"><i class="fas fa-lightbulb"></i> Tips for a strong password</div>
            <ul style="margin:0;padding-left:1.25rem;font-size:0.8rem;color:#78350f;line-height:1.8;">
                <li>At least 8 characters (6 minimum)</li>
                <li>Mix uppercase, lowercase, numbers and symbols</li>
                <li>Don't reuse passwords from other accounts</li>
            </ul>
        </div>

        <!-- Actions -->
        <div style="padding:1.25rem 2rem 1.75rem;display:flex;gap:.875rem;align-items:center;border-top:1px solid #f1f5f9;">
            <button type="submit" name="change_password" form="pwdForm" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-save"></i> Update Password
            </button>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

</div>

<script>
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    const ic  = btn.querySelector('i');
    inp.type  = inp.type === 'password' ? 'text' : 'password';
    ic.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function updateStrength(pw) {
    let s = 0;
    if (pw.length >= 6)  s++;
    if (pw.length >= 10) s++;
    if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) s++;
    if (/\d/.test(pw))   s++;
    if (/[^a-zA-Z\d]/.test(pw)) s++;

    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthText');
    const pcts  = [0, 20, 40, 65, 85, 100];
    const cols  = ['#ef4444','#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
    const labs  = ['','Weak','Fair','Moderate','Strong','Very Strong'];
    const lcols = ['','#ef4444','#f97316','#ca8a04','#16a34a','#15803d'];
    bar.style.width      = pcts[s] + '%';
    bar.style.background = cols[s];
    label.textContent    = labs[s];
    label.style.color    = lcols[s];
    checkMatch();
}

function checkMatch() {
    const np  = document.getElementById('newPwd').value;
    const cp  = document.getElementById('confirmPwd').value;
    const lbl = document.getElementById('matchLabel');
    if (!cp) { lbl.style.color = 'transparent'; return; }
    if (np === cp) {
        lbl.textContent  = '✓ Passwords match';
        lbl.style.color  = '#16a34a';
    } else {
        lbl.textContent  = '✗ Passwords do not match';
        lbl.style.color  = '#ef4444';
    }
}

document.getElementById('pwdForm').addEventListener('submit', function(e) {
    const np = document.getElementById('newPwd').value;
    const cp = document.getElementById('confirmPwd').value;
    if (np.length < 6)  { e.preventDefault(); alert('New password must be at least 6 characters.'); return; }
    if (np !== cp)      { e.preventDefault(); alert('New passwords do not match.'); return; }
});
</script>

<?php include 'includes/admin_footer.php'; ?>