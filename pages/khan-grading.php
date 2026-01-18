<?php
$page_title = "Khan Grading Structure";
include '../includes/header.php';
?>

<style>
    .khan-hero {
        background: linear-gradient(135deg, rgba(178, 34, 34, 0.95), rgba(139, 0, 0, 0.9)), url('../assets/images/mma.png') center / cover no-repeat;
        padding: 5rem 2rem;
        border-radius: 16px;
        color: white;
        text-align: center;
        margin-bottom: 4rem;
        position: relative;
        overflow: hidden;
    }

    .khan-hero::before {
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

    .khan-intro {
        max-width: 900px;
        margin: 0 auto 5rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: center;
    }

    .intro-content {
        padding: 2rem;
    }

    .intro-image {
        position: relative;
        aspect-ratio: 4/3;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(178, 34, 34, 0.3);
    }

    .intro-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .intro-image:hover img {
        transform: scale(1.1);
    }

    .khan-level-section {
        margin: 5rem 0;
        position: relative;
    }

    .level-header {
        text-align: center;
        margin-bottom: 3rem;
        position: relative;
    }

    .level-badge {
        display: inline-block;
        padding: 0.5rem 2rem;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 1rem;
    }

    .khan-showcase {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 3rem;
        align-items: center;
        margin-bottom: 3rem;
        background: #380404;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .khan-showcase:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
    }

    .khan-showcase.reverse {
        grid-template-columns: 1.2fr 1fr;
    }

    .showcase-image {
        position: relative;
        height: 400px;
        overflow: hidden;
    }

    .showcase-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .khan-showcase:hover .showcase-image img {
        transform: scale(1.05);
    }

    .showcase-content {
        padding: 3rem;
        color: #fff;
    }

    .khan-number {
        font-size: 4rem;
        font-weight: bold;
        line-height: 1;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--color-primary), #FFD700);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .prajioud-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.7rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        margin: 1rem 0;
        font-size: 0.95rem;
    }

    .prajioud-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 3px solid currentColor;
    }

    .focus-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .focus-item {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.8rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        font-size: 0.9rem;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .focus-icon {
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    /* Color schemes */
    .level-white .level-badge {
        background: #f8f9fa;
        color: #333;
        border: 2px solid #ddd;
    }

    .level-white .prajioud-badge {
        background: white;
        color: #333;
        border: 2px solid #ddd;
    }

    .level-white .prajioud-icon {
        background: white;
        border-color: #999;
    }

    .level-yellow .level-badge {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        color: #333;
    }

    .level-yellow .prajioud-badge {
        background: linear-gradient(135deg, #FFD700, #90EE90);
        color: #333;
    }

    .level-yellow .prajioud-icon {
        background: linear-gradient(135deg, #FFD700, #90EE90);
        border-color: #FFD700;
    }

    .level-blue .level-badge {
        background: linear-gradient(135deg, #4169E1, #1E90FF);
        color: white;
    }

    .level-blue .prajioud-badge {
        background: linear-gradient(135deg, #4169E1, #DC143C);
        color: white;
    }

    .level-blue .prajioud-icon {
        background: linear-gradient(135deg, #4169E1, #DC143C);
        border-color: #DC143C;
    }

    .level-red .level-badge {
        background: linear-gradient(135deg, #DC143C, #B22222);
        color: white;
    }

    .level-red .prajioud-badge {
        background: linear-gradient(135deg, #DC143C, #FFD700);
        color: #333;
    }

    .level-red .prajioud-icon {
        background: linear-gradient(135deg, #DC143C, #FFD700);
        border-color: #FFD700;
    }

    .level-master .level-badge {
        background: linear-gradient(135deg, #000, #8B0000);
        color: #FFD700;
    }

    .level-master .prajioud-badge {
        background: linear-gradient(135deg, #000, #FFD700);
        color: #FFD700;
        font-weight: bold;
    }

    .level-master .prajioud-icon {
        background: linear-gradient(135deg, #000, #FFD700);
        border-color: #FFD700;
    }

    .progression-visual {
        background: #8b6e19;
        padding: 4rem 2rem;
        border-radius: 20px;
        margin: 5rem 0;
        text-align: center;
    }

    .belt-progression {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .belt-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .belt-visual {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 4px solid;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .belt-item:hover .belt-visual {
        transform: scale(1.2);
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
    }

    .arrow-icon {
        font-size: 2rem;
        color: var(--color-primary);
    }

    .cta-section {
        background: linear-gradient(135deg, var(--color-primary), #8B0000);
        border-radius: 20px;
        padding: 4rem 2rem;
        text-align: center;
        color: white;
        margin-top: 5rem;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '🥋';
        position: absolute;
        font-size: 20rem;
        opacity: 0.05;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    @media (max-width: 968px) {
        .khan-intro {
            grid-template-columns: 1fr;
        }

        .khan-showcase,
        .khan-showcase.reverse {
            grid-template-columns: 1fr;
        }

        .focus-list {
            grid-template-columns: 1fr;
        }

        .belt-progression {
            flex-direction: column;
        }

        .arrow-icon {
            transform: rotate(90deg);
        }
    }
</style>

<section class="section">
    <div class="container">
        <!-- Hero Section -->
        <div class="khan-hero">
            <div style="position: relative; z-index: 1;">
                <p style="text-transform: uppercase; letter-spacing: 3px; margin-bottom: 1rem; opacity: 0.9;">
                    Traditional Ranking System</p>
                <h1 style="font-size: clamp(2.5rem, 5vw, 4rem); margin-bottom: 1rem;">Khan Grading Structure</h1>
                <p style="font-size: 1.2rem; max-width: 600px; margin: 0 auto; opacity: 0.95;">
                    16 Levels of Mastery from Beginner to Grand Master
                </p>
            </div>
        </div>

        <!-- Introduction with Image -->
        <div class="khan-intro">
            <div class="intro-content">
                <h2 style="color: var(--color-primary); margin-bottom: 1.5rem; font-size: 2rem;">The Path of Excellence
                </h2>
                <p style="color: #333; font-size: 1.05rem; line-height: 1.8; margin-bottom: 1rem;">
                    The Khan system represents centuries of traditional Muayboran ranking, where each level demands
                    mastery of techniques, forms, and philosophy.
                </p>
                <p style="color: #666; line-height: 1.7;">
                    From white prajioud to grand master status, your journey reflects dedication, skill, and deep
                    understanding of authentic Siamese martial arts.
                </p>
            </div>
            <div class="intro-image">
                <img src="../assets/images/rusha.png" alt="Muayboran Training">
            </div>
        </div>

        <!-- Progression Visual -->
        <div class="progression-visual">
            <h2 style="color: var(--color-primary); margin-bottom: 1rem; font-size: 2rem;">Your Journey Ahead</h2>
            <p style="color: #ffffff; margin-bottom: 2rem;">Progress through 5 major levels from foundation to mastery
            </p>
            <div class="belt-progression">
                <div class="belt-item">
                    <div class="belt-visual" style="background: white; color: #333; border-color: #999;">1-3</div>
                    <span style="font-size: 0.85rem; color: #ffffff;">Foundation</span>
                </div>
                <span class="arrow-icon">→</span>
                <div class="belt-item">
                    <div class="belt-visual"
                        style="background: linear-gradient(135deg, #FFD700, #90EE90); color: #333; border-color: #FFD700;">
                        4-6</div>
                    <span style="font-size: 0.85rem; color: #ffffff;">Intermediate</span>
                </div>
                <span class="arrow-icon">→</span>
                <div class="belt-item">
                    <div class="belt-visual"
                        style="background: linear-gradient(135deg, #4169E1, #DC143C); color: white; border-color: #4169E1;">
                        7-9</div>
                    <span style="font-size: 0.85rem; color: #ffffff;">Advanced</span>
                </div>
                <span class="arrow-icon">→</span>
                <div class="belt-item">
                    <div class="belt-visual"
                        style="background: linear-gradient(135deg, #DC143C, #FFD700); color: #333; border-color: #DC143C;">
                        10-12</div>
                    <span style="font-size: 0.85rem; color: #ffffff;">Expert</span>
                </div>
                <span class="arrow-icon">→</span>
                <div class="belt-item">
                    <div class="belt-visual"
                        style="background: linear-gradient(135deg, #000, #FFD700); color: #FFD700; border-color: #FFD700;">
                        13-16</div>
                    <span style="font-size: 0.85rem; color: #ffffff;">Master</span>
                </div>
            </div>
        </div>

        <!-- Khan 1-3: Foundation -->
        <div class="khan-level-section level-white">
            <div class="level-header">
                <span class="level-badge">Foundation Level</span>
                <h2 style="color: var(--color-primary); font-size: 2.5rem;">Khan 1-3</h2>
            </div>
            <div class="khan-showcase">
                <div class="showcase-image">
                    <img src="../assets/images/mma.png" alt="Khan 1-3 Training">
                </div>
                <div class="showcase-content">
                    <h3 style="font-size: 1.8rem; margin-bottom: 0.5rem; color: #fff;">Building Your Foundation</h3>
                    <div class="prajioud-badge">
                        <div class="prajioud-icon"></div>
                        White Prajioud
                    </div>
                    <p style="color: #ddd; line-height: 1.8; margin: 1.5rem 0;">
                        Begin your journey with fundamental techniques, proper stances, and basic movements. Learn the
                        sacred Wai Kru ceremony and develop discipline.
                    </p>
                    <div class="focus-list">
                        <div class="focus-item">
                            <span class="focus-icon">👊</span>
                            <span>Basic Strikes</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">🦵</span>
                            <span>Fundamental Kicks</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">🧘</span>
                            <span>Wai Kru Ritual</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">💪</span>
                            <span>Conditioning</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Khan 4-6: Intermediate -->
        <div class="khan-level-section level-yellow">
            <div class="level-header">
                <span class="level-badge">Intermediate Level</span>
                <h2 style="color: var(--color-primary); font-size: 2.5rem;">Khan 4-6</h2>
            </div>
            <div class="khan-showcase reverse">
                <div class="showcase-content"> 
                    <h3 style="font-size: 1.8rem; margin-bottom: 0.5rem; color: #fff;">Developing Skill</h3>
                    <div class="prajioud-badge">
                        <div class="prajioud-icon"></div>
                        Yellow/Green Prajioud
                    </div>
                    <p style="color: #ddd; line-height: 1.8; margin: 1.5rem 0;">
                        Advance your techniques with complex combinations, defensive maneuvers, and Look Mai trick
                        techniques. Begin assisting in teaching.
                    </p>
                    <div class="focus-list">
                        <div class="focus-item">
                            <span class="focus-icon">🎯</span>
                            <span>Advanced Combos</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">🛡️</span>
                            <span>Defense Systems</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">🥋</span>
                            <span>Look Mai Techniques</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">👥</span>
                            <span>Teaching Aid</span>
                        </div>
                    </div>
                </div>
                <div class="showcase-image">
                    <img src="../assets/images/rusha.png" alt="Khan 4-6 Training">
                </div>
            </div>
        </div>

        <!-- Khan 7-9: Advanced -->
        <div class="khan-level-section level-blue">
            <div class="level-header">
                <span class="level-badge">Advanced Level</span>
                <h2 style="color: var(--color-primary); font-size: 2.5rem;">Khan 7-9</h2>
            </div>
            <div class="khan-showcase">
                <div class="showcase-image">
                    <img src="../assets/images/mma.png" alt="Khan 7-9 Training">
                </div>
                <div class="showcase-content"> 
                    <h3 style="font-size: 1.8rem; margin-bottom: 0.5rem; color: #fff;">Mastering the Art</h3>
                    <div class="prajioud-badge">
                        <div class="prajioud-icon"></div>
                        Blue/Red Prajioud
                    </div>
                    <p style="color: #ddd; line-height: 1.8; margin: 1.5rem 0;">
                        Achieve technical mastery and begin weapons training. Qualify for instructor certification and
                        advanced combat strategies.
                    </p>
                    <div class="focus-list">
                        <div class="focus-item">
                            <span class="focus-icon">⚔️</span>
                            <span>Weapon Training</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">🎓</span>
                            <span>Instructor Path</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">🧠</span>
                            <span>Strategy & Tactics</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">⭐</span>
                            <span>Complete Mastery</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Khan 10-12: Expert -->
        <div class="khan-level-section level-red">
            <div class="level-header">
                <span class="level-badge">Expert Level</span>
                <h2 style="color: var(--color-primary); font-size: 2.5rem;">Khan 10-12</h2>
            </div>
            <div class="khan-showcase reverse">
                <div class="showcase-content"> 
                    <h3 style="font-size: 1.8rem; margin-bottom: 0.5rem; color: #fff;">Teaching Excellence</h3>
                    <div class="prajioud-badge">
                        <div class="prajioud-icon"></div>
                        Red/Gold Prajioud
                    </div>
                    <p style="color: #ddd; line-height: 1.8; margin: 1.5rem 0;">
                        Lead academies, certify students, and preserve traditional knowledge. Develop curriculum and
                        mentor future instructors.
                    </p>
                    <div class="focus-list">
                        <div class="focus-item">
                            <span class="focus-icon">🏆</span>
                            <span>Academy Leadership</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">📜</span>
                            <span>Curriculum Design</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">🎖️</span>
                            <span>Student Certification</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">🔱</span>
                            <span>Lineage Keeper</span>
                        </div>
                    </div>
                </div>
                <div class="showcase-image">
                    <img src="../assets/images/rusha.png" alt="Khan 10-12 Training">
                </div>
            </div>
        </div>

        <!-- Khan 13-16: Master -->
        <div class="khan-level-section level-master">
            <div class="level-header">
                <span class="level-badge">Grand Master Level</span>
                <h2 style="color: var(--color-primary); font-size: 2.5rem;">Khan 13-16</h2>
            </div>
            <div class="khan-showcase">
                <div class="showcase-image">
                    <img src="../assets/images/mma.png" alt="Khan 13-16 Grand Master">
                </div>
                <div class="showcase-content"> 
                    <h3 style="font-size: 1.8rem; margin-bottom: 0.5rem; color: #fff;">Lineage Holder</h3>
                    <div class="prajioud-badge">
                        <div class="prajioud-icon"></div>
                        Black/Gold Prajioud
                    </div>
                    <p style="color: #ddd; line-height: 1.8; margin: 1.5rem 0;">
                        The highest honor - recognized lineage holders who preserve and transmit authentic Muayboran
                        worldwide.
                    </p>
                    <div class="focus-list">
                        <div class="focus-item">
                            <span class="focus-icon">👑</span>
                            <span>Complete Mastery</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">🌏</span>
                            <span>Global Recognition</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">📚</span>
                            <span>Cultural Guardian</span>
                        </div>
                        <div class="focus-item">
                            <span class="focus-icon">🕉️</span>
                            <span>Lineage Transmission</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <div style="position: relative; z-index: 1;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Begin Your Khan Journey Today</h2>
                <p
                    style="font-size: 1.2rem; margin-bottom: 2.5rem; opacity: 0.95; max-width: 600px; margin-left: auto; margin-right: auto; margin-bottom: 2.5rem;">
                    Take the first step towards mastery. Every grand master started at Khan 1.
                </p>
                <a href="register.php" class="btn"
                    style="background: white; color: var(--color-primary); padding: 1.3rem 3.5rem; font-size: 1.15rem; border-radius: 50px; text-decoration: none; display: inline-block; font-weight: bold; box-shadow: 0 4px 20px rgba(0,0,0,0.3); transition: all 0.3s ease;">
                    Start Training Now →
                </a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>