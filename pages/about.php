<?php
$page_title = "About Us";
$extra_head = '<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">';
include '../includes/header.php';
?>

<style>
/* ============================================================
   DESIGN TOKENS — mirrors index_enhanced.php
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

/* ============================================================
   PAGE BASE
   ============================================================ */
body, .about-page-wrap {
    background: var(--dark);
    color: var(--white);
}

.about-page-wrap section {
    padding: 90px 0;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}

/* ---- Shared section header (same as index) ---- */
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
.section-description {
    font-family: var(--font-body);
    color: var(--muted);
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.75;
}
.section-header {
    text-align: center;
    margin-bottom: 4rem;
}
.gold-rule {
    width: 50px;
    height: 2px;
    background: var(--gold);
    margin: 1.5rem auto 0;
}

/* ============================================================
   HERO
   ============================================================ */
.about-hero {
    position: relative;
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--black);
}

.about-hero-bg {
    position: absolute;
    inset: 0;
}
.about-hero-bg img {
    width: 100%; height: 100%;
    object-fit: cover;
    opacity: 0.35;
    transform: scale(1.04);
    transition: transform 14s ease;
}
.about-hero:hover .about-hero-bg img { transform: scale(1.0); }

.about-hero-overlay {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(to right,  rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.9) 100%),
        linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.1) 50%, rgba(0,0,0,0.85) 100%);
}

/* Corner ornaments — same as index */
.hero-corner {
    position: absolute;
    width: 72px; height: 72px;
    z-index: 3;
    opacity: 0.55;
}
.hero-corner--tl { top: 24px; left: 24px; border-top: 2px solid var(--gold); border-left: 2px solid var(--gold); }
.hero-corner--tr { top: 24px; right: 24px; border-top: 2px solid var(--gold); border-right: 2px solid var(--gold); }
.hero-corner--bl { bottom: 24px; left: 24px; border-bottom: 2px solid var(--gold); border-left: 2px solid var(--gold); }
.hero-corner--br { bottom: 24px; right: 24px; border-bottom: 2px solid var(--gold); border-right: 2px solid var(--gold); }

.about-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    padding: 80px 24px;
    animation: heroFade 1s ease forwards;
    opacity: 0;
}
@keyframes heroFade {
    to { opacity: 1; }
}

