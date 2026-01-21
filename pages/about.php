<?php
$page_title = "About Us";
include '../includes/header.php';
?>

<style>
    /* Enhanced About Page Styles */
    .about-hero {
        min-height: 500px;
        background: url('../assets/images/aboutoma.png') center / cover no-repeat;
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
        background: linear-gradient(45deg, transparent 30%, rgba(255, 215, 0, 0.1) 50%, transparent 70%);
        animation: shine 3s infinite;
    }

    @keyframes shine {

        0%,
        100% {
            transform: translateX(-100%);
        }

        50% {
            transform: translateX(100%);
        }
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .timeline-container {
        position: relative;
        padding: 2rem 0;
        margin: 2rem 0 0 0;
        /* Reduced margins */
    }

    /* The Vertical Line with a Glow Effect */
    .timeline-line {
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom,
                transparent,
                var(--color-primary) 15%,
                #FFD700 50%,
                var(--color-primary) 85%,
                transparent);
        transform: translateX(-50%);
        box-shadow: 0 0 15px rgba(178, 34, 34, 0.3);
    }

    .timeline-item {
        display: grid;
        grid-template-columns: 1fr 100px 1fr;
        align-items: center;
        margin-bottom: 5rem;
        opacity: 0;
        /* For scroll animation if you add JS later, or keep as 1 */
        transform: translateY(20px);
        animation: fadeInUp 0.6s forwards;
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Timeline Content Card */
    .timeline-content {
        background: #ffffff;
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        position: relative;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .timeline-content::before {
        content: '';
        position: absolute;
        top: 50%;
        width: 20px;
        height: 20px;
        background: white;
        transform: translateY(-50%) rotate(45deg);
        z-index: -1;
    }

    /* Alternating triangles for the cards */
    .timeline-item:nth-child(odd) .timeline-content::before {
        right: -10px;
    }

    .timeline-item:nth-child(even) .timeline-content::before {
        left: -10px;
    }

    .timeline-content:hover {
        transform: scale(1.03);
        box-shadow: 0 15px 45px rgba(178, 34, 34, 0.15);
        border-color: var(--color-primary);
    }

    /* The Year Dot */
    .timeline-dot {
        width: 70px;
        height: 70px;
        background: #fff;
        border: 4px solid var(--color-primary);
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10;
        position: relative;
        transition: 0.3s;
    }

    .timeline-dot span {
        color: var(--color-primary);
        font-weight: 800;
        font-size: 1rem;
    }

    .timeline-item:hover .timeline-dot {
        background: var(--color-primary);
        transform: scale(1.2);
    }

    .timeline-item:hover .timeline-dot span {
        color: white;
    }

    /* Pulse Effect on Dots */
    .timeline-dot::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 2px solid var(--color-primary);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        100% {
            transform: scale(1.5);
            opacity: 0;
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .timeline-line {
            left: 30px;
        }

        .timeline-item {
            grid-template-columns: 60px 1fr;
            gap: 1rem;
        }

        .timeline-item:nth-child(even) .timeline-content {
            order: 2;
        }

        .timeline-item:nth-child(odd) .timeline-content::before,
        .timeline-item:nth-child(even) .timeline-content::before {
            left: -10px;
            right: auto;
        }
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
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .value-card:hover {
        transform: translateY(-10px);
        border-top-color: var(--color-primary);
        box-shadow: 0 12px 35px rgba(178, 34, 34, 0.15);
    }

    .value-icon {
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
        display: block;
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
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

    /* Updated Stats Grid for Swipeability */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 3rem;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .stats-grid {
            display: flex;
            /* Change to flex for horizontal scrolling */
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            /* Enables the "snap" effect */
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            /* Smooth scrolling on iOS */
            gap: 1.5rem;
            padding: 1rem 1rem 2rem 1rem;
            /* Extra padding at bottom for scrollbar space */
            margin: 0 -1rem;
            /* Bleed to the edges of the screen */
        }

        /* Hide scrollbar for a cleaner look (optional) */
        .stats-grid::-webkit-scrollbar {
            display: none;
        }

        .stats-grid {
            -ms-overflow-style: none;
            /* IE/Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        .stat-item {
            flex: 0 0 75%;
            /* Each card takes up 75% of screen width so next is visible */
            scroll-snap-align: center;
            /* Snaps the card to the center */
            background: rgba(255, 255, 255, 0.1);
            /* Subtle background to define card space */
            padding: 2rem;
            border-radius: 12px;
        }
    }

    .stat-number {
        font-size: 3.5rem;
        font-weight: bold;
        color: #FFD700;
        text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
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
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        border-left: 5px solid var(--color-primary);
        transition: all 0.3s ease;
    }

    .mission-card:hover {
        border-left-width: 8px;
        box-shadow: 0 8px 30px rgba(178, 34, 34, 0.2);
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
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
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
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.4s ease;
        border-top: 5px solid var(--color-primary);
    }

    .era-card:hover {
        transform: translateY(-15px) scale(1.02);
        box-shadow: 0 20px 50px rgba(178, 34, 34, 0.2);
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
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
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

    /* Change the container text color to black */
    .era-content {
        padding: 2rem;
        color: #000;
    }

    /* Force paragraphs and list items inside era-content to be black */
    .era-content p,
    .era-content ul li {
        color: #000 !important;
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
        <div class="hero-content" style="
    border: 1px solid rgba(255, 215, 0, 0.5); /* Semi-transparent Gold */
    padding: 3rem;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.2);           /* Subtle dark tint behind text */
    backdrop-filter: blur(4px);               /* Slight blur for readability */
">
            <p class="section-subtitle" ...>Our Heritage</p>
            <h1 ...>Oriental Muayboran Academy</h1>
            <p ...>Inheriting and promoting the authentic traditional warfare system of ancient Siam</p>
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
    <section style="padding: 2rem 0 0 0;">
        <div class=" text-center" style="margin-bottom: 5rem;">
            <p class="section-subtitle" style="color: var(--color-primary); font-weight: bold;">The Legacy Continues</p>
            <h2 class="section-title" style="font-size: 3rem;">Our Evolution</h2>
            <div style="width: 80px; height: 4px; background: #FFD700; margin: 1rem auto;"></div>
        </div>

        <div class="timeline-container">
            <div class="timeline-line"></div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); margin-bottom: 0.5rem; font-size: 1.5rem;">The Roots</h3>
                    <p style="color: #444; line-height: 1.7; font-size: 1rem; margin: 0;">
                        Our journey began in <strong>La Trinidad, Benguet</strong>. The seeds of traditional Muayboran
                        were planted in Philippine soil, blending ancient Siamese wisdom with local passion.
                    </p>
                </div>
                <div class="timeline-dot"><span>2007</span></div>
                <div class="spacer"></div>
            </div>

            <div class="timeline-item">
                <div class="spacer"></div>
                <div class="timeline-dot"><span>2016</span></div>
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); margin-bottom: 0.5rem; font-size: 1.5rem;">Official
                        Foundation</h3>
                    <p style="color: #444; line-height: 1.7; font-size: 1rem; margin: 0;">
                        <strong>Oriental Muayboran Academy</strong> was born. We formalized our mission to preserve the
                        lineage of <strong>Great Grandmaster Sane Tubthimtong</strong>.
                    </p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); margin-bottom: 0.5rem; font-size: 1.5rem;">The Great Move
                    </h3>
                    <p style="color: #444; line-height: 1.7; font-size: 1rem; margin: 0;">
                        Relocated to <strong>Quezon City</strong>. We established our flagship headquarters, creating a
                        sanctuary for those seeking the authentic "Art of Eight Limbs."
                    </p>
                </div>
                <div class="timeline-dot"><span>2018</span></div>
                <div class="spacer"></div>
            </div>

            <div class="timeline-item">
                <div class="spacer"></div>
                <div class="timeline-dot"><span>2026</span></div>
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); margin-bottom: 0.5rem; font-size: 1.5rem;">Modern Mastery
                    </h3>
                    <p style="color: #444; line-height: 1.7; font-size: 1rem; margin: 0;">
                        Under <strong>Ajarn Brendaley Tarnate</strong>, OMA stands as a pillar of the Muayboran
                        community, uniting tradition with modern excellence.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Era Cards (Alternative Layout) -->
    <section style="margin: 1.5rem 0;">
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

    <!-- Mission/Vision/Values CTA -->
    <section style="margin: 5rem 0;">
        <div
            style="background: linear-gradient(135deg, var(--color-primary), #8B0000); border-radius: 20px; padding: 4rem 3rem; text-align: center; color: white;">
            <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem; color: white;">Our Guiding Principles</h2>
            <p
                style="font-size: 1.3rem; margin-bottom: 2.5rem; opacity: 0.95; max-width: 700px; margin-left: auto; margin-right: auto; margin-bottom: 2.5rem;">
                Discover the mission, vision, and core values that guide every OMA practitioner on their martial journey
            </p>
            <a href="mvc.php" class="btn"
                style="background: white; color: var(--color-primary); padding: 1.2rem 3rem; font-size: 1.2rem; border-radius: 50px; text-decoration: none; display: inline-block; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: all 0.3s ease;">
                View Mission, Vision & Core Values →
            </a>
        </div>
    </section>


</div>

<?php include '../includes/footer.php'; ?>