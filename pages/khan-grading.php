<?php
$page_title = "Khan Grading Structure";
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
    font-size: 2.6rem; font-weight: 700;
    color: var(--white); letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 0 16px; line-height: 1.1;
}
.gold-rule { width: 50px; height: 2px; background: var(--gold); margin: 1.5rem auto 0; }

/* ============================================================
   PAGE HEADER (no separate hero image — inline dark header)
   ============================================================ -->
.khan-page-header {
    background: var(--black);
    padding: 100px 0 70px;
    text-align: center;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid rgba(212,175,55,0.12);
}
.khan-page-header::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at center, rgba(202,19,19,0.08) 0%, transparent 65%);
    pointer-events: none;
}
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
.khan-page-header h1 {
    font-family: var(--font-display);
    font-size: 3.8rem; font-weight: 900;
    color: var(--white); text-transform: uppercase;
    letter-spacing: 3px; line-height: 1.05;
    margin: 0 0 24px;
    text-shadow: 0 4px 30px rgba(0,0,0,0.9);
    position: relative; z-index: 1;
}
.khan-page-header h1 span { color: var(--gold); }
.hero-divider {
    display: flex; align-items: center; justify-content: center;
    gap: 14px; margin: 0 auto 20px; max-width: 440px;
    position: relative; z-index: 1;
}
.hero-divider-line      { flex: 1; height: 1px; }
.hero-divider-line.l    { background: linear-gradient(to left,  var(--gold), transparent); }
.hero-divider-line.r    { background: linear-gradient(to right, var(--gold), transparent); }
.hero-divider-diamond   { width: 7px; height: 7px; background: var(--gold); transform: rotate(45deg); flex-shrink: 0; }
.khan-page-header p {
    font-family: var(--font-body);
    font-size: 1.2rem; color: var(--muted);
    font-style: italic; font-weight: 300;
    max-width: 500px; margin: 0 auto;
    position: relative; z-index: 1;
}

/* Corner ornaments on page header */
.hdr-corner {
    position: absolute;
    width: 60px; height: 60px;
    opacity: 0.4; z-index: 1;
}
.hdr-corner--tl { top: 24px; left: 24px;    border-top: 2px solid var(--gold); border-left:  2px solid var(--gold); }
.hdr-corner--tr { top: 24px; right: 24px;   border-top: 2px solid var(--gold); border-right: 2px solid var(--gold); }

/* ============================================================
   TIMELINE
   ============================================================ */
.khan-timeline-section {
    background: var(--dark);
    padding: 80px 0 100px;
}

.khan-timeline {
    position: relative;
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Central glowing line */
.khan-timeline::after {
    content: '';
    position: absolute;
    width: 1px;
    background: linear-gradient(to bottom,
        transparent,
        rgba(212,175,55,0.5) 8%,
        var(--gold) 50%,
        rgba(212,175,55,0.5) 92%,
        transparent);
    top: 0; bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    z-index: 0;
    box-shadow: 0 0 12px rgba(212,175,55,0.2);
}

/* Individual card wrapper */
.khan-card {
    padding: 0 56px 0 0;
    position: relative;
    width: 50%;
    box-sizing: border-box;
    z-index: 1;
    margin-bottom: 64px;
    opacity: 0;
    transform: translateX(-24px);
    animation: slideIn 0.7s ease forwards;
}
.khan-card.right {
    left: 50%;
    padding: 0 0 0 56px;
    transform: translateX(24px);
}

/* Stagger delays */
.khan-card:nth-child(1)  { animation-delay: 0.05s; }
.khan-card:nth-child(2)  { animation-delay: 0.12s; }
.khan-card:nth-child(3)  { animation-delay: 0.19s; }
.khan-card:nth-child(4)  { animation-delay: 0.26s; }
.khan-card:nth-child(5)  { animation-delay: 0.33s; }
.khan-card:nth-child(6)  { animation-delay: 0.40s; }
.khan-card:nth-child(7)  { animation-delay: 0.47s; }
.khan-card:nth-child(8)  { animation-delay: 0.54s; }
@keyframes slideIn { to { opacity: 1; transform: translateX(0); } }

/* Timeline node dot */
.khan-card::after {
    content: '';
    position: absolute;
    width: 14px; height: 14px;
    background: var(--dark);
    border: 2px solid var(--gold);
    top: 38px;
    border-radius: 50%;
    z-index: 2;
    box-shadow: 0 0 10px rgba(212,175,55,0.4);
    transition: background 0.3s, transform 0.3s;
}
.khan-card.left::after  { right: -7px; }
.khan-card.right::after { left: -7px; }
.khan-card:hover::after { background: var(--gold); transform: scale(1.3); }

/* Arrow pointer from card to line */
.khan-card.left  .content-box::before { content: ''; position: absolute; top: 40px; right: -10px; width: 0; height: 0; border-top: 10px solid transparent; border-bottom: 10px solid transparent; border-left: 10px solid rgba(212,175,55,0.15); }
.khan-card.right .content-box::before { content: ''; position: absolute; top: 40px; left: -10px;  width: 0; height: 0; border-top: 10px solid transparent; border-bottom: 10px solid transparent; border-right: 10px solid rgba(212,175,55,0.15); }

/* Content box */
.content-box {
    background: rgba(255,255,255,0.025);
    border: 1px solid rgba(212,175,55,0.12);
    border-radius: 4px;
    padding: 2.2rem;
    position: relative;
    transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
    overflow: hidden;
}
.content-box::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(to right, var(--red), var(--gold));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
}
.content-box:hover { border-color: rgba(212,175,55,0.38); box-shadow: 0 16px 48px rgba(0,0,0,0.5), 0 0 20px rgba(212,175,55,0.06); transform: translateY(-4px); }
.content-box:hover::after { transform: scaleX(1); }

