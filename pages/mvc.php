<?php
$page_title = "Mission, Vision & Core Values";
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
.mvc-hero {
    position: relative;
    min-height: 65vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--black);
}
.mvc-hero-bg {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(202,19,19,0.55) 0%, rgba(139,0,0,0.45) 100%),
                url('../assets/images/mma.png') center/cover no-repeat;
    transform: scale(1.04);
    transition: transform 14s ease;
}
.mvc-hero:hover .mvc-hero-bg { transform: scale(1.0); }
.mvc-hero-overlay {
    position: absolute; inset: 0;
    background:
        linear-gradient(to right,  rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.85) 100%),
        linear-gradient(to bottom, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.05) 50%, rgba(0,0,0,0.85) 100%);
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

.mvc-hero-content {
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
.mvc-hero-content h1 {
    font-family: var(--font-display);
    font-size: 3.4rem; font-weight: 900;
    color: var(--white); text-transform: uppercase;
    letter-spacing: 2px; line-height: 1.08;
    margin: 0 0 24px;
    text-shadow: 0 4px 30px rgba(0,0,0,0.9);
}
.mvc-hero-content h1 span { color: var(--gold); display: block; }
.hero-divider {
    display: flex; align-items: center; justify-content: center;
    gap: 14px; margin: 0 auto 24px; max-width: 440px;
}
.hero-divider-line      { flex: 1; height: 1px; }
.hero-divider-line.l    { background: linear-gradient(to left,  var(--gold), transparent); }
.hero-divider-line.r    { background: linear-gradient(to right, var(--gold), transparent); }
.hero-divider-diamond   { width: 7px; height: 7px; background: var(--gold); transform: rotate(45deg); flex-shrink: 0; }
.mvc-hero-content p {
    font-family: var(--font-body);
    font-size: 1.25rem; color: rgba(255,255,255,0.78);
    max-width: 520px; margin: 0 auto;
    line-height: 1.75; font-weight: 300; font-style: italic;
}
.hero-scroll {
    position: absolute; bottom: 28px; left: 50%;
    transform: translateX(-50%); z-index: 3;
    display: flex; flex-direction: column; align-items: center;
    gap: 6px; opacity: 0.45;
    animation: scrollBob 2.5s ease-in-out infinite;
}
.hero-scroll span { font-family: var(--font-ui); font-size: 0.6rem; letter-spacing: 3px; color: var(--gold); text-transform: uppercase; }
.hero-scroll-line { width: 1px; height: 32px; background: linear-gradient(to bottom, var(--gold), transparent); }
@keyframes scrollBob {
    0%,100% { transform: translateX(-50%) translateY(0); }
    50%      { transform: translateX(-50%) translateY(8px); }
}

/* ============================================================
   VISION SECTION
   ============================================================ */
.vision-section {
    background: var(--mid);
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}
.vision-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at center, rgba(212,175,55,0.04) 0%, transparent 65%);
    pointer-events: none;
}

.vision-box {
    max-width: 860px;
    margin: 0 auto;
    text-align: center;
    position: relative;
}
/* Large decorative quote mark */
.vision-box::before {
    content: '\201C';
    position: absolute;
    top: -40px; left: -20px;
    font-family: var(--font-display);
    font-size: 14rem;
    line-height: 1;
    color: rgba(212,175,55,0.06);
    pointer-events: none;
    z-index: 0;
}

.vision-quote {
    font-family: var(--font-body);
    font-size: 1.75rem;
    font-style: italic;
    color: var(--white);
    line-height: 1.8;
    font-weight: 300;
    position: relative;
    z-index: 1;
    padding: 0 2rem;
}
.vision-quote em {
    color: var(--gold);
    font-style: normal;
    font-weight: 400;
}

/* ============================================================
   MISSION SECTION
   ============================================================ */
.mission-section {
    background: var(--dark);
    padding: 100px 0;
}

.mission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 28px;
    max-width: 1000px;
    margin: 0 auto;
}

