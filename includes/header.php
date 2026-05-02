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
    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <?php if (isset($extra_head))
        echo $extra_head; ?>

    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v18.0"></script>

    <style>
        /* ══════════════════════════════════════════
       HEADER LOGOS GROUP (oma + skss always visible)
       ══════════════════════════════════════════ */
        .header-logos {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-shrink: 0;
        }

        .header-logos a {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .header-logos img {
            height: 70px;
            width: 70px;
            object-fit: contain;
            filter: drop-shadow(0 0 12px rgba(255, 255, 255, 0.8));
            transition: transform 0.2s;
        }

        .header-logos img:hover {
            transform: scale(1.07);
        }

        /* ══════════════════════════════════════════
       THFP — MOBILE: shown inside .header-logos
               DESKTOP: hidden from header, shown in nav
       ══════════════════════════════════════════ */
        /* Mobile default: thfp visible in header, nav tab hidden */
        .header-logo-thfp-mobile {
            display: flex !important;
        }

        .nav-thfp-tab {
            display: none !important;
        }

        /* Desktop: thfp hidden from header, visible as nav tab */
        @media (min-width: 769px) {

            .header-logo-thfp-mobile,
            .header-logo-thfp-mobile img,
            a.header-logo-thfp-mobile {
                display: none !important;
                visibility: hidden !important;
                width: 0 !important;
                height: 0 !important;
                overflow: hidden !important;
                padding: 0 !important;
                margin: 0 !important;
                gap: 0 !important;
            }

            .nav-thfp-tab {
                display: flex !important;
            }
        }

        /* ══════════════════════════════════════════
       THFP NAV TAB — DESKTOP STYLE
       Tapology-inspired: logo + label pill at end of nav
       ══════════════════════════════════════════ */
        .nav-thfp-tab {
            align-items: center;
            margin-left: 8px;
            /* small gap from last nav item */
        }

        .nav-thfp-link {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 6px 14px 6px 8px;
            border-radius: 999px;
            border: 1.5px solid rgba(212, 175, 55, 0.35);
            background: rgba(212, 175, 55, 0.06);
            text-decoration: none;
            transition: border-color 0.25s, background 0.25s, transform 0.2s;
            white-space: nowrap;
        }

        .nav-thfp-link:hover {
            border-color: rgba(212, 175, 55, 0.75);
            background: rgba(212, 175, 55, 0.14);
            transform: translateY(-1px);
        }

        .nav-thfp-logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border-radius: 50%;
            filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.5));
            flex-shrink: 0;
        }

        .nav-thfp-label {
            font-family: 'Cinzel', serif;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: rgba(212, 175, 55, 0.9);
            line-height: 1;
        }

        .sub-text {
            display: block;
            font-size: 1.1rem;
            /* Bigger text */
            text-transform: uppercase;
        }

        .white-text {
            color: #ffffff;
            /* Sets Athletes and Warriors to white */
        }

        .nav-thfp-label small {
            display: block;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1rem;
            font-weight: 400;
            letter-spacing: 1px;
            color: rgb(255, 255, 255);
            margin-top: 2px;
            text-transform: none;
        }

        /* ══════════════════════════════════════════
       MOBILE SIZES
       ══════════════════════════════════════════ */
        @media (max-width: 768px) {
            .header-logos {
                gap: 10px;
            }

            .header-logos img {
                height: 60px;
                width: 60px;
            }
        }

        @media (max-width: 400px) {
            .header-logos img {
                height: 52px;
                width: 52px;
            }

            .header-logos {
                gap: 8px;
            }
        }
    </style>
</head>

