<?php
require_once __DIR__ . '/../config/database.php';

// Determine the current page for active nav highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$isInPages = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$basePath = $isInPages ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Oriental Muayboran Academy</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">

    <!-- Facebook SDK -->
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v18.0"></script>
</head>

<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo $basePath; ?>index.php" class="logo">
                    <img src="<?php echo $basePath; ?>assets/images/oma.png" alt="Oriental Muayboran Academy"
                        class="logo-img" style="height: 70px; width: auto;">
                </a>

                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <nav class="main-nav" id="mainNav">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/index.php"
                                class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>">Home</a>
                        </li>

                        <li class="nav-item has-dropdown">
                            <a href="<?php echo SITE_URL; ?>/pages/about.php" class="nav-link">About</a>
                            <ul class="dropdown">
                                <li><a href="<?php echo SITE_URL; ?>/pages/mvc.php">Mission, Vision & Core Values</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/pages/lineage.php">Lineage</a></li>
                            </ul>
                        </li>

                        <li class="nav-item has-dropdown">
                            <a href="<?php echo SITE_URL; ?>/pages/course.php" class="nav-link">Programs</a>
                        </li>

                        <li class="nav-item has-dropdown">
                            <a href="<?php echo SITE_URL; ?>/pages/membership-benefits.php" class="nav-link">Khan
                                Community</a>
                            <ul class="dropdown">
                                <li><a href="<?php echo SITE_URL; ?>/pages/khan-grading.php">Khan Grading</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/pages/khan-members.php">Khan Members</a></li>
                             </ul>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/pages/events.php"
                                class="nav-link <?php echo $current_page === 'events' ? 'active' : ''; ?>">Events</a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/pages/contact.php"
                                class="nav-link <?php echo $current_page === 'contact' ? 'active' : ''; ?>">Contact</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">