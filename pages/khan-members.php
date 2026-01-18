<?php
$page_title = "Khan Members";
require_once '../config/database.php';

// Fetch all khan members with their details
$conn = getDbConnection();
$sql = "SELECT km.*, u.name as user_name, i.name as instructor_name, i.photo_path as instructor_photo
        FROM khan_members km 
        LEFT JOIN users u ON km.user_id = u.id
        LEFT JOIN instructors i ON km.instructor_id = i.id 
        ORDER BY 
            CASE km.status
                WHEN 'active' THEN 1
                WHEN 'inactive' THEN 2
                WHEN 'graduated' THEN 3
            END,
            km.current_khan_level DESC, 
            km.full_name ASC";
$result = $conn->query($sql);
$members = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
}

// Khan level colors (1-16) with images
$khan_colors = [
    1 => ['color' => '#FFFFFF', 'text' => '#000000', 'name' => 'White Khan', 'desc' => 'Beginner - Pure potential'],
    2 => ['color' => '#FFEB3B', 'text' => '#000000', 'name' => 'Yellow Khan', 'desc' => 'First light of knowledge'],
    3 => ['color' => '#FFA726', 'text' => '#000000', 'name' => 'Orange Khan', 'desc' => 'Growing strength'],
    4 => ['color' => '#66BB6A', 'text' => '#FFFFFF', 'name' => 'Green Khan', 'desc' => 'Flourishing skills'],
    5 => ['color' => '#42A5F5', 'text' => '#FFFFFF', 'name' => 'Blue Khan', 'desc' => 'Deep understanding'],
    6 => ['color' => '#AB47BC', 'text' => '#FFFFFF', 'name' => 'Purple Khan', 'desc' => 'Noble warrior'],
    7 => ['color' => '#8D6E63', 'text' => '#FFFFFF', 'name' => 'Brown Khan', 'desc' => 'Grounded mastery'],
    8 => ['color' => '#EF5350', 'text' => '#FFFFFF', 'name' => 'Red Khan', 'desc' => 'Warrior spirit'],
    9 => ['color' => '#000000', 'text' => '#FFFFFF', 'name' => 'Black Khan', 'desc' => 'Advanced master'],
    10 => ['color' => '#D32F2F', 'text' => '#FFFFFF', 'name' => 'Red Master', 'desc' => 'Expert instructor'],
    11 => ['color' => '#C62828', 'text' => '#FFD700', 'name' => 'Red/Gold I', 'desc' => 'Senior master'],
    12 => ['color' => '#B71C1C', 'text' => '#FFD700', 'name' => 'Red/Gold II', 'desc' => 'Grand master'],
    13 => ['color' => '#880E4F', 'text' => '#FFD700', 'name' => 'Crimson/Gold', 'desc' => 'Supreme master'],
    14 => ['color' => '#4A148C', 'text' => '#FFD700', 'name' => 'Purple/Gold', 'desc' => 'Legendary master'],
    15 => ['color' => '#1A237E', 'text' => '#FFD700', 'name' => 'Navy/Gold', 'desc' => 'Grandmaster'],
    16 => ['color' => '#000000', 'text' => '#FFD700', 'name' => 'Black/Gold', 'desc' => 'Supreme Grandmaster']
];

include '../includes/header.php';
?>

<style>
/* Khan Level Slider Styles */
.khan-slider-container {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border-radius: 12px;
    padding: 3rem 1rem;
    margin-bottom: 3rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

.khan-slider {
    display: flex;
    transition: transform 0.5s ease-in-out;
    gap: 2rem;
    padding: 0 2rem;
}

.khan-slide {
    min-width: 300px;
    flex-shrink: 0;
    text-align: center;
    padding: 2rem;
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    border: 2px solid rgba(255,255,255,0.1);
    transition: transform 0.3s ease;
}

.khan-slide:hover {
    transform: translateY(-10px);
    border-color: rgba(255,255,255,0.3);
}

.khan-belt-visual {
    width: 200px;
    height: 200px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    font-weight: bold;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    position: relative;
    border: 5px solid rgba(255,255,255,0.2);
}

.khan-belt-visual::after {
    content: '';
    position: absolute;
    top: -10px;
    right: -10px;
    bottom: -10px;
    left: -10px;
    border-radius: 50%;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
    animation: shine 3s infinite;
}

@keyframes shine {
    0%, 100% { opacity: 0; }
    50% { opacity: 1; }
}

.slider-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.9);
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.5rem;
    color: #333;
    z-index: 10;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.slider-nav:hover {
    background: white;
    transform: translateY(-50%) scale(1.1);
}

.slider-nav.prev { left: 1rem; }
.slider-nav.next { right: 1rem; }

