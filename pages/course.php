<?php
$page_title = "Courses";
include '../includes/header.php';

// Get course materials if user is logged in
$conn = getDbConnection();
$user_khan_level = 0;

// Check if user is logged in and get their khan level
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $member_query = $conn->query("SELECT current_khan_level FROM khan_members WHERE user_id = $user_id");
    if ($member = $member_query->fetch_assoc()) {
        $user_khan_level = $member['current_khan_level'];
    }
}

// Get public course materials or member-accessible materials
if (isLoggedIn() && $user_khan_level > 0) {
    $courses = $conn->query("
        SELECT * FROM course_materials 
        WHERE status = 'published' 
        AND khan_level_min <= $user_khan_level 
        ORDER BY category, display_order ASC
    ");
} else {
    $courses = $conn->query("
        SELECT * FROM course_materials 
        WHERE status = 'published' 
        AND is_public = 1 
        ORDER BY category, display_order ASC
    ");
}
?>

<section class="section">
    <div class="container">
        <div class="section-header text-center" style="
    min-height: 300px;
    background:
      linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.35)),
      url('../assets/images/courses.jpg')
 center / cover no-repeat;
    padding: 2rem 2rem;
    border-radius: 12px;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
">
            <br><br><br>
            <p class="section-subtitle" style="margin-bottom: 0.5rem;">Training Programs</p>
            <h1 class="hero-title"
                style="color:#ffffff; text-shadow: 0 4px 8px rgba(202, 19, 19, 0.6); margin-bottom: 0.5rem;">
                Our Courses
            </h1>
            <p class="section-description" style="margin-bottom: 0;">
                Comprehensive training in traditional Muay Boran martial arts.
            </p>
        </div>

        <div style="max-width: 900px; margin: 3rem auto;">
            <div style="margin-bottom: 3rem;">
                <h2 style="color: var(--color-primary); margin-bottom: 1.5rem;">The Curriculum</h2>
                <p style="font-size: 1.15rem; line-height: 1.8; color: var(--color-text-light);">
                    We cover all aspects of traditional Muay Boran, from standing combat to ground fighting. Our
                    students study:
                </p>
                <ul style="font-size: 1.1rem; line-height: 2; color: var(--color-text-light); columns: 2; margin-top: 1rem;">
                    <li>Strikes (Punches, Kicks, Elbows, Knees)</li>
                    <li>Clinching & Grappling</li>
                    <li>Throws, Breaking, & Defense</li>
                    <li>Ram Muay & Wai Kru Rituals</li>
                    <li><strong>Krabi Krabong</strong> (Traditional Weaponry)</li>
                    <li>Thai History & Philosophy</li>
                </ul>
            </div>

            <?php if (isLoggedIn() && $user_khan_level > 0): ?>
                <!-- Show available course materials for logged-in members -->
                <div style="margin-bottom: 3rem;">
                    <h2 style="color: var(--color-primary); margin-bottom: 1.5rem;">Available Course Materials</h2>
                    <p style="font-size: 1.1rem; color: var(--color-text-light); margin-bottom: 2rem;">
                        Based on your current level (Khan <?php echo $user_khan_level; ?>), these materials are available to you:
                    </p>

                    <?php if ($courses->num_rows > 0): ?>
                        <div style="display: grid; gap: 1.5rem;">
                            <?php 
                            $current_category = '';
                            while ($course = $courses->fetch_assoc()): 
                                if ($current_category != $course['category']) {
                                    if ($current_category != '') echo '</div>';
                                    $current_category = $course['category'];
                                    echo '<h3 style="color: var(--color-secondary); margin-top: 2rem; margin-bottom: 1rem;">' 
                                         . ucfirst($current_category) . ' Level</h3>';
                                    echo '<div style="display: grid; gap: 1rem;">';
                                }
                            ?>
                                <div class="card" style="padding: 1.5rem;">
                                    <div style="display: flex; gap: 1.5rem; align-items: start;">
                                        <?php if (!empty($course['thumbnail_path'])): ?>
                                            <img src="../<?php echo htmlspecialchars($course['thumbnail_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($course['title']); ?>"
                                                 style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px;">
                                        <?php endif; ?>
                                        <div style="flex: 1;">
                                            <h4 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($course['title']); ?></h4>
                                            <p style="color: var(--color-text-light); font-size: 0.95rem; margin-bottom: 0.5rem;">
                                                <?php echo htmlspecialchars($course['description']); ?>
                                            </p>
                                            <div style="display: flex; gap: 1rem; font-size: 0.9rem; color: var(--color-text-light);">
                                                <span>📊 Khan <?php echo $course['khan_level_min']; ?>-<?php echo $course['khan_level_max']; ?></span>
                                                <?php if ($course['duration_minutes']): ?>
                                                    <span>⏱️ <?php echo $course['duration_minutes']; ?> min</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($course['video_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($course['video_url']); ?>" 
                                                   target="_blank" 
                                                   class="btn btn-primary btn-sm" 
                                                   style="margin-top: 1rem;">
                                                    Watch Video
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($course['file_path'])): ?>
                                                <a href="../<?php echo htmlspecialchars($course['file_path']); ?>" 
                                                   target="_blank" 
                                                   class="btn btn-outline btn-sm" 
                                                   style="margin-top: 1rem;">
                                                    Download Material
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p style="text-align: center; padding: 2rem; background: var(--color-bg-light); border-radius: 8px;">
                                No course materials available for your current level yet. Check back soon!
                            </p>
                        <?php endif; ?>
                    </div>
            <?php else: ?>
                <!-- Show enrollment message for non-members -->
                <div style="margin-bottom: 3rem; background: var(--color-light); padding: 2rem; border-radius: 8px; border-left: 5px solid var(--color-primary);">
                    <h2 style="color: var(--color-primary); margin-bottom: 1.5rem;">Access Course Materials</h2>
                    <p style="font-size: 1.15rem; line-height: 1.8; color: var(--color-text-light); margin-bottom: 1rem;">
                        To access detailed course materials, training schedules, and exclusive content, enrollment in our
                        programs is required.
                    </p>
                    <p style="font-size: 1.15rem; line-height: 1.8; color: var(--color-text-light); margin-bottom: 1.5rem;">
                        Contact us to learn about enrollment options and begin your journey in traditional Muay Boran.
                    </p>
                    <div style="text-align: center;">
                        <?php if (isLoggedIn()): ?>
                            <a href="contact.php" class="btn btn-primary" style="margin-right: 1rem;">Contact Us to Enroll</a>
                        <?php else: ?>
                            <a href="contact.php" class="btn btn-primary" style="margin-right: 1rem;">Register Now</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
