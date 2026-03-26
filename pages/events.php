<?php
$page_title = "Event Gallery";
$extra_head = '<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">';
include '../includes/header.php';

$conn = getDbConnection();
$events = $conn->query("SELECT * FROM event_gallery WHERE status = 'active' ORDER BY display_order ASC, event_date DESC");
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
    --border-gold:  rgba(212,175,55,0.15);
    --font-display: 'Cinzel', serif;
    --font-body:    'Cormorant Garamond', serif;
    --font-ui:      'Rajdhani', sans-serif;
    --ease:         0.3s cubic-bezier(0.4,0,0.2,1);
}

body { background: var(--dark); color: var(--white); }

.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}

/* ============================================================
   HERO / PAGE HEADER
   ============================================================ */
.gallery-hero {
    position: relative;
    background: var(--black);
    padding: 100px 0 80px;
    text-align: center;
    overflow: hidden;
    border-bottom: 1px solid var(--border-gold);
}
.gallery-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 70% 60% at 60% 120%, rgba(202,19,19,0.12) 0%, transparent 65%),
        radial-gradient(ellipse 50% 40% at 30% -10%,  rgba(212,175,55,0.06) 0%, transparent 60%);
    pointer-events: none;
}

.hero-corner {
    position: absolute;
    width: 72px; height: 72px;
    z-index: 3; opacity: 0.5;
}
.hero-corner--tl { top: 24px; left: 24px;    border-top: 2px solid var(--gold); border-left:  2px solid var(--gold); }
.hero-corner--tr { top: 24px; right: 24px;   border-top: 2px solid var(--gold); border-right: 2px solid var(--gold); }
.hero-corner--bl { bottom: 24px; left: 24px;  border-bottom: 2px solid var(--gold); border-left:  2px solid var(--gold); }
.hero-corner--br { bottom: 24px; right: 24px; border-bottom: 2px solid var(--gold); border-right: 2px solid var(--gold); }