.mission-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: 4px;
    padding: 2.8rem 2.2rem;
    position: relative;
    transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
    opacity: 0;
    transform: translateY(24px);
    animation: cardUp 0.7s ease forwards;
}
.mission-card:nth-child(1) { animation-delay: 0.1s; }
.mission-card:nth-child(2) { animation-delay: 0.22s; }
.mission-card:nth-child(3) { animation-delay: 0.34s; }
@keyframes cardUp { to { opacity: 1; transform: translateY(0); } }

.mission-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 3px; height: 0;
    background: linear-gradient(to bottom, var(--gold), var(--red));
    transition: height 0.4s ease;
}
.mission-card:hover { border-color: rgba(212,175,55,0.35); box-shadow: 0 16px 48px rgba(0,0,0,0.5); transform: translateY(-5px); }
.mission-card:hover::before { height: 100%; }

.mission-number {
    font-family: var(--font-display);
    font-size: 3.5rem;
    font-weight: 900;
    color: rgba(212,175,55,0.12);
    line-height: 1;
    margin-bottom: 1rem;
    transition: color 0.3s;
}
.mission-card:hover .mission-number { color: rgba(212,175,55,0.22); }

.mission-title {
    font-family: var(--font-display);
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--gold);
    letter-spacing: 1px;
    text-transform: uppercase;
    margin: 0 0 14px;
}
.mission-text {
    font-family: var(--font-body);
    font-size: 1.1rem;
    color: var(--muted);
    line-height: 1.8;
    margin: 0;
}

/* ============================================================
   CORE VALUES SECTION
   ============================================================ */
.values-section {
    background: var(--mid);
    padding: 100px 0;
    overflow: hidden;
}

/* Scrollable track on mobile, grid on desktop */
.values-track {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
    margin-top: 1rem;
}

.value-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(212,175,55,0.12);
    border-radius: 4px;
    overflow: hidden;
    transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
    position: relative;
    opacity: 0;
    transform: translateY(20px);
    animation: cardUp 0.6s ease forwards;
}
.value-card:nth-child(1) { animation-delay: 0.05s; }
.value-card:nth-child(2) { animation-delay: 0.15s; }
.value-card:nth-child(3) { animation-delay: 0.25s; }
.value-card:nth-child(4) { animation-delay: 0.35s; }
.value-card:nth-child(5) { animation-delay: 0.45s; }

.value-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(to right, var(--red), var(--gold));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
}
.value-card:hover { border-color: rgba(212,175,55,0.38); box-shadow: 0 16px 48px rgba(0,0,0,0.5), 0 0 20px rgba(212,175,55,0.06); transform: translateY(-6px); }
.value-card:hover::after { transform: scaleX(1); }

.value-header {
    background: linear-gradient(160deg, rgba(202,19,19,0.8) 0%, rgba(100,0,0,0.85) 100%);
    padding: 2rem 1.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.value-header::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(212,175,55,0.07) 50%, transparent 70%);
    animation: sheen 4s ease-in-out infinite;
}
@keyframes sheen { 0%,100%{transform:translateX(-100%)} 50%{transform:translateX(100%)} }

.value-icon {
    font-size: 2.6rem;
    display: block;
    margin-bottom: 12px;
    position: relative; z-index: 1;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.5));
}
.value-name {
    font-family: var(--font-display);
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--white);
    letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0;
    position: relative; z-index: 1;
    line-height: 1.2;
}

.value-body {
    padding: 1.8rem 1.5rem;
}
.value-pledge {
    font-family: var(--font-body);
    font-size: 1rem;
    font-style: italic;
    color: var(--muted);
    line-height: 1.75;
    margin: 0;
    padding-left: 1rem;
    border-left: 2px solid rgba(212,175,55,0.25);
    transition: border-color 0.3s;
}
.value-card:hover .value-pledge { border-left-color: var(--gold); }

