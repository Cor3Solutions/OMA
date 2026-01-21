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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>OMA Portal</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/user/assets/css/user_style.css">
</head>
<body class="user-body">
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>
    
    <!-- Header -->
    <header class="user-header">
        <div class="header-container">
            <div class="header-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="logo">
                    <i class="fas fa-fist-raised"></i>
                    <span>OMA Portal</span>
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="desktop-nav">
                <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/user/courses.php" class="nav-link <?php echo $current_page === 'courses' ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Courses</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/user/profile.php" class="nav-link <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
            </nav>
            
            <div class="header-right">
                <div class="user-menu">
                    <button class="user-menu-btn" id="userMenuBtn">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <div class="user-dropdown" id="userDropdown">
                        <div class="dropdown-header">
                            <div class="dropdown-avatar">
                                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="dropdown-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                                <div class="dropdown-role"><?php echo ucfirst($user_role); ?></div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo SITE_URL; ?>/user/profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="<?php echo SITE_URL; ?>/user/change_password.php" class="dropdown-item">
                            <i class="fas fa-key"></i> Change Password
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo SITE_URL; ?>/index.php" class="dropdown-item">
                            <i class="fas fa-globe"></i> View Website
                        </a>
                        <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Mobile Navigation -->
    <nav class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <div class="mobile-user-info">
                <div class="mobile-avatar">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
                <div>
                    <div class="mobile-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div class="mobile-role"><?php echo ucfirst($user_role); ?></div>
                </div>
            </div>
        </div>
        
        <div class="mobile-nav-links">
            <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="mobile-nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/user/courses.php" class="mobile-nav-link <?php echo $current_page === 'courses' ? 'active' : ''; ?>">
                <i class="fas fa-graduation-cap"></i>
                <span>Courses</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/user/profile.php" class="mobile-nav-link <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/user/change_password.php" class="mobile-nav-link <?php echo $current_page === 'change_password' ? 'active' : ''; ?>">
                <i class="fas fa-key"></i>
                <span>Change Password</span>
            </a>
            
            <div class="mobile-nav-divider"></div>
            
            <a href="<?php echo SITE_URL; ?>/index.php" class="mobile-nav-link">
                <i class="fas fa-globe"></i>
                <span>View Website</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="mobile-nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="user-main">
    
    <script>
        // Initialize mobile menu
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileNav = document.getElementById('mobileNav');
            const mobileOverlay = document.getElementById('mobileOverlay');
            
            if (mobileMenuBtn && mobileNav && mobileOverlay) {
                // Toggle mobile menu
                mobileMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    this.classList.toggle('active');
                    mobileNav.classList.toggle('active');
                    mobileOverlay.classList.toggle('active');
                    document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
                });
                
                // Close on overlay click
                mobileOverlay.addEventListener('click', function() {
                    mobileMenuBtn.classList.remove('active');
                    mobileNav.classList.remove('active');
                    this.classList.remove('active');
                    document.body.style.overflow = '';
                });
                
                // Close on link click
                document.querySelectorAll('.mobile-nav-link').forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuBtn.classList.remove('active');
                        mobileNav.classList.remove('active');
                        mobileOverlay.classList.remove('active');
                        document.body.style.overflow = '';
                    });
                });
            }
            
            // Initialize user dropdown
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userDropdown = document.getElementById('userDropdown');
            
            if (userMenuBtn && userDropdown) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    this.classList.toggle('active');
                    userDropdown.classList.toggle('active');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                        userMenuBtn.classList.remove('active');
                        userDropdown.classList.remove('active');
                    }
                });
                
                // Prevent dropdown from closing when clicking inside
                userDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });
    </script>