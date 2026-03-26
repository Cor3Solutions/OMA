<?php
$page_title = "Membership Benefits";
$extra_head = '<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">';
include '../includes/header.php';
?>

<style>
/* ============================================================
   DESIGN TOKENS
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
.gold-rule { width: 50px; height: 2px; background: var(--gold); margin: 1.5rem auto 0; }

/* ============================================================
   HERO
   ============================================================ */
.benefits-hero {
    position: relative;
    min-height: 65vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--black);
}
.benefits-hero-bg {
    position: absolute; inset: 0;
}
.benefits-hero-bg img {
    width: 100%; height: 100%;
    object-fit: cover;
    opacity: 0.35;
    transform: scale(1.04);
    transition: transform 14s ease;
}
.benefits-hero:hover .benefits-hero-bg img { transform: scale(1.0); }
.benefits-hero-overlay {
    position: absolute; inset: 0;
    background:
        linear-gradient(to right,  rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.9) 100%),
        linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.05) 50%, rgba(0,0,0,0.85) 100%);
}

.hero-corner {
    position: absolute;
    width: 72px; height: 72px;
    z-index: 3; opacity: 0.55;
}
.hero-corner--tl { top: 24px; left: 24px;    border-top: 2px solid var(--gold); border-left:  2px solid var(--gold); }
.hero-corner--tr { top: 24px; right: 24px;   border-top: 2px solid var(--gold); border-right: 2px solid var(--gold); }
.hero-corner--bl { bottom: 24px; left: 24px;  border-bottom: 2px solid var(--gold); border-left:  2px solid var(--gold); }
.hero-corner--br { bottom: 24px; right: 24px; border-bottom: 2px solid var(--gold); border-right: 2px solid var(--gold); }

.benefits-hero-content {
    position: relative; z-index: 2;
    text-align: center;
    padding: 80px 24px;
    opacity: 0;
    animation: heroFade 1s ease 0.1s forwards;
}
@keyframes heroFade { to { opacity: 1; } }

.hero-eyebrow {
    display: inline-block;
    font-family: var(--font-ui);
    font-size: 0.72rem; font-weight: 700;
    letter-spacing: 6px; text-transform: uppercase;
    color: var(--gold);
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.3);
    padding: 6px 18px; border-radius: 2px;
    margin-bottom: 22px;
}
.benefits-hero-content h1 {
    font-family: var(--font-display);
    font-size: 3.8rem; font-weight: 900;
    color: var(--white); text-transform: uppercase;
    letter-spacing: 3px; line-height: 1.05;
    margin: 0 0 24px;
    text-shadow: 0 4px 30px rgba(0,0,0,0.9);
}
.benefits-hero-content h1 span { color: var(--gold); }
.hero-divider {
    display: flex; align-items: center; justify-content: center;
    gap: 14px; margin: 0 auto 24px; max-width: 440px;
}
.hero-divider-line      { flex: 1; height: 1px; }
.hero-divider-line.l    { background: linear-gradient(to left,  var(--gold), transparent); }
.hero-divider-line.r    { background: linear-gradient(to right, var(--gold), transparent); }
.hero-divider-diamond   { width: 7px; height: 7px; background: var(--gold); transform: rotate(45deg); flex-shrink: 0; }
.benefits-hero-content p {
    font-family: var(--font-body);
    font-size: 1.25rem; color: rgba(255,255,255,0.78);
    max-width: 500px; margin: 0 auto;
    line-height: 1.75; font-weight: 300; font-style: italic;
}
.hero-scroll {
    position: absolute; bottom: 28px; left: 50%;
    transform: translateX(-50%); z-index: 3;
    display: flex; flex-direction: column;
    align-items: center; gap: 6px; opacity: 0.45;
    animation: scrollBob 2.5s ease-in-out infinite;
}
.hero-scroll span { font-family: var(--font-ui); font-size: 0.6rem; letter-spacing: 3px; color: var(--gold); text-transform: uppercase; }
.hero-scroll-line { width: 1px; height: 32px; background: linear-gradient(to bottom, var(--gold), transparent); }
@keyframes scrollBob {
    0%,100% { transform: translateX(-50%) translateY(0); }
    50%      { transform: translateX(-50%) translateY(8px); }
}

/* ============================================================
   KHAN SYSTEM SECTION
   ============================================================ */
.khan-section {
    background: var(--mid);
    padding: 100px 0;
}

