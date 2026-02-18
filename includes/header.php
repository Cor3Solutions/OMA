<?php
require_once __DIR__ . '/../config/database.php';

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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">

    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v18.0"></script>
</head>

<body>
    <header class="site-header" id="siteHeader">
        <div class="container">
            <div class="header-content">

                <!-- Logos -->
                <div style="display:flex; align-items:center; gap:16px; flex-shrink:0;">
                    <a href="<?php echo $basePath; ?>index.php" class="logo">
                        <img src="<?php echo $basePath; ?>assets/images/oma.png"
                             alt="Oriental Muayboran Academy"
                             style="height:60px; width:auto; filter:drop-shadow(0 0 15px white);">
                    </a>
                    <a href="<?php echo $basePath; ?>index.php" class="logo">
                        <img src="<?php echo $basePath; ?>assets/images/skss.png"
                             alt="Sit Kru Sane Siamyout"
                             style="height:60px; width:auto; filter:drop-shadow(0 0 15px white);">
                    </a>
                </div>

                <!-- Hamburger — style.css already shows it on mobile -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <!-- Nav — style.css uses .active to open it -->
                <nav class="main-nav" id="mainNav">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/index.php"
                               class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>">Home</a>
                        </li>

                        <li class="nav-item has-dropdown">
                            <a href="<?php echo SITE_URL; ?>/pages/about.php" class="nav-link">About</a>
                            <ul class="dropdown">
                                <li><a href="<?php echo SITE_URL; ?>/pages/mvc.php">Mission, Vision &amp; Core Values</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/pages/lineage.php">Lineage</a></li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/pages/course.php"
                               class="nav-link <?php echo $current_page === 'course' ? 'active' : ''; ?>">Programs</a>
                        </li>

                        <li class="nav-item has-dropdown">
                            <a href="<?php echo SITE_URL; ?>/pages/membership-benefits.php" class="nav-link">Khan Community</a>
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

    <script>
    (function () {
        var toggle = document.getElementById('mobileMenuToggle');
        var nav    = document.getElementById('mainNav');
        var header = document.getElementById('siteHeader');

        if (!toggle || !nav) return;

        // ── Open/close the nav ─────────────────────────────────────
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = nav.classList.toggle('active');   // .main-nav.active in style.css
            toggle.classList.toggle('active', isOpen);
            toggle.setAttribute('aria-expanded', String(isOpen));
            document.body.style.overflow = isOpen ? 'hidden' : '';
        });

        // ── Dropdowns: tap parent link to expand on mobile ─────────
        document.querySelectorAll('.has-dropdown').forEach(function (item) {
            var parentLink = item.querySelector(':scope > .nav-link');
            parentLink.addEventListener('click', function (e) {
                if (window.innerWidth > 768) return; // desktop uses hover
                e.preventDefault();
                var alreadyOpen = item.classList.contains('active');
                // Close all dropdowns first
                document.querySelectorAll('.has-dropdown.active').forEach(function (el) {
                    el.classList.remove('active');
                });
                // Open this one if it wasn't already open
                if (!alreadyOpen) {
                    item.classList.add('active'); // .has-dropdown.active .dropdown in style.css
                }
            });
        });

        // ── Close nav when a leaf link is tapped ──────────────────
        nav.querySelectorAll('a').forEach(function (link) {
            // Skip parent dropdown links (handled above)
            if (link.closest('.has-dropdown') && link.classList.contains('nav-link')) {
                var parent = link.closest('.has-dropdown');
                // Allow if it's a direct link (no sub-items) — skip if has .dropdown
                if (parent.querySelector('.dropdown')) return;
            }
            link.addEventListener('click', function () {
                nav.classList.remove('active');
                toggle.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        });

        // ── Close on tap outside ───────────────────────────────────
        document.addEventListener('click', function (e) {
            if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                nav.classList.remove('active');
                toggle.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });

        // ── Scroll shadow ──────────────────────────────────────────
        window.addEventListener('scroll', function () {
            header.classList.toggle('scrolled', window.scrollY > 20);
        }, { passive: true });
    })();
    </script>