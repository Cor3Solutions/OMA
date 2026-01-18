<?php
$page_title = "About Us";
include '../includes/header.php';
?>

<style>
    /* Enhanced About Page Styles */
    .about-hero {
        min-height: 500px;
        background: linear-gradient(135deg, rgba(178,34,34,0.9), rgba(139,0,0,0.8)), url('../assets/images/mma.png') center / cover no-repeat;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        border-radius: 16px;
        color: #fff;
        margin-bottom: 4rem;
        padding: 4rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .about-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255,215,0,0.1) 50%, transparent 70%);
        animation: shine 3s infinite;
    }

    @keyframes shine {
        0%, 100% { transform: translateX(-100%); }
        50% { transform: translateX(100%); }
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    /* Timeline Section */
    .timeline-container {
        position: relative;
        padding: 2rem 0;
        margin: 4rem 0;
    }

    .timeline-line {
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(to bottom, var(--color-primary), #FFD700);
        transform: translateX(-50%);
    }

    .timeline-item {
        display: grid;
        grid-template-columns: 1fr 80px 1fr;
        gap: 2rem;
        margin-bottom: 3rem;
        position: relative;
    }

    .timeline-content {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .timeline-content:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(178,34,34,0.2);
    }

    .timeline-item:nth-child(even) .timeline-content:first-child {
        order: 3;
    }

    .timeline-dot {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--color-primary), #8B0000);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
        box-shadow: 0 0 0 8px rgba(178,34,34,0.2);
        z-index: 10;
        position: relative;
    }

    /* Story Grid with Parallax Effect */
    .story-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
        margin: 5rem 0;
        position: relative;
    }

    .story-image-wrapper {
        position: relative;
        aspect-ratio: 4/5;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .story-image-wrapper::before {
        content: '';
        position: absolute;
        top: -20px;
        left: -20px;
        right: -20px;
        bottom: -20px;
        background: linear-gradient(135deg, var(--color-primary), #FFD700);
        z-index: -1;
        border-radius: 16px;
    }

    .story-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .story-image-wrapper:hover .story-image {
        transform: scale(1.05);
    }

    /* Values Grid with Icons */
    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }

    .value-card {
        background: white;
        padding: 2.5rem 2rem;
        border-radius: 12px;
        text-align: center;
        transition: all 0.3s ease;
        border-top: 4px solid transparent;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    .value-card:hover {
        transform: translateY(-10px);
        border-top-color: var(--color-primary);
        box-shadow: 0 12px 35px rgba(178,34,34,0.15);
    }

    .value-icon {
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
        display: block;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    }

    /* Stats Counter */
    .stats-section {
        background: linear-gradient(135deg, var(--color-primary), #8B0000);
        border-radius: 16px;
        padding: 4rem 2rem;
        margin: 5rem 0;
        color: white;
        text-align: center;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 3rem;
        margin-top: 2rem;
    }

    .stat-item {
        position: relative;
    }

    .stat-number {
        font-size: 3.5rem;
        font-weight: bold;
        color: #FFD700;
        text-shadow: 0 4px 8px rgba(0,0,0,0.3);
        display: block;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 1.1rem;
        opacity: 0.95;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Mission Cards */
    .mission-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }

    .mission-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        border-left: 5px solid var(--color-primary);
        transition: all 0.3s ease;
    }

    .mission-card:hover {
        border-left-width: 8px;
        box-shadow: 0 8px 30px rgba(178,34,34,0.2);
        transform: translateX(5px);
    }

    /* Facilities Showcase */
    .facilities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin: 3rem 0;
    }

    .facility-item {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 2rem;
        border-radius: 12px;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .facility-item:hover {
        background: white;
        border-color: var(--color-primary);
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    .facility-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }

    /* Oath Pledge Box */
    .oath-pledge {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        color: white;
        padding: 3rem;
        border-radius: 16px;
        text-align: center;
        margin: 4rem 0;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }

    .oath-pledge::before {
        content: '🥋';
        position: absolute;
        font-size: 15rem;
        opacity: 0.05;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .oath-text {
        position: relative;
        z-index: 1;
        font-size: 1.5rem;
        font-style: italic;
        color: #FFD700;
        margin: 1.5rem 0;
    }

    /* Responsive */
    @media (max-width: 968px) {
        .story-section {
            grid-template-columns: 1fr;
            gap: 3rem;
        }
        
        .timeline-item {
            grid-template-columns: 1fr;
        }
        
        .timeline-line {
            left: 20px;
        }
        
        .timeline-dot {
            width: 60px;
            height: 60px;
            font-size: 1rem;
        }
        
        .timeline-item:nth-child(even) .timeline-content:first-child {
            order: 1;
        }
    }
    
    /* Era Cards */
    .era-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2.5rem;
        margin: 4rem 0;
    }

    .era-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        transition: all 0.4s ease;
        border-top: 5px solid var(--color-primary);
    }

    .era-card:hover {
        transform: translateY(-15px) scale(1.02);
        box-shadow: 0 20px 50px rgba(178,34,34,0.2);
    }

    .era-header {
        background: linear-gradient(135deg, var(--color-primary), #8B0000);
        padding: 2.5rem 2rem;
        text-align: center;
        color: white;
        position: relative;
    }

    .era-icon {
        font-size: 4rem;
        display: block;
        margin-bottom: 1rem;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
    }

    .era-title {
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .era-subtitle {
        font-size: 1rem;
        opacity: 0.9;
    }

    .era-content {
        padding: 2rem;
    }

    .era-highlight {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 1rem 1.5rem;
        margin: 1.5rem 0;
        border-radius: 4px;
    }
</style>

<div class="container">
    <!-- Hero Section -->
    <header class="about-hero">
        <div class="hero-content">
            <p class="section-subtitle" style="text-transform: uppercase; letter-spacing: 3px; margin-bottom: 1rem;">Our Heritage</p>
            <h1 style="font-size: clamp(2.5rem, 6vw, 4.5rem); margin-bottom: 1.5rem; text-shadow: 0 4px 15px rgba(0,0,0,0.5);">
                Oriental Muayboran Academy
            </h1>
            <p style="font-size: 1.3rem; max-width: 700px; margin: 0 auto; line-height: 1.6; opacity: 0.95;">
                Inheriting and promoting the authentic traditional warfare system of ancient Siam
            </p>
        </div>
    </header>

    <!-- Stats Section -->
    <section class="stats-section">
        <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Our Journey in Numbers</h2>
        <p style="opacity: 0.9; font-size: 1.1rem; margin-bottom: 2rem;">Building a legacy of excellence since 2007</p>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number">19</span>
                <span class="stat-label">Years of Heritage</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">2007</span>
                <span class="stat-label">Roots Established</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">2016</span>
                <span class="stat-label">OMA Founded</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">2018</span>
                <span class="stat-label">Quezon City HQ</span>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section>
        <div class="text-center" style="margin-bottom: 4rem;">
            <p class="section-subtitle">Our Evolution</p>
            <h2 class="section-title">The Path of OMA</h2>
        </div>
        
        <div class="timeline-container">
            <div class="timeline-line"></div>
            
            <div class="timeline-item">
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); margin-bottom: 1rem;">The Beginning</h3>
                    <p style="color: var(--color-text-light); line-height: 1.8;">
                        Our journey began in <strong>La Trinidad, Benguet</strong>, rooted in profound Siamese martial arts heritage. The seeds of traditional Muayboran were planted in Philippine soil.
                    </p>
                </div>
                <div class="timeline-dot">2007</div>
                <div></div>
            </div>
            
            <div class="timeline-item">
                <div></div>
                <div class="timeline-dot">2016</div>
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); margin-bottom: 1rem;">OMA is Born</h3>
                    <p style="color: var(--color-text-light); line-height: 1.8;">
                        <strong>Oriental Muayboran Academy</strong> was officially established, formalizing our commitment to preserving and teaching authentic Siamese combat arts under the lineage of Great Grandmaster Sane Tubthimtong.
                    </p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); margin-bottom: 1rem;">New Headquarters</h3>
                    <p style="color: var(--color-text-light); line-height: 1.8;">
                        Relocated to <strong>Quezon City</strong>, establishing our main training facility with state-of-the-art equipment and a dedicated space for deepening the study of Muayboran.
                    </p>
                </div>
                <div class="timeline-dot">2018</div>
                <div></div>
            </div>
            
            <div class="timeline-item">
                <div></div>
                <div class="timeline-dot">2026</div>
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); margin-bottom: 1rem;">Present Day</h3>
                    <p style="color: var(--color-text-light); line-height: 1.8;">
                        Leading the Filipino Muayboran community with <strong>Ajarn Brendaley Tarnate</strong> at the helm, carrying forward sacred teachings and building a united community of practitioners.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Story Section with Image -->
    <section class="story-section">
        <div class="story-content">
            <p class="section-subtitle">Our Lineage</p>
            <h2 style="color: var(--color-primary); margin-bottom: 1.5rem; font-size: 2.5rem;">Master & Tradition</h2>
            <p style="font-size: 1.15rem; line-height: 1.9; color: var(--color-text-light); margin-bottom: 1.5rem;">
                We carry forward the sacred teachings of <strong style="color: var(--color-primary);">Great Grandmaster Sane Tubthimtong</strong> through our founder and head instructor, <strong style="color: var(--color-primary);">Ajarn Brendaley Tarnate</strong>.
            </p>
            <p style="font-size: 1.15rem; line-height: 1.9; color: var(--color-text-light);">
                Our curriculum represents the authentic traditional warfare system of ancient Siam, preserved and passed down through generations. Every technique, form, and principle taught at OMA maintains the integrity of this profound martial heritage.
            </p>
        </div>
        <div class="story-image-wrapper">
            <img src="../assets/images/rusha.png" alt="OMA Training" class="story-image">
        </div>
    </section>

    <!-- Mission/Vision/Values CTA -->
    <section style="margin: 5rem 0;">
        <div style="background: linear-gradient(135deg, var(--color-primary), #8B0000); border-radius: 20px; padding: 4rem 3rem; text-align: center; color: white;">
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem; color: white;">Our Guiding Principles</h2>
            <p style="font-size: 1.3rem; margin-bottom: 2.5rem; opacity: 0.95; max-width: 700px; margin-left: auto; margin-right: auto; margin-bottom: 2.5rem;">
                Discover the mission, vision, and core values that guide every OMA practitioner on their martial journey
            </p>
            <a href="mvc.php" class="btn" style="background: white; color: var(--color-primary); padding: 1.2rem 3rem; font-size: 1.2rem; border-radius: 50px; text-decoration: none; display: inline-block; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: all 0.3s ease;">
                View Mission, Vision & Core Values →
            </a>
        </div>
    </section>
 
     <!-- Era Cards (Alternative Layout) -->
    <section style="margin: 5rem 0;">
        <div class="text-center" style="margin-bottom: 4rem;">
            <p class="section-subtitle">Understanding Our Art</p>
            <h2 class="section-title">The Essence of Muayboran</h2>
        </div>

        <div class="era-grid">
            <div class="era-card">
                <div class="era-header"> 
                    <h3 class="era-title">Combat System</h3>
                    <p class="era-subtitle">Complete Martial Art</p>
                </div>
                <div class="era-content">
                    <p style="line-height: 1.8; color: var(--color-text-light); margin-bottom: 1rem;">
                        A comprehensive fighting system combining:
                    </p>
                    <ul style="line-height: 2; color: var(--color-text-light); padding-left: 1.5rem;">
                        <li>Striking techniques</li>
                        <li>Grappling methods</li>
                        <li>Weapons training</li>
                        <li>Combat strategy</li>
                    </ul>
                </div>
            </div>

            <div class="era-card">
                <div class="era-header"> 
                    <h3 class="era-title">Tradition</h3>
                    <p class="era-subtitle">Authentic Preservation</p>
                </div>
                <div class="era-content">
                    <p style="line-height: 1.8; color: var(--color-text-light);">
                        We maintain the integrity of traditional forms while making the art accessible to modern
                        practitioners through structured training and proven teaching methods.
                    </p>
                </div>
            </div>

            <div class="era-card">
                <div class="era-header"> 
                    <h3 class="era-title">Philosophy</h3>
                    <p class="era-subtitle">Beyond Physical Technique</p>
                </div>
                <div class="era-content">
                    <p style="line-height: 1.8; color: var(--color-text-light);">
                        More than combat skills, Muayboran teaches discipline, respect, and the warrior spirit that has
                        guided practitioners for millennia.
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>