/* ---- Header row inside card ---- */
.header-layout {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
    margin-bottom: 20px;
}
.text-side { flex: 1; }

.khan-label {
    font-family: var(--font-display);
    font-size: 1.6rem; font-weight: 900;
    color: var(--white); letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 0 10px; line-height: 1;
}
.khan-label span { color: var(--gold); }

.mongkon-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: var(--font-ui);
    font-size: 0.7rem; font-weight: 700;
    letter-spacing: 3px; text-transform: uppercase;
    color: var(--gold);
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.2);
    padding: 4px 12px; border-radius: 2px;
    margin-bottom: 18px;
}

.color-swatch-dot {
    width: 14px; height: 14px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.2);
    flex-shrink: 0;
}

/* Portrait image */
.portrait-main {
    width: 160px;
    aspect-ratio: 2 / 2.8;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid rgba(212,175,55,0.2);
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    flex-shrink: 0;
    transition: transform 0.3s, box-shadow 0.3s;
    filter: brightness(0.95) contrast(1.05);
}
.content-box:hover .portrait-main { transform: scale(1.03); box-shadow: 0 12px 32px rgba(0,0,0,0.6); }

/* Curriculum list */
.curriculum-details {
    list-style: none;
    padding: 0; margin: 0;
}
.curriculum-details li {
    font-family: var(--font-body);
    font-size: 1rem;
    color: var(--muted);
    margin-bottom: 7px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    line-height: 1.55;
}
.curriculum-details li::before {
    content: '';
    width: 5px; height: 5px;
    background: var(--gold);
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 7px;
    opacity: 0.7;
}

/* Divider */
.card-divider {
    width: 100%;
    height: 1px;
    background: linear-gradient(to right, transparent, rgba(212,175,55,0.2), transparent);
    margin: 20px 0;
}

/* Mongkon image */
.action-square-wrapper {
    display: flex;
    justify-content: center;
    padding-top: 4px;
}
.action-square-img {
    width: 200px; height: auto;
    object-fit: contain;
    filter: drop-shadow(0 8px 16px rgba(0,0,0,0.5));
    transition: transform 0.3s, filter 0.3s;
}
.content-box:hover .action-square-img { transform: scale(1.04); filter: drop-shadow(0 12px 20px rgba(212,175,55,0.25)); }

/* ============================================================
   KHAN COLOR SWATCHES
   ============================================================ */