.gallery-hero-content {
    position: relative; z-index: 1;
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
.gallery-hero h1 {
    font-family: var(--font-display);
    font-size: 3.8rem; font-weight: 900;
    color: var(--white); text-transform: uppercase;
    letter-spacing: 3px; line-height: 1.05;
    margin: 0 0 12px;
    text-shadow: 0 4px 30px rgba(0,0,0,0.9);
}
.gallery-hero h1 span { color: var(--gold); }

.thai-subtitle {
    display: block;
    font-family: var(--font-body);
    font-size: 1.4rem;
    font-style: italic;
    color: rgba(212,175,55,0.6);
    letter-spacing: 6px;
    margin-bottom: 24px;
}

.hero-divider {
    display: flex; align-items: center; justify-content: center;
    gap: 14px; margin: 0 auto; max-width: 440px;
}
.hero-divider-line      { flex: 1; height: 1px; }
.hero-divider-line.l    { background: linear-gradient(to left,  var(--gold), transparent); }
.hero-divider-line.r    { background: linear-gradient(to right, var(--gold), transparent); }
.hero-divider-diamond   { width: 7px; height: 7px; background: var(--gold); transform: rotate(45deg); flex-shrink: 0; }

/* ============================================================
   GALLERY SECTION
   ============================================================ */
.gallery-section {
    background: var(--dark);
    padding: 80px 0 100px;
}

/* Central timeline line */
.gallery-wrapper {
    position: relative;
    padding: 2rem 0;
}
.gallery-wrapper::before {
    content: '';
    position: absolute;
    top: 0; bottom: 0;
    left: 50%;
    width: 1px;
    transform: translateX(-50%);
    background: linear-gradient(to bottom,
        transparent,
        rgba(212,175,55,0.4) 8%,
        var(--gold) 50%,
        rgba(212,175,55,0.4) 92%,
        transparent);
    box-shadow: 0 0 10px rgba(212,175,55,0.15);
}

/* Row */
.gallery-row {
    display: flex;
    align-items: center;
    gap: 56px;
    margin-bottom: 80px;
    position: relative;
    z-index: 1;
    opacity: 0;
    transform: translateY(28px);
    animation: rowUp 0.7s ease forwards;
}
.gallery-row:nth-child(1)  { animation-delay: 0.05s; }
.gallery-row:nth-child(2)  { animation-delay: 0.15s; }
.gallery-row:nth-child(3)  { animation-delay: 0.25s; }
.gallery-row:nth-child(4)  { animation-delay: 0.35s; }
.gallery-row:nth-child(5)  { animation-delay: 0.45s; }
@keyframes rowUp { to { opacity: 1; transform: translateY(0); } }

.gallery-row.reverse { flex-direction: row-reverse; }

/* Hidden events (load more) */
.event-hidden {
    display: none;
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}

/* Timeline node dot */
.gallery-row::after {
    content: '';
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 12px; height: 12px;
    border-radius: 50%;
    background: var(--dark);
    border: 2px solid var(--gold);
    box-shadow: 0 0 10px rgba(212,175,55,0.4);
    z-index: 2;
    transition: background 0.3s, transform 0.3s;
}
.gallery-row:hover::after { background: var(--gold); transform: translate(-50%, -50%) scale(1.4); }

/* ---- Image column ---- */
.gallery-col-img {
    flex: 1.1;
    position: relative;
    cursor: pointer;
}
.gallery-photo-wrap {
    position: relative;
    overflow: hidden;
    border-radius: 4px;
    border: 1px solid var(--border-gold);
    box-shadow: 0 16px 48px rgba(0,0,0,0.5);
    transition: border-color var(--ease), box-shadow var(--ease);
}
.gallery-col-img:hover .gallery-photo-wrap {
    border-color: rgba(212,175,55,0.4);
    box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 30px rgba(212,175,55,0.1);
}

/* Accent corner lines on photos */
.gallery-photo-wrap::before,
.gallery-photo-wrap::after {
    content: '';
    position: absolute;
    width: 28px; height: 28px;
    z-index: 2;
    opacity: 0;
    transition: opacity var(--ease);
}
.gallery-photo-wrap::before { top: 8px; left: 8px;  border-top: 2px solid var(--gold); border-left:  2px solid var(--gold); }
.gallery-photo-wrap::after  { bottom: 8px; right: 8px; border-bottom: 2px solid var(--gold); border-right: 2px solid var(--gold); }
.gallery-col-img:hover .gallery-photo-wrap::before,
.gallery-col-img:hover .gallery-photo-wrap::after { opacity: 1; }

.gallery-photo {
    width: 100%; height: 360px;
    object-fit: cover;
    display: block;
    filter: brightness(0.88) contrast(1.05);
    transition: transform 0.5s ease, filter 0.4s ease;
}
.gallery-col-img:hover .gallery-photo { transform: scale(1.04); filter: brightness(0.95) contrast(1.08); }

/* Hover overlay */
.img-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity var(--ease);
    z-index: 1;
}
.gallery-col-img:hover .img-overlay { opacity: 1; }
.view-text {
    font-family: var(--font-ui);
    font-size: 0.8rem; font-weight: 700;
    letter-spacing: 3px; text-transform: uppercase;
    color: var(--white);
    border: 1px solid rgba(212,175,55,0.6);
    background: rgba(212,175,55,0.12);
    padding: 10px 22px;
    border-radius: 2px;
    display: flex; align-items: center; gap: 8px;
}

/* Photo count badge */
.photo-count-badge {
    position: absolute;
    bottom: 12px; right: 12px;
    z-index: 2;
    font-family: var(--font-ui);
    font-size: 0.65rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    background: rgba(0,0,0,0.7);
    border: 1px solid rgba(212,175,55,0.35);
    color: var(--gold);
    padding: 4px 12px; border-radius: 2px;
    backdrop-filter: blur(4px);
}

/* Left accent bar — alternates gold/red */
.accent-left  { border-left: 3px solid var(--gold); }
.accent-right { border-left: 3px solid var(--red); }

/* ---- Text column ---- */
.gallery-col-text {
    flex: 1;
    background: rgba(255,255,255,0.025);
    border: 1px solid var(--border-gold);
    border-radius: 4px;
    padding: 2.4rem 2.2rem;
    position: relative;
    transition: border-color var(--ease), box-shadow var(--ease);
}
.gallery-col-text::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(to right, var(--red), var(--gold));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
}
.gallery-row:hover .gallery-col-text { border-color: rgba(212,175,55,0.3); box-shadow: 0 12px 40px rgba(0,0,0,0.4); }
.gallery-row:hover .gallery-col-text::before { transform: scaleX(1); }

