<?php
$page_title = "Home";
include 'includes/header.php';

// Get active affiliates from database
$conn = getDbConnection();
$affiliates = $conn->query("SELECT * FROM affiliates WHERE status = 'active' ORDER BY display_order ASC");
?>

<section class="hero-section" style="position: relative; overflow: hidden; min-height: 80vh; display: flex; align-items: center; background: #000;">
    <div class="hero-background" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;">
        <img src="assets/images/cover1.png" alt="Muayboran Training" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.5;">
        <div class="hero-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(0,0,0,0.4), rgba(0,0,0,0.8));"></div>
    </div>

    <div class="hero-content" style="position: relative; z-index: 2; width: 100%; padding: 60px 0;">
        <div style="display: flex; flex-direction: row; align-items: stretch; justify-content: center; gap: 60px; width: 95%; max-width: 1600px; margin: 0 auto;">
            
            <div style="flex-shrink: 0; display: flex; flex-direction: column; align-items: center;">
                <div style="width: 140px; height: 280px; border: 3px solid #D4AF37; padding: 5px; background: rgba(212, 175, 55, 0.1); box-shadow: 0 0 25px rgba(212, 175, 55, 0.3);">
                    <img src="assets/images/flag-ph.png" alt="PH Flag" style="width: 100%; height: 100%; object-fit: cover; filter: brightness(1.1);">
                </div>
                <div style="color: #D4AF37; margin-top: 10px; font-weight: bold; letter-spacing: 2px; font-size: 0.9rem;">PILIPINAS</div>
            </div>

            <div style="text-align: center; flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
                <h2 style="color: #D4AF37; text-transform: uppercase; letter-spacing: 5px; margin-bottom: 10px; font-size: 1.2rem;">Traditional Martial Arts</h2>
                <h1 style="margin: 0; font-size: 4.5rem; color: #fff; text-transform: uppercase; line-height: 1; text-shadow: 0 5px 15px rgba(0,0,0,1);">Oriental <span style="color: #D4AF37;">Muayboran</span> Academy</h1>
                
                <div style="display: flex; align-items: center; justify-content: center; margin: 25px 0;">
                    <div style="height: 1px; width: 100px; background: linear-gradient(to left, #D4AF37, transparent);"></div>
                    <div style="color: #fff; padding: 0 15px; font-style: italic; letter-spacing: 1px;">Sit Kru Sane Siamyout</div>
                    <div style="height: 1px; width: 100px; background: linear-gradient(to right, #D4AF37, transparent);"></div>
                </div>

                <p style="margin: 0 auto 40px; font-size: 1.3rem; color: #ddd; max-width: 700px; line-height: 1.6;">
                    An embodiment of martial tradition and discipline. Preserving the ancient Thai arts under the lineage of Teacher Sane.
                </p>
                
                <div class="hero-buttons" style="display: flex; justify-content: center; gap: 20px;">
                    <a href="pages/membership-benefits.php" style="background: #D4AF37; color: #000; padding: 18px 40px; text-decoration: none; font-weight: bold; text-transform: uppercase; border-radius: 2px; transition: 0.3s;">Become a Member</a>
                    <a href="pages/about.php" style="border: 2px solid #fff; color: #fff; padding: 16px 40px; text-decoration: none; font-weight: bold; text-transform: uppercase; border-radius: 2px; transition: 0.3s;">Learn More</a>
                </div>
            </div>

            <div style="flex-shrink: 0; display: flex; flex-direction: column; align-items: center;">
                <div style="width: 140px; height: 280px; border: 3px solid #D4AF37; padding: 5px; background: rgba(212, 175, 55, 0.1); box-shadow: 0 0 25px rgba(212, 175, 55, 0.3);">
                    <img src="assets/images/flag-thai.png" alt="TH Flag" style="width: 100%; height: 100%; object-fit: cover; filter: brightness(1.1);">
                </div>
                <div style="color: #D4AF37; margin-top: 10px; font-weight: bold; letter-spacing: 2px; font-size: 0.9rem;">THAILAND</div>
            </div>

        </div>
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
                            <div
                                style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold;">
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

