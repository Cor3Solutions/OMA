<?php
$page_title = "Home";
$extra_head = '<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">';
include 'includes/header.php';

// Get active affiliates from database
$conn = getDbConnection();
$affiliates = $conn->query("SELECT * FROM affiliates WHERE status = 'active' ORDER BY display_order ASC");
?>

<!-- ============================================================
     ENHANCED STYLES
     ============================================================ -->
<style>
/* ============================================================
   ROOT VARIABLES
   ============================================================ */
:root {
    --gold:        #D4AF37;
    --gold-light:  #F0D060;
    --gold-dark:   #A07C10;
    --red:         #ca1313;
    --black:       #0a0a0a;
    --dark:        #111;
    --mid:         #1a1a1a;
    --white:       #fff;
    --muted:       rgba(255,255,255,0.65);
    --font-display: 'Cinzel', serif;
    --font-body:    'Cormorant Garamond', serif;
    --font-ui:      'Rajdhani', sans-serif;
}

/* ============================================================
   HERO SECTION
   ============================================================ */
.hero-section {
    position: relative;
    overflow: hidden;
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: var(--black);
}

/* Background image + layered overlays */
.hero-background {
    position: absolute;
    inset: 0;
    z-index: 0;
}
.hero-background img {
    width: 100%; height: 100%;
    object-fit: cover;
    opacity: 0.38;
    transform: scale(1.03);
    transition: transform 12s ease;
}
.hero-section:hover .hero-background img {
    transform: scale(1.0);
}
.hero-overlay {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(to right,  rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.2) 40%, rgba(0,0,0,0.2) 60%, rgba(0,0,0,0.85) 100%),
        linear-gradient(to bottom, rgba(0,0,0,0.7)  0%, rgba(0,0,0,0.1) 50%, rgba(0,0,0,0.8) 100%);
}

/* Decorative corner ornaments */
.hero-corner {
    position: absolute;
    width: 80px; height: 80px;
    z-index: 3;
    opacity: 0.6;
}
.hero-corner--tl { top: 24px; left: 24px; border-top: 2px solid var(--gold); border-left: 2px solid var(--gold); }
.hero-corner--tr { top: 24px; right: 24px; border-top: 2px solid var(--gold); border-right: 2px solid var(--gold); }
.hero-corner--bl { bottom: 24px; left: 24px; border-bottom: 2px solid var(--gold); border-left: 2px solid var(--gold); }
.hero-corner--br { bottom: 24px; right: 24px; border-bottom: 2px solid var(--gold); border-right: 2px solid var(--gold); }

/* Hero content */
.hero-content {
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 80px 0;
}

.hero-inner {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 56px;
    width: 92%;
    max-width: 1400px;
    margin: 0 auto;
}

/* ---- Flag Columns ---- */
.hero-flag-col {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    animation: flagFadeIn 1.2s ease forwards;
    opacity: 0;
}
#flag-ph { animation-delay: 0.2s; }
#flag-th  { animation-delay: 0.4s; }

@keyframes flagFadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Outer glow frame */
.hero-flag-outer {
    position: relative;
    padding: 4px;
    background: linear-gradient(145deg, var(--gold-light), var(--gold-dark), var(--gold));
    border-radius: 4px;
    box-shadow:
        0 0 30px rgba(212, 175, 55, 0.45),
        0 0 60px rgba(212, 175, 55, 0.15),
        inset 0 0 10px rgba(0,0,0,0.3);
}

/* Inner frame — ensures the image is fully visible */
.hero-flag-frame {
    position: relative;
    width: 130px;
    height: 195px;          /* 2:3 portrait — flags won't be cut */
    overflow: hidden;
    border-radius: 2px;
    background: #000;
    display: block;
}
.hero-flag-frame img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;      /* fills the box without cutting the flag design */
    object-position: center;
    filter: brightness(1.05) saturate(1.15);
    transition: transform 0.5s ease;
}
.hero-flag-outer:hover .hero-flag-frame img {
    transform: scale(1.06);
}