.event-date-tag {
    display: inline-flex;
    align-items: center; gap: 7px;
    font-family: var(--font-ui);
    font-size: 0.65rem; font-weight: 700;
    letter-spacing: 3px; text-transform: uppercase;
    color: var(--gold);
    background: rgba(212,175,55,0.07);
    border: 1px solid rgba(212,175,55,0.2);
    padding: 4px 12px; border-radius: 2px;
    margin-bottom: 16px;
}

.event-title {
    font-family: var(--font-display);
    font-size: 1.5rem; font-weight: 700;
    color: var(--white); letter-spacing: 1px;
    text-transform: uppercase;
    margin: 0 0 14px; line-height: 1.2;
}

.event-desc {
    font-family: var(--font-body);
    color: var(--muted);
    font-size: 1.1rem; line-height: 1.8;
    margin: 0 0 20px;
}

.event-meta {
    display: flex; gap: 20px; flex-wrap: wrap;
    padding-top: 16px;
    border-top: 1px solid rgba(212,175,55,0.1);
}
.event-meta-item {
    display: flex; align-items: center; gap: 7px;
    font-family: var(--font-ui);
    font-size: 0.72rem; letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.4);
}
.event-meta-item i { color: rgba(212,175,55,0.5); font-size: 0.65rem; }

/* ============================================================
   LOAD MORE
   ============================================================ */
.show-more-container { text-align: center; margin: 3rem 0 0; }
.btn-show-more {
    font-family: var(--font-ui);
    font-weight: 700; font-size: 0.8rem;
    letter-spacing: 3px; text-transform: uppercase;
    background: transparent;
    color: var(--gold);
    border: 1px solid rgba(212,175,55,0.4);
    padding: 16px 44px; border-radius: 2px;
    cursor: pointer;
    transition: background var(--ease), border-color var(--ease), box-shadow var(--ease), transform 0.2s;
}
.btn-show-more:hover {
    background: rgba(212,175,55,0.1);
    border-color: var(--gold);
    box-shadow: 0 0 24px rgba(212,175,55,0.2);
    transform: translateY(-2px);
}

/* ============================================================
   LIGHTBOX
   ============================================================ */
.lightbox {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.96);
    z-index: 9999;
    display: none;
    align-items: center; justify-content: center;
    flex-direction: column;
    backdrop-filter: blur(8px);
}

.lightbox-close {
    position: absolute; top: 28px; right: 36px;
    width: 40px; height: 40px;
    border-radius: 2px;
    border: 1px solid rgba(212,175,55,0.3);
    background: rgba(212,175,55,0.06);
    color: var(--gold);
    font-size: 1.2rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background var(--ease), border-color var(--ease);
    font-family: sans-serif;
    line-height: 1;
}
.lightbox-close:hover { background: rgba(212,175,55,0.15); border-color: var(--gold); }

.lightbox-img {
    max-height: 78vh; max-width: 88%;
    object-fit: contain;
    border: 1px solid var(--border-gold);
    border-radius: 2px;
    box-shadow: 0 0 60px rgba(0,0,0,0.8);
}

.lightbox-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 48px; height: 48px;
    border-radius: 2px;
    border: 1px solid rgba(212,175,55,0.25);
    background: rgba(212,175,55,0.06);
    color: var(--gold);
    font-size: 1.2rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background var(--ease), border-color var(--ease);
}
.lightbox-nav:hover { background: rgba(212,175,55,0.15); border-color: var(--gold); }
.nav-left  { left: 24px; }
.nav-right { right: 24px; }

.lightbox-counter {
    font-family: var(--font-ui);
    font-size: 0.65rem; letter-spacing: 4px;
    text-transform: uppercase;
    color: rgba(212,175,55,0.5);
    margin-top: 18px;
}

/* Lightbox caption */
.lightbox-caption {
    font-family: var(--font-body);
    font-size: 1rem; font-style: italic;
    color: var(--muted);
    margin-top: 8px;
    letter-spacing: 1px;
}

/* ============================================================
   CTA SECTION
   ============================================================ */