<!-- About Section -->
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
                <a href="pages/course.php" class="btn btn-outline" style="margin-top: 1rem;">
                    View Syllabus
                </a>
            </div>

            <div class="card">
                <h3 class="card-title">Kru (Instructor)</h3>
                <p class="card-description">
                    Levels Khan 11–16. Advanced mastership training for those
                    called to teach and preserve the Sit Kru Sane lineage.
                </p>
                <a href="pages/course.php" class="btn btn-outline" style="margin-top: 1rem;">
                    Instructor Path
                </a>
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
    </div>
</section>


<!-- Social Media Section -->
<section class="section bg-light">
    <div class="container">
        <div class="section-header text-center" style="margin-bottom: 4rem;">
            <p class="section-subtitle"
                style="color: #d4af37; letter-spacing: 4px; font-weight: 700; text-transform: uppercase;">Join Our
                Community</p>
            <h2 class="section-title" style="color: #fff; font-size: 3rem; font-weight: 800;">Stay Connected</h2>
            <div style="width: 50px; height: 2px; background: #d4af37; margin: 1.5rem auto;"></div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 3rem; align-items: flex-start;">

            <div style="flex: 1; min-width: 300px; color: #fff;">
                <h3 style="font-size: 1.8rem; color: #d4af37; margin-bottom: 1.5rem;">Digital Overview</h3>
                <p style="color: #fff; line-height: 1.8; margin-bottom: 2rem; font-size: 1.1rem;">
                    Follow our daily training, seminar highlights, and technical breakdowns.
                    Be the first to know about upcoming Khan graduactions and international workshops.
                </p>

                <div style="display: grid; gap: 1rem;">
                    <div
                        style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #ca1313;">
                        <strong style="display: block; color: #fff;">Live Updates</strong>
                        <span style="font-size: 0.9rem; color: #fff;">Real-time event coverage and academy news.</span>
                    </div>
                    <div
                        style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #d4af37;">
                        <strong style="display: block; color: #fff;">Technique Clips</strong>
                        <span style="font-size: 0.9rem; color: #fff;">Slow-motion breakdowns of Boran techniques.</span>
                    </div>
                </div>
            </div>

            <div style="flex: 1.2; min-width: 320px;">
                <div class="fb-card-frame" style="
                    background: #fff;
                    padding: 10px;
                    border-radius: 20px;
                    box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 20px rgba(212, 175, 55, 0.1);
                    position: relative;
                ">
                    <div
                        style="position: absolute; top: -15px; right: 20px; background: #1877F2; color: #fff; padding: 8px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: bold; z-index: 10; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                        OFFICIAL PAGE
                    </div>

                    <div class="fb-container" style="border-radius: 12px; overflow: hidden; background: #f0f2f5;">
                        <div class="fb-page" data-href="https://www.facebook.com/OrientalMuayboranAcademy"
                            data-tabs="timeline" data-width="500" data-height="600" data-small-header="false"
                            data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/OrientalMuayboranAcademy"
                                class="fb-xfbml-parse-ignore">
                                <a href="https://www.facebook.com/OrientalMuayboranAcademy">Oriental Muayboran
                                    Academy</a>
                            </blockquote>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Contact CTA Section -->
<section class="section contact-cta-bg">
    <div class="container">
        <div class="section-header text-center">
            <p class="section-subtitle">GET IN TOUCH</p>
            <h4 class="section-title white-text">
                <span class="thai-word">คำถาม?</span> (Questions?)
            </h4>
            <p class="section-description">
                We're here to help you begin or continue your Muayboran journey.
                Reach out to learn more about our programs.
            </p>
        </div>

        <div class="cta-actions text-center">
            <a href="pages/contact.php" class="btn btn-red-glow">Contact Us</a>
            <a href="tel:+639605667175" class="btn btn-outline-yellow">Call Now</a>
        </div>
    </div>
</section>

<script>
    // Enhanced card hover effects with background images
    document.addEventListener('DOMContentLoaded', function () {
        const cards = document.querySelectorAll('.card[data-bg-image]');

        cards.forEach(card => {
            const bgImage = card.getAttribute('data-bg-image');

            card.addEventListener('mouseenter', function () {
                this.style.backgroundImage = `url(${bgImage})`;
                this.style.backgroundSize = 'cover';
                this.style.backgroundPosition = 'center';
            });

            card.addEventListener('mouseleave', function () {
                this.style.backgroundImage = 'none';
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>