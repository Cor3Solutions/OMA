<?php
/**
 * login.php — Hardened login page
 *
 * Security measures applied:
 *  1. Security headers (CSP, X-Frame-Options, etc.)
 *  2. CSRF token validation
 *  3. Rate limiting — max 5 attempts per IP per 15 minutes (stored in DB)
 *  4. Session fixation prevention — session_regenerate_id() after login
 *  5. User enumeration prevention — generic error for suspended/inactive too
 *  6. Login attempt logging (failed + successful)
 *  7. Session set BEFORE DB close to avoid race conditions
 *  8. Input length capping
 *  9. Secure session cookie flags
 */

// ── 1. Security headers ───────────────────────────────────────────────────────
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.gstatic.com; font-src https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'");

// ── 2. Secure session configuration (must be before session_start) ───────────
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure',   isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

$page_title = "Login";
require_once '../config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? '../admin/index.php' : '../user/dashboard.php'));
    exit;
}

// ── Rate limiting helper ──────────────────────────────────────────────────────
// Uses the login_attempts table. Schema:
//   CREATE TABLE IF NOT EXISTS login_attempts (
//     id         INT AUTO_INCREMENT PRIMARY KEY,
//     ip_address VARCHAR(45) NOT NULL,
//     email      VARCHAR(255) NOT NULL DEFAULT '',
//     attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
//     INDEX idx_ip (ip_address),
//     INDEX idx_time (attempted_at)
//   );
//
// If you don't have this table yet, the functions degrade gracefully.
function getClientIp(): string {
    // Trust X-Forwarded-For only if behind a known proxy — adjust as needed.
    // For most XAMPP/shared hosts, REMOTE_ADDR is safest.
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function checkRateLimit($conn, string $ip): bool {
    try {
        $window = date('Y-m-d H:i:s', strtotime('-15 minutes'));
        $ip_esc  = $conn->real_escape_string($ip);
        $res = $conn->query("
            SELECT COUNT(*) AS cnt FROM login_attempts
            WHERE ip_address = '$ip_esc'
              AND attempted_at > '$window'
        ");
        if (!$res) return false;
        $row = $res->fetch_assoc();
        return (int)$row['cnt'] >= 5;
    } catch (\mysqli_sql_exception $e) {
        // Table doesn't exist yet — fail open (no blocking)
        return false;
    }
}

function recordAttempt($conn, string $ip, string $email): void {
    try {
        $ip_esc    = $conn->real_escape_string($ip);
        $email_esc = $conn->real_escape_string(substr($email, 0, 255));
        $conn->query("INSERT INTO login_attempts (ip_address, email) VALUES ('$ip_esc', '$email_esc')");
    } catch (\mysqli_sql_exception $e) {
        // Fail silently if table doesn't exist
    }
}

function clearAttempts($conn, string $ip): void {
    try {
        $ip_esc = $conn->real_escape_string($ip);
        $conn->query("DELETE FROM login_attempts WHERE ip_address = '$ip_esc'");
    } catch (\mysqli_sql_exception $e) {
        // Fail silently if table doesn't exist
    }
}

// ── Login attempt logger ──────────────────────────────────────────────────────
// Reuses activity_log if available, otherwise fails silently.
function logLogin($conn, string $action, $userId, string $name, string $email, string $detail): void {
    try {
        $action_esc = $conn->real_escape_string($action);
        $name_esc   = $conn->real_escape_string(substr($name,   0, 255));
        $detail_esc = $conn->real_escape_string(substr($detail, 0, 500));
        $ip         = $conn->real_escape_string(getClientIp());
        $uid        = $userId ? (int)$userId : 'NULL';
        $conn->query("
            INSERT INTO activity_log (admin_name, action, module, record_id, record_label, details, ip_address)
            VALUES ('$name_esc', '$action_esc', 'login', $uid, '$name_esc', '$detail_esc', '$ip')
        ");
    } catch (\mysqli_sql_exception $e) {
        // Fail silently if activity_log table structure differs
    }
}

// ── CSRF token generation/validation ─────────────────────────────────────────
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ── Process login ─────────────────────────────────────────────────────────────
$error        = '';
$rate_limited = false;
$client_ip    = getClientIp();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    // ── CSRF check ────────────────────────────────────────────────────────────
    if (empty($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        // ── Input sanitization + length caps ──────────────────────────────────
        $email    = strtolower(trim(substr($_POST['email']    ?? '', 0, 254)));
        $password = substr($_POST['password'] ?? '', 0, 1000); // cap to avoid bcrypt DoS

        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email or password.'; // don't reveal format hint
        } else {
            $conn = getDbConnection();

            // ── Rate limit check ──────────────────────────────────────────────
            if (checkRateLimit($conn, $client_ip)) {
                $rate_limited = true;
                $error = 'Too many failed attempts. Please wait 15 minutes before trying again.';
                logLogin($conn, 'login_blocked', null, 'Unknown', $email,
                    "Rate limited: IP $client_ip tried email $email");
            } else {
                $stmt = $conn->prepare(
                    "SELECT id, name, email, password, role, status, serial_number
                     FROM users WHERE email = ? LIMIT 1"
                );
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user   = $result->fetch_assoc();
                $stmt->close();

                if ($user && password_verify($password, $user['password'])) {

                    // ── Account status check ──────────────────────────────────
                    if ($user['status'] === 'suspended') {
                        // Record attempt but show generic message to prevent enumeration
                        recordAttempt($conn, $client_ip, $email);
                        logLogin($conn, 'login_fail', $user['id'], $user['name'], $email,
                            "Login rejected: account suspended");
                        $error = 'Your account is not active. Please contact the admin.';

                    } elseif ($user['status'] === 'inactive') {
                        recordAttempt($conn, $client_ip, $email);
                        logLogin($conn, 'login_fail', $user['id'], $user['name'], $email,
                            "Login rejected: account inactive");
                        $error = 'Your account is not active. Please contact the admin.';

                    } else {
                        // ── SUCCESS ───────────────────────────────────────────

                        // Clear rate-limit counter on successful login
                        clearAttempts($conn, $client_ip);

                        // ★ Set session BEFORE closing DB connection
                        // ★ Regenerate session ID to prevent session fixation
                        session_regenerate_id(true);

                        $_SESSION['user_id']       = (int)$user['id'];
                        $_SESSION['user_name']     = $user['name'];
                        $_SESSION['user_role']     = $user['role'];
                        $_SESSION['serial_number'] = $user['serial_number'];
                        $_SESSION['login_time']    = time();
                        $_SESSION['login_ip']      = $client_ip;

                        // Rotate CSRF token after login
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                        logLogin($conn, 'login', $user['id'], $user['name'], $email,
                            "Successful login from IP $client_ip");

                        $conn->close();

                        $redirect = $user['role'] === 'admin'
                            ? '../admin/index.php'
                            : '../user/dashboard.php';

                        header('Location: ' . $redirect);
                        exit;
                    }

                } else {
                    // ── FAILED — wrong password or unknown email ───────────────
                    recordAttempt($conn, $client_ip, $email);

                    // Use the same generic message regardless of whether email exists
                    $error = 'Invalid email or password.';

                    logLogin($conn, 'login_fail', $user['id'] ?? null,
                        $user['name'] ?? 'Unknown', $email,
                        "Failed login attempt from IP $client_ip");
                }

                $conn->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Oriental Muayboran Academy</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;900&family=Sarabun:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --gold:       #D4AF37;
    --gold-dim:   #9C7E1E;
    --red:        #C8102E;
    --black:      #090909;
    --dark:       #111012;
    --panel:      #16141a;
    --muted:      rgba(255,255,255,0.45);
    --font-disp:  'Cinzel', serif;
    --font-body:  'Sarabun', sans-serif;
}

html, body {
    height: 100%;
    background: var(--black);
    color: #fff;
    font-family: var(--font-body);
    overflow: hidden;
}

.login-wrap {
    display: grid;
    grid-template-columns: 1fr 1fr;
    height: 100vh;
}

/* ── Left visual ── */
.login-visual {
    position: relative;
    overflow: hidden;
    background: var(--dark);
}
.visual-bg {
    position: absolute; inset: 0;
    background:
        linear-gradient(160deg, rgba(200,16,46,0.12) 0%, transparent 50%),
        linear-gradient(to bottom, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.85) 100%),
        url('../assets/images/omaa.jpg') center/cover no-repeat;
    animation: zoomDrift 14s ease-in-out infinite alternate;
}
@keyframes zoomDrift {
    from { transform: scale(1.04) translateX(0); }
    to   { transform: scale(1.0)  translateX(-8px); }
}
.visual-geo {
    position: absolute; inset: 0;
    background: repeating-linear-gradient(
        -55deg, transparent, transparent 38px,
        rgba(212,175,55,0.025) 38px, rgba(212,175,55,0.025) 39px
    );
}
.visual-slash {
    position: absolute;
    top: 0; right: -1px;
    width: 90px; height: 100%;
    background: linear-gradient(to bottom right, transparent 48%, var(--panel) 50%);
    z-index: 3;
}
.visual-content {
    position: absolute; inset: 0; z-index: 2;
    display: flex; flex-direction: column;
    justify-content: flex-end;
    padding: 3rem 3.5rem;
}
.visual-eyebrow {
    font-size: 0.68rem; font-weight: 500; letter-spacing: 5px;
    text-transform: uppercase; color: var(--gold); margin-bottom: 1rem;
    opacity: 0; animation: fadeUp 0.7s ease 0.3s forwards;
}
.visual-title {
    font-family: var(--font-disp);
    font-size: clamp(1.8rem, 3vw, 2.6rem);
    font-weight: 900; line-height: 1.1;
    letter-spacing: 1.5px; text-transform: uppercase; color: #fff;
    opacity: 0; animation: fadeUp 0.7s ease 0.5s forwards;
}
.visual-title span { color: var(--gold); }
.visual-divider {
    display: flex; align-items: center; gap: 10px; margin: 1.2rem 0;
    opacity: 0; animation: fadeUp 0.7s ease 0.65s forwards;
}
.vd-line { flex: 1; max-width: 60px; height: 1px; background: linear-gradient(to right, var(--gold), transparent); }
.vd-diamond { width: 6px; height: 6px; background: var(--gold); transform: rotate(45deg); flex-shrink: 0; }
.visual-tagline {
    font-size: 1rem; font-weight: 300; font-style: italic;
    color: var(--muted); line-height: 1.7; max-width: 320px;
    opacity: 0; animation: fadeUp 0.7s ease 0.8s forwards;
}
.corner {
    position: absolute; width: 28px; height: 28px; z-index: 4; opacity: 0.6;
}
.corner--tl { top: 20px; left: 20px; border-top: 1.5px solid var(--gold); border-left: 1.5px solid var(--gold); }
.corner--bl { bottom: 20px; left: 20px; border-bottom: 1.5px solid var(--gold); border-left: 1.5px solid var(--gold); }

/* ── Right form panel ── */
.login-form-panel {
    background: var(--panel);
    display: flex; flex-direction: column;
    justify-content: center; align-items: center;
    padding: 3rem 2rem; position: relative; overflow: hidden;
}
.login-form-panel::before {
    content: ''; position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 80% 60% at 50% 0%, rgba(212,175,55,0.06) 0%, transparent 70%),
        radial-gradient(ellipse 60% 40% at 50% 100%, rgba(200,16,46,0.04) 0%, transparent 70%);
    pointer-events: none;
}
.form-inner {
    width: 100%; max-width: 380px;
    position: relative; z-index: 1;
}

/* Logo */
.form-logo {
    display: flex; flex-direction: column; align-items: center;
    margin-bottom: 2.4rem;
    opacity: 0; animation: fadeDown 0.6s ease 0.2s forwards;
}
.logo-mark {
    width: 52px; height: 52px;
    border: 1.5px solid var(--gold);
    transform: rotate(45deg);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 1rem; position: relative;
}
.logo-mark::before {
    content: ''; position: absolute; inset: 4px;
    border: 1px solid rgba(212,175,55,0.35);
}
.logo-mark span {
    transform: rotate(-45deg);
    font-family: var(--font-disp);
    font-size: 1.1rem; font-weight: 900;
    color: var(--gold); letter-spacing: 1px;
}
.logo-sub {
    font-family: var(--font-disp);
    font-size: 0.62rem; letter-spacing: 4px;
    text-transform: uppercase; color: var(--gold-dim);
}

/* Heading */
.form-heading {
    margin-bottom: 2rem;
    opacity: 0; animation: fadeDown 0.6s ease 0.35s forwards;
}
.form-heading h1 {
    font-family: var(--font-disp);
    font-size: 1.55rem; font-weight: 600;
    letter-spacing: 2px; color: #fff;
    text-transform: uppercase; line-height: 1.2;
}
.form-heading p {
    font-size: 0.88rem; color: var(--muted);
    margin-top: 0.4rem; font-weight: 300; letter-spacing: 0.3px;
}

/* Alerts */
.alert-box {
    display: flex; align-items: flex-start; gap: 10px;
    border-radius: 4px; padding: 0.85rem 1rem;
    margin-bottom: 1.5rem; font-size: 0.875rem; line-height: 1.4;
}
.alert-box.error {
    background: rgba(200,16,46,0.12);
    border: 1px solid rgba(200,16,46,0.35);
    border-left: 3px solid var(--red);
    color: #ff8a8a;
    animation: shake 0.4s ease;
}
.alert-box.rate {
    background: rgba(255,150,0,0.1);
    border: 1px solid rgba(255,150,0,0.3);
    border-left: 3px solid #ff9600;
    color: #ffbe60;
}
@keyframes shake {
    0%,100%{transform:translateX(0)}
    20%{transform:translateX(-5px)} 40%{transform:translateX(5px)}
    60%{transform:translateX(-3px)} 80%{transform:translateX(3px)}
}
.alert-icon { flex-shrink: 0; margin-top: 1px; }

/* Fields */
.field-group {
    margin-bottom: 1.3rem;
    opacity: 0; animation: fadeUp 0.5s ease forwards;
}
.field-group:nth-child(1) { animation-delay: 0.45s; }
.field-group:nth-child(2) { animation-delay: 0.55s; }

.field-label {
    display: block;
    font-size: 0.7rem; font-weight: 500;
    letter-spacing: 2.5px; text-transform: uppercase;
    color: var(--gold-dim); margin-bottom: 0.55rem;
}
.field-wrap { position: relative; }
.field-icon {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: rgba(212,175,55,0.4);
    pointer-events: none; transition: color 0.2s;
}
.field-input {
    width: 100%;
    padding: 0.85rem 1rem 0.85rem 2.7rem;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 3px; color: #fff;
    font-family: var(--font-body);
    font-size: 0.95rem; font-weight: 300;
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
    outline: none; -webkit-appearance: none;
}
.field-input::placeholder { color: rgba(255,255,255,0.2); }
.field-input:focus {
    border-color: var(--gold);
    background: rgba(212,175,55,0.05);
    box-shadow: 0 0 0 3px rgba(212,175,55,0.08);
}
.field-wrap:focus-within .field-icon { color: var(--gold); }

/* Disabled state when rate limited */
.field-input:disabled {
    opacity: 0.45; cursor: not-allowed;
}

.pw-toggle {
    position: absolute; right: 13px; top: 50%;
    transform: translateY(-50%);
    background: none; border: none;
    color: rgba(255,255,255,0.25);
    cursor: pointer; padding: 4px;
    transition: color 0.2s;
}
.pw-toggle:hover { color: var(--gold); }

/* Submit */
.btn-login {
    width: 100%; margin-top: 0.5rem; padding: 1rem;
    background: var(--gold); color: var(--black);
    border: none; border-radius: 3px;
    font-family: var(--font-disp);
    font-size: 0.9rem; font-weight: 600;
    letter-spacing: 3px; text-transform: uppercase;
    cursor: pointer; position: relative; overflow: hidden;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    opacity: 0; animation: fadeUp 0.5s ease 0.65s forwards;
}
.btn-login::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to right, transparent, rgba(255,255,255,0.18), transparent);
    transform: translateX(-100%); transition: transform 0.5s ease;
}
.btn-login:hover:not(:disabled)::before { transform: translateX(100%); }
.btn-login:hover:not(:disabled) {
    background: #e8c84a;
    box-shadow: 0 6px 28px rgba(212,175,55,0.35);
    transform: translateY(-1px);
}
.btn-login:active:not(:disabled) { transform: translateY(0); }
.btn-login:disabled {
    opacity: 0.45; cursor: not-allowed; background: #888;
}
.btn-login.loading {
    pointer-events: none; opacity: 0.75;
}
.btn-login.loading::after {
    content: '';
    display: inline-block; width: 14px; height: 14px;
    border: 2px solid rgba(0,0,0,0.3); border-top-color: var(--black);
    border-radius: 50%; margin-left: 10px; vertical-align: middle;
    animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Countdown badge */
.countdown-badge {
    display: inline-block;
    background: rgba(255,150,0,0.15);
    color: #ffbe60;
    font-size: 0.75rem; letter-spacing: 1px;
    padding: 3px 10px; border-radius: 20px;
    margin-top: 0.5rem;
    font-family: var(--font-body);
}

/* Footer */
.form-footer {
    margin-top: 2rem; text-align: center;
    font-size: 0.78rem; color: rgba(255,255,255,0.2); letter-spacing: 0.5px;
    opacity: 0; animation: fadeUp 0.5s ease 0.75s forwards;
}
.form-footer a { color: var(--gold-dim); text-decoration: none; transition: color 0.2s; }
.form-footer a:hover { color: var(--gold); }

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes fadeDown {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    html, body { overflow: auto; }
    .login-wrap { grid-template-columns: 1fr; height: auto; min-height: 100vh; }
    .login-visual { display: none; }
    .login-form-panel { min-height: 100vh; padding: 2.5rem 1.5rem; }
}
</style>
</head>
<body>

<div class="login-wrap">

    <!-- Left visual -->
    <div class="login-visual">
        <div class="visual-bg"></div>
        <div class="visual-geo"></div>
        <div class="visual-slash"></div>
        <div class="corner corner--tl"></div>
        <div class="corner corner--bl"></div>
        <div class="visual-content">
            <div class="visual-eyebrow">Est. Oriental Muayboran Academy</div>
            <h2 class="visual-title">Ancient Art.<br><span>Modern Warriors.</span></h2>
            <div class="visual-divider">
                <div class="vd-line"></div>
                <div class="vd-diamond"></div>
                <div class="vd-line" style="background:linear-gradient(to left,var(--gold),transparent);"></div>
            </div>
            <p class="visual-tagline">
                Train with purpose. Preserve the heritage of Muayboran — the mother art of Muay Thai.
            </p>
        </div>
    </div>

    <!-- Right form -->
    <div class="login-form-panel">
        <div class="form-inner">

            <div class="form-logo">
                <div class="logo-mark"><span>OMA</span></div>
                <div class="logo-sub">Member Portal</div>
            </div>

            <div class="form-heading">
                <h1>Welcome Back</h1>
                <p>Sign in to access your training materials and track your progress.</p>
            </div>

            <?php if ($rate_limited): ?>
            <div class="alert-box rate">
                <span class="alert-icon">⏳</span>
                <div>
                    <strong>Too many attempts.</strong><br>
                    Please wait before trying again.
                    <br><span class="countdown-badge" id="countdown"></span>
                </div>
            </div>
            <?php elseif ($error): ?>
            <div class="alert-box error">
                <span class="alert-icon">⚠</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <!-- CSRF token — hidden, validated server-side -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="field-group">
                    <label for="email" class="field-label">Email Address</label>
                    <div class="field-wrap">
                        <svg class="field-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input type="email" id="email" name="email" class="field-input"
                               placeholder="your.email@example.com"
                               autocomplete="email" maxlength="254"
                               <?php echo $rate_limited ? 'disabled' : ''; ?>
                               value="<?php echo $rate_limited ? '' : (isset($_POST['email']) ? htmlspecialchars(substr($_POST['email'], 0, 254)) : ''); ?>">
                    </div>
                </div>

                <div class="field-group">
                    <label for="password" class="field-label">Password</label>
                    <div class="field-wrap">
                        <svg class="field-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <input type="password" id="password" name="password" class="field-input"
                               placeholder="Enter your password"
                               autocomplete="current-password" maxlength="1000"
                               <?php echo $rate_limited ? 'disabled' : ''; ?>>
                        <?php if (!$rate_limited): ?>
                        <button type="button" class="pw-toggle" id="pwToggle"
                                aria-label="Show password" title="Show/hide password">
                            <svg id="eyeIcon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" name="login" class="btn-login" id="loginBtn"
                        <?php echo $rate_limited ? 'disabled' : ''; ?>>
                    <?php echo $rate_limited ? 'Locked' : 'Enter the Dojo'; ?>
                </button>
            </form>

            <div class="form-footer">
                Having trouble? <a href="mailto:orientalmuayboranacademy@gmail.com">Contact Admin</a>
            </div>

        </div>
    </div>
</div>

<script>
<?php if (!$rate_limited): ?>
// Password show/hide
const pwToggle = document.getElementById('pwToggle');
const pwInput  = document.getElementById('password');
const eyeIcon  = document.getElementById('eyeIcon');

const eyeOpen   = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
const eyeClosed = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';

pwToggle.addEventListener('click', function () {
    const hidden = pwInput.type === 'password';
    pwInput.type = hidden ? 'text' : 'password';
    eyeIcon.innerHTML = hidden ? eyeClosed : eyeOpen;
    this.setAttribute('aria-label', hidden ? 'Hide password' : 'Show password');
    pwInput.focus();
});

// Loading state
document.getElementById('loginForm').addEventListener('submit', function (e) {
    const email = document.getElementById('email').value.trim();
    const pass  = document.getElementById('password').value;
    if (!email || !pass) { e.preventDefault(); return; }
    const btn = document.getElementById('loginBtn');
    btn.classList.add('loading');
    btn.textContent = 'Authenticating';
});

// Auto-focus
document.addEventListener('DOMContentLoaded', function () {
    const em = document.getElementById('email');
    if (!em.value) em.focus(); else document.getElementById('password').focus();
});
<?php else: ?>
// Countdown timer for rate limit (15 min window shown client-side)
(function () {
    let seconds = 15 * 60;
    const el = document.getElementById('countdown');
    if (!el) return;
    function tick() {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        el.textContent = m + ':' + String(s).padStart(2, '0') + ' remaining';
        if (seconds > 0) { seconds--; setTimeout(tick, 1000); }
        else { el.textContent = 'Refreshing…'; location.reload(); }
    }
    tick();
})();
<?php endif; ?>
</script>

</body>
</html>