/* Swipe hint — mobile only */
.swipe-hint {
    display: none;
    justify-content: center;
    align-items: center;
    gap: 12px;
    margin-top: 2rem;
    opacity: 0.5;
}
.swipe-hint span {
    font-family: var(--font-ui);
    font-size: 0.65rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--gold);
}
.swipe-hint-line { width: 36px; height: 1px; background: var(--gold); }

/* ============================================================
   SACRED OATH SECTION
   ============================================================ */
.oath-section {
    background: var(--black);
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}
.oath-section::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at center, rgba(212,175,55,0.04) 0%, transparent 65%);
}

.oath-inner {
    max-width: 780px;
    margin: 0 auto;
    text-align: center;
    border: 1px solid rgba(212,175,55,0.18);
    border-radius: 4px;
    padding: 5rem 3.5rem;
    position: relative;
}
/* Corner ornament on the oath box */
.oath-inner::before,
.oath-inner::after {
    content: '';
    position: absolute;
    width: 50px; height: 50px;
    opacity: 0.4;
}
.oath-inner::before { top: -1px; left: -1px; border-top: 2px solid var(--gold); border-left: 2px solid var(--gold); }
.oath-inner::after  { bottom: -1px; right: -1px; border-bottom: 2px solid var(--gold); border-right: 2px solid var(--gold); }

.oath-eyebrow {
    font-family: var(--font-ui);
    font-size: 0.7rem; font-weight: 700;
    letter-spacing: 5px; text-transform: uppercase;
    color: var(--gold); display: block;
    margin-bottom: 20px;
}
.oath-title {
    font-family: var(--font-display);
    font-size: 2.2rem; font-weight: 900;
    color: var(--white); letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 0 28px;
}
.oath-divider {
    display: flex; align-items: center; justify-content: center;
    gap: 14px; margin: 0 auto 32px; max-width: 360px;
}
.oath-divider-line { flex: 1; height: 1px; }
.oath-divider-line.l { background: linear-gradient(to left,  var(--gold), transparent); }
.oath-divider-line.r { background: linear-gradient(to right, var(--gold), transparent); }
.oath-divider-diamond { width: 7px; height: 7px; background: var(--gold); transform: rotate(45deg); }

.oath-pledge {
    font-family: var(--font-display);
    font-size: 2rem;
    color: var(--gold);
    font-style: italic;
    margin: 0 0 28px;
    text-shadow: 0 0 30px rgba(212,175,55,0.25);
    letter-spacing: 1px;
}
.oath-desc {
    font-family: var(--font-body);
    font-size: 1.15rem;
    color: var(--muted);
    line-height: 1.85;
    max-width: 600px;
    margin: 0 auto;
    font-weight: 300;
}

/* ============================================================
   CTA SECTION
   ============================================================ */
.mvc-cta-section {
    background: linear-gradient(135deg, #0d0d0d 0%, #1a0a0a 50%, #0d0d0d 100%);
    border-top: 1px solid rgba(202,19,19,0.2);
    padding: 100px 0;
}
.mvc-cta-box {
    max-width: 760px; margin: 0 auto;
    text-align: center;
    border: 1px solid rgba(212,175,55,0.18);
    border-radius: 4px; padding: 5rem 3rem;
    position: relative; overflow: hidden;
}
.mvc-cta-box::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at center, rgba(212,175,55,0.04) 0%, transparent 70%);
}
.mvc-cta-box h2 {
    font-family: var(--font-display);
    font-size: 2rem; color: var(--white);
    letter-spacing: 2px; text-transform: uppercase;
    margin: 0 0 1.2rem; position: relative;
}
.mvc-cta-box p {
    font-family: var(--font-body);
    font-size: 1.2rem; color: var(--muted);
    max-width: 480px; margin: 0 auto 2.5rem;
    line-height: 1.75; font-style: italic;
    position: relative;
}
.cta-buttons { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; position: relative; }

