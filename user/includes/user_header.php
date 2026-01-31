<?php
if (!defined('DB_HOST')) {
    require_once '../config/database.php';
}
requireLogin();

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$user_role = $_SESSION['user_role'];

// Redirect admins
if ($user_role === 'admin') {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

// Fetch user data for avatar
$user_id = $_SESSION['user_id'];
$conn = getDbConnection();
$user_data = $conn->query("SELECT name, email FROM users WHERE id = $user_id")->fetch_assoc();

// Get role-specific data for photo
$photo_path = '';
if ($user_role === 'member') {
    $member = $conn->query("SELECT photo_path FROM khan_members WHERE user_id = $user_id")->fetch_assoc();
    $photo_path = $member['photo_path'] ?? '';
} elseif ($user_role === 'instructor') {
    $instructor = $conn->query("SELECT photo_path FROM instructors WHERE user_id = $user_id")->fetch_assoc();
    $photo_path = $instructor['photo_path'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Oriental Muayboran Academy</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/user/assets/css/user_style.css">

    <!-- Security Meta Tags -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline';">
    
    <style>
        /* Anti-Screenshot & Print Protection */
        @media print {
            body { display: none !important; }
        }
        
        body, .protected-content {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
        
        img, video {
            pointer-events: none;
            -webkit-user-drag: none;
            -khtml-user-drag: none;
            -moz-user-drag: none;
            -o-user-drag: none;
        }
        
        /* Watermark Overlay */
        body::before {
            content: "CONFIDENTIAL - <?php echo strtoupper($_SESSION['user_name']); ?> - <?php echo date('Y-m-d H:i:s'); ?>";
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48px;
            color: rgba(139, 0, 0, 0.03);
            pointer-events: none;
            z-index: 9999;
            white-space: nowrap;
            font-weight: 700;
            letter-spacing: 8px;
        }
    </style>
</head>
<body class="user-body">
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Header -->
    <header class="user-header">
        <div class="header-container">
            <!-- Left Section -->
            <div class="header-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Navigation">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="logo">
                    <i class="fas fa-fire-alt"></i>
                    <span class="logo-text">OMA</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <nav class="desktop-nav" aria-label="Main Navigation">
                <a href="<?php echo SITE_URL; ?>/user/dashboard.php" 
                   class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>"
                   aria-current="<?php echo $current_page === 'dashboard' ? 'page' : 'false'; ?>">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>

                <?php if ($user_role === 'instructor'): ?>
                <a href="<?php echo SITE_URL; ?>/user/instructor_students.php" 
                   class="nav-link <?php echo $current_page === 'instructor_students' ? 'active' : ''; ?>"
                   aria-current="<?php echo $current_page === 'instructor_students' ? 'page' : 'false'; ?>">
                    <i class="fas fa-users"></i>
                    <span>My Students</span>
                </a>
                <?php elseif ($user_role === 'member'): ?>
                <a href="<?php echo SITE_URL; ?>/user/student_history.php" 
                   class="nav-link <?php echo $current_page === 'student_history' ? 'active' : ''; ?>"
                   aria-current="<?php echo $current_page === 'student_history' ? 'page' : 'false'; ?>">
                    <i class="fas fa-history"></i>
                    <span>My History</span>
                </a>
                <?php endif; ?>

                <a href="<?php echo SITE_URL; ?>/user/courses.php" 
                   class="nav-link <?php echo $current_page === 'courses' ? 'active' : ''; ?>"
                   aria-current="<?php echo $current_page === 'courses' ? 'page' : 'false'; ?>">
                    <i class="fas fa-book-open"></i>
                    <span>Materials</span>
                </a>
            </nav>

            <!-- User Menu -->
            <div class="header-right">
                <div class="user-menu">
                    <button class="user-menu-btn" id="userMenuBtn" aria-label="User Menu" aria-expanded="false">
                        <?php if (!empty($photo_path)): ?>
                            <img src="<?php echo SITE_URL . '/' . $photo_path; ?>" alt="Profile" class="user-avatar-img">
                        <?php else: ?>
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <div class="user-dropdown" id="userDropdown" role="menu">
                        <div class="dropdown-header">
                            <?php if (!empty($photo_path)): ?>
                                <img src="<?php echo SITE_URL . '/' . $photo_path; ?>" alt="Profile" class="dropdown-avatar-img">
                            <?php else: ?>
                                <div class="dropdown-avatar">
                                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="dropdown-user-info">
                                <div class="dropdown-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                                <div class="dropdown-email"><?php echo htmlspecialchars($user_data['email'] ?? ''); ?></div>
                                <span class="dropdown-role"><?php echo ucfirst($user_role); ?></span>
                            </div>
                        </div>
                        
                        <div class="dropdown-divider"></div>
                        
                        <a href="<?php echo SITE_URL; ?>/user/profile.php" class="dropdown-item" role="menuitem">
                            <i class="fas fa-user-circle"></i>
                            <span>View Profile</span>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/user/change_password.php" class="dropdown-item" role="menuitem">
                            <i class="fas fa-shield-alt"></i>
                            <span>Change Password</span>
                        </a>
                        
                        <div class="dropdown-divider"></div>
                        
                        <a href="<?php echo SITE_URL; ?>/index.php" class="dropdown-item" role="menuitem">
                            <i class="fas fa-globe"></i>
                            <span>Main Website</span>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="dropdown-item" role="menuitem">
                            <i class="fas fa-envelope"></i>
                            <span>Contact Admin</span>
                        </a>
                        
                        <div class="dropdown-divider"></div>
                        
                        <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="dropdown-item logout-item" role="menuitem">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Sign Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav" id="mobileNav" aria-label="Mobile Navigation">
        <div class="mobile-nav-header">
            <div class="mobile-user-info">
                <?php if (!empty($photo_path)): ?>
                    <img src="<?php echo SITE_URL . '/' . $photo_path; ?>" alt="Profile" class="mobile-avatar-img">
                <?php else: ?>
                    <div class="mobile-avatar">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div class="mobile-user-details">
                    <div class="mobile-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div class="mobile-role"><?php echo ucfirst($user_role); ?></div>
                </div>
            </div>
        </div>

        <div class="mobile-nav-links">
            <a href="<?php echo SITE_URL; ?>/user/dashboard.php" 
               class="mobile-nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>

            <?php if ($user_role === 'instructor'): ?>
            <a href="<?php echo SITE_URL; ?>/user/instructor_students.php" 
               class="mobile-nav-link <?php echo $current_page === 'instructor_students' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>My Students</span>
            </a>
            <?php elseif ($user_role === 'member'): ?>
            <a href="<?php echo SITE_URL; ?>/user/student_history.php" 
               class="mobile-nav-link <?php echo $current_page === 'student_history' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>My History</span>
            </a>
            <?php endif; ?>

            <a href="<?php echo SITE_URL; ?>/user/courses.php" 
               class="mobile-nav-link <?php echo $current_page === 'courses' ? 'active' : ''; ?>">
                <i class="fas fa-book-open"></i>
                <span>Training Materials</span>
            </a>

            <div class="mobile-nav-divider"></div>

            <a href="<?php echo SITE_URL; ?>/user/profile.php" 
               class="mobile-nav-link <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/user/change_password.php" 
               class="mobile-nav-link <?php echo $current_page === 'change_password' ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i>
                <span>Change Password</span>
            </a>

            <div class="mobile-nav-divider"></div>

            <a href="<?php echo SITE_URL; ?>/index.php" class="mobile-nav-link">
                <i class="fas fa-globe"></i>
                <span>Main Website</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="mobile-nav-link">
                <i class="fas fa-envelope"></i>
                <span>Contact Admin</span>
            </a>
            
            <div class="mobile-nav-divider"></div>
            
            <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="mobile-nav-link logout-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </nav>

    <main class="user-main protected-content">

    <script>
        // Enhanced Security - Anti-Screenshot and Content Protection
        (function() {
            'use strict';
            
            // Disable right-click
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                showSecurityAlert('Right-click is disabled for content protection.');
                return false;
            });

            // Disable keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + S (Save)
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    showSecurityAlert('Saving is disabled for content protection.');
                    return false;
                }
                
                // Ctrl/Cmd + P (Print)
                if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                    e.preventDefault();
                    showSecurityAlert('Printing is disabled for content protection.');
                    return false;
                }
                
                // Ctrl/Cmd + U (View Source)
                if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
                    e.preventDefault();
                    showSecurityAlert('View source is disabled.');
                    return false;
                }
                
                // Ctrl/Cmd + Shift + I (Dev Tools)
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'I') {
                    e.preventDefault();
                    return false;
                }
                
                // Ctrl/Cmd + Shift + C (Inspect)
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'C') {
                    e.preventDefault();
                    return false;
                }
                
                // F12 (Dev Tools)
                if (e.key === 'F12') {
                    e.preventDefault();
                    return false;
                }
                
                // PrintScreen detection
                if (e.key === 'PrintScreen') {
                    navigator.clipboard.writeText('');
                    showSecurityAlert('Screenshots are not permitted for this content.');
                }
            });

            // Screenshot detection for Windows
            window.addEventListener('keyup', function(e) {
                if (e.key === 'PrintScreen') {
                    navigator.clipboard.writeText('').catch(() => {});
                    showSecurityAlert('Screenshots are prohibited.');
                }
            });

            // Blur detection (potential screenshot attempt)
            let blurCount = 0;
            window.addEventListener('blur', function() {
                blurCount++;
                if (blurCount > 3) {
                    console.log('Multiple window blur events detected');
                }
            });

            // Security alert function
            function showSecurityAlert(message) {
                const alert = document.createElement('div');
                alert.className = 'security-alert';
                alert.innerHTML = `
                    <i class="fas fa-shield-alt"></i>
                    <span>${message}</span>
                `;
                document.body.appendChild(alert);
                
                setTimeout(() => {
                    alert.classList.add('show');
                }, 10);
                
                setTimeout(() => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 300);
                }, 3000);
            }

            // Mobile Navigation Toggle
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileNav = document.getElementById('mobileNav');
            const mobileOverlay = document.getElementById('mobileOverlay');

            if (mobileMenuBtn && mobileNav && mobileOverlay) {
                mobileMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    this.classList.toggle('active');
                    mobileNav.classList.toggle('active');
                    mobileOverlay.classList.toggle('active');
                    document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
                });

                mobileOverlay.addEventListener('click', function() {
                    mobileMenuBtn.classList.remove('active');
                    mobileNav.classList.remove('active');
                    this.classList.remove('active');
                    document.body.style.overflow = '';
                });

                // Close mobile menu on link click
                document.querySelectorAll('.mobile-nav-link').forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuBtn.classList.remove('active');
                        mobileNav.classList.remove('active');
                        mobileOverlay.classList.remove('active');
                        document.body.style.overflow = '';
                    });
                });
            }

            // User Dropdown Toggle
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userDropdown = document.getElementById('userDropdown');

            if (userMenuBtn && userDropdown) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isActive = this.classList.toggle('active');
                    userDropdown.classList.toggle('active');
                    this.setAttribute('aria-expanded', isActive);
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                        userMenuBtn.classList.remove('active');
                        userDropdown.classList.remove('active');
                        userMenuBtn.setAttribute('aria-expanded', 'false');
                    }
                });

                userDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        })();
    </script>