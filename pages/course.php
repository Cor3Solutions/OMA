<?php
$page_title = "Courses";
$extra_head = '<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">';
include '../includes/header.php';

$conn = getDbConnection();
$user_khan_level = 0;

if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $member_query = $conn->query("SELECT current_khan_level FROM khan_members WHERE user_id = $user_id");
    if ($member = $member_query->fetch_assoc()) {
        $user_khan_level = $member['current_khan_level'];
    }
}

if (isLoggedIn() && $user_khan_level > 0) {
    $courses = $conn->query("
        SELECT * FROM course_materials
        WHERE status = 'published'
        AND khan_level_min <= $user_khan_level
        ORDER BY category, display_order ASC
    ");
} else {
    $courses = $conn->query("
        SELECT * FROM course_materials
        WHERE status = 'published'
        AND is_public = 1
        ORDER BY category, display_order ASC
    ");
}
?>

<style>
/* ============================================================
   DESIGN TOKENS — mirrors index & about
   ============================================================ */
:root {
    --gold:         #D4AF37;
    --gold-light:   #F0D060;
    --gold-dark:    #A07C10;
    --red:          #ca1313;
    --black:        #0a0a0a;
    --dark:         #111;
    --mid:          #1a1a1a;
    --white:        #fff;
    --muted:        rgba(255,255,255,0.65);
    --font-display: 'Cinzel', serif;
    --font-body:    'Cormorant Garamond', serif;
    --font-ui:      'Rajdhani', sans-serif;
}

body { background: var(--dark); color: var(--white); }

.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}

/* ---- Shared header ---- */
.section-subtitle {
    font-family: var(--font-ui);
    font-size: 0.75rem;
    letter-spacing: 5px;
    text-transform: uppercase;
    color: var(--gold);
    display: block;
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
    line-height: 1.1;
}
.section-header { text-align: center; margin-bottom: 4rem; }
.gold-rule {
    width: 50px; height: 2px;
    background: var(--gold);
    margin: 1.5rem auto 0;
}

/* ============================================================
   HERO
   ============================================================ */
.courses-hero {
    position: relative;
    min-height: 65vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--black);
}
.courses-hero-bg {
    position: absolute;
    inset: 0;
}
.courses-hero-bg img {
    width: 100%; height: 100%;
    object-fit: cover;
    opacity: 0.35;
    transform: scale(1.04);
    transition: transform 14s ease;
}
.courses-hero:hover .courses-hero-bg img { transform: scale(1.0); }
.courses-hero-overlay {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(to right,  rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.9) 100%),
        linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.1) 50%, rgba(0,0,0,0.85) 100%);
}

.hero-corner {
    position: absolute;
    width: 72px; height: 72px;
    z-index: 3; opacity: 0.55;
}
.hero-corner--tl { top: 24px; left: 24px;   border-top: 2px solid var(--gold); border-left: 2px solid var(--gold); }
.hero-corner--tr { top: 24px; right: 24px;  border-top: 2px solid var(--gold); border-right: 2px solid var(--gold); }
.hero-corner--bl { bottom: 24px; left: 24px; border-bottom: 2px solid var(--gold); border-left: 2px solid var(--gold); }
.hero-corner--br { bottom: 24px; right: 24px; border-bottom: 2px solid var(--gold); border-right: 2px solid var(--gold); }

.courses-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    padding: 80px 24px;
    opacity: 0;
    animation: heroFade 1s ease 0.1s forwards;
}
@keyframes heroFade { to { opacity: 1; } }

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

.courses-hero-content h1 {
    font-family: var(--font-display);
    font-size: 3.8rem;
    font-weight: 900;
    color: var(--white);
    text-transform: uppercase;
    letter-spacing: 3px;
    line-height: 1.05;
    margin: 0 0 24px;
    text-shadow: 0 4px 30px rgba(0,0,0,0.9);
}
.courses-hero-content h1 span { color: var(--gold); }

.hero-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    margin: 0 auto 24px;
    max-width: 440px;
}
.hero-divider-line { flex: 1; height: 1px; }
.hero-divider-line.l { background: linear-gradient(to left,  var(--gold), transparent); }
.hero-divider-line.r { background: linear-gradient(to right, var(--gold), transparent); }
.hero-divider-diamond { width: 7px; height: 7px; background: var(--gold); transform: rotate(45deg); flex-shrink: 0; }

