<?php
$page_title = "Event Gallery";
include '../includes/header.php';

$conn = getDbConnection();
$events = $conn->query("SELECT * FROM event_gallery WHERE status = 'active' ORDER BY display_order ASC, event_date DESC");
?>

<style>
    :root { --thai-gold: #f5af19; --thai-red: #ca1313; }

    /* --- EXISTING STYLES --- */
    .thai-word { color: var(--thai-gold); font-family: serif; font-size: 1.5rem; display: block; letter-spacing: 5px; margin-top: 0.5rem; }
    .gallery-wrapper { position: relative; padding: 2rem 0; }
    .gallery-wrapper::before { content: ''; position: absolute; top: 0; bottom: 0; left: 50%; width: 2px; background: linear-gradient(to bottom, transparent, rgba(245, 175, 25, 0.2) 15%, rgba(245, 175, 25, 0.2) 85%, transparent); transform: translateX(-50%); }
    .gallery-row { display: flex; align-items: center; gap: 4rem; margin-bottom: 6rem; position: relative; z-index: 1; transition: all 0.6s ease; }
    .gallery-row.reverse { flex-direction: row-reverse; }
    .event-hidden { display: none; opacity: 0; transform: translateY(30px); }
    .gallery-col-text { flex: 1; padding: 2.5rem; background: rgba(20, 20, 20, 0.6); backdrop-filter: blur(15px); border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.05); }
    .event-title { font-size: 2.2rem; font-weight: 800; color: #fff; margin-bottom: 1rem; }
    .event-desc { color: #aaa; line-height: 1.7; margin-bottom: 1.5rem; }
    .show-more-container { text-align: center; margin: 4rem 0; }
    .btn-show-more { background: transparent; color: var(--thai-gold); border: 2px solid var(--thai-gold); padding: 1rem 3rem; border-radius: 50px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: all 0.3s ease; }
    .btn-show-more:hover { background: var(--thai-gold); color: #000; box-shadow: 0 0 20px rgba(245, 175, 25, 0.4); }

    /* --- NEW STYLES FOR CLICKABLE IMAGES --- */
    .gallery-col-img { flex: 1.2; position: relative; cursor: pointer; transition: transform 0.3s ease; }
    .gallery-col-img:hover { transform: scale(1.02); }
    
    .gallery-photo { width: 100%; height: 400px; object-fit: cover; border-radius: 20px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 255, 255, 0.1); }
    
    /* Hover Overlay with Icon */
    .img-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.4); border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: opacity 0.3s ease;
    }
    .gallery-col-img:hover .img-overlay { opacity: 1; }
    .view-text { color: white; font-weight: bold; font-size: 1.2rem; text-transform: uppercase; letter-spacing: 2px; border: 2px solid white; padding: 10px 20px; }

    /* --- LIGHTBOX (POPUP) STYLES --- */
    .lightbox {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.95); z-index: 9999;
        display: none; align-items: center; justify-content: center;
        flex-direction: column;
    }
    .lightbox-img { max-height: 80vh; max-width: 90%; object-fit: contain; box-shadow: 0 0 50px rgba(0,0,0,0.5); }
    .lightbox-close { position: absolute; top: 30px; right: 40px; color: white; font-size: 3rem; cursor: pointer; }
    .lightbox-nav {
        position: absolute; top: 50%; transform: translateY(-50%);
        color: white; font-size: 3rem; cursor: pointer; padding: 20px;
        background: rgba(0,0,0,0.2); transition: 0.3s;
    }
    .lightbox-nav:hover { background: rgba(245, 175, 25, 0.5); }
    .nav-left { left: 20px; } .nav-right { right: 20px; }
    .lightbox-counter { color: #888; margin-top: 15px; font-size: 0.9rem; letter-spacing: 2px; }

    @media (max-width: 992px) {
        .gallery-row, .gallery-row.reverse { flex-direction: column; gap: 2rem; }
        .gallery-wrapper::before { left: 20px; transform: none; }
        .gallery-photo { height: 300px; }
    }
</style>

<section class="section bg-dark">
    <div class="container">
        <div class="section-header text-center" style="margin-bottom: 6rem;">
            <p class="section-subtitle" style="color: var(--thai-gold); letter-spacing: 5px; font-weight: 700; text-transform: uppercase; margin-bottom: 1rem;">CHRONICLES OF OMA</p>
            <h1 style="font-size: clamp(3rem, 8vw, 4rem); font-weight: 900; color: #fff; margin: 0; text-transform: uppercase;">Events</h1>
            <span class="thai-word">แกลเลอรี่</span>
            <div style="width: 80px; height: 4px; background: var(--thai-red); margin: 2rem auto; border-radius: 2px;"></div>
        </div>

        <div class="gallery-wrapper" id="eventGallery">
            <?php
            $count = 0;
            while ($event = $events->fetch_assoc()):
                $count++;
                $is_reverse = ($count % 2 != 0);
                $is_hidden = ($count > 5);
                
                // 1. GET ALL PHOTOS FOR THIS EVENT
                $eid = $event['id'];
                
                // Start with the main cover image
                $photo_list = [];
                if(!empty($event['image_path'])) {
                    $photo_list[] = $event['image_path'];
                }

                // Fetch extra photos from the database table we made earlier
                $extras = $conn->query("SELECT image_path FROM event_photos WHERE event_id = $eid");
                while($p = $extras->fetch_assoc()){
                    $photo_list[] = $p['image_path'];
                }

                // Encode to JSON so JS can read it
                $json_photos = htmlspecialchars(json_encode($photo_list), ENT_QUOTES, 'UTF-8');
                $total_photos = count($photo_list);
            ?>

                <div class="gallery-row <?php echo $is_reverse ? 'reverse' : ''; ?> <?php echo $is_hidden ? 'event-hidden' : ''; ?>">
                    
                    <div class="gallery-col-img" onclick='openLightbox(<?php echo $json_photos; ?>)'>
                        <?php if (!empty($event['image_path'])): ?>
                            <img src="../<?php echo htmlspecialchars($event['image_path']); ?>" 
                                 class="gallery-photo"
                                 style="border-left: 6px solid <?php echo ($count % 2 == 0) ? 'var(--thai-red)' : 'var(--thai-gold)'; ?>;">
                            
                            <div class="img-overlay">
                                <span class="view-text">
                                    <i class="fas fa-images"></i> View Gallery (<?php echo $total_photos; ?>)
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="gallery-col-text">
                        <h2 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h2>
                        <p class="event-desc"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        <div style="display: flex; gap: 20px; font-size: 0.85rem; border-top: 1px solid #333; padding-top: 1rem;">
                            <span style="color: var(--thai-gold);">📍 <?php echo htmlspecialchars($event['location'] ?? 'N/A'); ?></span>
                            <span style="color: #666;">📅 <?php echo date('M d, Y', strtotime($event['event_date'])); ?></span>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
        </div>

        <?php if ($count > 5): ?>
            <div class="show-more-container">
                <button class="btn-show-more" id="loadMoreBtn">Show Older Events</button>
            </div>
        <?php endif; ?>
    </div>
</section>

<div id="lightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    
    <div class="lightbox-nav nav-left" onclick="changeSlide(-1)">&#10094;</div>
    <div class="lightbox-nav nav-right" onclick="changeSlide(1)">&#10095;</div>
    
    <img id="lightbox-img" class="lightbox-img" src="">
    <div id="lightbox-counter" class="lightbox-counter">1 / 5</div>
</div>

<script>
    // --- LOAD MORE FUNCTION ---
    document.getElementById('loadMoreBtn')?.addEventListener('click', function () {
        const hiddenEvents = document.querySelectorAll('.event-hidden');
        hiddenEvents.forEach((event, index) => {
            setTimeout(() => {
                event.style.display = 'flex';
                setTimeout(() => { event.style.opacity = '1'; event.style.transform = 'translateY(0)'; }, 50);
            }, index * 150);
        });
        this.style.display = 'none';
    });

    // --- LIGHTBOX FUNCTIONS ---
    let currentPhotos = [];
    let currentIndex = 0;
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const counter = document.getElementById('lightbox-counter');

    function openLightbox(photosArray) {
        if(photosArray.length === 0) return;

        currentPhotos = photosArray;
        currentIndex = 0;
        
        // Show lightbox
        lightbox.style.display = 'flex';
        updateLightboxImage();
        document.body.style.overflow = 'hidden'; // Stop background scrolling
    }

    function closeLightbox() {
        lightbox.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
    }

    function changeSlide(direction) {
        currentIndex += direction;
        
        // Loop around
        if (currentIndex >= currentPhotos.length) currentIndex = 0;
        if (currentIndex < 0) currentIndex = currentPhotos.length - 1;
        
        updateLightboxImage();
    }

    function updateLightboxImage() {
        // We assume photos are stored relative to root (e.g. assets/uploads...)
        // We add "../" because this file is likely in a subfolder like /pages/
        // Adjust the path prefix if your images break!
        lightboxImg.src = "../" + currentPhotos[currentIndex];
        counter.innerText = (currentIndex + 1) + " / " + currentPhotos.length;
    }

    // Close on background click
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) closeLightbox();
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (lightbox.style.display === 'flex') {
            if (e.key === 'ArrowLeft') changeSlide(-1);
            if (e.key === 'ArrowRight') changeSlide(1);
            if (e.key === 'Escape') closeLightbox();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>