<body>
    <header class="site-header" id="siteHeader">
        <div class="container">
            <div class="header-content">

                <!-- ── Header logos ── -->
                <div class="header-logos">
                    <!-- OMA — always visible -->
                    <a href="<?php echo $basePath; ?>index.php" title="Oriental Muayboran Academy">
                        <img src="<?php echo $basePath; ?>assets/images/oma.png" alt="Oriental Muayboran Academy">
                    </a>

                    <!-- SKSS — always visible -->
                    <a href="<?php echo $basePath; ?>index.php" title="Sit Kru Sane Siamyout">
                        <img src="<?php echo $basePath; ?>assets/images/skss.png" alt="Sit Kru Sane Siamyout">
                    </a>

                    <!-- THFP — mobile only (hidden on desktop via CSS) -->
                    <a href="<?php echo SITE_URL; ?>/pages/thfp.php" title="THFP" class="header-logo-thfp-mobile">
                        <img src="<?php echo $basePath; ?>assets/images/thfp.png" alt="THFP">
                    </a>
                </div>

                <!-- Hamburger -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <!-- Nav -->
                <nav class="main-nav" id="mainNav">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/index.php"
                                class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>">Home</a>
                        </li>

                        <li class="nav-item has-dropdown">
                            <a href="<?php echo SITE_URL; ?>/pages/about.php" class="nav-link">About</a>
                            <ul class="dropdown">
                                <li><a href="<?php echo SITE_URL; ?>/pages/mvc.php">Mission, Vision &amp; Core
                                        Values</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/pages/lineage.php">Lineage</a></li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/pages/course.php"
                                class="nav-link <?php echo $current_page === 'course' ? 'active' : ''; ?>">Programs</a>
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

                        <!-- ── THFP nav tab — desktop only ── -->
                        <li class="nav-item nav-thfp-tab">
                            <a href="<?php echo SITE_URL; ?>/pages/thfp.php" class="nav-thfp-link" title="THFP">
                                <img src="<?php echo $basePath; ?>assets/images/thfp.png" alt="THFP"
                                    class="nav-thfp-logo">
                                <span class="nav-thfp-label">
                                    <span class="brand-title">TRIBAL HUNTERS</span>
                                    <small class="sub-text">Fight Promotion</small>
                                    <small class="sub-text white-text">Athletes and Warriors</small>
                                </span>
                            </a>
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
                var nav = document.getElementById('mainNav');
                var header = document.getElementById('siteHeader');

                if (!toggle || !nav) return;

                toggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var isOpen = nav.classList.toggle('active');
                    toggle.classList.toggle('active', isOpen);
                    toggle.setAttribute('aria-expanded', String(isOpen));
                    document.body.style.overflow = isOpen ? 'hidden' : '';
                });

                document.querySelectorAll('.has-dropdown').forEach(function (item) {
                    var parentLink = item.querySelector(':scope > .nav-link');
                    parentLink.addEventListener('click', function (e) {
                        if (window.innerWidth > 768) return;
                        e.preventDefault();
                        var alreadyOpen = item.classList.contains('active');
                        document.querySelectorAll('.has-dropdown.active').forEach(function (el) {
                            el.classList.remove('active');
                        });
                        if (!alreadyOpen) item.classList.add('active');
                    });
                });

                nav.querySelectorAll('a').forEach(function (link) {
                    if (link.closest('.has-dropdown') && link.classList.contains('nav-link')) {
                        var parent = link.closest('.has-dropdown');
                        if (parent.querySelector('.dropdown')) return;
                    }
                    link.addEventListener('click', function () {
                        nav.classList.remove('active');
                        toggle.classList.remove('active');
                        toggle.setAttribute('aria-expanded', 'false');
                        document.body.style.overflow = '';
                    });
                });

                document.addEventListener('click', function (e) {
                    if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                        nav.classList.remove('active');
                        toggle.classList.remove('active');
                        toggle.setAttribute('aria-expanded', 'false');
                        document.body.style.overflow = '';
                    }
                });

                window.addEventListener('scroll', function () {
                    header.classList.toggle('scrolled', window.scrollY > 20);
                }, { passive: true });
            })();
        </script>