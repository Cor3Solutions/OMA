<?php
if (!defined('DB_HOST')) {
    require_once '../config/database.php';
}
requireAdmin();

$current_admin_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Panel - OMA</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/assets/css/admin_style.css">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>OMA Admin</h2>
                <p>Control Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="<?php echo SITE_URL; ?>/admin/index.php" class="nav-item <?php echo $current_admin_page === 'index' ? 'active' : ''; ?>">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/manage_users_centralized.php" class="nav-item <?php echo $current_admin_page === 'users' ? 'active' : ''; ?>">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    <span>Users</span>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/khan_members.php" class="nav-item <?php echo $current_admin_page === 'khan_members' ? 'active' : ''; ?>">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
                    </svg>
                    <span>Khan Members</span>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/instructors.php" class="nav-item <?php echo $current_admin_page === 'instructors' ? 'active' : ''; ?>">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/>
                    </svg>
                    <span>Instructors</span>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/affiliates.php" class="nav-item <?php echo $current_admin_page === 'affiliates' ? 'active' : ''; ?>">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l-5.5 9h11z M12 22l5.5-9h-11z"/>
                    </svg>
                    <span>Affiliates</span>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/courses.php" class="nav-item <?php echo $current_admin_page === 'courses' ? 'active' : ''; ?>">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                    </svg>
                    <span>Course Materials</span>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/events.php" class="nav-item <?php echo $current_admin_page === 'events' ? 'active' : ''; ?>">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/>
                    </svg>
                    <span>Events</span>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/messages.php" class="nav-item <?php echo $current_admin_page === 'messages' ? 'active' : ''; ?>">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                    </svg>
                    <span>Messages</span>
                </a>
                
                <div class="nav-divider"></div>
                
                <a href="<?php echo SITE_URL; ?>/index.php" class="nav-item">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    </svg>
                    <span>View Site</span>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="nav-item">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                    </svg>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>
        
        <div class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <button class="mobile-menu-toggle" id="sidebarToggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <h1><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?></h1>
                </div>
                <div class="header-right">
                    <div class="user-menu">
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                    </div>
                </div>
            </header>
            
            <div class="admin-content">