/* Country label */
.hero-flag-label {
    font-family: var(--font-ui);
    color: var(--gold);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 4px;
    text-transform: uppercase;
    text-shadow: 0 1px 8px rgba(0,0,0,0.8);
}

/* Small crossed swords between label & country tag */
.flag-separator {
    width: 40px;
    height: 1px;
    background: linear-gradient(to right, transparent, var(--gold), transparent);
    margin: 2px 0;
}

/* ---- Center Text Column ---- */
.hero-text-col {
    text-align: center;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    animation: textFadeIn 1.0s ease 0.6s forwards;
    opacity: 0;
}
@keyframes textFadeIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.hero-eyebrow {
    display: inline-block;
    font-family: var(--font-ui);
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 6px;
    text-transform: uppercase;
    color: var(--gold);
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.3);
    padding: 6px 18px;
    border-radius: 2px;
    margin-bottom: 22px;
}

.hero-title {
    font-family: var(--font-display);
    margin: 0;
    font-size: 4.2rem;
    font-weight: 900;
    color: var(--white);
    text-transform: uppercase;
    line-height: 1.05;
    letter-spacing: 2px;
    text-shadow: 0 4px 30px rgba(0,0,0,0.9);
}
.hero-title .accent {
    color: var(--gold);
    display: block;
}

/* Ornamental divider */
.hero-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    margin: 28px 0;
    width: 100%;
}
.hero-divider-line {
    flex: 1;
    max-width: 120px;
    height: 1px;
    background: linear-gradient(to var(--dir, right), var(--gold), transparent);
}
.hero-divider-line.left  { --dir: left; }
.hero-divider-line.right { --dir: right; }
.hero-divider-diamond {
    width: 8px; height: 8px;
    background: var(--gold);
    transform: rotate(45deg);
    flex-shrink: 0;
}
.hero-divider-text {
    font-family: var(--font-body);
    color: rgba(255,255,255,0.75);
    font-size: 0.95rem;
    font-style: italic;
    letter-spacing: 1px;
    white-space: nowrap;
}

.hero-desc {
    font-family: var(--font-body);
    margin: 0 auto 40px;
    font-size: 1.25rem;
    color: rgba(255,255,255,0.8);
    max-width: 580px;
    line-height: 1.75;
    font-weight: 300;
}

.hero-buttons {
    display: flex;
    justify-content: center;
    gap: 18px;
    flex-wrap: wrap;
}

.hero-btn-primary {
    font-family: var(--font-ui);
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    background: var(--gold);
    color: #000;
    padding: 16px 40px;
    text-decoration: none;
    border-radius: 2px;
    transition: background 0.25s, box-shadow 0.25s, transform 0.2s;
    box-shadow: 0 4px 20px rgba(212,175,55,0.35);
    white-space: nowrap;
}
.hero-btn-primary:hover {
    background: var(--gold-light);
    box-shadow: 0 6px 30px rgba(212,175,55,0.55);
    transform: translateY(-2px);
}

.hero-btn-secondary {
    font-family: var(--font-ui);
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    border: 1.5px solid rgba(255,255,255,0.6);
    color: var(--white);
    padding: 16px 40px;
    text-decoration: none;
    border-radius: 2px;
    transition: border-color 0.25s, color 0.25s, transform 0.2s;
    white-space: nowrap;
}
.hero-btn-secondary:hover {
    border-color: var(--gold);
    color: var(--gold);
    transform: translateY(-2px);
}

/* Scroll indicator */
.hero-scroll {
    position: absolute;
    bottom: 32px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    opacity: 0.5;
    animation: scrollBob 2.5s ease-in-out infinite;
}
.hero-scroll span {
    font-family: var(--font-ui);
    font-size: 0.65rem;
    letter-spacing: 3px;
    color: var(--gold);
    text-transform: uppercase;
}
.hero-scroll-line {
    width: 1px;
    height: 36px;
    background: linear-gradient(to bottom, var(--gold), transparent);
}
@keyframes scrollBob {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50%       { transform: translateX(-50%) translateY(8px); }
}