.about-hero-eyebrow {
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

.about-hero h1 {
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
.about-hero h1 span { color: var(--gold); }

.about-hero-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    margin: 0 auto 24px;
    max-width: 500px;
}
.about-hero-divider-line {
    flex: 1; height: 1px;
}
.about-hero-divider-line.l { background: linear-gradient(to left,  var(--gold), transparent); }
.about-hero-divider-line.r { background: linear-gradient(to right, var(--gold), transparent); }
.about-hero-divider-diamond {
    width: 7px; height: 7px;
    background: var(--gold);
    transform: rotate(45deg);
    flex-shrink: 0;
}

.about-hero p {
    font-family: var(--font-body);
    font-size: 1.25rem;
    color: rgba(255,255,255,0.78);
    max-width: 560px;
    margin: 0 auto;
    line-height: 1.75;
    font-weight: 300;
    font-style: italic;
}

/* Scroll indicator */
.hero-scroll {
    position: absolute;
    bottom: 28px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    opacity: 0.45;
    animation: scrollBob 2.5s ease-in-out infinite;
}
.hero-scroll span {
    font-family: var(--font-ui);
    font-size: 0.6rem;
    letter-spacing: 3px;
    color: var(--gold);
    text-transform: uppercase;
}
.hero-scroll-line { width: 1px; height: 32px; background: linear-gradient(to bottom, var(--gold), transparent); }
@keyframes scrollBob {
    0%,100% { transform: translateX(-50%) translateY(0); }
    50%      { transform: translateX(-50%) translateY(8px); }
}

/* ============================================================
   STATS SECTION
   ============================================================ */
.stats-section {
    background: linear-gradient(135deg, #0d0d0d 0%, #1a0808 60%, #0d0d0d 100%);
    border-top: 1px solid rgba(212,175,55,0.15);
    border-bottom: 1px solid rgba(212,175,55,0.15);
    padding: 70px 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2px;
    margin-top: 3rem;
}

.stat-item {
    text-align: center;
    padding: 2.5rem 1rem;
    position: relative;
    transition: background 0.3s;
}
.stat-item + .stat-item::before {
    content: '';
    position: absolute;
    left: 0; top: 20%; bottom: 20%;
    width: 1px;
    background: rgba(212,175,55,0.2);
}
.stat-item:hover { background: rgba(212,175,55,0.04); }

.stat-number {
    font-family: var(--font-display);
    font-size: 3.2rem;
    font-weight: 900;
    color: var(--gold);
    display: block;
    margin-bottom: 8px;
    text-shadow: 0 0 30px rgba(212,175,55,0.3);
    line-height: 1;
}
.stat-label {
    font-family: var(--font-ui);
    font-size: 0.75rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--muted);
}

/* ============================================================
   TIMELINE SECTION
   ============================================================ */
.timeline-section {
    background: var(--dark);
    padding: 90px 0;
}

.timeline-container {
    position: relative;
    padding: 2rem 0;
    margin-top: 1rem;
}

/* Central glowing line */
.timeline-line {
    position: absolute;
    left: 50%;
    top: 0; bottom: 0;
    width: 1px;
    background: linear-gradient(to bottom,
        transparent,
        rgba(212,175,55,0.6) 15%,
        var(--gold) 50%,
        rgba(212,175,55,0.6) 85%,
        transparent);
    transform: translateX(-50%);
    box-shadow: 0 0 12px rgba(212,175,55,0.25);
}

.timeline-item {
    display: grid;
    grid-template-columns: 1fr 80px 1fr;
    align-items: center;
    margin-bottom: 5rem;
    opacity: 0;
    transform: translateY(24px);
    animation: tlFade 0.7s ease forwards;
}
.timeline-item:nth-child(1) { animation-delay: 0.1s; }
.timeline-item:nth-child(2) { animation-delay: 0.25s; }
.timeline-item:nth-child(3) { animation-delay: 0.4s; }
.timeline-item:nth-child(4) { animation-delay: 0.55s; }
@keyframes tlFade {
    to { opacity: 1; transform: translateY(0); }
}

/* Cards */
.timeline-content {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(212,175,55,0.12);
    padding: 2rem 2.2rem;
    border-radius: 4px;
    position: relative;
    transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
}
.timeline-content::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 0; height: 0;
    transform: translateY(-50%);
    border: 10px solid transparent;
}
/* Arrow pointing right (odd items on left) */
.timeline-item:nth-child(odd)  .timeline-content::after {
    right: -20px;
    border-left-color: rgba(212,175,55,0.2);
}
/* Arrow pointing left (even items on right) */
.timeline-item:nth-child(even) .timeline-content::after {
    left: -20px;
    border-right-color: rgba(212,175,55,0.2);
}

.timeline-content:hover {
    border-color: rgba(212,175,55,0.45);
    box-shadow: 0 12px 40px rgba(0,0,0,0.4), 0 0 20px rgba(212,175,55,0.08);
    transform: translateY(-3px);
}
.timeline-item:nth-child(odd)  .timeline-content:hover { transform: translateY(-3px) translateX(-4px); }
.timeline-item:nth-child(even) .timeline-content:hover { transform: translateY(-3px) translateX(4px); }

.timeline-content h3 {
    font-family: var(--font-display);
    font-size: 1.15rem;
    color: var(--gold);
    letter-spacing: 1px;
    text-transform: uppercase;
    margin: 0 0 12px;
}
.timeline-content p {
    font-family: var(--font-body);
    color: var(--muted);
    font-size: 1.05rem;
    line-height: 1.75;
    margin: 0;
}
.timeline-content strong { color: var(--white); font-weight: 600; }

