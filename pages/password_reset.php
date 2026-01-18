<?php
// EMERGENCY PASSWORD RESET TOOL
// Delete this file after using it for security!

require_once '../config/database.php';

$message = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    
    if (empty($email) || empty($new_password)) {
        $message = '❌ Please enter both email and new password';
    } else {
        $conn = getDbConnection();
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update_stmt->bind_param("ss", $hashed_password, $email);
            
            if ($update_stmt->execute()) {
                $message = "✅ Password updated successfully for: " . htmlspecialchars($user['name']) . " (" . htmlspecialchars($email) . ")";
                $success = true;
                
                // Test the new password immediately
                $verify_stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
                $verify_stmt->bind_param("s", $email);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                $verify_row = $verify_result->fetch_assoc();
                
                if (password_verify($new_password, $verify_row['password'])) {
                    $message .= "<br>✅ Password verification test PASSED!<br><br>";
                    $message .= "<strong>You can now login with:</strong><br>";
                    $message .= "Email: " . htmlspecialchars($email) . "<br>";
                    $message .= "Password: " . htmlspecialchars($new_password) . "<br><br>";
                    $message .= "<a href='login.php' style='background: #1976d2; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;'>Go to Login Page</a>";
                } else {
                    $message .= "<br>⚠️ Password was updated but verification test failed. Please try again.";
                    $success = false;
                }
            } else {
                $message = "❌ Failed to update password. Error: " . $conn->error;
            }
            
            $update_stmt->close();
        } else {
            $message = "❌ No user found with email: " . htmlspecialchars($email);
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Emergency Password Reset - OMA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #1976d2;
            margin-top: 0;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            color: #856404;
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-bottom: 20px;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-bottom: 20px;
            color: #721c24;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="email"],
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        button {
            background: #dc3545;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover {
            background: #c82333;
        }
        .quick-preset {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .quick-preset button {
            background: #1976d2;
            padding: 8px 15px;
            font-size: 14px;
            margin-top: 10px;
        }
        .quick-preset button:hover {
            background: #1565c0;
        }
        .security-note {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin-top: 30px;
            color: #c62828;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🔧 Emergency Password Reset Tool</h2>
    
    <div class="warning">
        <strong>⚠️ Security Warning:</strong> This is an emergency tool. DELETE this file immediately after using it!
    </div>
    
    <?php if ($message): ?>
        <div class="<?php echo $success ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="quick-preset">
        <strong>Quick Fix for Admin Account:</strong>
        <form method="POST" style="margin-top: 10px;">
            <input type="hidden" name="email" value="admin@oma.com">
            <input type="hidden" name="new_password" value="admin123">
            <button type="submit" name="reset_password">Reset Admin Password to "admin123"</button>
        </form>
        <p style="margin: 10px 0 0 0; font-size: 13px;">
            This will set the admin password to: <code>admin123</code>
        </p>
    </div>
    
    <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
    
    <h3>Or Reset Any User Password:</h3>
    
    <form method="POST">
        <div class="form-group">
            <label for="email">User Email:</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                placeholder="user@example.com"
                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                required
            >
        </div>
        
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input 
                type="text" 
                id="new_password" 
                name="new_password" 
                placeholder="Enter new password"
                required
            >
            <small style="color: #666;">Showing as text so you can see what you're typing</small>
        </div>
        
        <button type="submit" name="reset_password">Reset Password</button>
    </form>
    
    <div class="security-note">
        <strong>🚨 IMPORTANT:</strong><br>
        After fixing your password, DELETE this file immediately!<br>
        File location: <code>/pages/password_reset.php</code>
    </div>
</div>

</body>
</html>