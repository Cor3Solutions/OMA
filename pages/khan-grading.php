<?php
$page_title = "Khan Grading Structure";
include '../includes/header.php';
?>

<style>
    :root {
        --oma-gold: #FFD700;
        --oma-dark-red: #8B0000;
        --card-bg: #ffffff;
    }

    .khan-timeline {
        position: relative;
        max-width: 1200px; /* Increased from 1000px to accommodate larger images */
        margin: 4rem auto;
        padding: 0 20px;
    }

    /* Vertical Timeline Line */
    .khan-timeline::after {
        content: '';
        position: absolute;
        width: 4px;
        background: linear-gradient(to bottom, #ddd, var(--oma-dark-red), #333);
        top: 0;
        bottom: 0;
        left: 50%;
        margin-left: -2px;
        z-index: 0;
    }

    .khan-card {
        padding: 20px 40px;
        position: relative;
        width: 50%;
        box-sizing: border-box;
        z-index: 1;
        margin-bottom: 60px; /* Increased margin for larger cards */
    }

    .left { left: 0; }
    .right { left: 50%; }

    /* Timeline circle markers */
    .khan-card::after {
        content: '';
        position: absolute;
        width: 24px;
        height: 24px;
        background: white;
        border: 4px solid var(--oma-dark-red);
        top: 40px;
        border-radius: 50%;
        z-index: 2;
    }
    .left::after { right: -12px; }
    .right::after { left: -12px; }

    .content-box {
        padding: 35px; /* Increased padding */
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        border-top: 8px solid var(--oma-dark-red);
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
    }

    .content-box:hover {
        transform: translateY(-8px);
    }

    /* Header Layout */
    .header-layout {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 30px;
        margin-bottom: 30px;
    }

    .text-side { flex: 1; }

    .khan-label {
        font-size: 2.4rem; /* Larger font */
        font-weight: 900;
        color: #1a1a1a;
        margin: 0;
        line-height: 1;
    }

    .mongkon-status {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--oma-dark-red);
        text-transform: uppercase;
        margin-top: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* BIGGER Portrait Image */
    .portrait-main {
        width: 200px; /* Increased from 140px */
        aspect-ratio: 2 / 2.8; /* Slightly taller aspect */
        object-fit: cover;
        border-radius: 15px;
        box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        border: 2px solid #eee;
        transition: transform 0.3s ease;
    }

    .content-box:hover .portrait-main {
        transform: scale(1.03);
    }

    /* MUCH BIGGER Action Square (Mongkon Design) */
    .action-square-wrapper {
        display: flex;
        justify-content: center;
        padding-top: 25px;
        border-top: 2px solid #f5f5f5;
    }

    .action-square-img {
        width: 250px; /* Significantly increased from 100px */
        height: auto;
        object-fit: contain;
        background: transparent;
        border-radius: 0;
        padding: 0;
        border: none;
        filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
    }

    .curriculum-details {
        list-style: none;
        padding: 0;
        margin: 20px 0;
    }

    .curriculum-details li {
        font-size: 1rem;
        color: #444;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .curriculum-details li::before {
        content: '';
        width: 7px;
        height: 7px;
        background: var(--oma-dark-red);
        border-radius: 50%;
    }

    .color-swatch-dot {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 1px solid #ddd;
    }

    @media (max-width: 992px) {
        .portrait-main { width: 160px; }
        .action-square-img { width: 200px; }
    }

    @media (max-width: 768px) {
        .khan-timeline::after { left: 20px; }
        .khan-card { width: 100%; left: 0; padding-left: 55px; padding-right: 15px; }
        .khan-card::after { left: 10px; }
        .header-layout { flex-direction: column-reverse; }
        .portrait-main { width: 100%; max-width: 300px; height: auto; }
        .action-square-img { width: 100%; max-width: 250px; }
    }

    /* Dynamic Swatches */
    .sw-1 { background: #fff; }
    .sw-2 { background: #FFD700; }
    .sw-3 { background: linear-gradient(90deg, #FFD700 50%, #fff 50%); }
    .sw-4 { background: #008000; }
    .sw-5 { background: linear-gradient(90deg, #008000 50%, #fff 50%); }
    .sw-6 { background: #0000FF; }
    .sw-7 { background: linear-gradient(90deg, #0000FF 50%, #fff 50%); }
    .sw-8 { background: #8B4513; }
    .sw-9 { background: linear-gradient(90deg, #8B4513 50%, #fff 50%); }
    .sw-10 { background: #FF0000; }
    .sw-11 { background: linear-gradient(90deg, #FF0000 50%, #fff 50%); }
    .sw-12 { background: linear-gradient(90deg, #FF0000 50%, #FFD700 50%); }
    .sw-13 { background: linear-gradient(90deg, #FF0000 50%, #C0C0C0 50%); }
    .sw-14 { background: #C0C0C0; }
    .sw-15 { background: linear-gradient(90deg, #C0C0C0 50%, #FFD700 50%); }
    .sw-16 { background: #FFD700; box-shadow: 0 0 5px gold; }
</style>
<br><br><br>
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 style="color: var(--oma-dark-red); font-weight: 900; font-size: 3.5rem;">Official Khan Curriculum</h1>
        <p class="lead text-muted">Mastery from White to Gold Mongkon</p>
    </div>

    <div class="khan-timeline">
        <?php
        $khans = [
            1 => ["color" => "White", "req" => ["The Origin of Muaythai", "Thai Culture & Traditions", "Benefits of Practicing Muaythai Boran", "Oath of Muaythai Boran"]],
            2 => ["color" => "Yellow", "req" => ["Vital Points of the Body", "Arts of Punches 1", "Arts of Kicks 1", "Arts of Shoving Kicks", "Sitting Wai Kru 1 (Prom Nang)"]],
            3 => ["color" => "Yellow/White", "req" => ["The Evolution of Muayboran to Muaythai", "Insight Meditation", "Arts of Knee Strikes 1", "Arts of Elbow Strikes", "Arts of Clinching", "Sitting Wai Kru 2 (For Competition)"]],
            4 => ["color" => "Green", "req" => ["The Life of King Naresuan", "Defensive Tactics", "Counter Attack for Punch", "Counter Attack for Kick", "Counter Attack for Knee-Strikes", "Standing Wai Kru 3 (Traditional)"]],
            5 => ["color" => "Green/White", "req" => ["The Life of Phrachao Suea", "Counter Attack for Elbow Strikes", "Counter Attack for Clinch", "Arts of Counter Attacks", "Standing Wai Kru 4 (For Competition)"]],
            6 => ["color" => "Blue", "req" => ["Trainings for Amateur Athletes", "Practicum: Full Sparring/Fight in Amateur Tournament"]],
            7 => ["color" => "Blue/White", "req" => ["Trainings for Professional Athletes", "Practicum: Full Sparring/Fight in Professional Tournament"]],
            8 => ["color" => "Brown", "req" => ["The Life of Phraya Pichai", "Arts of Punches Form", "Arts of Kicks Form"]],
            //9 => ["color" => "Brown/White", "req" => ["The Life of Nai Khanom Tom", "Arts of Knee-Strikes Form", "Arts of Elbow-Strikes Form"]],
            //10 => ["color" => "Red", "req" => ["History of Muaythai in the Philippines", "15 Mae Mai Muaythai (Major Form)", "15 Luk Mai Muaythai", "The Elephant Warrior Techniques"]],
            //11 => ["color" => "Red/White", "req" => ["Nutrition and Diet for Athletes", "Thai/Sports Massage & Human Anatomy", "Muaythai Pedagogy", "Code of Conduct of an Instructor (Ethics 1)", "Muaythai Aerobics"]],
            //12 => ["color" => "Red/Yellow", "req" => ["Basic First Aid", "Trainer's Duties", "Use of Punch Training Equipments", "Gym Management", "Teaching with Moving Targets", "Practice Teaching (Practicum)"]],
            //13 => ["color" => "Red/Silver", "req" => ["Code of Conduct of a Tournament Official", "Amateur & Professional Tournament Introduction", "Amateur Refereeing and Officiating Duties", "Amateur Muaythai Judging", "Professional OMA Referee & Judge 2"]],
            //14 => ["color" => "Silver", "req" => ["Introduction to Krabi Krabong (Ancient Weaponry)", "The Art of Battleaxe (Kwan) & Dagger", "The Art of Dap, Tomown, Spear (Tuan/Hok)", "The Art of Rattan Stick (Krabong)", "The Art of Shield (Lo, Dang, Ken, Mai Sok)"]],
            //15 => ["color" => "Silver/Gold", "req" => ["Muaythai Management & Promotion", "Methods and Procedures of Marketing", "Project Proposal (Written)", "Implementation of Project Proposal (Practicum)"]],
            //16 => ["color" => "Gold", "req" => ["Yearly Re-Assessment and Upgrading", "New Techniques, Rules, and Updates Seminar", "Conference and Presentation of New Found Techniques"]]
        ];

        foreach ($khans as $id => $data) {
            $align = ($id % 2 == 0) ? 'right' : 'left';
            ?>
            <div class="khan-card <?php echo $align; ?>">
                <div class="content-box">
                    <div class="header-layout">
                        <div class="text-side">
                            <h3 class="khan-label">KHAN <?php echo $id; ?></h3>
                            <div class="mongkon-status">
                                <div class="color-swatch-dot sw-<?php echo $id; ?>"></div>
                                <?php echo $data['color']; ?>
                            </div>
                            <ul class="curriculum-details">
                                <?php foreach ($data['req'] as $item) echo "<li>$item</li>"; ?>
                            </ul>
                        </div>
                        
                        <img src="../assets/color/<?php echo $id; ?>.png" class="portrait-main" alt="Khan Level Photo">
                    </div>

                    <div class="action-square-wrapper">
                        <img src="../assets/mongkhon/<?php echo $id; ?>.png" class="action-square-img" alt="Mongkon Design">
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>