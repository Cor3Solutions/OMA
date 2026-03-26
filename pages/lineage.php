<?php
$page_title = "Lineage";
$extra_head = '<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">';
include '../includes/header.php';

$conn = getDbConnection();

$khan_colors = [
    1  => ['color'=>'#FFFFFF',  'border'=>'#ccc',    'text'=>'#000', 'name'=>'White Khan'],
    2  => ['color'=>'#FFEB3B',  'border'=>'#f0c800', 'text'=>'#000', 'name'=>'Yellow Khan'],
    3  => ['color'=>'#FFEB3B',  'border'=>'#f0c800', 'text'=>'#000', 'name'=>'Yellow-White'],
    4  => ['color'=>'#4CAF50',  'border'=>'#388e3c', 'text'=>'#fff', 'name'=>'Green Khan'],
    5  => ['color'=>'#4CAF50',  'border'=>'#388e3c', 'text'=>'#fff', 'name'=>'Green-White'],
    6  => ['color'=>'#2196F3',  'border'=>'#1565c0', 'text'=>'#fff', 'name'=>'Blue Khan'],
    7  => ['color'=>'#2196F3',  'border'=>'#1565c0', 'text'=>'#fff', 'name'=>'Blue-White'],
    8  => ['color'=>'#795548',  'border'=>'#4e342e', 'text'=>'#fff', 'name'=>'Brown Khan'],
    9  => ['color'=>'#795548',  'border'=>'#4e342e', 'text'=>'#fff', 'name'=>'Brown-White'],
    10 => ['color'=>'#D32F2F',  'border'=>'#8b0000', 'text'=>'#fff', 'name'=>'Red Khan'],
    11 => ['color'=>'#D32F2F',  'border'=>'#8b0000', 'text'=>'#fff', 'name'=>'Red-White'],
    12 => ['color'=>'#D32F2F',  'border'=>'#8b0000', 'text'=>'#fff', 'name'=>'Red-Yellow'],
    13 => ['color'=>'#D32F2F',  'border'=>'#8b0000', 'text'=>'#fff', 'name'=>'Red-Silver'],
    14 => ['color'=>'#C0C0C0',  'border'=>'#9e9e9e', 'text'=>'#000', 'name'=>'Silver Khan'],
    15 => ['color'=>'#C0C0C0',  'border'=>'#9e9e9e', 'text'=>'#000', 'name'=>'Silver-Gold'],
    16 => ['color'=>'#FFD700',  'border'=>'#c9a84c', 'text'=>'#000', 'name'=>'Gold Khan'],
];