.btn-cta-gold {
    font-family: var(--font-ui); font-weight: 700;
    font-size: 0.82rem; letter-spacing: 3px; text-transform: uppercase;
    background: var(--gold); color: #000;
    padding: 16px 40px; text-decoration: none;
    border-radius: 2px; display: inline-block;
    transition: background 0.25s, box-shadow 0.25s, transform 0.2s;
    box-shadow: 0 4px 20px rgba(212,175,55,0.3);
}
.btn-cta-gold:hover { background: var(--gold-light); box-shadow: 0 6px 28px rgba(212,175,55,0.5); transform: translateY(-2px); }

.btn-cta-outline {
    font-family: var(--font-ui); font-weight: 700;
    font-size: 0.82rem; letter-spacing: 3px; text-transform: uppercase;
    border: 1.5px solid rgba(255,255,255,0.4); color: var(--white);
    padding: 16px 40px; text-decoration: none;
    border-radius: 2px; display: inline-block; background: transparent;
    transition: border-color 0.25s, color 0.25s, transform 0.2s;
}
.btn-cta-outline:hover { border-color: var(--gold); color: var(--gold); transform: translateY(-2px); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1100px) {
    .values-track { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 780px) {
    .values-track {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 1rem;
        gap: 16px;
    }
    .values-track::-webkit-scrollbar { display: none; }
    .value-card { flex: 0 0 78%; scroll-snap-align: center; }
    .swipe-hint { display: flex; }
    .mvc-hero-content h1 { font-size: 2.4rem; }
    .vision-quote { font-size: 1.35rem; }
    .oath-pledge { font-size: 1.6rem; }
    .oath-inner { padding: 3rem 1.8rem; }
}
@media (max-width: 640px) {
    .mvc-hero-content h1 { font-size: 1.9rem; }
    .hero-corner { width: 48px; height: 48px; }
    .section-title { font-size: 1.7rem; }
    .mvc-cta-box { padding: 2.5rem 1.5rem; }
    .cta-buttons { flex-direction: column; align-items: center; }
    .btn-cta-gold, .btn-cta-outline { width: 100%; text-align: center; }
}
</style>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="mvc-hero">
    <div class="mvc-hero-bg"></div>
    <div class="mvc-hero-overlay"></div>

    <div class="hero-corner hero-corner--tl"></div>
    <div class="hero-corner hero-corner--tr"></div>
    <div class="hero-corner hero-corner--bl"></div>
    <div class="hero-corner hero-corner--br"></div>

    <div class="mvc-hero-content">
        <span class="hero-eyebrow">What We Stand For</span>
        <h1>Mission, Vision<br><span>&amp; Core Values</span></h1>
        <div class="hero-divider">
            <div class="hero-divider-line l"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-line r"></div>
        </div>
        <p>The principles and purpose that guide our academy and every practitioner.</p>
    </div>

    <div class="hero-scroll">
        <span>Scroll</span>
        <div class="hero-scroll-line"></div>
    </div>
</section>

<!-- ============================================================
     VISION
     ============================================================ -->
<section class="vision-section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Looking Forward</span>
            <h2 class="section-title">Our Vision</h2>
            <div class="gold-rule"></div>
        </div>

        <div class="vision-box">
            <p class="vision-quote">
                "A united community of <em>Muaythai Boran practitioners</em> who passionately embody
                the core values of OMA, empowered to contribute to a
                <em>peaceful and progressive humanity.</em>"
            </p>
        </div>
    </div>
</section>

<!-- ============================================================
     MISSION
     ============================================================ -->
<section class="mission-section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Our Commitment</span>
            <h2 class="section-title">Our Mission</h2>
            <div class="gold-rule"></div>
            <p style="font-family:var(--font-body);color:var(--muted);font-size:1.1rem;max-width:580px;margin:1.5rem auto 0;line-height:1.75;">
                Three pillars that define our work and dedication to the art of Muayboran.
            </p>
        </div>

        <div class="mission-grid">
            <div class="mission-card">
                <div class="mission-number">01</div>
                <h3 class="mission-title">Preserve the Curriculum</h3>
                <p class="mission-text">Institutionalize the complete curriculum of Great Grandmaster Sane Tubthimtong, ensuring authentic techniques and teachings are passed down through generations.</p>
            </div>
            <div class="mission-card">
                <div class="mission-number">02</div>
                <h3 class="mission-title">Empower Our Members</h3>
                <p class="mission-text">Equip members with high-standard knowledge and skills for self-sufficiency, creating confident practitioners capable of teaching and leading.</p>
            </div>
            <div class="mission-card">
                <div class="mission-number">03</div>
                <h3 class="mission-title">Build Strong Kinship</h3>
                <p class="mission-text">Solidify strong kinship among all Kru in the Philippines, fostering unity, respect, and collaboration within the Muayboran community.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     CORE VALUES
     ============================================================ -->
<section class="values-section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Our Foundation</span>
            <h2 class="section-title">Core Values</h2>
            <div class="gold-rule"></div>
        </div>

        <div class="values-track">

            <div class="value-card">
                <div class="value-header">
                    <span class="value-icon">🙏</span>
                    <h3 class="value-name">Respect &amp; Honor</h3>
                </div>
                <div class="value-body">
                    <p class="value-pledge">"I will respect everyone, especially my family, mentors, and myself, never bringing disgrace."</p>
                </div>
            </div>

            <div class="value-card">
                <div class="value-header">
                    <span class="value-icon">🛡️</span>
                    <h3 class="value-name">Loyalty &amp; Truth</h3>
                </div>
                <div class="value-body">
                    <p class="value-pledge">"I will be loyal to my motherland, standing fearlessly to protect honor, truth, and justice."</p>
                </div>
            </div>

            <div class="value-card">
                <div class="value-header">
                    <span class="value-icon">💪</span>
                    <h3 class="value-name">Conviction</h3>
                </div>
                <div class="value-body">
                    <p class="value-pledge">"I will live by my principles, stand for the greater good, and hold myself responsible."</p>
                </div>
            </div>

            <div class="value-card">
                <div class="value-header">
                    <span class="value-icon">🧘</span>
                    <h3 class="value-name">Self-Control</h3>
                </div>
                <div class="value-body">
                    <p class="value-pledge">"I will maintain unwavering self-discipline and self-control under any circumstance."</p>
                </div>
            </div>

            <div class="value-card">
                <div class="value-header">
                    <span class="value-icon">⚖️</span>
                    <h3 class="value-name">Righteousness</h3>
                </div>
                <div class="value-body">
                    <p class="value-pledge">"I will use my skills to protect and defend what is right, never to boast or cause harm."</p>
                </div>
            </div>

        </div>

        <div class="swipe-hint">
            <div class="swipe-hint-line"></div>
            <span>Swipe to explore</span>
            <div class="swipe-hint-line"></div>
        </div>
    </div>
</section>

<!-- ============================================================
     SACRED OATH
     ============================================================ -->
<section class="oath-section">
    <div class="container">
        <div class="oath-inner">
            <span class="oath-eyebrow">Solemn Commitment</span>
            <h2 class="oath-title">The Sacred Pledge</h2>

            <div class="oath-divider">
                <div class="oath-divider-line l"></div>
                <div class="oath-divider-diamond"></div>
                <div class="oath-divider-line r"></div>
            </div>

            <p class="oath-pledge">"These I pledge."</p>

            <p class="oath-desc">
                This solemn commitment binds every OMA practitioner to the highest standards of
                martial and moral conduct — a promise of personal excellence and service to others.
                A covenant that transforms practitioners into warriors of character, discipline, and honor.
            </p>
        </div>
    </div>
</section>

<!-- ============================================================
     CTA
     ============================================================ -->
<section class="mvc-cta-section">
    <div class="container">
        <div class="mvc-cta-box">
            <h2>Ready to Live These Values?</h2>
            <p>Join a community dedicated to excellence, tradition, and the warrior spirit.</p>
            <div class="cta-buttons">
                <a href="contact.php" class="btn-cta-gold">Submit Membership Inquiry</a>
                <a href="about.php"    class="btn-cta-outline">Learn More About OMA</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>