.courses-hero-content p {
    font-family: var(--font-body);
    font-size: 1.25rem;
    color: rgba(255,255,255,0.78);
    max-width: 520px;
    margin: 0 auto;
    line-height: 1.75;
    font-weight: 300;
    font-style: italic;
}

.hero-scroll {
    position: absolute;
    bottom: 28px; left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    opacity: 0.45;
    animation: scrollBob 2.5s ease-in-out infinite;
}
.hero-scroll span { font-family: var(--font-ui); font-size: 0.6rem; letter-spacing: 3px; color: var(--gold); text-transform: uppercase; }
.hero-scroll-line { width: 1px; height: 32px; background: linear-gradient(to bottom, var(--gold), transparent); }
@keyframes scrollBob {
    0%,100% { transform: translateX(-50%) translateY(0); }
    50%      { transform: translateX(-50%) translateY(8px); }
}

/* ============================================================
   CURRICULUM SECTION
   ============================================================ */
.curriculum-section {
    background: var(--dark);
    padding: 90px 0;
}

.curriculum-inner {
    max-width: 960px;
    margin: 0 auto;
}

/* Intro paragraph */
.curriculum-intro {
    font-family: var(--font-body);
    font-size: 1.2rem;
    color: var(--muted);
    line-height: 1.8;
    text-align: center;
    max-width: 680px;
    margin: 0 auto 3.5rem;
}

/* Two-column skill list */
.curriculum-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    border: 1px solid rgba(212,175,55,0.12);
    border-radius: 4px;
    overflow: hidden;
}

.curriculum-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 1.4rem 2rem;
    border-bottom: 1px solid rgba(212,175,55,0.08);
    transition: background 0.25s;
    font-family: var(--font-body);
    font-size: 1.1rem;
    color: var(--muted);
}
.curriculum-item:nth-child(odd)  { border-right: 1px solid rgba(212,175,55,0.08); }
.curriculum-item:last-child,
.curriculum-item:nth-last-child(2):nth-child(odd) { border-bottom: none; }
.curriculum-item:hover { background: rgba(212,175,55,0.04); color: var(--white); }

.curriculum-item-dot {
    width: 6px; height: 6px;
    background: var(--gold);
    transform: rotate(45deg);
    flex-shrink: 0;
    opacity: 0.7;
    transition: opacity 0.25s, transform 0.25s;
}
.curriculum-item:hover .curriculum-item-dot { opacity: 1; transform: rotate(45deg) scale(1.4); }

/* Divider between curriculum & specialized */
.section-divider {
    width: 100%;
    height: 1px;
    background: linear-gradient(to right, transparent, rgba(212,175,55,0.25), transparent);
    margin: 70px 0;
}

/* ============================================================
   SPECIALIZED COURSES
   ============================================================ */
.specialized-section {
    background: var(--mid);
    padding: 90px 0;
}

.spec-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 28px;
    max-width: 960px;
    margin: 0 auto;
}

.spec-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(212,175,55,0.12);
    border-radius: 4px;
    overflow: hidden;
    position: relative;
    transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
}
.spec-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 3px; height: 0;
    background: var(--gold);
    transition: height 0.4s ease;
}
.spec-card:hover {
    border-color: rgba(212,175,55,0.38);
    box-shadow: 0 16px 50px rgba(0,0,0,0.5), 0 0 20px rgba(212,175,55,0.06);
    transform: translateY(-5px);
}
.spec-card:hover::before { height: 100%; }