$instructors_result = $conn->query("
    SELECT i.*, km.id AS member_id
    FROM instructors i
    LEFT JOIN users u         ON i.user_id = u.id
    LEFT JOIN khan_members km ON km.user_id = u.id
    WHERE i.status = 'active'
    ORDER BY i.display_order ASC
");

$instructors = [];
while ($row = $instructors_result->fetch_assoc()) {
    $mid = (int)$row['member_id'];
    $row['journey'] = [];
    if ($mid > 0) {
        $jq = $conn->query("
            SELECT khan_level, certified_date, location
            FROM khan_training_history
            WHERE member_id = $mid AND status = 'certified'
            ORDER BY khan_level ASC
        ");
        if ($jq) {
            while ($j = $jq->fetch_assoc()) $row['journey'][] = $j;
        }
    }
    $instructors[] = $row;
}
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
.lineage-hero {
    position: relative;
    min-height: 65vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--black);
}
.lineage-hero-bg { position: absolute; inset: 0; }
.lineage-hero-bg img {
    width: 100%; height: 100%;
    object-fit: cover;
    opacity: 0.35;
    transform: scale(1.04);
    transition: transform 14s ease;
}
.lineage-hero:hover .lineage-hero-bg img { transform: scale(1.0); }
.lineage-hero-overlay {
    position: absolute; inset: 0;
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

.lineage-hero-content {
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
.lineage-hero-content h1 {
    font-family: var(--font-display);
    font-size: 3.8rem; font-weight: 900;
    color: var(--white); text-transform: uppercase;
    letter-spacing: 3px; line-height: 1.05;
    margin: 0 0 24px;
    text-shadow: 0 4px 30px rgba(0,0,0,0.9);
}
.lineage-hero-content h1 span { color: var(--gold); }

.hero-divider {
    display: flex; align-items: center; justify-content: center;
    gap: 14px; margin: 0 auto 24px; max-width: 440px;
}
.hero-divider-line { flex: 1; height: 1px; }
.hero-divider-line.l { background: linear-gradient(to left,  var(--gold), transparent); }
.hero-divider-line.r { background: linear-gradient(to right, var(--gold), transparent); }
.hero-divider-diamond { width: 7px; height: 7px; background: var(--gold); transform: rotate(45deg); flex-shrink: 0; }

.lineage-hero-content p {
    font-family: var(--font-body);
    font-size: 1.25rem; color: rgba(255,255,255,0.78);
    max-width: 520px; margin: 0 auto;
    line-height: 1.75; font-weight: 300; font-style: italic;
}

.hero-scroll {
    position: absolute; bottom: 28px; left: 50%;
    transform: translateX(-50%);
    z-index: 3; display: flex; flex-direction: column;
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
   LINEAGE MASTERS SECTION
   ============================================================ */
.masters-section { background: var(--dark); padding: 100px 0; }

.master-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 64px;
    align-items: center;
    max-width: 1000px;
    margin: 0 auto 100px;
    opacity: 0;
    transform: translateY(28px);
    animation: rowFade 0.8s ease forwards;
}
.master-row:last-child { margin-bottom: 0; }
.master-row:nth-child(1) { animation-delay: 0.1s; }
.master-row:nth-child(2) { animation-delay: 0.3s; }
@keyframes rowFade { to { opacity: 1; transform: translateY(0); } }

/* Reverse column order for second master */
.master-row--reverse .master-photo-col { order: 2; }
.master-row--reverse .master-text-col  { order: 1; }

/* Photo */
.master-photo-wrap {
    position: relative;
}
.master-photo-border {
    position: absolute;
    inset: -10px;
    border: 1px solid rgba(212,175,55,0.25);
    border-radius: 4px;
    pointer-events: none;
    z-index: 0;
}
.master-photo-accent {
    position: absolute;
    bottom: -16px; right: -16px;
    width: 80px; height: 80px;
    border-bottom: 2px solid var(--gold);
    border-right: 2px solid var(--gold);
    opacity: 0.5;
    z-index: 0;
}
.master-photo-accent-tl {
    position: absolute;
    top: -16px; left: -16px;
    width: 80px; height: 80px;
    border-top: 2px solid var(--gold);
    border-left: 2px solid var(--gold);
    opacity: 0.5;
    z-index: 0;
}
.master-photo-wrap img {
    position: relative; z-index: 1;
    width: 100%; height: 360px;
    object-fit: cover;
    border-radius: 2px;
    display: block;
    filter: brightness(0.95) contrast(1.05);
    transition: filter 0.4s;
}
.master-photo-wrap:hover img { filter: brightness(1) contrast(1.08); }

/* Connector line between the two masters */
.lineage-connector {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
    margin: 0 auto 60px;
    opacity: 0.7;
}
.lineage-connector-line { width: 1px; height: 48px; background: linear-gradient(to bottom, transparent, var(--gold), transparent); }
.lineage-connector-label {
    font-family: var(--font-ui);
    font-size: 0.62rem;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: var(--gold);
    background: var(--dark);
    padding: 4px 14px;
    border: 1px solid rgba(212,175,55,0.25);
    border-radius: 2px;
    white-space: nowrap;
}
.lineage-connector-line2 { width: 1px; height: 48px; background: linear-gradient(to bottom, var(--gold), transparent); }

/* Text column */
.master-rank-tag {
    display: inline-block;
    font-family: var(--font-ui);
    font-size: 0.68rem; font-weight: 700;
    letter-spacing: 4px; text-transform: uppercase;
    color: var(--gold);
    border: 1px solid rgba(212,175,55,0.3);
    background: rgba(212,175,55,0.06);
    padding: 5px 14px; border-radius: 2px;
    margin-bottom: 18px;
}
.master-text-col h2 {
    font-family: var(--font-display);
    font-size: 2rem; font-weight: 700;
    color: var(--white); letter-spacing: 1px;
    text-transform: uppercase; margin: 0 0 20px;
    line-height: 1.15;
}
.master-text-col h2 span { color: var(--gold); }
.master-text-col p {
    font-family: var(--font-body);
    font-size: 1.15rem; color: var(--muted);
    line-height: 1.85; margin: 0;
    font-weight: 300;
}

/* Gold divider under name */
.master-name-rule {
    width: 36px; height: 1px;
    background: var(--gold);
    margin: 0 0 20px;
    opacity: 0.6;
}

/* ============================================================
   INSTRUCTORS SECTION
   ============================================================ */
.instructors-section { background: var(--mid); padding: 100px 0; }

.instructors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 28px;
    margin-top: 1rem;
}

.instructor-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: 4px;
    padding: 2.2rem 1.5rem 1.8rem;
    text-align: center;
    transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
    position: relative;
    overflow: hidden;
    opacity: 0;
    transform: translateY(20px);
    animation: cardFade 0.6s ease forwards;
}
.instructor-card:nth-child(1) { animation-delay: 0.05s; }
.instructor-card:nth-child(2) { animation-delay: 0.12s; }
.instructor-card:nth-child(3) { animation-delay: 0.19s; }
.instructor-card:nth-child(4) { animation-delay: 0.26s; }
.instructor-card:nth-child(5) { animation-delay: 0.33s; }
.instructor-card:nth-child(6) { animation-delay: 0.40s; }
@keyframes cardFade { to { opacity: 1; transform: translateY(0); } }

.instructor-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(to right, var(--red), var(--gold));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
}
.instructor-card:hover {
    border-color: rgba(212,175,55,0.35);
    box-shadow: 0 16px 48px rgba(0,0,0,0.5), 0 0 20px rgba(212,175,55,0.06);
    transform: translateY(-6px);
}
.instructor-card:hover::before { transform: scaleX(1); }

