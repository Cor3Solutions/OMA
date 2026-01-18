<?php
$page_title = "Home";
include 'includes/header.php';

// Get active affiliates from database
$conn = getDbConnection();
$affiliates = $conn->query("SELECT * FROM affiliates WHERE status = 'active' ORDER BY display_order ASC");
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-background">
        <img src="assets/images/cover1.png" alt="Muayboran Training at Oriental Muayboran Academy">
        <div class="hero-overlay"></div>
    </div>

    <div class="hero-content">
        <h2 class="hero-subtitle">Sit Kru Sane Siamyout Philippines</h2>
        <h1 class="hero-title">Oriental Muayboran Academy</h1>
        <p class="hero-description">
            An embodiment of martial tradition and discipline.<br>
            Student of Teacher Sane – Preserving ancient Thai martial arts.
        </p>
        <div class="hero-buttons">
            <a href="pages/membership-benefits.php" class="btn btn-primary">Become a Member</a>
            <a href="pages/about.php" class="btn btn-outline">Learn More</a>
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
                       class="platform-item <?php echo $circle_class; ?>" 
                       target="_blank">
                        <?php if (!empty($affiliate['logo_path'])): ?>
                            <img src="<?php echo htmlspecialchars($affiliate['logo_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($affiliate['name']); ?>">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold;">
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
            <p class="section-subtitle">Who We Are</p>
            <h2 class="section-title">The Path of Muayboran</h2>
            <p class="section-description">
                Oriental Muay Boran Academy (OMA) is a sanctuary for ancient Siamese warfare,
                rooting its practice in the profound lineage of Great Grandmaster Sane Tubthimtong.
            </p>
        </div>

        <div class="card-grid">
            <div class="card card-heritage" data-bg-image="assets/images/mt.jpg"
                style="background-image: none; transition: background-image 0.3s ease;">
                <h3 class="card-title">Authentic Heritage</h3>
                <p class="card-description">
                    Our curriculum, developed by Ajarn Brendaley Tarnate, preserves the
                    traditional warfare systems, weaponry (Krabi Krabong), and cultural rituals.
                </p>
            </div>

            <div class="card card-khan" data-bg-image="assets/images/omaa.jpg"
                style="background-image: none; transition: background-image 0.3s ease;">
                <h3 class="card-title">The Khan System</h3>
                <p class="card-description">
                    A structured 16-level progression based on constructivist learning,
                    guiding students from fundamental mastery to international mastership.
                </p>
            </div>

            <div class="card card-mindful" data-bg-image="assets/images/mt1.jpg"
                style="background-image: none; transition: background-image 0.3s ease;">
                <h3 class="card-title">Mindful Growth</h3>
                <p class="card-description">
                    Beyond physical striking, we integrate meditation and Thai philosophy
                    to cultivate discipline, humility, and a grounded spirit.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Training Programs Section -->
<section class="section bg-light">
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
</section>

<!-- Social Media Section -->
<section class="section" style="background: #0a0a0a; padding: 6rem 0; overflow: hidden;">
    <div class="container">
        <div class="section-header text-center" style="margin-bottom: 4rem;">
            <p class="section-subtitle" style="color: #d4af37; letter-spacing: 4px; font-weight: 700; text-transform: uppercase;">Join Our Community</p>
            <h2 class="section-title" style="color: #fff; font-size: 3rem; font-weight: 800;">Stay Connected</h2>
            <div style="width: 50px; height: 2px; background: #d4af37; margin: 1.5rem auto;"></div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 3rem; align-items: flex-start;">
            
            <div style="flex: 1; min-width: 300px; color: #fff;">
                <h3 style="font-size: 1.8rem; color: #d4af37; margin-bottom: 1.5rem;">The Digital Dojo</h3>
                <p style="color: #888; line-height: 1.8; margin-bottom: 2rem; font-size: 1.1rem;">
                    Follow our daily training, seminar highlights, and technical breakdowns. 
                    Be the first to know about upcoming Khan graduactions and international workshops.
                </p>
                
                <div style="display: grid; gap: 1rem;">
                    <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #ca1313;">
                        <strong style="display: block; color: #fff;">Live Updates</strong>
                        <span style="font-size: 0.9rem; color: #666;">Real-time event coverage and academy news.</span>
                    </div>
                    <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #d4af37;">
                        <strong style="display: block; color: #fff;">Technique Clips</strong>
                        <span style="font-size: 0.9rem; color: #666;">Slow-motion breakdowns of Boran techniques.</span>
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
                    <div style="position: absolute; top: -15px; right: 20px; background: #1877F2; color: #fff; padding: 8px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: bold; z-index: 10; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                        OFFICIAL PAGE
                    </div>

                    <div class="fb-container" style="border-radius: 12px; overflow: hidden; background: #f0f2f5;">
                        <div class="fb-page" 
                             data-href="https://www.facebook.com/OrientalMuayboranAcademy" 
                             data-tabs="timeline"
                             data-width="500" 
                             data-height="600" 
                             data-small-header="false" 
                             data-adapt-container-width="true"
                             data-hide-cover="false" 
                             data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/OrientalMuayboranAcademy" class="fb-xfbml-parse-ignore">
                                <a href="https://www.facebook.com/OrientalMuayboranAcademy">Oriental Muayboran Academy</a>
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
