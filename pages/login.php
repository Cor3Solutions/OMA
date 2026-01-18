<?php
$page_title = "Login";
require_once '../config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ../admin/index.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if ($user['status'] === 'suspended') {
                $error = 'Your account has been suspended. Please contact admin.';
            } elseif ($user['status'] === 'inactive') {
                $error = 'Your account is inactive. Please contact admin.';
            } elseif (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: ../admin/index.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
        
        $stmt->close();
        $conn->close();
    }
}

include '../includes/header.php';
?>

<section class="section">
    <div class="container" style="max-width: 500px;">
        <div class="section-header text-center">
            <p class="section-subtitle">Welcome Back</p>
            <h1 class="section-title">Member Login</h1>
            <p class="section-description">
                Access your account to view training materials and track your progress
            </p>
        </div>
        
        <div class="card" style="margin-top: 2rem;">
            <?php if ($error): ?>
                <div class="alert alert-error" style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="your.email@example.com"
                        required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Enter your password"
                        required
                    >
                </div>
                
                <button type="submit" name="login" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Login
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border);">
                <p style="color: var(--color-text-light);">
                    Don't have an account? 
                    <a href="register.php" style="color: var(--color-primary); font-weight: bold;">Register here</a>
                </p>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <p style="background: #e3f2fd; padding: 1rem; border-radius: 8px; color: #1565c0;">
                <strong>Admin Login:</strong> Use this same page with your admin credentials.<br>
                You'll be automatically redirected to the admin panel.
            </p>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