/* Avatar */
.instructor-avatar-wrap {
    position: relative;
    width: 110px; height: 110px;
    margin: 0 auto 1.4rem;
}
.instructor-avatar-ring {
    position: absolute;
    inset: -5px;
    border-radius: 50%;
    border: 1px solid rgba(212,175,55,0.3);
    transition: border-color 0.3s;
}
.instructor-card:hover .instructor-avatar-ring { border-color: rgba(212,175,55,0.7); }

.instructor-avatar-ring-outer {
    position: absolute;
    inset: -11px;
    border-radius: 50%;
    border: 1px dashed rgba(212,175,55,0.12);
    animation: spinSlow 18s linear infinite;
}
@keyframes spinSlow { to { transform: rotate(360deg); } }

.instructor-avatar {
    width: 110px; height: 110px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    position: relative; z-index: 1;
    border: 2px solid rgba(212,175,55,0.25);
    transition: border-color 0.3s;
}
.instructor-card:hover .instructor-avatar { border-color: var(--gold); }

.instructor-avatar-initials {
    width: 110px; height: 110px;
    border-radius: 50%;
    background: rgba(212,175,55,0.08);
    border: 2px solid rgba(212,175,55,0.25);
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-display);
    font-size: 2.4rem; font-weight: 700;
    color: var(--gold);
    position: relative; z-index: 1;
    transition: border-color 0.3s, background 0.3s;
}
.instructor-card:hover .instructor-avatar-initials { border-color: var(--gold); background: rgba(212,175,55,0.12); }