.khan-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    border: 1px solid rgba(212,175,55,0.12);
    border-radius: 4px;
    overflow: hidden;
    max-width: 1000px;
    margin: 0 auto;
}

.khan-card {
    padding: 3rem 2.2rem;
    position: relative;
    transition: background 0.3s;
    border-right: 1px solid rgba(212,175,55,0.1);
}
.khan-card:last-child { border-right: none; }
.khan-card:hover { background: rgba(212,175,55,0.03); }

/* Featured centre card */
.khan-card--featured {
    background: rgba(212,175,55,0.04);
    border-right: 1px solid rgba(212,175,55,0.15);
    border-left: 1px solid rgba(212,175,55,0.15);
}
.khan-card--featured::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(to right, var(--red), var(--gold));
}

.khan-card-num {
    font-family: var(--font-display);
    font-size: 4rem; font-weight: 900;
    color: rgba(212,175,55,0.08);
    line-height: 1;
    margin-bottom: 1rem;
    transition: color 0.3s;
}
.khan-card:hover .khan-card-num,
.khan-card--featured .khan-card-num { color: rgba(212,175,55,0.15); }

.khan-card h3 {
    font-family: var(--font-display);
    font-size: 1rem; font-weight: 700;
    color: var(--gold);
    letter-spacing: 1px; text-transform: uppercase;
    margin: 0 0 14px;
}
.khan-card p {
    font-family: var(--font-body);
    font-size: 1.05rem;
    color: var(--muted);
    line-height: 1.75;
    margin: 0;
}

/* ============================================================
   MAIN BENEFITS SECTION
   ============================================================ */
.benefits-section {
    background: var(--dark);
    padding: 100px 0;
}

.benefits-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    max-width: 1000px;
    margin: 0 auto;
}

.benefit-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: 4px;
    padding: 2.8rem 2.2rem;
    position: relative;
    overflow: hidden;
    transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
    opacity: 0;
    transform: translateY(24px);
    animation: cardUp 0.7s ease forwards;
}
.benefit-card:nth-child(1) { animation-delay: 0.1s; }
.benefit-card:nth-child(2) { animation-delay: 0.22s; }
.benefit-card:nth-child(3) { animation-delay: 0.34s; }
@keyframes cardUp { to { opacity: 1; transform: translateY(0); } }

.benefit-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 3px; height: 0;
    background: linear-gradient(to bottom, var(--gold), var(--red));
    transition: height 0.4s ease;
}
.benefit-card:hover {
    border-color: rgba(212,175,55,0.35);
    box-shadow: 0 16px 48px rgba(0,0,0,0.5), 0 0 20px rgba(212,175,55,0.05);
    transform: translateY(-5px);
}
.benefit-card:hover::before { height: 100%; }

.benefit-icon {
    font-size: 2.2rem;
    display: block;
    margin-bottom: 1.4rem;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.4));
}
.benefit-card h3 {
    font-family: var(--font-display);
    font-size: 1rem; font-weight: 700;
    color: var(--gold);
    letter-spacing: 1px; text-transform: uppercase;
    margin: 0 0 14px;
}
.benefit-card p {
    font-family: var(--font-body);
    font-size: 1.05rem;
    color: var(--muted);
    line-height: 1.75;
    margin: 0;
}

/* ============================================================
   CTA SECTION
   ============================================================ */