/* Year dot */
.timeline-dot {
    width: 68px; height: 68px;
    border: 2px solid var(--gold);
    border-radius: 50%;
    background: var(--dark);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    position: relative;
    margin: 0 auto;
    box-shadow: 0 0 20px rgba(212,175,55,0.2);
    transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
}
.timeline-dot span {
    font-family: var(--font-ui);
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 1px;
    color: var(--gold);
    transition: color 0.3s;
}
.timeline-dot::after {
    content: '';
    position: absolute;
    inset: -6px;
    border-radius: 50%;
    border: 1px solid rgba(212,175,55,0.25);
    animation: dotPulse 2.5s ease-in-out infinite;
}
@keyframes dotPulse {
    0%,100% { transform: scale(1); opacity: 0.6; }
    50%      { transform: scale(1.3); opacity: 0; }
}
.timeline-item:hover .timeline-dot {
    background: var(--gold);
    transform: scale(1.15);
    box-shadow: 0 0 30px rgba(212,175,55,0.5);
}
.timeline-item:hover .timeline-dot span { color: var(--black); }

/* ============================================================
   ERA / ESSENCE CARDS
   ============================================================ */
.essence-section {
    background: var(--mid);
    padding: 90px 0;
}

.era-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 28px;
    margin-top: 1rem;
}

.era-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(212,175,55,0.12);
    border-radius: 4px;
    overflow: hidden;
    transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
    position: relative;
}
.era-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 3px;
    background: linear-gradient(to right, var(--red), var(--gold));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
}
.era-card:hover {
    border-color: rgba(212,175,55,0.35);
    box-shadow: 0 16px 50px rgba(0,0,0,0.5), 0 0 25px rgba(212,175,55,0.06);
    transform: translateY(-6px);
}
.era-card:hover::before { transform: scaleX(1); }

.era-header {
    background: linear-gradient(135deg, rgba(202,19,19,0.85) 0%, rgba(120,0,0,0.9) 100%);
    padding: 2.2rem 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.era-header::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(212,175,55,0.08) 50%, transparent 70%);
    animation: eraShine 4s ease-in-out infinite;
}
@keyframes eraShine {
    0%,100% { transform: translateX(-100%); }
    50%      { transform: translateX(100%); }
}

.era-icon {
    font-size: 3.2rem;
    display: block;
    margin-bottom: 12px;
    position: relative;
    z-index: 1;
    filter: drop-shadow(0 4px 10px rgba(0,0,0,0.4));
}
.era-title {
    font-family: var(--font-display);
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--white);
    letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 0 4px;
    position: relative;
    z-index: 1;
}
.era-subtitle {
    font-family: var(--font-body);
    font-size: 0.95rem;
    color: rgba(255,255,255,0.75);
    font-style: italic;
    position: relative;
    z-index: 1;
    margin: 0;
}

.era-content {
    padding: 2rem 2.2rem;
}
.era-content p {
    font-family: var(--font-body);
    color: var(--muted);
    font-size: 1.05rem;
    line-height: 1.75;
    margin: 0 0 1rem;
}
.era-content ul {
    padding-left: 0;
    list-style: none;
    margin: 0;
}
.era-content ul li {
    font-family: var(--font-body);
    color: var(--muted);
    font-size: 1.05rem;
    line-height: 1.8;
    padding-left: 1.4rem;
    position: relative;
}
.era-content ul li::before {
    content: '◆';
    position: absolute;
    left: 0;
    color: var(--gold);
    font-size: 0.5rem;
    top: 0.6rem;
}

/* ============================================================
   CTA SECTION
   ============================================================ */
.about-cta-section {
    background: linear-gradient(135deg, #0d0d0d 0%, #1a0a0a 50%, #0d0d0d 100%);
    border-top: 1px solid rgba(202,19,19,0.2);
    padding: 90px 0;
}

.cta-box {
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 4px;
    padding: 5rem 3rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    max-width: 860px;
    margin: 0 auto;
}
.cta-box::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at center, rgba(212,175,55,0.05) 0%, transparent 70%);
}

