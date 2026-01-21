<?php
$page_title = "Dashboard";
require_once '../config/database.php';
requireLogin();

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Redirect admins to admin panel
if ($user_role === 'admin') {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

// Get user details
$user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();

// Get role-specific data
$role_data = null;
$courses = [];

if ($user_role === 'member') {
    // Get member data
    $member_query = $conn->query("
        SELECT km.*, i.name as instructor_name 
        FROM khan_members km 
        LEFT JOIN instructors i ON km.instructor_id = i.id 
        WHERE km.user_id = $user_id
    ");
    $role_data = $member_query->fetch_assoc();
    
    // Get enrolled courses for member
    $courses_query = $conn->query("
        SELECT * FROM course_materials 
        WHERE status = 'published' 
        AND (is_public = 1 OR khan_level_min <= " . ($role_data['current_khan_level'] ?? 1) . " 
        AND khan_level_max >= " . ($role_data['current_khan_level'] ?? 1) . ")
        ORDER BY display_order, title
    ");
    while ($course = $courses_query->fetch_assoc()) {
        $courses[] = $course;
    }
    
} elseif ($user_role === 'instructor') {
    // Get instructor data
    $instructor_query = $conn->query("SELECT * FROM instructors WHERE user_id = $user_id");
    $role_data = $instructor_query->fetch_assoc();
    
    // Get all courses for instructors
    $courses_query = $conn->query("
        SELECT * FROM course_materials 
        WHERE status = 'published' 
        ORDER BY category, display_order, title
    ");
    while ($course = $courses_query->fetch_assoc()) {
        $courses[] = $course;
    }
}

include 'includes/user_header.php';
?>

<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
            <p class="user-role-badge">
                <?php if ($user_role === 'member'): ?>
                    <span class="badge badge-member">Member</span>
                <?php else: ?>
                    <span class="badge badge-instructor">Instructor</span>
                <?php endif; ?>
            </p>
        </div>
        <?php if ($role_data && !empty($role_data['photo_path'])): ?>
            <div class="user-avatar-large">
                <img src="<?php echo SITE_URL . '/' . $role_data['photo_path']; ?>" alt="Profile">
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-id-card"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Serial Number</div>
                <div class="stat-value"><?php echo htmlspecialchars($user['serial_number']); ?></div>
            </div>
        </div>

        <?php if ($user_role === 'member' && $role_data): ?>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Current Rank</div>
                    <div class="stat-value">Khan <?php echo $role_data['current_khan_level']; ?></div>
                </div>
            </div>

            <?php if ($role_data['khan_color']): ?>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-palette"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Khan Color</div>
                    <div class="stat-value"><?php echo htmlspecialchars($role_data['khan_color']); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($role_data['instructor_name']): ?>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Instructor</div>
                    <div class="stat-value"><?php echo htmlspecialchars($role_data['instructor_name']); ?></div>
                </div>
            </div>
            <?php endif; ?>
        <?php elseif ($user_role === 'instructor' && $role_data): ?>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-award"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Khan Level</div>
                    <div class="stat-value"><?php echo htmlspecialchars($role_data['khan_level']); ?></div>
                </div>
            </div>

            <?php if ($role_data['title']): ?>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Title</div>
                    <div class="stat-value"><?php echo htmlspecialchars($role_data['title']); ?></div>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Available Courses</div>
                <div class="stat-value"><?php echo count($courses); ?></div>
            </div>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-user-circle"></i> Profile Information</h2>
            <a href="profile.php" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
        </div>
        <div class="profile-grid">
            <div class="profile-item">
                <label>Full Name</label>
                <p><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <div class="profile-item">
                <label>Email</label>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="profile-item">
                <label>Phone</label>
                <p><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></p>
            </div>
            <?php if ($user_role === 'member' && $role_data): ?>
                <div class="profile-item">
                    <label>Date Joined</label>
                    <p><?php echo formatDate($role_data['date_joined']); ?></p>
                </div>
                <?php if ($role_data['training_location']): ?>
                <div class="profile-item">
                    <label>Training Location</label>
                    <p><?php echo htmlspecialchars($role_data['training_location']); ?></p>
                </div>
                <?php endif; ?>
            <?php elseif ($user_role === 'instructor' && $role_data): ?>
                <?php if ($role_data['location']): ?>
                <div class="profile-item">
                    <label>Location</label>
                    <p><?php echo htmlspecialchars($role_data['location']); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($role_data['specialization']): ?>
                <div class="profile-item">
                    <label>Specialization</label>
                    <p><?php echo htmlspecialchars($role_data['specialization']); ?></p>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Available Courses -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-graduation-cap"></i> Available Courses</h2>
        </div>
        
        <?php if (count($courses) > 0): ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <?php if ($course['thumbnail_path']): ?>
                        <div class="course-thumbnail">
                            <img src="<?php echo SITE_URL . '/' . $course['thumbnail_path']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        </div>
                    <?php endif; ?>
                    <div class="course-content">
                        <div class="course-category"><?php echo ucfirst($course['category']); ?></div>
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($course['description'], 0, 120)) . '...'; ?></p>
                        <div class="course-meta">
                            <span><i class="fas fa-layer-group"></i> Khan <?php echo $course['khan_level_min']; ?>-<?php echo $course['khan_level_max']; ?></span>
                            <?php if ($course['duration_minutes']): ?>
                                <span><i class="fas fa-clock"></i> <?php echo $course['duration_minutes']; ?> min</span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-primary btn-block" onclick="viewCourse(<?php echo $course['id']; ?>)">
                            <i class="fas fa-play-circle"></i> View Course
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h3>No Courses Available</h3>
                <p>There are currently no courses available for your level.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function viewCourse(courseId) {
    // Implement course viewing logic
    alert('Course viewing feature coming soon! Course ID: ' + courseId);
}
</script>

<?php include 'includes/user_footer.php'; ?>