<?php
$page_title = "Mission, Vision & Core Values";
include '../includes/header.php';
?>

<style>
    /* MVC Page Styles */
    .mvc-hero {
        background: linear-gradient(135deg, rgba(178,34,34,0.95), rgba(139,0,0,0.9)), 
                    url('../assets/images/mma.png') center/cover no-repeat;
        min-height: 450px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        margin-bottom: 5rem;
        padding: 4rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .mvc-hero::before {
        content: '⚡';
        position: absolute;
        font-size: 25rem;
        opacity: 0.03;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-15deg);
    }

    .mvc-hero-content {
        position: relative;
        z-index: 1;
    }

    /* Vision Card - Featured */
    .vision-spotlight {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        border-radius: 20px;
        padding: 4rem 3rem;
        margin: 5rem 0;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    }

    .vision-spotlight::before {
        content: '🎯';
        position: absolute;
        font-size: 20rem;
        opacity: 0.05;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .vision-content {
        position: relative;
        z-index: 1;
        max-width: 900px;
        margin: 0 auto;
    }

    .vision-icon {
        font-size: 5rem;
        display: block;
        margin-bottom: 2rem;
        filter: drop-shadow(0 8px 16px rgba(255,215,0,0.3));
    }

    .vision-title {
        font-size: 3rem;
        margin-bottom: 1.5rem;
        background: linear-gradient(to right, #FFD700, #FFA500);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .vision-text {
        font-size: 1.5rem;
        line-height: 2;
        color: rgba(255,255,255,0.95);
        font-style: italic;
    }

    /* Mission Section */
    .mission-section {
        margin: 6rem 0;
    }

    .mission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2.5rem;
        margin-top: 3rem;
    }

    .mission-card {
        background: white;
        border-radius: 16px;
        padding: 2.5rem;
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        transition: all 0.4s ease;
        position: relative;
        border-top: 5px solid var(--color-primary);
    }

    .mission-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, transparent 0%, rgba(178,34,34,0.03) 100%);
        border-radius: 16px;
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .mission-card:hover::before {
        opacity: 1;
    }

    .mission-card:hover {
        transform: translateY(-15px) scale(1.03);
        box-shadow: 0 20px 50px rgba(178,34,34,0.2);
        border-top-width: 8px;
    }

    .mission-number {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--color-primary), #8B0000);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFD700;
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(178,34,34,0.3);
    }

    .mission-title {
        font-size: 1.4rem;
        color: var(--color-primary);
        margin-bottom: 1rem;
        font-weight: bold;
    }

    .mission-text {
        font-size: 1.05rem;
        line-height: 1.8;
        color: #333;
    }

    /* Core Values */
    .values-showcase {
        margin: 6rem 0;
    }

    .values-intro {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 3rem;
        border-radius: 16px;
        text-align: center;
        margin-bottom: 4rem;
        border-left: 5px solid var(--color-primary);
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 3rem;
    }

    .value-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        transition: all 0.4s ease;
    }

    .value-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 20px 60px rgba(178,34,34,0.15);
    }

    .value-header {
        background: linear-gradient(135deg, var(--color-primary), #8B0000);
        padding: 3rem 2rem;
        text-align: center;
        color: white;
        position: relative;
    }

    .value-icon {
        font-size: 4.5rem;
        display: block;
        margin-bottom: 1.5rem;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
    }

    .value-name {
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .value-body {
        padding: 2.5rem;
    }

    .value-pledge {
        font-size: 1.1rem;
        line-height: 1.9;
        color: #333;
        font-style: italic;
        position: relative;
        padding-left: 1.5rem;
        border-left: 3px solid var(--color-primary);
    }

    /* Sacred Oath Section */
    .sacred-oath {
        background: linear-gradient(135deg, #000000, #1a1a1a);
        border-radius: 20px;
        padding: 5rem 3rem;
        margin: 6rem 0;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .sacred-oath::before {
        content: '🥋';
        position: absolute;
        font-size: 20rem;
        opacity: 0.05;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .oath-content {
        position: relative;
        z-index: 1;
        max-width: 800px;
        margin: 0 auto;
    }

    .oath-title {
        font-size: 3rem;
        margin-bottom: 2rem;
        color: #FFD700;
    }

    .oath-pledge-text {
        font-size: 2rem;
        font-style: italic;
        color: #FFD700;
        margin: 2rem 0;
        text-shadow: 0 4px 12px rgba(255,215,0,0.3);
    }

    .oath-description {
        font-size: 1.2rem;
        line-height: 2;
        opacity: 0.95;
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, var(--color-primary), #8B0000);
        border-radius: 20px;
        padding: 4rem 3rem;
        text-align: center;
        color: white;
        margin: 5rem 0;
    }

    .cta-title {
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
    }

    .cta-text {
        font-size: 1.3rem;
        margin-bottom: 2.5rem;
        opacity: 0.95;
    }

    .cta-buttons {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-light {
        background: white;
        color: var(--color-primary);
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: bold;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .btn-light:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }

    .btn-outline-light {
        background: transparent;
        color: white;
        border: 2px solid white;
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: bold;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-outline-light:hover {
        background: white;
        color: var(--color-primary);
        transform: translateY(-3px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .vision-title {
            font-size: 2rem;
        }
        
        .vision-text {
            font-size: 1.2rem;
        }
        
        .oath-title {
            font-size: 2rem;
        }
        
        .oath-pledge-text {
            font-size: 1.5rem;
        }
    }
</style>

<div class="container">
    <!-- Hero Section -->
    <header class="mvc-hero">
        <div class="mvc-hero-content">
            <p class="section-subtitle" style="text-transform: uppercase; letter-spacing: 3px; margin-bottom: 1rem;">What We Stand For</p>
            <h1 style="font-size: clamp(2.5rem, 6vw, 4.5rem); margin-bottom: 1rem;">
                Mission, Vision & Core Values
            </h1>
            <p style="font-size: 1.3rem; opacity: 0.95; max-width: 700px; margin: 0 auto;">
                The principles and purpose that guide our academy and every practitioner
            </p>
        </div>
    </header>

    <!-- Vision Spotlight -->
    <section class="vision-spotlight">
        <div class="vision-content">
            <h2 class="vision-title">Our Vision</h2>
            <p class="vision-text">
                "A united community of Muaythai Boran practitioners who passionately embody the core values of OMA, 
                empowered to contribute to a peaceful and progressive humanity."
            </p>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section">
        <div class="text-center" style="margin-bottom: 4rem;">
            <p class="section-subtitle">Our Commitment</p>
            <h2 class="section-title" style="font-size: 3rem;">Our Mission</h2>
            <p class="section-description" style="max-width: 800px; margin: 1rem auto; font-size: 1.2rem;">
                Four pillars that define our work and dedication to the art of Muayboran
            </p>
        </div>

        <div class="mission-grid">
            <div class="mission-card">
                <div class="mission-number">1</div>
                <h3 class="mission-title">Preserve the Curriculum</h3>
                <p class="mission-text">
                    Institutionalize the complete curriculum of Great Grandmaster Sane Tubthimtong, 
                    ensuring authentic techniques and teachings are passed down through generations.
                </p>
            </div>

            <div class="mission-card">
                <div class="mission-number">2</div>
                <h3 class="mission-title">Empower Our Members</h3>
                <p class="mission-text">
                    Equip members with high-standard knowledge and skills for self-sufficiency, 
                    creating confident practitioners capable of teaching and leading.
                </p>
            </div>

            <div class="mission-card">
                <div class="mission-number">3</div>
                <h3 class="mission-title">Build Strong Kinship</h3>
                <p class="mission-text">
                    Solidify strong kinship among all Kru in the Philippines, fostering unity, 
                    respect, and collaboration within the Muayboran community.
                </p>
            </div>
        </div>
    </section>

    <section class="values-showcase" style="background: #0f0f0f; padding: 5rem 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; overflow: hidden;">
    
    <div style="text-align: center; margin-bottom: 4rem; padding: 0 1rem;">
        <p style="color: #d4af37; text-transform: uppercase; letter-spacing: 3px; font-weight: 700; margin-bottom: 0.5rem; font-size: 0.9rem;">Our Foundation</p>
        <h2 style="color: #ffffff; font-size: clamp(2rem, 5vw, 3.5rem); margin: 0; font-weight: 800; letter-spacing: -1px;">Core Values & <span style="color: #d4af37;">Ethical Oath</span></h2>
        <div style="width: 60px; height: 3px; background: #d4af37; margin: 1.5rem auto;"></div>
    </div>

    <div class="values-grid" style="
        display: flex; 
        gap: 2rem; 
        flex-wrap: nowrap; 
        overflow-x: auto; 
        padding: 1rem 2rem 4rem 2rem;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    ">
        
        <div class="value-card">
            <div class="card-inner">
                <span class="value-icon">🙏</span>
                <h3 class="value-name">Respect & Honor</h3>
                <div class="card-line"></div>
                <p class="value-pledge">"I will respect everyone, especially my family, mentors, and myself, never bringing disgrace."</p>
            </div>
        </div>

        <div class="value-card">
            <div class="card-inner">
                <span class="value-icon">🛡️</span>
                <h3 class="value-name">Loyalty & Truth</h3>
                <div class="card-line"></div>
                <p class="value-pledge">"I will be loyal to my motherland, standing fearlessly to protect honor, truth, and justice."</p>
            </div>
        </div>

        <div class="value-card">
            <div class="card-inner">
                <span class="value-icon">💪</span>
                <h3 class="value-name">Conviction</h3>
                <div class="card-line"></div>
                <p class="value-pledge">"I will live by my principles, stand for the greater good, and hold myself responsible."</p>
            </div>
        </div>

        <div class="value-card">
            <div class="card-inner">
                <span class="value-icon">🧘</span>
                <h3 class="value-name">Self-Control</h3>
                <div class="card-line"></div>
                <p class="value-pledge">"I will maintain unwavering self-discipline and self-control under any circumstance."</p>
            </div>
        </div>

        <div class="value-card">
            <div class="card-inner">
                <span class="value-icon">⚖️</span>
                <h3 class="value-name">Righteousness</h3>
                <div class="card-line"></div>
                <p class="value-pledge">"I will use my skills to protect and defend what is right, never to boast or cause harm."</p>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: center; align-items: center; gap: 10px; opacity: 0.6;">
        <div style="width: 40px; height: 1px; background: #d4af37;"></div>
        <p style="color: #fff; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; margin: 0;">Swipe to explore</p>
        <div style="width: 40px; height: 1px; background: #d4af37;"></div>
    </div>
</section>

<style>
    /* Premium Card Styling */
    .value-card {
        flex: 0 0 320px;
        scroll-snap-align: center;
        position: relative;
        background: linear-gradient(145deg, #1a1a1a, #121212);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 20px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        cursor: grab;
    }

    .value-card:hover {
        transform: translateY(-10px);
        border-color: #d4af37;
        box-shadow: 0 15px 30px rgba(0,0,0,0.5), 0 0 15px rgba(212, 175, 55, 0.1);
    }

    .card-inner {
        padding: 3rem 2rem;
        text-align: center;
    }

    .value-icon {
        font-size: 3rem;
        display: block;
        margin-bottom: 1.5rem;
        filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.3));
    }

    .value-name {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        letter-spacing: 0.5px;
    }

    .card-line {
        width: 30px;
        height: 2px;
        background: #d4af37;
        margin: 0 auto 1.5rem auto;
    }

    .value-pledge {
        color: #b0b0b0;
        font-size: 1rem;
        line-height: 1.6;
        font-style: italic;
    }

    /* Hide scrollbar */
    .values-grid::-webkit-scrollbar {
        display: none;
    }
    .values-grid {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    @media (max-width: 768px) {
        .value-card {
            flex: 0 0 85%; /* Shows a peek of the next card on mobile */
        }
    }
</style>

    <!-- Sacred Oath -->
    <section class="sacred-oath">
        <div class="oath-content">
            <h2 class="oath-title">The Sacred Pledge</h2>
            <p class="oath-pledge-text">"These I pledge."</p>
            <p class="oath-description">
                This solemn commitment binds every OMA practitioner to the highest standards of martial 
                and moral conduct. It is a promise of personal excellence and service to others—a covenant 
                that transforms practitioners into warriors of character, discipline, and honor.
            </p>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <h2 class="cta-title">Ready to Live These Values?</h2>
        <p class="cta-text">
            Join a community dedicated to excellence, tradition, and the warrior spirit
        </p>
        <div class="cta-buttons">
            <a href="register.php" class="btn-light">Submit Membership Inquiry</a>
            <a href="about.php" class="btn-outline-light">Learn More About OMA</a>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>