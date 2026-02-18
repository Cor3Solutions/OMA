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
    <meta name="description" content="Oriental Muayboran Academy Admin Panel">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Admin Panel - OMA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/assets/css/admin_style.css">

    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">

    <style>
        .sidebar-nav .nav-item {
            display: flex;
            /* Aligns icon and text in a row */
            align-items: center;
            /* Centers them vertically */
            gap: 12px;
            /* Adds space between icon and text */
        }
    </style>
</head>

<body class="admin-body">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="sidebar-header" style="display: flex; align-items: center; padding: 15px;">
                <img src="../assets/images/oma.png" alt="OMA Logo"
                    style="width: 80px; height: auto; margin-right: 10px;">
                <span style="font-size: 14px; font-weight: bold; color: white; line-height: 1.2;">
                    Oriental Muay Boran Academy Admin
                </span>
            </div>

            <nav class="sidebar-nav">
                <a href="<?php echo SITE_URL; ?>/admin/index.php"
                    class="nav-item <?php echo $current_admin_page === 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt fa-fw"></i>
                    <span>Dashboard</span>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/manage_users_centralized.php"
                    class="nav-item <?php echo $current_admin_page === 'manage_users_centralized' ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog fa-fw"></i>
                    <span>User Management</span>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/khan_members.php"
                    class="nav-item <?php echo $current_admin_page === 'khan_members' || $current_admin_page === 'member_training_history' ? 'active' : ''; ?>">
                    <i class="fas fa-user-graduate fa-fw"></i>
                    <span>Khan Members</span>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/instructors.php"
                    class="nav-item <?php echo $current_admin_page === 'instructors' ? 'active' : ''; ?>">
                    <i class="fas fa-chalkboard-teacher fa-fw"></i>
                    <span>Instructors</span>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/affiliates.php"
                    class="nav-item <?php echo $current_admin_page === 'affiliates' ? 'active' : ''; ?>">
                    <i class="fas fa-handshake fa-fw"></i>
                    <span>Affiliates</span>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/courses.php"
                    class="nav-item <?php echo $current_admin_page === 'courses' ? 'active' : ''; ?>">
                    <i class="fas fa-book-open fa-fw"></i>
                    <span>Course Materials</span>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/events.php"
                    class="nav-item <?php echo $current_admin_page === 'events' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt fa-fw"></i>
                    <span>Events Gallery</span>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/messages.php"
                    class="nav-item <?php echo $current_admin_page === 'messages' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope fa-fw"></i>
                    <span>Messages</span>
                    <?php
                    $conn_check = getDbConnection();
                    $new_messages = $conn_check->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'")->fetch_assoc()['count'];
                    if ($new_messages > 0):
                        ?>
                        <span class="badge"
                            style="background: var(--admin-danger); color: white; margin-left: auto; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem;"><?php echo $new_messages; ?></span>
                    <?php endif; ?>
                </a>

                <div class="nav-divider"></div>

                <a href="<?php echo SITE_URL; ?>/index.php" class="nav-item">
                    <i class="fas fa-globe fa-fw"></i>
                    <span>View Site</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/activity_log.php"
                    class="nav-item <?php echo $current_admin_page === 'activity_log' ? 'active' : ''; ?>">
                    <i class="fas fa-history fa-fw"></i>
                    <span>Activity Log</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/backup_database.php"
                    class="nav-item <?php echo $current_admin_page === 'backup_database' ? 'active' : ''; ?>">
                    <i class="fas fa-database fa-fw"></i>
                    <span>Backup Database</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt fa-fw"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <div class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <button class="mobile-menu-toggle" id="sidebarToggle" aria-label="Toggle menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <h1><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel'; ?></h1>
                </div>
                <div class="header-right">
                    <div class="user-menu">
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                    </div>
                </div>
            </header>

            <div class="admin-content">