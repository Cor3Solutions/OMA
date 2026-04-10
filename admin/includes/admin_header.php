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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/assets/css/admin_style.css">
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">

    <style>
    /* ── Sidebar nav item base ── */
    .sidebar-nav .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* ══════════════════════════════════════════
       SIDEBAR HEADER — logo group
       ══════════════════════════════════════════ */
    .sidebar-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 18px 16px;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        background: rgba(0,0,0,0.15);
    }

    /* OMA logo */
    .sidebar-logo-oma {
        width: 52px;
        height: 52px;
        object-fit: contain;
        flex-shrink: 0;
        filter: drop-shadow(0 0 8px rgba(255,255,255,0.3));
    }

    /* Text block */
    .sidebar-logo-text {
        flex: 1;
        min-width: 0;
    }
    .sidebar-logo-text strong {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.3;
        letter-spacing: 0.3px;
    }
    .sidebar-logo-text span {
        display: block;
        font-size: 0.65rem;
        color: rgba(255,255,255,0.45);
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    /* THFP partner badge — sits at the right of the sidebar header */
    .sidebar-thfp-wrap {
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 6px 8px;
        border-radius: 8px;
        border: 1px solid rgba(212,175,55,0.25);
        background: rgba(212,175,55,0.06);
        transition: border-color 0.2s, background 0.2s;
        text-decoration: none;
        cursor: default;
    }
    .sidebar-thfp-wrap:hover {
        border-color: rgba(212,175,55,0.55);
        background: rgba(212,175,55,0.12);
    }
    .sidebar-thfp-logo {
        width: 38px;
        height: 38px;
        object-fit: contain;
        filter: drop-shadow(0 0 6px rgba(255,255,255,0.25));
    }
    .sidebar-thfp-label {
        font-size: 0.52rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: rgba(212,175,55,0.8);
        text-align: center;
        line-height: 1;
    }

    /* ══════════════════════════════════════════
       THFP FOOTER CARD inside sidebar
       (shows at bottom of nav, above logout)
       ══════════════════════════════════════════ */
    .sidebar-thfp-footer {
        margin: 12px 12px 4px;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(212,175,55,0.2);
        background: rgba(212,175,55,0.04);
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        transition: border-color 0.2s, background 0.2s;
    }
    .sidebar-thfp-footer:hover {
        border-color: rgba(212,175,55,0.45);
        background: rgba(212,175,55,0.1);
    }
    .sidebar-thfp-footer img {
        width: 32px; height: 32px;
        object-fit: contain;
        flex-shrink: 0;
        filter: drop-shadow(0 0 4px rgba(255,255,255,0.2));
    }
    .sidebar-thfp-footer-text strong {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        color: rgba(212,175,55,0.9);
        letter-spacing: 1px;
    }
    .sidebar-thfp-footer-text span {
        display: block;
        font-size: 0.62rem;
        color: rgba(255,255,255,0.4);
        margin-top: 1px;
    }
    </style>
</head>

<body class="admin-body">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">

            <!-- ── Sidebar Header: OMA logo + title + THFP badge ── -->
            <div class="sidebar-header">
                <img src="../assets/images/oma.png"
                     alt="OMA Logo"
                     class="sidebar-logo-oma">

                <div class="sidebar-logo-text">
                    <strong>Oriental Muay Boran Academy</strong>
                    <span>Admin Panel</span>
                </div>

                <!-- THFP badge — right side of header -->
                <div class="sidebar-thfp-wrap" title="THFP — Official Partner">
                    <img src="../assets/images/thfp.png"
                         alt="THFP"
                         class="sidebar-thfp-logo">
                    <span class="sidebar-thfp-label">THFP</span>
                </div>
            </div>

            <!-- ── Sidebar Nav ── -->
            <nav class="sidebar-nav">
                <a href="<?php echo SITE_URL; ?>/admin/index.php"
                    class="nav-item <?php echo $current_admin_page === 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt fa-fw"></i>
                    <span>Dashboard</span>
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

                <a href="<?php echo SITE_URL; ?>/admin/manage_admin_accounts.php"
                    class="nav-item <?php echo $current_admin_page === 'manage_admin_accounts' ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog fa-fw"></i>
                    <span>Admin Accounts</span>
                </a>

                <a href="<?php echo SITE_URL; ?>/admin/admin_change_password.php"
                    class="nav-item <?php echo $current_admin_page === 'admin_change_password' ? 'active' : ''; ?>">
                    <i class="fas fa-lock fa-fw"></i>
                    <span>Change Password</span>
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

                <a href="<?php echo SITE_URL; ?>/admin/refresher_requests.php"
                    class="nav-item <?php echo $current_admin_page === 'refresher_requests' ? 'active' : ''; ?>">
                    <i class="fas fa-redo fa-fw"></i>
                    <span>Refresher Requests</span>
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
                    if ($new_messages > 0): ?>
                        <span class="badge" style="background:var(--admin-danger);color:white;margin-left:auto;padding:0.25rem 0.5rem;border-radius:9999px;font-size:0.75rem;">
                            <?php echo $new_messages; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <div class="nav-divider"></div>

                <!-- ── THFP card above the utility links ── -->
                <a href="<?php echo SITE_URL; ?>/index.php"
                   class="sidebar-thfp-footer"
                   title="THFP — Official Partner">
                    <img src="../assets/images/thfp.png" alt="THFP">
                    <div class="sidebar-thfp-footer-text">
                        <strong>THFP</strong>
                        <span>Official Partner</span>
                    </div>
                </a>

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