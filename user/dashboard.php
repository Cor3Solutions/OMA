<?php
$page_title = "Dashboard";
require_once '../config/database.php';
requireLogin();

$conn = getDbConnection();
$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($user_role === 'admin') {
    header('Location: ' . SITE_URL . '/admin/index.php'); exit;
}

$user      = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$role_data = null;
$courses   = [];
$my_students = [];

if ($user_role === 'member') {
    $role_data = $conn->query("
        SELECT km.*, i.name as instructor_name
        FROM khan_members km
        LEFT JOIN instructors i ON km.instructor_id = i.id
        WHERE km.user_id = $user_id
    ")->fetch_assoc();

    $pending_refresher = null;
    if ($role_data && $role_data['status'] === 'refresher') {
        $pending_refresher = $conn->query("
            SELECT id FROM refresher_requests
            WHERE member_id = " . (int)$role_data['id'] . " AND status = 'pending' LIMIT 1
        ")->fetch_assoc();
    }

    $lvl = $role_data['current_khan_level'] ?? 1;
    $courses_q = $conn->query("
        SELECT * FROM course_materials
        WHERE status = 'published' AND (is_public = 1 OR khan_level_min <= $lvl)
        ORDER BY khan_level_min ASC, display_order ASC, title ASC
    ");
    while ($c = $courses_q->fetch_assoc()) { $courses[] = $c; }

} elseif ($user_role === 'instructor') {
    $role_data = $conn->query("SELECT * FROM instructors WHERE user_id = $user_id")->fetch_assoc();

    $courses_q = $conn->query("SELECT * FROM course_materials WHERE status = 'published' ORDER BY category, display_order, title");
    while ($c = $courses_q->fetch_assoc()) { $courses[] = $c; }

    if ($role_data) {
        $sq = $conn->query("SELECT * FROM khan_members WHERE instructor_id = " . $role_data['id'] . " ORDER BY current_khan_level DESC");
        while ($s = $sq->fetch_assoc()) { $my_students[] = $s; }
    }
}

include 'includes/user_header.php';
?>

<div class="dashboard-container">

    <?php if ($user_role === 'member' && !empty($role_data) && $role_data['status'] === 'refresher'): ?>
    <div style="background:linear-gradient(135deg,#7c2d12,#431407);color:white;border-radius:12px;padding:1.25rem 1.75rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;box-shadow:0 4px 20px rgba(124,45,18,0.35);border:1px solid rgba(255,159,67,0.25);">
        <div style="display:flex;align-items:center;gap:1rem;">
            <div style="width:44px;height:44px;background:rgba(255,255,255,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:2px solid rgba(255,200,100,0.3);">
                <i class="fas fa-exclamation-triangle" style="color:#fbbf24;font-size:1.1rem;"></i>
            </div>
            <div>
                <strong style="display:block;font-family:'Cinzel',serif;font-size:0.9rem;margin-bottom:0.2rem;letter-spacing:0.04em;">Status: Needs Refresher</strong>
                <span style="font-size:0.8rem;opacity:0.8;">No training recorded in 3 months. Submit a request to restore Active status.</span>
            </div>
        </div>
        <?php if ($pending_refresher): ?>
            <div style="background:rgba(255,255,255,0.12);padding:0.5rem 1.1rem;border-radius:8px;font-size:0.8rem;font-weight:600;white-space:nowrap;border:1px solid rgba(255,255,255,0.2);">
                <i class="fas fa-clock"></i> Request Pending Review
            </div>
        <?php else: ?>
            <a href="refresher_request.php" style="background:rgba(255,255,255,0.9);color:#7c2d12;padding:0.55rem 1.25rem;border-radius:8px;font-size:0.83rem;font-weight:700;text-decoration:none;white-space:nowrap;">
                <i class="fas fa-paper-plane"></i> Submit Request
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?></h1>
            <p><?php echo date('l, F j, Y'); ?></p>
            <div class="user-role-badge">
                <span class="badge badge-<?php echo $user_role; ?>"><?php echo ucfirst($user_role); ?></span>
            </div>
        </div>
        <?php if ($role_data && !empty($role_data['photo_path'])): ?>
            <div class="user-avatar-large">
                <img src="<?php echo SITE_URL . '/' . $role_data['photo_path']; ?>" alt="Profile">
            </div>
        <?php else: ?>
            <div class="user-avatar-large-placeholder">
                <?php echo strtoupper(substr($user['name'],0,1)); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-id-badge"></i></div>
            <div class="stat-content">
                <div class="stat-label">Serial No.</div>
                <div class="stat-value" style="font-size:1.2rem;font-family:'DM Sans',sans-serif;font-weight:700;">
                    <?php echo htmlspecialchars($user['serial_number']); ?>
                </div>
            </div>
        </div>

        <?php if ($user_role === 'member'): ?>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#92400e,#451a03);"><i class="fas fa-award"></i></div>
            <div class="stat-content">
                <div class="stat-label">Khan Rank</div>
                <div class="stat-value">
                    <?php echo $role_data['current_khan_level'] ?? 1; ?>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,var(--gold),#a07c2a);"><i class="fas fa-user-tie"></i></div>
            <div class="stat-content">
                <div class="stat-label">Instructor</div>
                <div class="stat-value" style="font-size:1rem;font-family:'DM Sans',sans-serif;line-height:1.3;padding-top:4px;">
                    <?php echo htmlspecialchars($role_data['instructor_name'] ?? 'None'); ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#1e40af,#1e3a8a);"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <div class="stat-label">Students</div>
                <div class="stat-value"><?php echo count($my_students); ?></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#065f46,#022c22);"><i class="fas fa-book-open"></i></div>
            <div class="stat-content">
                <div class="stat-label">Courses</div>
                <div class="stat-value"><?php echo count($courses); ?></div>
            </div>
        </div>
    </div>

    <!-- Recent Materials -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-layer-group"></i> Recent Materials</h2>
            <a href="courses.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-right"></i> View All</a>
        </div>

        <?php if (count($courses) > 0): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.25rem;">
            <?php foreach (array_slice($courses,0,3) as $course): ?>
            <div style="background:var(--light);border-radius:10px;overflow:hidden;border:1px solid var(--border);transition:var(--transition);display:flex;flex-direction:column;"
                 onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.1)'"
                 onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                <div style="height:140px;background:linear-gradient(135deg,var(--crimson-dark),var(--ink));position:relative;overflow:hidden;">
                    <?php if ($course['thumbnail_path']): ?>
                        <img src="<?php echo SITE_URL . '/' . $course['thumbnail_path']; ?>" style="width:100%;height:100%;object-fit:cover;opacity:0.85;">
                    <?php else: ?>
                        <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:2.5rem;color:rgba(201,168,76,0.35);">
                            <i class="fas fa-scroll"></i>
                        </div>
                    <?php endif; ?>
                    <div style="position:absolute;top:10px;left:10px;">
                        <span style="background:rgba(0,0,0,0.6);color:var(--gold);padding:3px 10px;border-radius:50px;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;backdrop-filter:blur(4px);">
                            <?php echo ucfirst($course['category']); ?>
                        </span>
                    </div>
                </div>
                <div style="padding:1.125rem;flex:1;display:flex;flex-direction:column;">
                    <div style="font-family:'Cinzel',serif;font-weight:700;font-size:0.925rem;color:var(--ink);margin-bottom:0.375rem;line-height:1.3;">
                        <?php echo htmlspecialchars($course['title']); ?>
                    </div>
                    <div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:1rem;flex:1;">
                        Khan Level <?php echo $course['khan_level_min']; ?>–<?php echo $course['khan_level_max']; ?>
                    </div>
                    <a href="view_course.php?id=<?php echo $course['id']; ?>"
                       class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-play"></i> Start Module
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h3>No Materials Yet</h3>
            <p>Training materials will appear here as they are assigned to your level.</p>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($user_role === 'instructor' && count($my_students) > 0): ?>
    <!-- Instructor quick view -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-users"></i> My Students</h2>
            <a href="instructor_students.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-right"></i> View All</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
            <?php foreach (array_slice($my_students,0,6) as $s): ?>
            <div style="background:var(--light);padding:1.125rem;border-radius:var(--radius);border:1px solid var(--border);display:flex;align-items:center;gap:0.875rem;">
                <div class="cell-avatar" style="width:44px;height:44px;font-size:1rem;flex-shrink:0;"><?php echo strtoupper(substr($s['full_name'],0,1)); ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:0.875rem;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($s['full_name']); ?></div>
                    <div style="font-size:0.75rem;color:var(--text-muted);">Khan <?php echo $s['current_khan_level']; ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include 'includes/user_footer.php'; ?>