/* ============================================================
   TRAINING PROGRAMS SECTION
   ============================================================ */
.section {
    padding: 90px 0;
    background: var(--dark);
}
.section.bg-light {
    background: var(--mid);
}

.section-header {
    text-align: center;
    margin-bottom: 3.5rem;
}
.section-subtitle {
    font-family: var(--font-ui);
    font-size: 0.75rem;
    letter-spacing: 5px;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 10px;
}
.section-title {
    font-family: var(--font-display);
    font-size: 2.6rem;
    font-weight: 700;
    color: var(--white);
    letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 0 16px;
}
.section-title.white-text { color: var(--white); }
.section-description {
    font-family: var(--font-body);
    color: var(--muted);
    font-size: 1.1rem;
    max-width: 560px;
    margin: 0 auto;
    line-height: 1.7;
}

.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 28px;
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}

.card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(212,175,55,0.15);
    border-radius: 4px;
    padding: 2.5rem;
    transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
    position: relative;
    overflow: hidden;
}
.card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 3px; height: 0;
    background: var(--gold);
    transition: height 0.4s ease;
}
.card:hover { border-color: rgba(212,175,55,0.4); box-shadow: 0 10px 40px rgba(0,0,0,0.4); transform: translateY(-4px); }
.card:hover::before { height: 100%; }

.card-title {
    font-family: var(--font-display);
    font-size: 1.2rem;
    color: var(--gold);
    letter-spacing: 1px;
    margin-bottom: 1rem;
    text-transform: uppercase;
}
.card-description {
    font-family: var(--font-body);
    color: var(--muted);
    font-size: 1rem;
    line-height: 1.7;
}