.cta-box h2 {
    font-family: var(--font-display);
    font-size: 2.4rem;
    color: var(--white);
    letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 0 1.2rem;
    position: relative;
}
.cta-box p {
    font-family: var(--font-body);
    font-size: 1.2rem;
    color: var(--muted);
    max-width: 600px;
    margin: 0 auto 2.5rem;
    line-height: 1.75;
    font-style: italic;
    position: relative;
}

.btn-cta-gold {
    font-family: var(--font-ui);
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    background: var(--gold);
    color: #000;
    padding: 18px 48px;
    text-decoration: none;
    border-radius: 2px;
    display: inline-block;
    transition: background 0.25s, box-shadow 0.25s, transform 0.2s;
    box-shadow: 0 4px 20px rgba(212,175,55,0.35);
    position: relative;
}
.btn-cta-gold:hover {
    background: var(--gold-light);
    box-shadow: 0 6px 30px rgba(212,175,55,0.55);
    transform: translateY(-2px);
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 900px) {
    .about-hero h1 { font-size: 2.8rem; }
    .section-title  { font-size: 2rem; }
    .stats-grid     { grid-template-columns: repeat(2, 1fr); }
    .stat-item + .stat-item::before { display: none; }
}

@media (max-width: 640px) {
    .about-hero h1     { font-size: 1.9rem; }
    .about-hero p      { font-size: 1rem; }
    .section-title     { font-size: 1.7rem; }

    .stats-grid        { grid-template-columns: repeat(2, 1fr); gap: 0; }
    .stat-number       { font-size: 2.4rem; }

    .timeline-line     { left: 24px; transform: none; }
    .timeline-item     { grid-template-columns: 56px 1fr; gap: 16px; }
    .timeline-item .spacer { display: none; }
    /* All even items: push content to col 2 */
    .timeline-item:nth-child(even) .timeline-dot  { order: 1; }
    .timeline-item:nth-child(even) .timeline-content { order: 2; }
    .timeline-dot { width: 52px; height: 52px; margin: 0; }
    .timeline-dot span { font-size: 0.72rem; }
    .timeline-content::after { display: none; }

    .cta-box { padding: 3rem 1.5rem; }
    .cta-box h2 { font-size: 1.7rem; }
}
</style>

