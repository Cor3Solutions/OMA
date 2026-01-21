<?php
$page_title = "Change Password";
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
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current user password
    $user_query = $conn->query("SELECT password FROM users WHERE id = $user_id");
    $user = $user_query->fetch_assoc();
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $error = 'Current password is incorrect';
    }
    // Check if new passwords match
    elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    }
    // Check password length
    elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    }
    else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $success = 'Password changed successfully!';
            // Clear form
            $_POST = [];
        } else {
            $error = 'Failed to change password';
        }
        $stmt->close();
    }
}

include 'includes/user_header.php';
?>

<div class="password-container">
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

    <div class="password-card">
        <div class="card-header">
            <div class="header-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div>
                <h1>Change Password</h1>
                <p>Keep your account secure with a strong password</p>
            </div>
        </div>

        <form method="POST" class="password-form" id="passwordForm">
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-lock"></i> Current Password *
                </label>
                <div class="password-input-wrapper">
                    <input type="password" name="current_password" class="form-input" id="currentPassword" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('currentPassword', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-key"></i> New Password *
                </label>
                <div class="password-input-wrapper">
                    <input type="password" name="new_password" class="form-input" id="newPassword" required minlength="6">
                    <button type="button" class="toggle-password" onclick="togglePassword('newPassword', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength" id="passwordStrength"></div>
                <small class="form-hint">Minimum 6 characters</small>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-check-double"></i> Confirm New Password *
                </label>
                <div class="password-input-wrapper">
                    <input type="password" name="confirm_password" class="form-input" id="confirmPassword" required minlength="6">
                    <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="password-tips">
                <h3><i class="fas fa-info-circle"></i> Password Tips</h3>
                <ul>
                    <li>Use at least 6 characters</li>
                    <li>Mix uppercase and lowercase letters</li>
                    <li>Include numbers and special characters</li>
                    <li>Avoid common words or personal information</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Change Password
                </button>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.password-container {
    max-width: 600px;
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

.password-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    padding: 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.header-icon {
    width: 64px;
    height: 64px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.card-header h1 {
    font-size: 1.75rem;
    margin-bottom: 0.25rem;
}

.card-header p {
    opacity: 0.9;
    font-size: 0.9375rem;
}

.password-form {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text);
}

.password-input-wrapper {
    position: relative;
}

.form-input {
    width: 100%;
    padding: 0.875rem 3rem 0.875rem 1rem;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    transition: var(--transition);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
}

.toggle-password {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: 0.5rem;
    transition: var(--transition);
}

.toggle-password:hover {
    color: var(--primary);
}

.password-strength {
    margin-top: 0.5rem;
    height: 4px;
    background: var(--border);
    border-radius: 2px;
    overflow: hidden;
    transition: var(--transition);
}

.password-strength::before {
    content: '';
    display: block;
    height: 100%;
    width: 0%;
    transition: var(--transition);
}

.password-strength.weak::before {
    width: 33%;
    background: #f44336;
}

.password-strength.medium::before {
    width: 66%;
    background: #ffc107;
}

.password-strength.strong::before {
    width: 100%;
    background: #4caf50;
}

.form-hint {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-light);
}

.password-tips {
    background: var(--light);
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.password-tips h3 {
    font-size: 1rem;
    margin-bottom: 0.75rem;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.password-tips ul {
    list-style: none;
    padding-left: 0;
}

.password-tips li {
    padding: 0.375rem 0;
    color: var(--text-light);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.password-tips li::before {
    content: '✓';
    color: var(--success);
    font-weight: 700;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
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
    .card-header {
        flex-direction: column;
        text-align: center;
    }
    
    .card-header h1 {
        font-size: 1.5rem;
    }
    
    .password-form {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength indicator
const newPassword = document.getElementById('newPassword');
const strengthBar = document.getElementById('passwordStrength');

newPassword.addEventListener('input', function() {
    const password = this.value;
    const strength = calculatePasswordStrength(password);
    
    strengthBar.className = 'password-strength';
    if (strength > 0) {
        if (strength < 3) {
            strengthBar.classList.add('weak');
        } else if (strength < 5) {
            strengthBar.classList.add('medium');
        } else {
            strengthBar.classList.add('strong');
        }
    }
});

function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    return strength;
}

// Form validation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPass = document.getElementById('newPassword').value;
    const confirmPass = document.getElementById('confirmPassword').value;
    
    if (newPass !== confirmPass) {
        e.preventDefault();
        alert('New passwords do not match!');
        return false;
    }
    
    if (newPass.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters!');
        return false;
    }
});
</script>

<?php include 'includes/user_footer.php'; ?>