.btn {
    font-family: var(--font-ui);
    font-weight: 600;
    letter-spacing: 2px;
    font-size: 0.8rem;
    text-transform: uppercase;
    text-decoration: none;
    padding: 12px 28px;
    border-radius: 2px;
    display: inline-block;
    transition: all 0.25s;
    cursor: pointer;
    border: none;
}
.btn-outline {
    border: 1.5px solid rgba(212,175,55,0.5);
    color: var(--gold);
    background: transparent;
}
.btn-outline:hover { background: var(--gold); color: #000; }

.btn-red-glow {
    background: var(--red);
    color: var(--white);
    box-shadow: 0 4px 20px rgba(202,19,19,0.35);
}
.btn-red-glow:hover { background: #e02020; box-shadow: 0 6px 30px rgba(202,19,19,0.55); transform: translateY(-2px); }

.btn-outline-yellow {
    border: 1.5px solid var(--gold);
    color: var(--gold);
    background: transparent;
}
.btn-outline-yellow:hover { background: var(--gold); color: #000; }

/* ============================================================
   SOCIAL SECTION
   ============================================================ */
.social-inner {
    display: flex;
    flex-wrap: wrap;
    gap: 3rem;
    align-items: flex-start;
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}
.social-text-col { flex: 1; min-width: 300px; }
.social-fb-col   { flex: 1.2; min-width: 320px; }

.fb-card-frame {
    width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}
.fb-responsive-wrap {
    border-radius: 12px;
    overflow: hidden;
    background: #f0f2f5;
    width: 100%;
}
.fb-responsive-wrap iframe,
.fb-responsive-wrap span { max-width: 100% !important; }

/* ============================================================
   CONTACT CTA
   ============================================================ */
.contact-cta-bg {
    background: linear-gradient(135deg, #0d0d0d 0%, #1a0a0a 50%, #0d0d0d 100%);
    border-top: 1px solid rgba(202,19,19,0.2);
}
.cta-actions { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: 2rem; }
.thai-word { color: var(--gold); font-family: var(--font-display); }

 
/* ============================================================
   CONTAINER
   ============================================================ */
.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}

/* ============================================================
   RESPONSIVE — TABLET (≤ 900px)
   ============================================================ */
@media (max-width: 900px) {
    .hero-inner  { gap: 32px; }
    .hero-title  { font-size: 3rem; }
    .hero-flag-frame { width: 100px; height: 150px; }
    .hero-desc   { font-size: 1.1rem; }
    .section-title { font-size: 2rem; }
}

/* ============================================================
   RESPONSIVE — MOBILE (≤ 640px)
   ============================================================ */
@media (max-width: 640px) {
    .hero-section  { min-height: auto; }
    .hero-content  { padding: 48px 0 72px; }

    .hero-inner {
        flex-direction: column;
        align-items: center;
        gap: 28px;
        width: 92%;
    }

    /* Flags sit in a row above the text on mobile */
    .hero-flags-row {
        display: flex;
        flex-direction: row;
        justify-content: center;
        gap: 32px;
        width: 100%;
    }

    .hero-flag-frame { width: 72px; height: 108px; }
    .hero-flag-outer { padding: 3px; }
    .hero-flag-label { font-size: 0.68rem; letter-spacing: 2px; }

    .hero-eyebrow  { font-size: 0.65rem; letter-spacing: 4px; padding: 5px 14px; }
    .hero-title    { font-size: 1.85rem; line-height: 1.1; }
    .hero-desc     { font-size: 0.98rem; margin-bottom: 28px; }

    .hero-btn-primary,
    .hero-btn-secondary {
        padding: 14px 24px;
        font-size: 0.8rem;
        width: 100%;
        text-align: center;
    }
    .hero-buttons {
        flex-direction: column;
        gap: 12px;
        width: 100%;
        padding: 0 16px;
        box-sizing: border-box;
    }

    .hero-corner { width: 48px; height: 48px; }

    .section-title { font-size: 1.7rem; }
    .social-text-col, .social-fb-col { min-width: unset; width: 100%; }
    .cta-actions { flex-direction: column; align-items: center; }
}
</style>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="hero-section">
    <div class="hero-background">
        <img src="assets/images/cover1.png" alt="Muayboran Training">
        <div class="hero-overlay"></div>
    </div>

    <!-- Decorative corners -->
    <div class="hero-corner hero-corner--tl"></div>
    <div class="hero-corner hero-corner--tr"></div>
    <div class="hero-corner hero-corner--bl"></div>
    <div class="hero-corner hero-corner--br"></div>

    <div class="hero-content">
        <div class="hero-inner">

            <!-- Philippine Flag -->
            <div class="hero-flag-col" id="flag-ph">
                <div class="hero-flag-outer">
                    <div class="hero-flag-frame">
                        <img src="assets/images/flag-ph.png" alt="Philippine Flag">
                    </div>
                </div>
                <div class="flag-separator"></div>
                <div class="hero-flag-label">Pilipinas</div>
            </div>

            <!-- Center Text -->
            <div class="hero-text-col">
                <span class="hero-eyebrow">Traditional Martial Arts</span>

                <h1 class="hero-title">
                    Oriental
                    <span class="accent">Muayboran</span>
                    Academy
                </h1>

                <div class="hero-divider">
                    <div class="hero-divider-line left"></div>
                    <div class="hero-divider-diamond"></div>
                    <div class="hero-divider-text">Sit Kru Sane Siamyout</div>
                    <div class="hero-divider-diamond"></div>
                    <div class="hero-divider-line right"></div>
                </div>

                <p class="hero-desc">
                    An embodiment of martial tradition and discipline. Preserving the ancient Thai arts under the lineage of Teacher Sane.
                </p>

                <div class="hero-buttons">
                    <a href="pages/membership-benefits.php" class="hero-btn-primary">Become a Member</a>
                    <a href="pages/about.php" class="hero-btn-secondary">Learn More</a>
                </div>
            </div>

            <!-- Thai Flag -->
            <div class="hero-flag-col" id="flag-th">
                <div class="hero-flag-outer">
                    <div class="hero-flag-frame">
                        <img src="assets/images/flag-thai.png" alt="Thai Flag">
                    </div>
                </div>
                <div class="flag-separator"></div>
                <div class="hero-flag-label">Thailand</div>
            </div>

        </div><!-- /.hero-inner -->
    </div><!-- /.hero-content -->

    <!-- Scroll hint -->
    <div class="hero-scroll">
        <span>Scroll</span>
        <div class="hero-scroll-line"></div>
    </div>
</section>

<!-- Affiliates Section - Dynamic from Database -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <br>
        <h2 class="fw-bold text-dark mb-4">Our Affiliates</h2>
        <p>Proudly partnered with industry leaders worldwide</p>

        <div class="marquee">
            <div class="marquee-content">
                <?php
                $count = 0;
                while ($affiliate = $affiliates->fetch_assoc()):
                    $circle_class = ($count % 2 == 0) ? 'circle-red' : 'circle-yellow';
                    $count++;
                    ?>
                    <a href="<?php echo htmlspecialchars($affiliate['website_url'] ?: $affiliate['facebook_url'] ?: '#'); ?>"
                        class="platform-item <?php echo $circle_class; ?>" target="_blank">
                        <?php if (!empty($affiliate['logo_path'])): ?>
                            <img src="<?php echo htmlspecialchars($affiliate['logo_path']); ?>"
                                alt="<?php echo htmlspecialchars($affiliate['name']); ?>">
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:bold;">
                                <?php echo strtoupper(substr($affiliate['name'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                        <div class="info-overlay">
                            <span><?php echo htmlspecialchars($affiliate['name']); ?></span>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     TRAINING PROGRAMS SECTION
     ============================================================ -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Academic Progression</p>
            <h2 class="section-title">Training Programs</h2>
            <p class="section-description">
                From Khan 1 to Mastership, our programs are designed to transform
                practitioners into guardians of the art.
            </p>
        </div>

        <div class="card-grid">
            <div class="card">
                <h3 class="card-title">Nakmuay (Student)</h3>
                <p class="card-description">
                    Levels Khan 1–10. Focuses on the "Eight Limbs," footwork,
                    traditional forms (Ram Muay), and foundational defense.
                </p>
                <a href="pages/course.php" class="btn btn-outline" style="margin-top: 1.5rem;">View Syllabus</a>
            </div>

            <div class="card">
                <h3 class="card-title">Kru (Instructor)</h3>
                <p class="card-description">
                    Levels Khan 11–16. Advanced mastership training for those
                    called to teach and preserve the Sit Kru Sane lineage.
                </p>
                <a href="pages/course.php" class="btn btn-outline" style="margin-top: 1.5rem;">Instructor Path</a>
            </div>

            <div class="card">
                <h3 class="card-title">Krabi Krabong</h3>
                <p class="card-description">
                    The specialized study of Thai weaponry, an essential branch
                    of the traditional OMA curriculum.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SOCIAL SECTION
     ============================================================ -->
<section class="section bg-light">
    <div class="container">
        <div class="section-header" style="margin-bottom: 4rem;">
            <p class="section-subtitle">Join Our Community</p>
            <h2 class="section-title">Stay Connected</h2>
            <div style="width:50px;height:2px;background:var(--gold);margin:1.5rem auto 0;"></div>
        </div>

        <div class="social-inner">

            <div class="social-text-col">
                <h3 style="font-family:var(--font-display);font-size:1.6rem;color:var(--gold);margin-bottom:1.5rem;letter-spacing:1px;">Digital Overview</h3>
                <p style="font-family:var(--font-body);color:var(--muted);line-height:1.8;margin-bottom:2rem;font-size:1.1rem;">
                    Follow our daily training, seminar highlights, and technical breakdowns.
                    Be the first to know about upcoming Khan graduations and international workshops.
                </p>

                <div style="display:grid;gap:1rem;">
                    <div style="background:rgba(255,255,255,0.03);padding:1.5rem;border-radius:6px;border-left:3px solid var(--red);">
                        <strong style="display:block;color:var(--white);font-family:var(--font-ui);letter-spacing:1px;margin-bottom:4px;">Live Updates</strong>
                        <span style="font-family:var(--font-body);font-size:0.95rem;color:var(--muted);">Real-time event coverage and academy news.</span>
                    </div>
                    <div style="background:rgba(255,255,255,0.03);padding:1.5rem;border-radius:6px;border-left:3px solid var(--gold);">
                        <strong style="display:block;color:var(--white);font-family:var(--font-ui);letter-spacing:1px;margin-bottom:4px;">Technique Clips</strong>
                        <span style="font-family:var(--font-body);font-size:0.95rem;color:var(--muted);">Slow-motion breakdowns of Boran techniques.</span>
                    </div>
                </div>
            </div>

            <div class="social-fb-col">
                <div class="fb-card-frame" style="background:#fff;padding:10px;border-radius:16px;box-shadow:0 20px 50px rgba(0,0,0,0.5),0 0 20px rgba(212,175,55,0.1);position:relative;overflow:hidden;width:100%;box-sizing:border-box;">
                    <div style="position:absolute;top:-14px;right:20px;background:#1877F2;color:#fff;padding:7px 14px;border-radius:50px;font-family:var(--font-ui);font-size:0.75rem;font-weight:700;z-index:10;box-shadow:0 4px 10px rgba(0,0,0,0.3);">
                        OFFICIAL PAGE
                    </div>
                    <div id="fb-page-wrapper" class="fb-responsive-wrap">
                        <div class="fb-page"
                            data-href="https://www.facebook.com/OrientalMuayboranAcademy"
                            data-tabs="timeline"
                            data-height="600"
                            data-small-header="false"
                            data-adapt-container-width="true"
                            data-hide-cover="false"
                            data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/OrientalMuayboranAcademy" class="fb-xfbml-parse-ignore">
                                <a href="https://www.facebook.com/OrientalMuayboranAcademy">Oriental Muayboran Academy</a>
                            </blockquote>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     CONTACT CTA SECTION
     ============================================================ -->
<section class="section contact-cta-bg">
    <div class="container">
        <div class="section-header text-center">
            <p class="section-subtitle">Get In Touch</p>
            <h4 class="section-title white-text">
                <span class="thai-word">คำถาม?</span> Questions?
            </h4>
            <p class="section-description">
                We're here to help you begin or continue your Muayboran journey.
                Reach out to learn more about our programs.
            </p>
        </div>

        <div class="cta-actions">
            <a href="pages/contact.php" class="btn btn-red-glow">Contact Us</a>
            <a href="tel:+639605667175" class="btn btn-outline-yellow">Call Now</a>
        </div>
    </div>
</section>

<!-- ============================================================
     SCRIPTS
     ============================================================ -->
<script>
// Rearrange flags on mobile: group both into a flex row above the text
function rearrangeFlags() {
    var inner  = document.querySelector('.hero-inner');
    var flagPh = document.getElementById('flag-ph');
    var flagTh = document.getElementById('flag-th');
    var textCol = document.querySelector('.hero-text-col');
    var flagsRow = document.getElementById('hero-flags-row');

    if (window.innerWidth <= 640) {
        if (!flagsRow) {
            flagsRow = document.createElement('div');
            flagsRow.id = 'hero-flags-row';
            flagsRow.className = 'hero-flags-row';
            flagsRow.appendChild(flagPh);
            flagsRow.appendChild(flagTh);
            inner.insertBefore(flagsRow, textCol);
        }
    } else {
        if (flagsRow) {
            inner.insertBefore(flagPh, flagsRow);
            inner.appendChild(flagTh);
            flagsRow.remove();
        }
    }
}

document.addEventListener('DOMContentLoaded', rearrangeFlags);
window.addEventListener('resize', rearrangeFlags);

// Re-render Facebook embed at proper container width
setTimeout(function () {
    if (window.FB && window.FB.XFBML) {
        window.FB.XFBML.parse(document.getElementById('fb-page-wrapper'));
    }
}, 800);
</script>

<?php include 'includes/footer.php'; ?>