/* Member Card Enhanced Styles */
.member-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
}

.member-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.member-photo-container {
    width: 100%;
    height: 280px;
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
}

.member-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.member-initial-circle {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 5rem;
    font-weight: bold;
}

.status-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.75rem;
    font-weight: bold;
    text-transform: uppercase;
    color: white;
    z-index: 10;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.khan-level-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1rem;
    text-align: center;
    backdrop-filter: blur(10px);
}

.khan-badge {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border-radius: 30px;
    font-weight: bold;
    font-size: 0.95rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    border: 2px solid rgba(255,255,255,0.3);
}

.member-info {
    padding: 1.5rem;
}

.member-name {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1a1a1a;
    margin-bottom: 1rem;
    text-align: center;
}

.member-details {
    font-size: 0.95rem;
    color: #666;
    line-height: 1.8;
}

.member-details-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.member-details-item:last-child {
    border-bottom: none;
}

.member-details-item strong {
    color: #333;
    min-width: 100px;
}

/* Responsive */
@media (max-width: 768px) {
    .khan-slide {
        min-width: 250px;
    }
    .khan-belt-visual {
        width: 150px;
        height: 150px;
        font-size: 3rem;
    }
    .member-photo-container {
        height: 220px;
    }
}
</style>

<section class="section">
    <div class="container">
        <div class="section-header text-center">
            <p class="section-subtitle">Our Community</p>
            <h1 class="section-title">Khan Members</h1>
            <p class="section-description">
                Certified practitioners advancing through the Khan grading system
            </p>
        </div>
        
        <div style="max-width: 1400px; margin: 3rem auto;">
            
            <!-- Khan Level Colors Slider -->
            <div style="margin-bottom: 3rem;">
                <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2rem; color: var(--color-primary);">
                    🥋 The Khan Journey: 16 Levels of Mastery
                </h2>
                
                <div class="khan-slider-container">
                    <button class="slider-nav prev" onclick="slideKhan(-1)">‹</button>
                    <button class="slider-nav next" onclick="slideKhan(1)">›</button>
                    
                    <div class="khan-slider" id="khanSlider">
                        <?php foreach ($khan_colors as $level => $info): ?>
                            <div class="khan-slide">
                                <div class="khan-belt-visual" style="background: <?php echo $info['color']; ?>; color: <?php echo $info['text']; ?>;">
                                    <?php echo $level; ?>
                                </div>
                                <h3 style="color: white; margin-bottom: 0.5rem; font-size: 1.3rem;">
                                    Khan <?php echo $level; ?>
                                </h3>
                                <h4 style="color: <?php echo $info['color']; ?>; margin-bottom: 0.5rem;">
                                    <?php echo $info['name']; ?>
                                </h4>
                                <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                                    <?php echo $info['desc']; ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 1.5rem; color: #666;">
                    <p>← Swipe or use arrows to explore all 16 Khan levels →</p>
                </div>
            </div>
            
            <!-- What is Khan Info -->
            <div class="card" style="margin-bottom: 3rem; padding: 2rem;">
                <h3 style="color: var(--color-primary); margin-bottom: 1rem; font-size: 1.5rem;">📖 What is Khan?</h3>
                <p style="color: var(--color-text-light); line-height: 1.8; font-size: 1.05rem;">
                    The Khan system is the traditional ranking structure in Muayboran, similar to belt 
                    levels in other martial arts. It represents a practitioner's knowledge, skill, and 
                    dedication to the art. Each Khan level requires mastery of specific techniques, forms, 
                    and philosophical understanding. The journey from White Khan to Black/Gold Grandmaster 
                    represents years of dedication, discipline, and mastery.
                </p>
            </div>
            
            <!-- Members List -->
            <div class="section-header" style="margin-top: 4rem; text-align: center;">
                <h2 class="section-title">Our Distinguished Members</h2>
                <p class="section-description" style="font-size: 1.1rem;">
                    <?php echo count($members); ?> certified practitioners on their Khan journey
                </p>
            </div>
            
            <?php if (empty($members)): ?>
                <div class="card text-center" style="padding: 4rem; margin-top: 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🥋</div>
                    <p style="color: var(--color-text-light); font-size: 1.2rem;">
                        Our Khan members will be showcased here soon!
                    </p>
                    <p style="color: var(--color-text-light); margin-top: 1rem;">
                        Check back to see our growing community of practitioners.
                    </p>
                </div>
            <?php else: ?>
                <!-- Members Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2.5rem; margin-top: 3rem;">
                    <?php foreach ($members as $member): 
                        $khan_level = (int)$member['current_khan_level'];
                        $color_info = isset($khan_colors[$khan_level]) ? $khan_colors[$khan_level] : ['color' => '#999', 'text' => '#FFF', 'name' => 'Khan ' . $khan_level, 'desc' => ''];
                        
                        // Status colors
                        $status_colors = [
                            'active' => '#4CAF50',
                            'inactive' => '#9E9E9E',
                            'graduated' => '#2196F3'
                        ];
                        $status_color = isset($status_colors[$member['status']]) ? $status_colors[$member['status']] : '#999';
                        
                        // Determine photo path
                        $has_photo = !empty($member['photo_path']) && file_exists('../' . $member['photo_path']);
                        $photo_path = $has_photo ? '../' . $member['photo_path'] : '';
                        
                        // Get initial for fallback
                        $initial = strtoupper(substr($member['full_name'], 0, 1));
                    ?>
                        <div class="member-card">
                            <!-- Member Photo Container -->
                            <div class="member-photo-container" style="background: <?php echo $color_info['color']; ?>;">
                                <!-- Status Badge -->
                                <div class="status-badge" style="background: <?php echo $status_color; ?>;">
                                    <?php echo htmlspecialchars($member['status']); ?>
                                </div>
                                
                                <!-- Photo or Initial -->
                                <?php if ($has_photo): ?>
                                    <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="<?php echo htmlspecialchars($member['full_name']); ?>" class="member-photo">
                                <?php else: ?>
                                    <div class="member-initial-circle" style="color: <?php echo $color_info['text']; ?>;">
                                        <?php echo $initial; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Khan Level Overlay -->
                                <div class="khan-level-overlay">
                                    <div class="khan-badge" style="background: <?php echo $color_info['color']; ?>; color: <?php echo $color_info['text']; ?>;">
                                        ⭐ Khan Level <?php echo $khan_level; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Member Info -->
                            <div class="member-info">
                                <h3 class="member-name">
                                    <?php echo htmlspecialchars($member['full_name']); ?>
                                </h3>
                                
                                <div class="member-details">
                                    <?php if (!empty($member['instructor_name'])): ?>
                                        <div class="member-details-item">
                                            <span>👨‍🏫</span>
                                            <strong>Instructor:</strong>
                                            <span><?php echo htmlspecialchars($member['instructor_name']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($member['training_location'])): ?>
                                        <div class="member-details-item">
                                            <span>📍</span>
                                            <strong>Location:</strong>
                                            <span><?php echo htmlspecialchars($member['training_location']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($member['date_joined'])): ?>
                                        <div class="member-details-item">
                                            <span>📅</span>
                                            <strong>Since:</strong>
                                            <span><?php echo date('F Y', strtotime($member['date_joined'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($member['date_promoted']) && $member['date_promoted'] != '0000-00-00'): ?>
                                        <div class="member-details-item">
                                            <span>⬆️</span>
                                            <strong>Promoted:</strong>
                                            <span><?php echo date('M Y', strtotime($member['date_promoted'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Call to Action -->
            <div style="text-align: center; margin-top: 5rem; padding: 3rem; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); border-radius: 16px; color: white;">
                <h2 style="margin-bottom: 1rem; font-size: 2rem; color: white;">Begin Your Khan Journey Today</h2>
                <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.95;">
                    Join our community of dedicated practitioners and start your path to mastery
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="khan-grading.php" class="btn" style="background: white; color: var(--color-primary); border: none; padding: 1rem 2rem; font-size: 1.1rem;">
                        📊 View Grading Structure
                    </a>
                    <a href="contact.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white; padding: 1rem 2rem; font-size: 1.1rem;">
                        ✍️ Submit Membership Inquiry
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Khan Level Slider
let currentSlide = 0;
const slider = document.getElementById('khanSlider');
const slides = slider.children.length;
const slideWidth = 316; // 300px + 16px gap

function slideKhan(direction) {
    currentSlide += direction;
    
    // Loop around
    if (currentSlide < 0) {
        currentSlide = slides - 4;
    } else if (currentSlide > slides - 4) {
        currentSlide = 0;
    }
    
    slider.style.transform = `translateX(-${currentSlide * slideWidth}px)`;
}

// Auto-slide every 5 seconds
setInterval(() => {
    slideKhan(1);
}, 5000);

// Touch support for mobile
let touchStartX = 0;
let touchEndX = 0;

slider.addEventListener('touchstart', e => {
    touchStartX = e.changedTouches[0].screenX;
});

slider.addEventListener('touchend', e => {
    touchEndX = e.changedTouches[0].screenX;
    if (touchStartX - touchEndX > 50) {
        slideKhan(1);
    } else if (touchEndX - touchStartX > 50) {
        slideKhan(-1);
    }
});
</script>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>