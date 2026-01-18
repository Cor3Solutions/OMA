<?php
$page_title = "History";
include '../includes/header.php';
?>

<style>
    /* History Page Styles */
    .history-hero {
        background: linear-gradient(135deg, rgba(178,34,34,0.95), rgba(139,0,0,0.9)), 
                    url('../assets/images/mma.png') center/cover no-repeat;
        min-height: 400px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        margin-bottom: 4rem;
        padding: 3rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .history-hero::before {
        content: '🥋';
        position: absolute;
        font-size: 20rem;
        opacity: 0.05;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
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

    /* Visual Timeline */
    .visual-timeline {
        position: relative;
        padding: 3rem 0;
        margin: 5rem 0;
    }

    .timeline-path {
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(to bottom, 
            var(--color-primary) 0%, 
            #FFD700 50%, 
            var(--color-primary) 100%);
        transform: translateX(-50%);
    }

    .timeline-era {
        display: grid;
        grid-template-columns: 1fr 100px 1fr;
        gap: 3rem;
        margin-bottom: 4rem;
        position: relative;
    }

    .timeline-year {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, var(--color-primary), #8B0000);
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        box-shadow: 0 0 0 10px rgba(178,34,34,0.1), 0 8px 25px rgba(0,0,0,0.2);
        z-index: 10;
        position: relative;
    }

    .timeline-year-number {
        font-size: 1.5rem;
        color: #FFD700;
    }

    .timeline-year-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .timeline-content {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .timeline-content:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(178,34,34,0.15);
    }

    .timeline-era:nth-child(even) .timeline-content:first-child {
        order: 3;
    }

    /* Key Figures Section */
    .figures-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2.5rem;
        margin: 4rem 0;
    }

    .figure-card {
        text-align: center;
        background: white;
        padding: 2.5rem 2rem;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
    }

    .figure-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(to right, var(--color-primary), #FFD700);
        border-radius: 16px 16px 0 0;
    }

    .figure-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(178,34,34,0.15);
    }

    .figure-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        margin: 0 auto 1.5rem;
        border: 5px solid var(--color-primary);
        box-shadow: 0 8px 25px rgba(178,34,34,0.2);
    }

    /* Impact Stats */
    .impact-section {
        background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
        border-radius: 16px;
        padding: 4rem 2rem;
        margin: 5rem 0;
        color: white;
        text-align: center;
    }

    .impact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 3rem;
        margin-top: 3rem;
    }

    .impact-stat {
        position: relative;
    }

    .impact-icon {
        font-size: 3.5rem;
        display: block;
        margin-bottom: 1rem;
    }

    .impact-number {
        font-size: 3rem;
        font-weight: bold;
        color: #FFD700;
        display: block;
        margin-bottom: 0.5rem;
    }

    .impact-label {
        font-size: 1.1rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Responsive */
    @media (max-width: 968px) {
        .timeline-era {
            grid-template-columns: 1fr;
        }
        
        .timeline-path {
            left: 25px;
        }
        
        .timeline-year {
            width: 80px;
            height: 80px;
        }
        
        .timeline-era:nth-child(even) .timeline-content:first-child {
            order: 1;
        }
    }
</style>

<div class="container">
    <!-- Hero Section -->
    <header class="history-hero">
        <div style="position: relative; z-index: 1;">
            <p class="section-subtitle" style="text-transform: uppercase; letter-spacing: 3px; margin-bottom: 1rem;">Our Heritage</p>
            <h1 style="font-size: clamp(2.5rem, 6vw, 4rem); margin-bottom: 1rem;">
                2,000+ Years of Tradition
            </h1>
            <p style="font-size: 1.3rem; opacity: 0.95; max-width: 700px; margin: 0 auto;">
                From ancient Siamese battlefields to modern academies worldwide
            </p>
        </div>
    </header>

    <!-- Impact Stats -->
    <section class="impact-section">
        <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Living History</h2>
        <p style="opacity: 0.9; font-size: 1.1rem; margin-bottom: 2rem;">The legacy continues through generations</p>
        <div class="impact-grid">
            <div class="impact-stat">
                <span class="impact-icon">⚔️</span>
                <span class="impact-number">2,000+</span>
                <span class="impact-label">Years Old</span>
            </div>
            <div class="impact-stat">
                <span class="impact-icon">🌏</span>
                <span class="impact-number">Global</span>
                <span class="impact-label">Expansion</span>
            </div>
            <div class="impact-stat">
                <span class="impact-icon">👥</span>
                <span class="impact-number">Lineage</span>
                <span class="impact-label">Preserved</span>
            </div>
            <div class="impact-stat">
                <span class="impact-icon">🥋</span>
                <span class="impact-number">Authentic</span>
                <span class="impact-label">Tradition</span>
            </div>
        </div>
    </section>

    <!-- Visual Timeline -->
    <section>
        <div class="text-center" style="margin-bottom: 4rem;">
            <p class="section-subtitle">The Journey</p>
            <h2 class="section-title">Evolution Through Time</h2>
        </div>

        <div class="visual-timeline">
            <div class="timeline-path"></div>
            
            <!-- Ancient Era -->
            <div class="timeline-era">
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); font-size: 1.8rem; margin-bottom: 1rem;">
                        ⚔️ Ancient Origins
                    </h3>
                    <p style="font-size: 1.05rem; line-height: 1.8; color: var(--color-text-light);">
                        Born on the battlefields of ancient Siam over 2,000 years ago as a comprehensive combat system for Thai warriors.
                    </p>
                    <div class="era-highlight">
                        <strong>Key Development:</strong> Combined striking, grappling, and weapons training into one formidable martial art
                    </div>
                </div>
                <div class="timeline-year">
                    <span class="timeline-year-number">2000+</span>
                    <span class="timeline-year-label">Years Ago</span>
                </div>
                <div></div>
            </div>

            <!-- Classical Era -->
            <div class="timeline-era">
                <div></div>
                <div class="timeline-year">
                    <span class="timeline-year-number">🏛️</span>
                    <span class="timeline-year-label">Classical</span>
                </div>
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); font-size: 1.8rem; margin-bottom: 1rem;">
                        🏛️ Classical Period
                    </h3>
                    <p style="font-size: 1.05rem; line-height: 1.8; color: var(--color-text-light);">
                        Refined through centuries of warfare and royal patronage, becoming an essential part of Thai military training.
                    </p>
                    <div class="era-highlight">
                        <strong>Key Development:</strong> Formalized techniques and training methods passed from master to student
                    </div>
                </div>
            </div>

            <!-- Modern Evolution -->
            <div class="timeline-era">
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); font-size: 1.8rem; margin-bottom: 1rem;">
                        🥊 Modern Evolution
                    </h3>
                    <p style="font-size: 1.05rem; line-height: 1.8; color: var(--color-text-light);">
                        While Muay Thai evolved into a popular combat sport, Muayboran maintained its traditional forms and combat applications.
                    </p>
                    <div class="era-highlight">
                        <strong>Key Development:</strong> Preservation of ancient techniques alongside modern sport evolution
                    </div>
                </div>
                <div class="timeline-year">
                    <span class="timeline-year-number">📈</span>
                    <span class="timeline-year-label">Modern</span>
                </div>
                <div></div>
            </div>

            <!-- Global Present -->
            <div class="timeline-era">
                <div></div>
                <div class="timeline-year">
                    <span class="timeline-year-number">🌍</span>
                    <span class="timeline-year-label">Today</span>
                </div>
                <div class="timeline-content">
                    <h3 style="color: var(--color-primary); font-size: 1.8rem; margin-bottom: 1rem;">
                        🌍 Global Expansion
                    </h3>
                    <p style="font-size: 1.05rem; line-height: 1.8; color: var(--color-text-light);">
                        Oriental Muayboran Academy spreads authentic teachings worldwide while maintaining the highest standards of instruction.
                    </p>
                    <div class="era-highlight">
                        <strong>Our Mission:</strong> Making this ancient art accessible to dedicated practitioners across the globe
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Figures -->
    <section>
        <div class="text-center" style="margin-bottom: 4rem;">
            <p class="section-subtitle">Our Masters</p>
            <h2 class="section-title">Lineage of Excellence</h2>
            <p class="section-description" style="max-width: 700px; margin: 1rem auto;">
                The knowledge flows through generations of dedicated masters
            </p>
        </div>

        <div class="figures-grid">
            <div class="figure-card">
                <div class="figure-avatar">👴</div>
                <h3 style="color: var(--color-primary); font-size: 1.6rem; margin-bottom: 0.5rem;">
                    Great Grandmaster Sane Tubthimtong
                </h3>
                <p style="color: #666; margin-bottom: 1rem;">Grandmaster</p>
                <p style="line-height: 1.7; color: var(--color-text-light);">
                    Dedicated his life to preserving authentic Muayboran techniques and philosophy for future generations.
                </p>
            </div>

            <div class="figure-card">
                <div class="figure-avatar">🥋</div>
                <h3 style="color: var(--color-primary); font-size: 1.6rem; margin-bottom: 0.5rem;">
                    Ajarn Brendaley Tarnate
                </h3>
                <p style="color: #666; margin-bottom: 1rem;">Head Instructor</p>
                <p style="line-height: 1.7; color: var(--color-text-light);">
                    Carries forward the sacred teachings, leading OMA with dedication to authenticity and excellence.
                </p>
            </div>

            <div class="figure-card">
                <div class="figure-avatar">👥</div>
                <h3 style="color: var(--color-primary); font-size: 1.6rem; margin-bottom: 0.5rem;">
                    Our Instructors
                </h3>
                <p style="color: #666; margin-bottom: 1rem;">Teaching Team</p>
                <p style="line-height: 1.7; color: var(--color-text-light);">
                    Certified practitioners maintaining the lineage through dedicated training and instruction.
                </p>
            </div>
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
                    <span class="era-icon">🎯</span>
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
                    <span class="era-icon">🏛️</span>
                    <h3 class="era-title">Tradition</h3>
                    <p class="era-subtitle">Authentic Preservation</p>
                </div>
                <div class="era-content">
                    <p style="line-height: 1.8; color: var(--color-text-light);">
                        We maintain the integrity of traditional forms while making the art accessible to modern practitioners through structured training and proven teaching methods.
                    </p>
                </div>
            </div>

            <div class="era-card">
                <div class="era-header">
                    <span class="era-icon">🌟</span>
                    <h3 class="era-title">Philosophy</h3>
                    <p class="era-subtitle">Beyond Physical Technique</p>
                </div>
                <div class="era-content">
                    <p style="line-height: 1.8; color: var(--color-text-light);">
                        More than combat skills, Muayboran teaches discipline, respect, and the warrior spirit that has guided practitioners for millennia.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <div style="text-align: center; margin-top: 5rem; padding: 3rem; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 16px;">
        <h2 style="margin-bottom: 1.5rem; color: var(--color-primary);">Become Part of This Legacy</h2>
        <p style="font-size: 1.2rem; margin-bottom: 2rem; color: var(--color-text-light);">
            Join thousands of practitioners who have walked this path before you
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="about.php" class="btn btn-outline">Learn More About OMA</a>
            <a href="lineage.php" class="btn btn-primary">Explore Our Lineage</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
