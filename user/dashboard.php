<?php
$page_title = "Dashboard";
require_once '../config/database.php';
requireLogin();

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($user_role === 'admin') {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$role_data = null;
$courses = [];
$my_students = [];

// FETCH DATA
if ($user_role === 'member') {
    $member_query = $conn->query("SELECT km.*, i.name as instructor_name FROM khan_members km LEFT JOIN instructors i ON km.instructor_id = i.id WHERE km.user_id = $user_id");
    $role_data = $member_query->fetch_assoc();
    
    $courses_query = $conn->query("SELECT * FROM course_materials WHERE status = 'published' AND (is_public = 1 OR khan_level_min <= " . ($role_data['current_khan_level'] ?? 1) . " AND khan_level_max >= " . ($role_data['current_khan_level'] ?? 1) . ") ORDER BY display_order, title");
    while ($course = $courses_query->fetch_assoc()) { $courses[] = $course; }
    
} elseif ($user_role === 'instructor') {
    $instructor_query = $conn->query("SELECT * FROM instructors WHERE user_id = $user_id");
    $role_data = $instructor_query->fetch_assoc();
    
    $courses_query = $conn->query("SELECT * FROM course_materials WHERE status = 'published' ORDER BY category, display_order, title");
    while ($course = $courses_query->fetch_assoc()) { $courses[] = $course; }

    if ($role_data) {
        $students_query = $conn->query("SELECT * FROM khan_members WHERE instructor_id = " . $role_data['id'] . " ORDER BY current_khan_level DESC");
        while ($student = $students_query->fetch_assoc()) { $my_students[] = $student; }
    }
}

include 'includes/user_header.php';
?>

<style>
    :root {
        --primary: #2c3e50;
        --secondary: #34495e;
        --accent: #3498db;
        --bg: #f4f6f9;
        --card: #ffffff;
        --text: #333333;
        --muted: #7f8c8d;
        --border: #e0e0e0;
        --radius: 12px;
        --shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    }

    body {
        background-color: var(--bg);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: var(--text);
        margin: 0;
        padding-bottom: 80px; /* Space for bottom nav if needed */
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    /* Mobile Typography */
    h1 { font-size: 1.5rem; margin: 0; }
    h2 { font-size: 1.25rem; margin-bottom: 1rem; color: var(--secondary); display: flex; align-items: center; gap: 10px; }
    
    /* Header */
    .dashboard-header {
        background: var(--card);
        padding: 1.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .user-info { display: flex; align-items: center; gap: 15px; }
    .avatar { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; background: #eee; }
    .badge-pill { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; background: #e3f2fd; color: #1565c0; }

    /* Grid Layouts */
    .grid-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* Responsive */
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--card);
        padding: 1.25rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border-left: 4px solid var(--accent);
    }
    .stat-card h4 { margin: 0; font-size: 0.75rem; color: var(--muted); text-transform: uppercase; }
    .stat-card p { margin: 5px 0 0; font-size: 1.5rem; font-weight: 700; color: var(--primary); }

    /* Course Cards */
    .grid-courses {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .course-card {
        background: var(--card);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
    }
    
    .course-thumb {
        height: 160px;
        background: #eee;
        position: relative;
    }
    .course-thumb img { width: 100%; height: 100%; object-fit: cover; }
    
    .course-body { padding: 1.25rem; flex: 1; display: flex; flex-direction: column; }
    .course-title { font-size: 1.1rem; font-weight: 700; margin: 0 0 0.5rem 0; }
    .course-desc { font-size: 0.9rem; color: var(--muted); margin-bottom: 1rem; flex: 1; }
    
    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 100%; padding: 12px; border-radius: 8px;
        border: none; font-weight: 600; cursor: pointer;
        font-size: 0.95rem; text-decoration: none; gap: 8px;
        transition: opacity 0.2s;
    }
    .btn-primary { background: var(--primary); color: white; }
    .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
    .btn:active { opacity: 0.8; transform: scale(0.98); }

    /* Mobile Optimization */
    @media (max-width: 768px) {
        .container { padding: 1rem; }
        .dashboard-header { flex-direction: column; align-items: flex-start; }
        .grid-stats { grid-template-columns: 1fr 1fr; }
        .grid-courses { grid-template-columns: 1fr; } /* Stack courses on phone */
    }
</style>

<div class="container">
    <div class="dashboard-header">
        <div>
            <h1>Hello, <?php echo htmlspecialchars($user['name']); ?></h1>
            <p style="margin:5px 0 0; color:var(--muted); font-size:0.9rem;"><?php echo date('F j, Y'); ?></p>
        </div>
        <div class="user-info">
            <span class="badge-pill"><?php echo ucfirst($user_role); ?></span>
            <?php if ($role_data && !empty($role_data['photo_path'])): ?>
                <img src="<?php echo SITE_URL . '/' . $role_data['photo_path']; ?>" class="avatar">
            <?php else: ?>
                <div class="avatar" style="display:flex;align-items:center;justify-content:center;font-weight:bold;color:#777;">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid-stats">
        <div class="stat-card">
            <h4>Serial No.</h4>
            <p><?php echo htmlspecialchars($user['serial_number']); ?></p>
        </div>
        
        <?php if ($user_role === 'member'): ?>
            <div class="stat-card" style="border-color: #e67e22;">
                <h4>Rank</h4>
                <p>Khan <?php echo $role_data['current_khan_level'] ?? 1; ?></p>
            </div>
            <div class="stat-card" style="border-color: #27ae60;">
                <h4>Instructor</h4>
                <p style="font-size:1rem; margin-top:10px;"><?php echo htmlspecialchars($role_data['instructor_name'] ?? 'None'); ?></p>
            </div>
        <?php else: ?>
             <div class="stat-card" style="border-color: #e67e22;">
                <h4>Students</h4>
                <p><?php echo count($my_students); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="stat-card" style="border-color: #3498db;">
            <h4>Courses</h4>
            <p><?php echo count($courses); ?></p>
        </div>
    </div>

    <div class="section">
        <h2><i class="fas fa-layer-group"></i> Recent Materials</h2>
        <?php if (count($courses) > 0): ?>
            <div class="grid-courses">
                <?php foreach (array_slice($courses, 0, 3) as $course): ?>
                <div class="course-card">
                    <div class="course-thumb">
                        <?php if ($course['thumbnail_path']): ?>
                            <img src="<?php echo SITE_URL . '/' . $course['thumbnail_path']; ?>">
                        <?php endif; ?>
                    </div>
                    <div class="course-body">
                        <div class="course-title"><?php echo htmlspecialchars($course['title']); ?></div>
                        <div class="course-desc">Level <?php echo $course['khan_level_min']; ?>-<?php echo $course['khan_level_max']; ?></div>
                        <a href="courses.php" class="btn btn-outline">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:1rem; text-align:center;">
                <a href="courses.php" class="btn btn-primary" style="max-width:200px;">View All Courses</a>
            </div>
        <?php else: ?>
            <div style="padding:2rem; text-align:center; background:white; border-radius:12px;">
                <p>No courses available currently.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/user_footer.php'; ?>