.benefits-cta-section {
    background: linear-gradient(135deg, #0d0d0d 0%, #1a0a0a 50%, #0d0d0d 100%);
    border-top: 1px solid rgba(202,19,19,0.2);
    padding: 100px 0;
}
.benefits-cta-box {
    max-width: 760px; margin: 0 auto;
    text-align: center;
    border: 1px solid rgba(212,175,55,0.18);
    border-radius: 4px;
    padding: 5rem 3rem;
    position: relative; overflow: hidden;
}
.benefits-cta-box::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at center, rgba(212,175,55,0.04) 0%, transparent 70%);
}
.benefits-cta-box h2 {
    font-family: var(--font-display);
    font-size: 2rem; color: var(--white);
    letter-spacing: 2px; text-transform: uppercase;
    margin: 0 0 1.2rem; position: relative;
}
.benefits-cta-box p {
    font-family: var(--font-body);
    font-size: 1.2rem; color: var(--muted);
    max-width: 480px; margin: 0 auto 2.5rem;
    line-height: 1.75; font-style: italic;
    position: relative;
}
.btn-cta-gold {
    font-family: var(--font-ui); font-weight: 700;
    font-size: 0.82rem; letter-spacing: 3px; text-transform: uppercase;
    background: var(--gold); color: #000;
    padding: 18px 48px; text-decoration: none;
    border-radius: 2px; display: inline-block;
    transition: background 0.25s, box-shadow 0.25s, transform 0.2s;
    box-shadow: 0 4px 20px rgba(212,175,55,0.35);
    position: relative;
}
.btn-cta-gold:hover { background: var(--gold-light); box-shadow: 0 6px 30px rgba(212,175,55,0.55); transform: translateY(-2px); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 900px) {
    .khan-grid    { grid-template-columns: 1fr; border: none; gap: 2px; }
    .khan-card    { border-right: none; border-bottom: 1px solid rgba(212,175,55,0.1); border: 1px solid rgba(212,175,55,0.1); border-radius: 4px; }
    .benefits-grid { grid-template-columns: 1fr 1fr; }
    .benefits-hero-content h1 { font-size: 2.8rem; }
    .section-title { font-size: 2rem; }
}
@media (max-width: 640px) {
    .benefits-hero-content h1 { font-size: 1.9rem; }
    .hero-corner { width: 48px; height: 48px; }
    .section-title { font-size: 1.7rem; }
    .benefits-grid { grid-template-columns: 1fr; }
    .benefits-cta-box { padding: 2.5rem 1.5rem; }
}
</style>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="benefits-hero">
    <div class="benefits-hero-bg">
        <img src="../assets/images/mma.png" alt="Member Benefits">
    </div>
    <div class="benefits-hero-overlay"></div>

    <div class="hero-corner hero-corner--tl"></div>
    <div class="hero-corner hero-corner--tr"></div>
    <div class="hero-corner hero-corner--bl"></div>
    <div class="hero-corner hero-corner--br"></div>

    <div class="benefits-hero-content">
        <span class="hero-eyebrow">Elevate Your Training</span>
        <h1>Member <span>Benefits</span></h1>
        <div class="hero-divider">
            <div class="hero-divider-line l"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-line r"></div>
        </div>
        <p>Everything you gain when you join the Oriental Muayboran Academy family.</p>
    </div>

    <div class="hero-scroll">
        <span>Scroll</span>
        <div class="hero-scroll-line"></div>
    </div>
</section>

<!-- ============================================================
     KHAN SYSTEM
     ============================================================ -->
<section class="khan-section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Structured Progression</span>
            <h2 class="section-title">The Khan System</h2>
            <div class="gold-rule"></div>
        </div>

        <div class="khan-grid">

            <div class="khan-card">
                <div class="khan-card-num">I</div>
                <h3>Constructivist Learning</h3>
                <p>Mastery through core fundamentals, elevating to complex application and traditional wisdom.</p>
            </div>

            <div class="khan-card khan-card--featured">
                <div class="khan-card-num">II</div>
                <h3>Nakmuay (Khan 1–10)</h3>
                <p>Ten levels for students to ensure technical proficiency and discipline before promotion.</p>
            </div>

            <div class="khan-card">
                <div class="khan-card-num">III</div>
                <h3>Mastership (Khan 11–16)</h3>
                <p>Advanced instructor and mastership certification led by certified Kru lineage.</p>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     MAIN BENEFITS
     ============================================================ -->
<section class="benefits-section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">What You Receive</span>
            <h2 class="section-title">Member Privileges</h2>
            <div class="gold-rule"></div>
        </div>

        <div class="benefits-grid">

            <div class="benefit-card">
                <span class="benefit-icon">🥋</span>
                <h3>Certified Training</h3>
                <p>Access to authentic Muayboran instruction directly from the Sit Kru Sane lineage — uncompromised and traditional.</p>
            </div>

            <div class="benefit-card">
                <span class="benefit-icon">📜</span>
                <h3>Official Certification</h3>
                <p>Earn internationally recognized Khan levels and certificates respected by the global Muayboran community.</p>
            </div>

            <div class="benefit-card">
                <span class="benefit-icon">📚</span>
                <h3>Exclusive Materials</h3>
                <p>Access comprehensive training videos, curriculum guides, and historical documentation unavailable elsewhere.</p>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     CTA
     ============================================================ -->
<section class="benefits-cta-section">
    <div class="container">
        <div class="benefits-cta-box">
            <h2>Experience All These Benefits</h2>
            <p>Join Oriental Muayboran Academy and start your journey today.</p>
            <a href="contact.php" class="btn-cta-gold">Become a Member →</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>