.spec-card-header {
    padding: 2rem 2.2rem 1.4rem;
    border-bottom: 1px solid rgba(212,175,55,0.08);
}
.spec-badge {
    display: inline-block;
    font-family: var(--font-ui);
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 2px;
    margin-bottom: 12px;
}
.spec-badge--blue { background: rgba(16,59,102,0.8); color: #7ab3e0; border: 1px solid rgba(122,179,224,0.25); }
.spec-badge--red  { background: rgba(139,0,0,0.4);   color: #e07a7a; border: 1px solid rgba(224,122,122,0.25); }

.spec-card-header h3 {
    font-family: var(--font-display);
    font-size: 1.2rem;
    color: var(--white);
    letter-spacing: 1px;
    text-transform: uppercase;
    margin: 0;
}

.spec-card-body {
    padding: 1.6rem 2.2rem 2rem;
}
.spec-card-body p {
    font-family: var(--font-body);
    color: var(--muted);
    font-size: 1.05rem;
    line-height: 1.75;
    margin: 0;
}

/* ============================================================
   MATERIALS SECTION (logged-in members)
   ============================================================ */
.materials-section {
    background: var(--dark);
    padding: 90px 0;
}

.khan-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-family: var(--font-ui);
    font-size: 0.75rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--gold);
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.25);
    padding: 8px 20px;
    border-radius: 2px;
    margin-bottom: 3rem;
}
.khan-badge-dot { width: 6px; height: 6px; background: var(--gold); border-radius: 50%; animation: blink 2s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }

.category-label {
    font-family: var(--font-ui);
    font-size: 0.7rem;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: var(--gold);
    margin: 2.5rem 0 1.2rem;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(212,175,55,0.15);
}

.material-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: 4px;
    padding: 1.6rem 2rem;
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
    transition: border-color 0.25s, background 0.25s;
    margin-bottom: 1rem;
}
.material-card:hover { border-color: rgba(212,175,55,0.3); background: rgba(212,175,55,0.03); }

.material-thumb {
    width: 120px; height: 78px;
    object-fit: cover;
    border-radius: 2px;
    flex-shrink: 0;
    border: 1px solid rgba(212,175,55,0.15);
}

.material-info { flex: 1; }
.material-info h4 {
    font-family: var(--font-display);
    font-size: 1rem;
    color: var(--white);
    letter-spacing: 1px;
    margin: 0 0 8px;
    text-transform: uppercase;
}
.material-info p {
    font-family: var(--font-body);
    color: var(--muted);
    font-size: 1rem;
    line-height: 1.6;
    margin: 0 0 10px;
}
.material-meta {
    display: flex;
    gap: 1.2rem;
    font-family: var(--font-ui);
    font-size: 0.72rem;
    letter-spacing: 1px;
    color: rgba(255,255,255,0.45);
    text-transform: uppercase;
    margin-bottom: 12px;
}
.material-actions { display: flex; gap: 10px; flex-wrap: wrap; }

.btn-watch {
    font-family: var(--font-ui);
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    background: var(--gold);
    color: #000;
    padding: 9px 22px;
    text-decoration: none;
    border-radius: 2px;
    transition: background 0.2s, transform 0.2s;
    display: inline-block;
}
.btn-watch:hover { background: var(--gold-light); transform: translateY(-1px); }

.btn-download {
    font-family: var(--font-ui);
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    border: 1px solid rgba(212,175,55,0.4);
    color: var(--gold);
    padding: 9px 22px;
    text-decoration: none;
    border-radius: 2px;
    transition: background 0.2s, color 0.2s;
    display: inline-block;
    background: transparent;
}
.btn-download:hover { background: var(--gold); color: #000; }

.no-materials {
    font-family: var(--font-body);
    color: var(--muted);
    font-size: 1.1rem;
    font-style: italic;
    text-align: center;
    padding: 3rem 2rem;
    border: 1px dashed rgba(212,175,55,0.2);
    border-radius: 4px;
}

/* ============================================================
   ENROLL CTA (non-members)
   ============================================================ */
.enroll-section {
    background: var(--dark);
    padding: 90px 0;
}

.enroll-box {
    max-width: 780px;
    margin: 0 auto;
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 4px;
    padding: 4rem 3rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.enroll-box::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at center, rgba(212,175,55,0.04) 0%, transparent 70%);
}

.enroll-box h2 {
    font-family: var(--font-display);
    font-size: 2rem;
    color: var(--white);
    letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 0 1rem;
    position: relative;
}
.enroll-box p {
    font-family: var(--font-body);
    color: var(--muted);
    font-size: 1.15rem;
    line-height: 1.75;
    max-width: 560px;
    margin: 0 auto 1rem;
    position: relative;
}
.enroll-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-top: 2.5rem;
    flex-wrap: wrap;
    position: relative;
}