.gallery-cta-section {
    background: linear-gradient(135deg, #0d0d0d 0%, #1a0a0a 50%, #0d0d0d 100%);
    border-top: 1px solid rgba(202,19,19,0.2);
    padding: 90px 0;
}
.gallery-cta-box {
    max-width: 700px; margin: 0 auto;
    text-align: center;
    border: 1px solid rgba(212,175,55,0.18);
    border-radius: 4px; padding: 4.5rem 3rem;
    position: relative; overflow: hidden;
}
.gallery-cta-box::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at center, rgba(212,175,55,0.04) 0%, transparent 70%);
}
.gallery-cta-box h2 { font-family: var(--font-display); font-size: 1.9rem; color: var(--white); letter-spacing: 2px; text-transform: uppercase; margin: 0 0 1rem; position: relative; }
.gallery-cta-box p  { font-family: var(--font-body); font-size: 1.15rem; color: var(--muted); max-width: 440px; margin: 0 auto 2.5rem; line-height: 1.75; font-style: italic; position: relative; }
.btn-cta-gold {
    font-family: var(--font-ui); font-weight: 700;
    font-size: 0.82rem; letter-spacing: 3px; text-transform: uppercase;
    background: var(--gold); color: #000;
    padding: 16px 44px; text-decoration: none;
    border-radius: 2px; display: inline-block;
    transition: background 0.25s, box-shadow 0.25s, transform 0.2s;
    box-shadow: 0 4px 20px rgba(212,175,55,0.3);
    position: relative;
}
.btn-cta-gold:hover { background: var(--gold-light); box-shadow: 0 6px 28px rgba(212,175,55,0.5); transform: translateY(-2px); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 900px) {
    .gallery-hero h1 { font-size: 2.6rem; }
    .gallery-wrapper::before { left: 20px; transform: none; }
    .gallery-row,
    .gallery-row.reverse { flex-direction: column; gap: 24px; }
    .gallery-row::after { left: 20px; top: 0; transform: translate(-50%, 0); }
    .gallery-photo { height: 280px; }
}
@media (max-width: 640px) {
    .gallery-hero h1 { font-size: 1.9rem; }
    .hero-corner { width: 48px; height: 48px; }
    .gallery-photo { height: 220px; }
    .gallery-col-text { padding: 1.6rem 1.4rem; }
    .gallery-cta-box { padding: 2.5rem 1.5rem; }
    .lightbox-nav { width: 36px; height: 36px; font-size: 1rem; }
    .lightbox-nav.nav-left  { left: 8px; }
    .lightbox-nav.nav-right { right: 8px; }
}
</style>

<!-- ============================================================
     HERO
     ============================================================ -->
<header class="gallery-hero">
    <div class="hero-corner hero-corner--tl"></div>
    <div class="hero-corner hero-corner--tr"></div>
    <div class="hero-corner hero-corner--bl"></div>
    <div class="hero-corner hero-corner--br"></div>

    <div class="gallery-hero-content">
        <span class="hero-eyebrow">Chronicles of OMA</span>
        <h1>Event <span>Gallery</span></h1>
        <span class="thai-subtitle">แกลเลอรี่</span>
        <div class="hero-divider">
            <div class="hero-divider-line l"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-line r"></div>
        </div>
    </div>
</header>

<!-- ============================================================
     GALLERY
     ============================================================ -->
<section class="gallery-section">
    <div class="container">
        <div class="gallery-wrapper" id="eventGallery">
            <?php
            $count = 0;
            while ($event = $events->fetch_assoc()):
                $count++;
                $is_reverse = ($count % 2 !== 0);
                $is_hidden  = ($count > 5);

                $eid        = $event['id'];
                $photo_list = [];
                if (!empty($event['image_path'])) $photo_list[] = $event['image_path'];

                $extras = $conn->query("SELECT image_path FROM event_photos WHERE event_id = $eid");
                while ($p = $extras->fetch_assoc()) $photo_list[] = $p['image_path'];

                $json_photos  = htmlspecialchars(json_encode($photo_list), ENT_QUOTES, 'UTF-8');
                $total_photos = count($photo_list);
                $accent_class = ($count % 2 === 0) ? 'accent-right' : 'accent-left';
            ?>

            <div class="gallery-row <?php echo $is_reverse ? 'reverse' : ''; ?> <?php echo $is_hidden ? 'event-hidden' : ''; ?>">

                <!-- Image -->
                <div class="gallery-col-img" onclick='openLightbox(<?php echo $json_photos; ?>, <?php echo htmlspecialchars(json_encode($event['title']), ENT_QUOTES); ?>)'>
                    <?php if (!empty($event['image_path'])): ?>
                    <div class="gallery-photo-wrap <?php echo $accent_class; ?>">
                        <img src="../<?php echo htmlspecialchars($event['image_path']); ?>"
                             class="gallery-photo"
                             alt="<?php echo htmlspecialchars($event['title']); ?>">
                        <div class="img-overlay">
                            <span class="view-text">
                                <i class="fas fa-images"></i> View Gallery
                            </span>
                        </div>
                        <?php if ($total_photos > 1): ?>
                        <div class="photo-count-badge"><?php echo $total_photos; ?> Photos</div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Text -->
                <div class="gallery-col-text">
                    <div class="event-date-tag">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                    </div>
                    <h2 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h2>
                    <p class="event-desc"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    <div class="event-meta">
                        <?php if (!empty($event['location'])): ?>
                        <div class="event-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($event['location']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($total_photos > 0): ?>
                        <div class="event-meta-item">
                            <i class="fas fa-images"></i>
                            <?php echo $total_photos; ?> Photo<?php echo $total_photos !== 1 ? 's' : ''; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <?php endwhile; ?>
        </div><!-- /.gallery-wrapper -->

        <?php if ($count > 5): ?>
        <div class="show-more-container">
            <button class="btn-show-more" id="loadMoreBtn">
                <i class="fas fa-chevron-down" style="margin-right:8px;font-size:0.7rem;"></i>Show Older Events
            </button>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- ============================================================
     CTA
     ============================================================ -->
