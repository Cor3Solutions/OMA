<?php
$page_title = "Event Gallery";
include '../includes/header.php';

$conn = getDbConnection();
// We fetch all active events, but we will handle the "Show More" via CSS/JS
$events = $conn->query("SELECT * FROM event_gallery WHERE status = 'active' ORDER BY display_order ASC, event_date DESC");
?>

<style>
    :root {
        --thai-gold: #f5af19;
        --thai-red: #ca1313;
    }

    /* Section Header Polish */
    .thai-word {
        color: var(--thai-gold);
        font-family: serif;
        font-size: 1.5rem;
        display: block;
        letter-spacing: 5px;
        margin-top: 0.5rem;
    }

    /* Vertical Timeline Track */
    .gallery-wrapper {
        position: relative;
        padding: 2rem 0;
    }

    .gallery-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 50%;
        width: 2px;
        background: linear-gradient(to bottom, transparent, rgba(245, 175, 25, 0.2) 15%, rgba(245, 175, 25, 0.2) 85%, transparent);
        transform: translateX(-50%);
    }

    .gallery-row {
        display: flex;
        align-items: center;
        gap: 4rem;
        margin-bottom: 6rem;
        position: relative;
        z-index: 1;
        transition: all 0.6s ease;
    }

    .gallery-row.reverse {
        flex-direction: row-reverse;
    }

    /* Hidden State for "Show More" */
    .event-hidden {
        display: none;
        opacity: 0;
        transform: translateY(30px);
    }

    .gallery-photo {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .gallery-col-text {
        flex: 1;
        padding: 2.5rem;
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(15px);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .event-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 1rem;
    }

    .event-desc {
        color: #aaa;
        line-height: 1.7;
        margin-bottom: 1.5rem;
    }

    /* Show More Button */
    .show-more-container {
        text-align: center;
        margin: 4rem 0;
    }

    .btn-show-more {
        background: transparent;
        color: var(--thai-gold);
        border: 2px solid var(--thai-gold);
        padding: 1rem 3rem;
        border-radius: 50px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-show-more:hover {
        background: var(--thai-gold);
        color: #000;
        box-shadow: 0 0 20px rgba(245, 175, 25, 0.4);
    }

    @media (max-width: 992px) {

        .gallery-row,
        .gallery-row.reverse {
            flex-direction: column;
            gap: 2rem;
        }

        .gallery-wrapper::before {
            left: 20px;
            transform: none;
        }
    }
</style>

<section class="section bg-dark">
    <div class="container">
        <div class="section-header text-center" style="margin-bottom: 6rem; position: relative;">
            <p class="section-subtitle" style="
        color: var(--thai-gold); 
        letter-spacing: 5px; 
        font-weight: 700; 
        text-transform: uppercase; 
        margin-bottom: 1rem;
        font-size: 1.1rem;
    ">
                CHRONICLES OF OMA
            </p>

            <h1 style="
        font-size: clamp(3rem, 8vw, 4rem); 
        font-weight: 900; 
        color: #fff; 
        line-height: 1; 
        margin: 0;
        text-transform: uppercase;
    ">
                Events
            </h1>

            <span class="thai-word" style="
        color: var(--thai-gold);
        font-family: 'Noto Sans Thai', serif;
        font-size: clamp(2rem, 5vw, 2.5rem);
        display: block;
        letter-spacing: 10px;
        margin-top: -10px;
        opacity: 0.8;
        filter: drop-shadow(0 0 15px rgba(245, 175, 25, 0.4));
    ">
                แกลเลอรี่
            </span>

            <div style="width: 80px; height: 4px; background: var(--thai-red); margin: 2rem auto; border-radius: 2px;">
            </div>
        </div>

        <div class="gallery-wrapper" id="eventGallery">
            <?php
            $count = 0;
            while ($event = $events->fetch_assoc()):
                $count++;
                $is_reverse = ($count % 2 != 0);
                $is_hidden = ($count > 5); // Hide anything after the 5th event
                $border_class = ($count % 2 == 0) ? 'border-red' : 'border-yellow';
                ?>

                <div
                    class="gallery-row <?php echo $is_reverse ? 'reverse' : ''; ?> <?php echo $is_hidden ? 'event-hidden' : ''; ?>">
                    <div class="gallery-col-img" style="flex: 1.2;">
                        <?php if (!empty($event['image_path'])): ?>
                            <img src="../<?php echo htmlspecialchars($event['image_path']); ?>"
                                alt="<?php echo htmlspecialchars($event['title']); ?>" class="gallery-photo"
                                style="border-left: 6px solid <?php echo ($count % 2 == 0) ? 'var(--thai-red)' : 'var(--thai-gold)'; ?>;">
                        <?php endif; ?>
                    </div>

                    <div class="gallery-col-text">
                        <h2 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h2>
                        <p class="event-desc"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>

                        <div
                            style="display: flex; gap: 20px; font-size: 0.85rem; border-top: 1px solid #333; padding-top: 1rem;">
                            <span style="color: var(--thai-gold);">📍
                                <?php echo htmlspecialchars($event['location'] ?? 'N/A'); ?></span>
                            <span style="color: #666;">📅
                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?></span>
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

<script>
    document.getElementById('loadMoreBtn')?.addEventListener('click', function () {
        const hiddenEvents = document.querySelectorAll('.event-hidden');

        hiddenEvents.forEach((event, index) => {
            setTimeout(() => {
                event.style.display = 'flex';
                // Trigger animation
                setTimeout(() => {
                    event.style.opacity = '1';
                    event.style.transform = 'translateY(0)';
                }, 50);
            }, index * 150); // Staggered reveal effect
        });

        // Hide the button after showing all
        this.style.display = 'none';
    });
</script>

<?php include '../includes/footer.php'; ?>