<div class="about-page-wrap">

    <!-- ============================================================
         HERO
         ============================================================ -->
    <section class="about-hero">
        <div class="about-hero-bg">
            <img src="../assets/images/aboutoma.png" alt="About OMA">
        </div>
        <div class="about-hero-overlay"></div>

        <div class="hero-corner hero-corner--tl"></div>
        <div class="hero-corner hero-corner--tr"></div>
        <div class="hero-corner hero-corner--bl"></div>
        <div class="hero-corner hero-corner--br"></div>

        <div class="about-hero-content">
            <span class="about-hero-eyebrow">Our Heritage</span>

            <h1>Oriental <span>Muayboran</span><br>Academy</h1>

            <div class="about-hero-divider">
                <div class="about-hero-divider-line l"></div>
                <div class="about-hero-divider-diamond"></div>
                <div class="about-hero-divider-diamond"></div>
                <div class="about-hero-divider-line r"></div>
            </div>

            <p>Inheriting and promoting the authentic traditional warfare system of ancient Siam</p>
        </div>

        <div class="hero-scroll">
            <span>Scroll</span>
            <div class="hero-scroll-line"></div>
        </div>
    </section>

    <!-- ============================================================
         STATS
         ============================================================ -->
    <section class="stats-section">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">By The Numbers</span>
                <h2 class="section-title">Our Journey in Numbers</h2>
                <div class="gold-rule"></div>
            </div>

            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number">19</span>
                    <span class="stat-label">Years of Heritage</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">2007</span>
                    <span class="stat-label">Roots Established</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">2016</span>
                    <span class="stat-label">OMA Founded</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">2018</span>
                    <span class="stat-label">Quezon City HQ</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         TIMELINE
         ============================================================ -->
    <section class="timeline-section">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">The Legacy Continues</span>
                <h2 class="section-title">Our Evolution</h2>
                <div class="gold-rule"></div>
            </div>

            <div class="timeline-container">
                <div class="timeline-line"></div>

                <!-- 2007 -->
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h3>The Roots</h3>
                        <p>Our journey began in <strong>La Trinidad, Benguet</strong>. The seeds of traditional Muayboran were planted in Philippine soil, blending ancient Siamese wisdom with local passion.</p>
                    </div>
                    <div class="timeline-dot"><span>2007</span></div>
                    <div class="spacer"></div>
                </div>

                <!-- 2016 -->
                <div class="timeline-item">
                    <div class="spacer"></div>
                    <div class="timeline-dot"><span>2016</span></div>
                    <div class="timeline-content">
                        <h3>Official Foundation</h3>
                        <p><strong>Oriental Muayboran Academy</strong> was born. We formalized our mission to preserve the lineage of <strong>Great Grandmaster Sane Tubthimtong</strong>.</p>
                    </div>
                </div>

                <!-- 2018 -->
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h3>The Great Move</h3>
                        <p>Relocated to <strong>Quezon City</strong>. We established our flagship headquarters — a sanctuary for those seeking the authentic Art of Eight Limbs.</p>
                    </div>
                    <div class="timeline-dot"><span>2018</span></div>
                    <div class="spacer"></div>
                </div>

                <!-- 2026 -->
                <div class="timeline-item">
                    <div class="spacer"></div>
                    <div class="timeline-dot"><span>2026</span></div>
                    <div class="timeline-content">
                        <h3>Modern Mastery</h3>
                        <p>Under <strong>Ajarn Brendaley Tarnate</strong>, OMA stands as a pillar of the Muayboran community — uniting tradition with modern excellence.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================
         ESSENCE OF MUAYBORAN
         ============================================================ -->
    <section class="essence-section">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Understanding Our Art</span>
                <h2 class="section-title">The Essence of Muayboran</h2>
                <div class="gold-rule"></div>
            </div>

            <div class="era-grid">

                <div class="era-card">
                    <div class="era-header">
                        <span class="era-icon">⚔️</span>
                        <h3 class="era-title">Combat System</h3>
                        <p class="era-subtitle">Complete Martial Art</p>
                    </div>
                    <div class="era-content">
                        <p>A comprehensive fighting system combining the ancient teachings of Siam into one cohesive discipline:</p>
                        <ul>
                            <li>Striking techniques</li>
                            <li>Grappling methods</li>
                            <li>Weapons training</li>
                            <li>Combat strategy</li>
                        </ul>
                    </div>
                </div>

                <div class="era-card">
                    <div class="era-header">
                        <span class="era-icon">🏛️</span>
                        <h3 class="era-title">Tradition</h3>
                        <p class="era-subtitle">Authentic Preservation</p>
                    </div>
                    <div class="era-content">
                        <p>We maintain the integrity of traditional forms while making the art accessible to modern practitioners through structured training and proven teaching methods.</p>
                    </div>
                </div>

                <div class="era-card">
                    <div class="era-header">
                        <span class="era-icon">🧘</span>
                        <h3 class="era-title">Philosophy</h3>
                        <p class="era-subtitle">Beyond Physical Technique</p>
                    </div>
                    <div class="era-content">
                        <p>More than combat skills, Muayboran teaches discipline, respect, and the warrior spirit that has guided practitioners for millennia.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================
         GUIDING PRINCIPLES CTA
         ============================================================ -->
    <section class="about-cta-section">
        <div class="container">
            <div class="cta-box">
                <h2>Our Guiding Principles</h2>
                <p>Discover the mission, vision, and core values that guide every OMA practitioner on their martial journey.</p>
                <a href="mvc.php" class="btn-cta-gold">View Mission, Vision &amp; Core Values →</a>
            </div>
        </div>
    </section>

</div><!-- /.about-page-wrap -->

<?php include '../includes/footer.php'; ?>
