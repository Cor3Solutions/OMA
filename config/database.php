<?php
/**
 * Database Configuration
 * Oriental Muayboran Academy
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'u156115548_db_c7q18hWL');
define('DB_USER', 'u156115548_usr_c7q18hWL');
define('DB_PASS', 'lZ2|INcfX6my');

// Site configuration
define('SITE_NAME', 'Oriental Muayboran Academy');
define('SITE_URL', 'https://muayboranacademyph.com');
define('ADMIN_EMAIL', 'admin@oma.com');

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/');

// ── Session configuration ──────────────────────────────────────────────────
// ini_set() for session settings MUST run before session_start().
// We guard with session_status() so this file is safe to include anywhere,
// even after another part of the app has already opened a session.
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1); // Hostinger provides SSL/HTTPS
    session_start();
}
// If a session is already active (status === PHP_SESSION_ACTIVE) we skip the
// ini_set calls entirely — they would have had no effect anyway, and PHP 8+
// throws a warning if you try.

/**
 * Get database connection (singleton)
 * @return mysqli
 */
function getDbConnection() {
    static $conn = null;

    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $conn->set_charset("utf8mb4");
    }

    return $conn;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

/**
 * Require login — redirect to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/pages/login.php');
        exit();
    }
}

/**
 * Require admin — redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/pages/dashboard.php');
        exit();
    }
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

/**
 * Upload file with validation
 */
function uploadFile($file, $targetDir, $allowedTypes = []) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error: ' . $file['error']];
    }

    $fileType = mime_content_type($file['tmp_name']);
    if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename   = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $filename;

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Delete file
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Get user by ID
 */
function getUserById($userId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, name, email, phone, role, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();
    $stmt->close();
    return $user;
}