/* Info */
.instructor-name {
    font-family: var(--font-display);
    font-size: 1rem; font-weight: 700;
    color: var(--white); letter-spacing: 1px;
    text-transform: uppercase; margin: 0 0 6px;
    line-height: 1.2;
}
.instructor-khan {
    font-family: var(--font-ui);
    font-size: 0.72rem; font-weight: 700;
    letter-spacing: 3px; text-transform: uppercase;
    color: var(--gold); margin-bottom: 4px;
}
.instructor-title {
    font-family: var(--font-body);
    font-size: 0.95rem; font-style: italic;
    color: var(--muted); margin-bottom: 4px;
}
.instructor-location {
    font-family: var(--font-ui);
    font-size: 0.7rem; letter-spacing: 1px;
    color: rgba(255,255,255,0.38);
    text-transform: uppercase; margin-bottom: 4px;
}
.instructor-spec {
    font-family: var(--font-body);
    font-size: 0.88rem; font-style: italic;
    color: rgba(255,255,255,0.35); margin-top: 2px;
}

/* Khan Journey */
.kru-journey { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(212,175,55,0.08); }
.kru-journey-label {
    font-family: var(--font-ui);
    font-size: 0.6rem; letter-spacing: 3px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.3);
    margin-bottom: 8px;
}
.kru-journey-strip {
    display: flex; flex-wrap: wrap;
    justify-content: center; align-items: center;
    gap: 3px;
}
.kru-journey-dot {
    position: relative;
    width: 22px; height: 22px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.58rem; font-weight: 700;
    font-family: var(--font-ui);
    cursor: default;
    transition: transform 0.15s, box-shadow 0.15s;
}
.kru-journey-dot:hover { transform: scale(1.35); z-index: 5; box-shadow: 0 0 8px rgba(0,0,0,0.5); }
.kru-journey-dot::after {
    content: attr(data-tip);
    position: absolute;
    bottom: calc(100% + 6px);
    left: 50%; transform: translateX(-50%);
    background: rgba(0,0,0,0.9);
    color: #fff;
    font-family: var(--font-ui);
    font-size: 0.6rem; white-space: nowrap;
    padding: 4px 8px; border-radius: 2px;
    pointer-events: none; opacity: 0;
    transition: opacity 0.15s; z-index: 10;
    border: 1px solid rgba(212,175,55,0.2);
}
.kru-journey-dot:hover::after { opacity: 1; }
.kru-journey-arrow { font-size: 0.5rem; color: rgba(255,255,255,0.2); line-height: 1; }
.kru-journey-none  { font-family: var(--font-body); font-size: 0.8rem; color: rgba(255,255,255,0.25); font-style: italic; }

/* ============================================================
   CTA SECTION
   ============================================================ */
