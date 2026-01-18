<?php
$page_title = "My Dashboard";
require_once '../config/database.php';
requireLogin();

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];

// Get user information
$user = getUserById($user_id);

// Check if user is a khan member
$khan_member_query = $conn->query("SELECT * FROM khan_members WHERE user_id = $user_id");
$khan_member = $khan_member_query->fetch_assoc();

// Get recent course materials (if member)
$recent_materials = null;
if ($khan_member) {
    $khan_level = $khan_member['current_khan_level'];
    $recent_materials = $conn->query("SELECT * FROM course_materials WHERE khan_level_min <= $khan_level AND status = 'published' ORDER BY created_at DESC LIMIT 5");
}

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
            <p style="font-size: 1.1rem; color: var(--color-text-light);">Your Oriental Muayboran Academy Dashboard</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
            <!-- User Info Card -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem; color: var(--color-primary);">Account Information</h3>
                <div style="line-height: 2;">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></p>
                    <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
                    <p><strong>Status:</strong> <span class="badge badge-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></p>
                </div>
            </div>
            
            <!-- Khan Progress Card -->
            <?php if ($khan_member): ?>
            <div class="card" style="border-left: 5px solid var(--color-primary);">
                <h3 style="margin-bottom: 1.5rem; color: var(--color-primary);">Khan Progress</h3>
                <div style="line-height: 2;">
                    <p><strong>Current Level:</strong> Khan <?php echo $khan_member['current_khan_level']; ?></p>
                    <p><strong>Color Band:</strong> <?php echo htmlspecialchars($khan_member['khan_color'] ?: 'Not assigned'); ?></p>
                    <p><strong>Date Joined:</strong> <?php echo formatDate($khan_member['date_joined']); ?></p>
                    <?php if ($khan_member['date_promoted']): ?>
                    <p><strong>Last Promotion:</strong> <?php echo formatDate($khan_member['date_promoted']); ?></p>
                    <?php endif; ?>
                    <p><strong>Training Location:</strong> <?php echo htmlspecialchars($khan_member['training_location'] ?: 'Not specified'); ?></p>
                </div>
            </div>
            <?php else: ?>
            <div class="card" style="background: var(--color-bg-light);">
                <h3 style="margin-bottom: 1rem;">Join Khan Training</h3>
                <p style="margin-bottom: 1.5rem;">You're not currently enrolled in the Khan grading system. Contact us to begin your journey!</p>
                <a href="contact.php" class="btn btn-primary">Contact Us</a>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($recent_materials && $recent_materials->num_rows > 0): ?>
        <div class="card">
            <h2 style="margin-bottom: 1.5rem; color: var(--color-primary);">Available Course Materials</h2>
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--color-bg-light);">
                            <th style="padding: 1rem; text-align: left;">Title</th>
                            <th style="padding: 1rem; text-align: left;">Category</th>
                            <th style="padding: 1rem; text-align: left;">Khan Level</th>
                            <th style="padding: 1rem; text-align: left;">Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($material = $recent_materials->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid var(--color-border);">
                            <td style="padding: 1rem;">
                                <strong><?php echo htmlspecialchars($material['title']); ?></strong><br>
                                <small style="color: var(--color-text-light);"><?php echo htmlspecialchars(substr($material['description'], 0, 80)); ?>...</small>
                            </td>
                            <td style="padding: 1rem;"><?php echo ucfirst($material['category']); ?></td>
                            <td style="padding: 1rem;">Khan <?php echo $material['khan_level_min']; ?>-<?php echo $material['khan_level_max']; ?></td>
                            <td style="padding: 1rem;"><?php echo $material['duration_minutes'] ? $material['duration_minutes'] . ' min' : 'N/A'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 3rem; text-align: center;">
            <h2 style="margin-bottom: 1rem;">Quick Actions</h2>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="course.php" class="btn btn-primary">View All Courses</a>
                <a href="khan-grading.php" class="btn btn-outline">Khan Grading Info</a>
                <a href="contact.php" class="btn btn-outline">Contact Us</a>
                <?php if (isAdmin()): ?>
                <a href="../admin/index.php" class="btn btn-primary">Admin Panel</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
