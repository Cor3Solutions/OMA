<?php
$page_title = "Membership Benefits";
include '../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="section-header text-center" style="
    min-height: 300px;
    background:
      linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.35)),
      url('../assets/images/mma.png')
 center / cover no-repeat;
    padding: 2rem 2rem;
    border-radius: 12px;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
">
            <br><br><br>
            <h1 class="hero-title"
                style="color:#ffffff; text-shadow: 0 4px 8px rgba(202, 19, 19, 0.6); margin-bottom: 0.5rem;">
                MEMBER BENEFITS
            </h1>
            <p class="section-description" style="margin-bottom: 0;">
                Elevate Your Training </p>
        </div>
         

        <section class="khan-system-section py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <p class="section-subtitle">Structured Progression</p>
                    <h2 class="section-title white-text">The Khan System</h2>
                </div>

                <div class="card-grid progression-grid">
                    <div class="card khan-card">
                        <div class="khan-number">01</div>
                        <h3>Constructivist Learning</h3>
                        <p>Mastery through core fundamentals, elevating to complex application and traditional wisdom.
                        </p>
                    </div>
                    <div class="card khan-card featured">
                        <div class="khan-number yellow">02</div>
                        <h3>Nakmuay (Khan 1-10)</h3>
                        <p>Ten levels for students to ensure technical proficiency and discipline before promotion.</p>
                    </div>
                    <div class="card khan-card">
                        <div class="khan-number">03</div>
                        <h3>Mastership (Khan 11-16)</h3>
                        <p>Advanced instructor and mastership certification led by certified Kru lineage.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section main-benefits">
            <div class="container">
                <div class="card-grid">
                    <div class="card benefit-card">
                        <h3 class="card-title">Certified Training</h3>
                        <p class="card-description">Access to authentic Muayboran instruction from the Sit Kru Sane
                            lineage.</p>
                    </div>

                    <div class="card benefit-card">
                        <h3 class="card-title">Official Certification</h3>
                        <p class="card-description">Earn internationally recognized Khan levels and certificates
                            respected globally.</p>
                    </div>


                    <div class="card benefit-card">
                        <h3 class="card-title">Exclusive Materials</h3>
                        <p class="card-description">Access comprehensive training videos, curriculum guides, and
                            historical docs.</p>
                    </div>
                </div>

                <br>
                <div class="cta-banner text-center">
                    <h2 class="white-text">Experience All These Benefits</h2>
                    <p class="mb-4">Join Oriental Muayboran Academy and start your journey today.</p>
                    <div class="cta-buttons">
                        <a href="contact.php" class="btn btn-red-glow">Become a Member</a>
                     </div>
                </div>
            </div>
        </section>

        <?php include '../includes/footer.php'; ?>