.lineage-cta-section {
    background: linear-gradient(135deg, #0d0d0d 0%, #1a0a0a 50%, #0d0d0d 100%);
    border-top: 1px solid rgba(202,19,19,0.2);
    padding: 100px 0;
}
.lineage-cta-box {
    max-width: 700px; margin: 0 auto;
    text-align: center;
    border: 1px solid rgba(212,175,55,0.18);
    border-radius: 4px; padding: 5rem 3rem;
    position: relative; overflow: hidden;
}
.lineage-cta-box::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at center, rgba(212,175,55,0.04) 0%, transparent 70%);
}
.lineage-cta-box h2 {
    font-family: var(--font-display);
    font-size: 2rem; color: var(--white);
    letter-spacing: 2px; text-transform: uppercase;
    margin: 0 0 1.2rem; position: relative;
}
.lineage-cta-box p {
    font-family: var(--font-body);
    font-size: 1.2rem; color: var(--muted);
    max-width: 480px; margin: 0 auto 2.5rem;
    line-height: 1.75; font-style: italic;
    position: relative;
}
.btn-cta-gold {
    font-family: var(--font-ui);
    font-weight: 700; font-size: 0.85rem;
    letter-spacing: 3px; text-transform: uppercase;
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
    .master-row { grid-template-columns: 1fr; gap: 36px; }
    .master-row--reverse .master-photo-col,
    .master-row--reverse .master-text-col  { order: unset; }
    .master-photo-wrap img { height: 280px; }
    .lineage-hero-content h1 { font-size: 2.8rem; }
    .section-title { font-size: 2rem; }
}
@media (max-width: 640px) {
    .lineage-hero-content h1 { font-size: 1.9rem; }
    .hero-corner { width: 48px; height: 48px; }
    .section-title { font-size: 1.7rem; }
    .lineage-cta-box { padding: 2.5rem 1.5rem; }
    .master-text-col h2 { font-size: 1.6rem; }
}
</style>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="lineage-hero">
    <div class="lineage-hero-bg">
        <img src="../assets/images/mt.jpg" alt="Martial Lineage">
    </div>
    <div class="lineage-hero-overlay"></div>

    <div class="hero-corner hero-corner--tl"></div>
    <div class="hero-corner hero-corner--tr"></div>
    <div class="hero-corner hero-corner--bl"></div>
    <div class="hero-corner hero-corner--br"></div>

    <div class="lineage-hero-content">
        <span class="hero-eyebrow">Our Heritage</span>
        <h1>Martial <span>Lineage</span></h1>
        <div class="hero-divider">
            <div class="hero-divider-line l"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-line r"></div>
        </div>
        <p>The unbroken chain of masters preserving ancient Siamese warfare traditions.</p>
    </div>

    <div class="hero-scroll">
        <span>Scroll</span>
        <div class="hero-scroll-line"></div>
    </div>
</section>

<!-- ============================================================
     LINEAGE MASTERS
     ============================================================ -->
<section class="masters-section">
    <div class="container">

        <div class="section-header">
            <span class="section-subtitle">The Unbroken Chain</span>
            <h2 class="section-title">Masters of the Lineage</h2>
            <div class="gold-rule"></div>
        </div>

        <!-- Grandmaster -->
        <div class="master-row">
            <div class="master-photo-col">
                <div class="master-photo-wrap">
                    <div class="master-photo-border"></div>
                    <div class="master-photo-accent-tl"></div>
                    <div class="master-photo-accent"></div>
                    <img src="../assets/images/1.png" alt="Grandmaster Sane Tubtimtong">
                </div>
            </div>
            <div class="master-text-col">
                <span class="master-rank-tag">Great Grandmaster</span>
                <h2>Sane <span>Tubtimtong</span></h2>
                <div class="master-name-rule"></div>
                <p>
                    The foundation of our lineage rests upon Grandmaster Sane Tubtimtong, a legendary figure in the
                    world of Muay Boran. His teachings form the cornerstone of our curriculum, passed down through
                    generations of dedicated practitioners — a living testament to the ancient warfare arts of Siam.
                </p>
            </div>
        </div>

        <!-- Connector -->
        <div class="lineage-connector">
            <div class="lineage-connector-line"></div>
            <div class="lineage-connector-label">Direct Lineage</div>
            <div class="lineage-connector-line2"></div>
        </div>

        <!-- Ajarn -->
        <div class="master-row master-row--reverse">
            <div class="master-photo-col">
                <div class="master-photo-wrap">
                    <div class="master-photo-border"></div>
                    <div class="master-photo-accent-tl"></div>
                    <div class="master-photo-accent"></div>
                    <img src="../assets/images/2.png" alt="Ajarn Brendaley Tarnate">
                </div>
            </div>
            <div class="master-text-col">
                <span class="master-rank-tag">Ajarn</span>
                <h2>Brendaley <span>Tarnate</span></h2>
                <div class="master-name-rule"></div>
                <p>
                    As the direct student of Grandmaster Sane, Ajarn Brendaley Tarnate carries forward the authentic
                    techniques and philosophy. His meticulous documentation and teaching methodology ensure the
                    preservation of traditional knowledge for future generations of practitioners worldwide.
                </p>
            </div>
        </div>

    </div>