.btn-primary-gold {
    font-family: var(--font-ui);
    font-weight: 700;
    font-size: 0.82rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    background: var(--gold);
    color: #000;
    padding: 16px 40px;
    text-decoration: none;
    border-radius: 2px;
    display: inline-block;
    transition: background 0.25s, box-shadow 0.25s, transform 0.2s;
    box-shadow: 0 4px 20px rgba(212,175,55,0.3);
}
.btn-primary-gold:hover { background: var(--gold-light); box-shadow: 0 6px 28px rgba(212,175,55,0.5); transform: translateY(-2px); }

.btn-outline-gold {
    font-family: var(--font-ui);
    font-weight: 700;
    font-size: 0.82rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    border: 1.5px solid rgba(212,175,55,0.5);
    color: var(--gold);
    padding: 16px 40px;
    text-decoration: none;
    border-radius: 2px;
    display: inline-block;
    transition: background 0.25s, color 0.25s, transform 0.2s;
    background: transparent;
}
.btn-outline-gold:hover { background: var(--gold); color: #000; transform: translateY(-2px); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 900px) {
    .courses-hero-content h1 { font-size: 2.8rem; }
    .section-title { font-size: 2rem; }
    .curriculum-grid { grid-template-columns: 1fr; }
    .curriculum-item:nth-child(odd) { border-right: none; }
}
@media (max-width: 640px) {
    .courses-hero-content h1 { font-size: 1.9rem; }
    .courses-hero-content p  { font-size: 1rem; }
    .hero-corner { width: 48px; height: 48px; }
    .section-title { font-size: 1.7rem; }
    .material-card { flex-direction: column; }
    .material-thumb { width: 100%; height: 160px; }
    .enroll-box { padding: 2.5rem 1.5rem; }
    .enroll-actions { flex-direction: column; align-items: center; }
    .btn-primary-gold, .btn-outline-gold { width: 100%; text-align: center; }
}
</style>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="courses-hero">
    <div class="courses-hero-bg">
        <img src="../assets/images/courses.jpg" alt="Courses">
    </div>
    <div class="courses-hero-overlay"></div>

    <div class="hero-corner hero-corner--tl"></div>
    <div class="hero-corner hero-corner--tr"></div>
    <div class="hero-corner hero-corner--bl"></div>
    <div class="hero-corner hero-corner--br"></div>

    <div class="courses-hero-content">
        <span class="hero-eyebrow">Training Programs</span>
        <h1>Our <span>Courses</span></h1>

        <div class="hero-divider">
            <div class="hero-divider-line l"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-line r"></div>
        </div>

        <p>Comprehensive training in traditional Muay Boran martial arts.</p>
    </div>

    <div class="hero-scroll">
        <span>Scroll</span>
        <div class="hero-scroll-line"></div>
    </div>
</section>

<!-- ============================================================
     TRADITIONAL CURRICULUM
     ============================================================ -->
<section class="curriculum-section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Khan Grading System</span>
            <h2 class="section-title">Traditional Muay Boran Curriculum</h2>
            <div class="gold-rule"></div>
        </div>

        <div class="curriculum-inner">
            <p class="curriculum-intro">
                Our core curriculum follows the traditional Khan grading system, preserving the ancient techniques
                of the Thai battlefield through structured, progressive study.
            </p>

            <div class="curriculum-grid">
                <div class="curriculum-item">
                    <span class="curriculum-item-dot"></span>
                    Basic &amp; Advanced Strikes
                </div>
                <div class="curriculum-item">
                    <span class="curriculum-item-dot"></span>
                    Mae Mai &amp; Look Mai Techniques
                </div>
                <div class="curriculum-item">
                    <span class="curriculum-item-dot"></span>
                    Clinching &amp; Grappling (Muay Pram)
                </div>
                <div class="curriculum-item">
                    <span class="curriculum-item-dot"></span>
                    Ram Muay &amp; Wai Kru Rituals
                </div>
                <div class="curriculum-item">
                    <span class="curriculum-item-dot"></span>
                    Krabi Krabong (Weaponry)
                </div>
                <div class="curriculum-item">
                    <span class="curriculum-item-dot"></span>
                    Thai Martial Philosophy
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SPECIALIZED COURSES
     ============================================================ -->