.sw-1  { background: #fff; }
.sw-2  { background: #FFD700; }
.sw-3  { background: linear-gradient(90deg, #FFD700 50%, #fff 50%); }
.sw-4  { background: #008000; }
.sw-5  { background: linear-gradient(90deg, #008000 50%, #fff 50%); }
.sw-6  { background: #0000FF; }
.sw-7  { background: linear-gradient(90deg, #0000FF 50%, #fff 50%); }
.sw-8  { background: #8B4513; }
.sw-9  { background: linear-gradient(90deg, #8B4513 50%, #fff 50%); }
.sw-10 { background: #FF0000; }
.sw-11 { background: linear-gradient(90deg, #FF0000 50%, #fff 50%); }
.sw-12 { background: linear-gradient(90deg, #FF0000 50%, #FFD700 50%); }
.sw-13 { background: linear-gradient(90deg, #FF0000 50%, #C0C0C0 50%); }
.sw-14 { background: #C0C0C0; }
.sw-15 { background: linear-gradient(90deg, #C0C0C0 50%, #FFD700 50%); }
.sw-16 { background: #FFD700; box-shadow: 0 0 6px gold; }

/* ============================================================
   CTA
   ============================================================ */
.khan-cta-section {
    background: linear-gradient(135deg, #0d0d0d 0%, #1a0a0a 50%, #0d0d0d 100%);
    border-top: 1px solid rgba(202,19,19,0.2);
    padding: 100px 0;
}
.khan-cta-box {
    max-width: 700px; margin: 0 auto;
    text-align: center;
    border: 1px solid rgba(212,175,55,0.18);
    border-radius: 4px; padding: 5rem 3rem;
    position: relative; overflow: hidden;
}
.khan-cta-box::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at center, rgba(212,175,55,0.04) 0%, transparent 70%);
}
.khan-cta-box h2 {
    font-family: var(--font-display);
    font-size: 2rem; color: var(--white);
    letter-spacing: 2px; text-transform: uppercase;
    margin: 0 0 1.2rem; position: relative;
}
.khan-cta-box p {
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
@media (max-width: 860px) {
    .khan-page-header h1 { font-size: 2.6rem; }
    .portrait-main { width: 130px; }
    .action-square-img { width: 160px; }
}
@media (max-width: 768px) {
    .khan-timeline::after { left: 20px; transform: none; }
    .khan-card,
    .khan-card.right {
        width: 100%; left: 0;
        padding: 0 0 0 52px;
        transform: translateX(0);
        animation: fadeUp 0.6s ease forwards;
    }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    .khan-card.left::after,
    .khan-card.right::after { left: 13px; right: auto; }
    .khan-card.left  .content-box::before,
    .khan-card.right .content-box::before { display: none; }
    .header-layout { flex-direction: column-reverse; }
    .portrait-main { width: 100%; max-width: 280px; height: auto; aspect-ratio: auto; }
    .action-square-img { width: 100%; max-width: 220px; }
    .khan-page-header h1 { font-size: 2rem; }
}
@media (max-width: 640px) {
    .khan-cta-box { padding: 2.5rem 1.5rem; }
    .hdr-corner { width: 40px; height: 40px; }
}
</style>

<!-- ============================================================
     PAGE HEADER
     ============================================================ -->
<header class="khan-page-header">
    <div class="hdr-corner hdr-corner--tl"></div>
    <div class="hdr-corner hdr-corner--tr"></div>

    <div class="container" style="position:relative;z-index:1;">
        <span class="hero-eyebrow">Belt & Rank System</span>
        <h1>Official <span>Khan</span> Curriculum</h1>
        <div class="hero-divider">
            <div class="hero-divider-line l"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-line r"></div>
        </div>
        <p>Mastery from White to Gold Mongkon — the unbroken path of progression.</p>
    </div>
</header>

<!-- ============================================================
     TIMELINE
     ============================================================ -->
<section class="khan-timeline-section">
    <div class="container">
        <div class="khan-timeline">

        <?php
        $khans = [
            1 => ["color" => "White",        "req" => ["The Origin of Muaythai", "Thai Culture & Traditions", "Benefits of Practicing Muaythai Boran", "Oath of Muaythai Boran"]],
            2 => ["color" => "Yellow",        "req" => ["Vital Points of the Body", "Arts of Punches 1", "Arts of Kicks 1", "Arts of Shoving Kicks", "Sitting Wai Kru 1 (Prom Nang)"]],
            3 => ["color" => "Yellow / White","req" => ["The Evolution of Muayboran to Muaythai", "Insight Meditation", "Arts of Knee Strikes 1", "Arts of Elbow Strikes", "Arts of Clinching", "Sitting Wai Kru 2 (For Competition)"]],
            4 => ["color" => "Green",         "req" => ["The Life of King Naresuan", "Defensive Tactics", "Counter Attack for Punch", "Counter Attack for Kick", "Counter Attack for Knee-Strikes", "Standing Wai Kru 3 (Traditional)"]],
            5 => ["color" => "Green / White", "req" => ["The Life of Phrachao Suea", "Counter Attack for Elbow Strikes", "Counter Attack for Clinch", "Arts of Counter Attacks", "Standing Wai Kru 4 (For Competition)"]],
            6 => ["color" => "Blue",          "req" => ["Trainings for Amateur Athletes", "Practicum: Full Sparring/Fight in Amateur Tournament"]],
            7 => ["color" => "Blue / White",  "req" => ["Trainings for Professional Athletes", "Practicum: Full Sparring/Fight in Professional Tournament"]],
            8 => ["color" => "Brown",         "req" => ["The Life of Phraya Pichai", "Arts of Punches Form", "Arts of Kicks Form"]],
        ];

        foreach ($khans as $id => $data):
            $align = ($id % 2 == 0) ? 'right' : 'left';
        ?>
        <div class="khan-card <?php echo $align; ?>">
            <div class="content-box">

                <div class="header-layout">
                    <div class="text-side">
                        <h3 class="khan-label">Khan <span><?php echo $id; ?></span></h3>
                        <div class="mongkon-status">
                            <div class="color-swatch-dot sw-<?php echo $id; ?>"></div>
                            <?php echo htmlspecialchars($data['color']); ?> Mongkon
                        </div>
                        <ul class="curriculum-details">
                            <?php foreach ($data['req'] as $item): ?>
                                <li><?php echo htmlspecialchars($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                     
                </div>

                <div class="card-divider"></div>

                <div class="action-square-wrapper">
                    <img src="../assets/mongkhon/<?php echo $id; ?>.png"
                         class="action-square-img"
                         alt="Khan <?php echo $id; ?> Mongkon Design">
                </div>

            </div>
        </div>
        <?php endforeach; ?>

        </div><!-- /.khan-timeline -->
    </div>
</section>

<!-- ============================================================
     CTA
     ============================================================ -->
<section class="khan-cta-section">
    <div class="container">
        <div class="khan-cta-box">
            <h2>Begin Your Journey</h2>
            <p>Start at Khan 1 and walk the path to Gold — one level at a time.</p>
            <a href="membership-benefits.php" class="btn-cta-gold">Become a Member →</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>