<section class="gallery-cta-section">
    <div class="container">
        <div class="gallery-cta-box">
            <h2>Be Part of the Story</h2>
            <p>Join OMA and become part of the living tradition we chronicle here.</p>
            <a href="membership-benefits.php" class="btn-cta-gold">Become a Member →</a>
        </div>
    </div>
</section>

<!-- ============================================================
     LIGHTBOX
     ============================================================ -->
<div id="lightbox" class="lightbox">
    <button class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>

    <button class="lightbox-nav nav-left"  onclick="changeSlide(-1)"><i class="fas fa-chevron-left"></i></button>
    <button class="lightbox-nav nav-right" onclick="changeSlide(1)"><i class="fas fa-chevron-right"></i></button>

    <img id="lightbox-img" class="lightbox-img" src="" alt="">
    <div id="lightbox-counter" class="lightbox-counter">1 / 1</div>
    <div id="lightbox-caption" class="lightbox-caption"></div>
</div>

<script>
/* ---- Load More ---- */
document.getElementById('loadMoreBtn')?.addEventListener('click', function () {
    const hiddenEvents = document.querySelectorAll('.event-hidden');
    hiddenEvents.forEach((ev, i) => {
        setTimeout(() => {
            ev.style.display = 'flex';
            setTimeout(() => { ev.style.opacity = '1'; ev.style.transform = 'translateY(0)'; }, 50);
        }, i * 150);
    });
    this.parentElement.style.display = 'none';
});

/* ---- Lightbox ---- */
let currentPhotos  = [];
let currentIndex   = 0;
let currentCaption = '';
const lightbox      = document.getElementById('lightbox');
const lightboxImg   = document.getElementById('lightbox-img');
const counter       = document.getElementById('lightbox-counter');
const captionEl     = document.getElementById('lightbox-caption');

function openLightbox(photosArray, caption) {
    if (!photosArray.length) return;
    currentPhotos  = photosArray;
    currentIndex   = 0;
    currentCaption = caption || '';
    lightbox.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    updateLightboxImage();
}

function closeLightbox() {
    lightbox.style.display = 'none';
    document.body.style.overflow = '';
}

function changeSlide(dir) {
    currentIndex = (currentIndex + dir + currentPhotos.length) % currentPhotos.length;
    updateLightboxImage();
}

function updateLightboxImage() {
    lightboxImg.src = '../' + currentPhotos[currentIndex];
    lightboxImg.alt = currentCaption;
    counter.textContent = (currentIndex + 1) + ' / ' + currentPhotos.length;
    captionEl.textContent = currentPhotos.length > 1 ? currentCaption : '';
}

lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });

document.addEventListener('keydown', e => {
    if (lightbox.style.display === 'flex') {
        if (e.key === 'ArrowLeft')  changeSlide(-1);
        if (e.key === 'ArrowRight') changeSlide(1);
        if (e.key === 'Escape')     closeLightbox();
    }
});
</script>

<?php include '../includes/footer.php'; ?>