<section class="specialized-section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Outside Standard Grading</span>
            <h2 class="section-title">Specialized Courses</h2>
            <div class="gold-rule"></div>
        </div>

        <p style="font-family:var(--font-body);font-size:1.15rem;color:var(--muted);text-align:center;max-width:640px;margin:0 auto 3.5rem;line-height:1.8;">
            Intensive specialized programs focused on tactical application and modern survival — taught alongside the traditional curriculum.
        </p>

        <div class="spec-grid">

            <div class="spec-card">
                <div class="spec-card-header">
                    <span class="spec-badge spec-badge--blue">Unarmed Combat Track</span>
                    <h3>Specialized Self Defense</h3>
                </div>
                <div class="spec-card-body">
                    <p>A reality-based program focused on rapid neutralization of threats. Covers situational awareness, defense against common street attacks, and high-pressure survival tactics.</p>
                </div>
            </div>

            <div class="spec-card">
                <div class="spec-card-header">
                    <span class="spec-badge spec-badge--red">Filipino Fighting Arts</span>
                    <h3>Pekiti Tirsia Kali (PTK)</h3>
                </div>
                <div class="spec-card-body">
                    <p>A highly effective close-quarter combat system from the Philippines. Focuses on blade and stick application, as well as the transition into empty-hand combat (Mano-Mano).</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     MATERIALS / ENROLL
     ============================================================ -->
<?php if (isLoggedIn() && $user_khan_level > 0): ?>

<section class="materials-section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Member Access</span>
            <h2 class="section-title">Available Course Materials</h2>
            <div class="gold-rule"></div>
        </div>

        <div style="max-width:900px;margin:0 auto;">
            <div class="khan-badge">
                <span class="khan-badge-dot"></span>
                Your Level — Khan <?php echo $user_khan_level; ?>
            </div>

            <?php if ($courses->num_rows > 0): ?>
                <?php
                $current_category = '';
                while ($course = $courses->fetch_assoc()):
                    if ($current_category !== $course['category']):
                        $current_category = $course['category'];
                ?>
                    <div class="category-label"><?php echo ucfirst($current_category); ?> Level</div>
                <?php endif; ?>

                <div class="material-card">
                    <?php if (!empty($course['thumbnail_path'])): ?>
                        <img class="material-thumb"
                             src="../<?php echo htmlspecialchars($course['thumbnail_path']); ?>"
                             alt="<?php echo htmlspecialchars($course['title']); ?>">
                    <?php endif; ?>
                    <div class="material-info">
                        <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                        <div class="material-meta">
                            <span>Khan <?php echo $course['khan_level_min']; ?>–<?php echo $course['khan_level_max']; ?></span>
                            <?php if ($course['duration_minutes']): ?>
                                <span><?php echo $course['duration_minutes']; ?> min</span>
                            <?php endif; ?>
                        </div>
                        <div class="material-actions">
                            <?php if (!empty($course['video_url'])): ?>
                                <a href="<?php echo htmlspecialchars($course['video_url']); ?>" target="_blank" class="btn-watch">Watch Video</a>
                            <?php endif; ?>
                            <?php if (!empty($course['file_path'])): ?>
                                <a href="../<?php echo htmlspecialchars($course['file_path']); ?>" target="_blank" class="btn-download">Download Material</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-materials">No course materials available for your current level yet. Check back soon.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php else: ?>

<!-- ============================================================
     ENROLL CTA (non-members / guests)
     ============================================================ -->
<section class="enroll-section">
    <div class="container">
        <div class="enroll-box">
            <h2>Access Course Materials</h2>
            <p>To access detailed course materials, training schedules, and exclusive content, enrollment in our programs is required.</p>
            <p>Contact us to learn about enrollment options and begin your journey in traditional Muay Boran.</p>
            <div class="enroll-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="contact.php" class="btn-primary-gold">Contact Us to Enroll</a>
                <?php else: ?>
                    <a href="contact.php" class="btn-primary-gold">Register Now</a>
                    <a href="login.php" class="btn-outline-gold">Member Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>