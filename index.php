<?php
$page_title = "Home";
include 'includes/header.php';

// Get active affiliates from database
$conn = getDbConnection();
$affiliates = $conn->query("SELECT * FROM affiliates WHERE status = 'active' ORDER BY display_order ASC");
?>

<!-- ============================================================
     RESPONSIVE STYLES
     ============================================================ -->
<style>
/* ---- Hero Section ---- */
.hero-section {
    position: relative;
    overflow: hidden;
    min-height: 80vh;
    display: flex;
    align-items: center;
    background: #000;
}

.hero-background {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    z-index: 1;
}

.hero-background img {
    width: 100%; height: 100%;
    object-fit: cover;
    opacity: 0.5;
}

.hero-overlay {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(0,0,0,0.4), rgba(0,0,0,0.8));
}

.hero-content {
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 60px 0;
}

.hero-inner {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    justify-content: center;
    gap: 60px;
    width: 95%;
    max-width: 1600px;
    margin: 0 auto;
}

/* Flag columns */
.hero-flag-col {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.hero-flag-frame {
    width: 140px;
    height: 280px;
    border: 3px solid #D4AF37;
    padding: 5px;
    background: rgba(212, 175, 55, 0.1);
    box-shadow: 0 0 25px rgba(212, 175, 55, 0.3);
}

.hero-flag-frame img {
    width: 100%; height: 100%;
    object-fit: cover;
    filter: brightness(1.1);
}

.hero-flag-label {
    color: #D4AF37;
    margin-top: 10px;
    font-weight: bold;
    letter-spacing: 2px;
    font-size: 0.9rem;
}

/* Center text column */
.hero-text-col {
    text-align: center;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.hero-subtitle {
    color: #D4AF37;
    text-transform: uppercase;
    letter-spacing: 5px;
    margin-bottom: 10px;
    font-size: 1.2rem;
}

.hero-title {
    margin: 0;
    font-size: 4.5rem;
    color: #fff;
    text-transform: uppercase;
    line-height: 1;
    text-shadow: 0 5px 15px rgba(0,0,0,1);
}

.hero-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 25px 0;
}

.hero-divider-line {
    height: 1px;
    width: 100px;
}

.hero-divider-line.left  { background: linear-gradient(to left,  #D4AF37, transparent); }
.hero-divider-line.right { background: linear-gradient(to right, #D4AF37, transparent); }

.hero-divider-text {
    color: #fff;
    padding: 0 15px;
    font-style: italic;
    letter-spacing: 1px;
}

.hero-desc {
    margin: 0 auto 40px;
    font-size: 1.3rem;
    color: #ddd;
    max-width: 700px;
    line-height: 1.6;
}

.hero-buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.hero-btn-primary {
    background: #D4AF37;
    color: #000;
    padding: 18px 40px;
    text-decoration: none;
    font-weight: bold;
    text-transform: uppercase;
    border-radius: 2px;
    transition: 0.3s;
    white-space: nowrap;
}

.hero-btn-secondary {
    border: 2px solid #fff;
    color: #fff;
    padding: 16px 40px;
    text-decoration: none;
    font-weight: bold;
    text-transform: uppercase;
    border-radius: 2px;
    transition: 0.3s;
    white-space: nowrap;
}

/* ---- Social Section ---- */
.social-inner {
    display: flex;
    flex-wrap: wrap;
    gap: 3rem;
    align-items: flex-start;
}

.social-text-col {
    flex: 1;
    min-width: 300px;
    color: #fff;
}

.social-fb-col {
    flex: 1.2;
    min-width: 320px;
    width: 100%;
    box-sizing: border-box;
}

/* FB embed responsive fix */
.fb-card-frame {
    width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}

.fb-responsive-wrap {
    border-radius: 12px;
    overflow: hidden;
    background: #f0f2f5;
    width: 100%;
}

/* Force FB iframe to never overflow */
.fb-responsive-wrap iframe,
.fb-responsive-wrap span {
    max-width: 100% !important;
}

/* ---- Responsive: Tablet (≤ 900px) ---- */
@media (max-width: 900px) {
    .hero-inner {
        gap: 30px;
    }

    .hero-title {
        font-size: 3rem;
    }

    .hero-flag-frame {
        width: 100px;
        height: 200px;
    }

    .hero-desc {
        font-size: 1.1rem;
    }
}

/* ---- Responsive: Mobile (≤ 640px) ---- */
@media (max-width: 640px) {
    .hero-section {
        min-height: auto;
    }

    .hero-content {
        padding: 40px 0;
    }

    .hero-inner {
        flex-direction: column;
        align-items: center;
        gap: 24px;
        width: 92%;
    }

    /* Show flags side by side above/below title on mobile */
    .hero-flags-row {
        display: flex;
        flex-direction: row;
        justify-content: center;
        gap: 24px;
        width: 100%;
    }

    .hero-flag-col {
        /* flags stack into a row via .hero-flags-row */
    }

    .hero-flag-frame {
        width: 70px;
        height: 140px;
    }

    .hero-flag-label {
        font-size: 0.7rem;
        letter-spacing: 1px;
    }

    .hero-subtitle {
        font-size: 0.85rem;
        letter-spacing: 3px;
    }

    .hero-title {
        font-size: 2rem;
        line-height: 1.1;
    }

    .hero-divider-line {
        width: 50px;
    }

    .hero-divider-text {
        font-size: 0.85rem;
        padding: 0 8px;
    }

    .hero-desc {
        font-size: 1rem;
        margin-bottom: 28px;
    }

    .hero-btn-primary,
    .hero-btn-secondary {
        padding: 14px 24px;
        font-size: 0.9rem;
        width: 100%;
        text-align: center;
    }

    .hero-buttons {
        flex-direction: column;
        gap: 12px;
        width: 100%;
        padding: 0 20px;
        box-sizing: border-box;
    }

    /* Social section */
    .social-text-col,
    .social-fb-col {
        min-width: unset;
        width: 100%;
    }
}
</style>

<section class="hero-section">
    <div class="hero-background">
        <img src="assets/images/cover1.png" alt="Muayboran Training">
        <div class="hero-overlay"></div>
    </div>

    <div class="hero-content">
        <div class="hero-inner">

            <!-- On desktop: flags are siblings of the text col.
                 On mobile: wrapped into .hero-flags-row via JS reorder trick below. -->
            <div class="hero-flag-col" id="flag-ph">
                <div class="hero-flag-frame">
                    <img src="assets/images/flag-ph.png" alt="PH Flag">
                </div>
                <div class="hero-flag-label">PILIPINAS</div>
            </div>

            <div class="hero-text-col">
                <h2 class="hero-subtitle">Traditional Martial Arts</h2>
                <h1 class="hero-title">Oriental <span style="color: #D4AF37;">Muayboran</span> Academy</h1>

                <div class="hero-divider">
                    <div class="hero-divider-line left"></div>
                    <div class="hero-divider-text">Sit Kru Sane Siamyout</div>
                    <div class="hero-divider-line right"></div>
                </div>

                <p class="hero-desc">
                    An embodiment of martial tradition and discipline. Preserving the ancient Thai arts under the lineage of Teacher Sane.
                </p>

                <div class="hero-buttons">
                    <a href="pages/membership-benefits.php" class="hero-btn-primary">Become a Member</a>
                    <a href="pages/about.php" class="hero-btn-secondary">Learn More</a>
                </div>
            </div>

            <div class="hero-flag-col" id="flag-th">
                <div class="hero-flag-frame">
                    <img src="assets/images/flag-thai.png" alt="TH Flag">
                </div>
                <div class="hero-flag-label">THAILAND</div>
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
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:bold;">
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
                <a href="pages/course.php" class="btn btn-outline" style="margin-top: 1rem;">View Syllabus</a>
            </div>

            <div class="card">
                <h3 class="card-title">Kru (Instructor)</h3>
                <p class="card-description">
                    Levels Khan 11–16. Advanced mastership training for those
                    called to teach and preserve the Sit Kru Sane lineage.
                </p>
                <a href="pages/course.php" class="btn btn-outline" style="margin-top: 1rem;">Instructor Path</a>
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
<section class="section bg-light">
    <div class="container">
        <div class="section-header text-center" style="margin-bottom: 4rem;">
            <p class="section-subtitle" style="color: #d4af37; letter-spacing: 4px; font-weight: 700; text-transform: uppercase;">Join Our Community</p>
            <h2 class="section-title" style="color: #fff; font-size: 3rem; font-weight: 800;">Stay Connected</h2>
            <div style="width: 50px; height: 2px; background: #d4af37; margin: 1.5rem auto;"></div>
        </div>

        <div class="social-inner">

            <div class="social-text-col">
                <h3 style="font-size: 1.8rem; color: #d4af37; margin-bottom: 1.5rem;">Digital Overview</h3>
                <p style="color: #fff; line-height: 1.8; margin-bottom: 2rem; font-size: 1.1rem;">
                    Follow our daily training, seminar highlights, and technical breakdowns.
                    Be the first to know about upcoming Khan graduations and international workshops.
                </p>

                <div style="display: grid; gap: 1rem;">
                    <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #ca1313;">
                        <strong style="display: block; color: #fff;">Live Updates</strong>
                        <span style="font-size: 0.9rem; color: #fff;">Real-time event coverage and academy news.</span>
                    </div>
                    <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #d4af37;">
                        <strong style="display: block; color: #fff;">Technique Clips</strong>
                        <span style="font-size: 0.9rem; color: #fff;">Slow-motion breakdowns of Boran techniques.</span>
                    </div>
                </div>
            </div>

            <div class="social-fb-col">
                <div class="fb-card-frame" style="background:#fff; padding:10px; border-radius:20px; box-shadow:0 20px 50px rgba(0,0,0,0.5), 0 0 20px rgba(212,175,55,0.1); position:relative; overflow:hidden; width:100%; box-sizing:border-box;">
                    <div style="position:absolute; top:-15px; right:20px; background:#1877F2; color:#fff; padding:8px 15px; border-radius:50px; font-size:0.8rem; font-weight:bold; z-index:10; box-shadow:0 4px 10px rgba(0,0,0,0.3);">
                        OFFICIAL PAGE
                    </div>

                    <!-- Wrapper div — FB SDK reads THIS element's rendered width -->
                    <div id="fb-page-wrapper" class="fb-responsive-wrap">
                        <div class="fb-page"
                            data-href="https://www.facebook.com/OrientalMuayboranAcademy"
                            data-tabs="timeline"
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
    // On mobile, group both flag columns into a single flex row
    function rearrangeFlags() {
        var inner = document.querySelector('.hero-inner');
        var flagPh = document.getElementById('flag-ph');
        var flagTh = document.getElementById('flag-th');
        var textCol = document.querySelector('.hero-text-col');
        var flagsRow = document.getElementById('hero-flags-row');

        if (window.innerWidth <= 640) {
            if (!flagsRow) {
                flagsRow = document.createElement('div');
                flagsRow.id = 'hero-flags-row';
                flagsRow.className = 'hero-flags-row';
                flagsRow.appendChild(flagPh);
                flagsRow.appendChild(flagTh);
                inner.insertBefore(flagsRow, textCol);
            }
        } else {
            if (flagsRow) {
                inner.insertBefore(flagPh, flagsRow);
                inner.appendChild(flagTh);
                flagsRow.remove();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', rearrangeFlags);
    window.addEventListener('resize', rearrangeFlags);

    // Re-render Facebook embed so it reads actual container width (fixes mobile overflow)
    setTimeout(function () {
        if (window.FB && window.FB.XFBML) {
            window.FB.XFBML.parse(document.getElementById('fb-page-wrapper'));
        }
    }, 800);

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