</section>

<!-- ============================================================
     KRU INSTRUCTORS
     ============================================================ -->
<section class="instructors-section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Guardians of the Tradition</span>
            <h2 class="section-title">Our Kru (Instructors)</h2>
            <div class="gold-rule"></div>
        </div>

        <div class="instructors-grid">
            <?php foreach ($instructors as $instructor): ?>
            <div class="instructor-card">

                <div class="instructor-avatar-wrap">
                    <div class="instructor-avatar-ring-outer"></div>
                    <div class="instructor-avatar-ring"></div>
                    <?php if (!empty($instructor['photo_path'])): ?>
                        <img class="instructor-avatar"
                             src="../<?php echo htmlspecialchars($instructor['photo_path']); ?>"
                             alt="<?php echo htmlspecialchars($instructor['name']); ?>">
                    <?php else: ?>
                        <div class="instructor-avatar-initials">
                            <?php echo strtoupper(substr($instructor['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="instructor-name"><?php echo htmlspecialchars($instructor['name']); ?></div>

                <?php if (!empty($instructor['khan_level'])): ?>
                    <div class="instructor-khan"><?php echo htmlspecialchars($instructor['khan_level']); ?></div>
                <?php endif; ?>

                <?php if (!empty($instructor['title'])): ?>
                    <div class="instructor-title"><?php echo htmlspecialchars($instructor['title']); ?></div>
                <?php endif; ?>

                <?php if (!empty($instructor['location'])): ?>
                    <div class="instructor-location">📍 <?php echo htmlspecialchars($instructor['location']); ?></div>
                <?php endif; ?>

                <?php if (!empty($instructor['specialization'])): ?>
                    <div class="instructor-spec"><?php echo htmlspecialchars($instructor['specialization']); ?></div>
                <?php endif; ?>

                <!-- Khan Journey -->
                <div class="kru-journey">
                    <div class="kru-journey-label">Khan Journey</div>
                    <?php if (empty($instructor['journey'])): ?>
                        <span class="kru-journey-none">—</span>
                    <?php else: ?>
                        <div class="kru-journey-strip">
                            <?php foreach ($instructor['journey'] as $i => $step):
                                $kl  = (int)$step['khan_level'];
                                $ci  = $khan_colors[$kl] ?? ['color'=>'#ccc','border'=>'#aaa','text'=>'#000','name'=>'Khan '.$kl];
                                $yr  = (!empty($step['certified_date']) && $step['certified_date'] !== '0000-00-00')
                                       ? date('Y', strtotime($step['certified_date'])) : '';
                                $tip = 'Khan '.$kl.' · '.$ci['name'].($yr ? ' · '.$yr : '');
                            ?>
                            <?php if ($i > 0): ?><span class="kru-journey-arrow">›</span><?php endif; ?>
                            <div class="kru-journey-dot"
                                 style="background:<?= $ci['color'] ?>;border:2px solid <?= $ci['border'] ?>;color:<?= $ci['text'] ?>;"
                                 data-tip="<?= htmlspecialchars($tip) ?>">
                                <?= $kl ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     CTA
     ============================================================ -->
<section class="lineage-cta-section">
    <div class="container">
        <div class="lineage-cta-box">
            <h2>Join the Lineage</h2>
            <p>Become part of this living tradition — carry the ancient art forward.</p>
            <a href="membership-benefits.php" class="btn-cta-gold">Begin Your Journey →</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>