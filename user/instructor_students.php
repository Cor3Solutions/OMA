<?php
$page_title = "My Students";
require_once '../config/database.php';
requireLogin();

$conn      = getDbConnection();
$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($user_role !== 'instructor') { header('Location: dashboard.php'); exit; }

$inst_query = $conn->prepare("SELECT id FROM instructors WHERE user_id = ?");
$inst_query->bind_param("i", $user_id);
$inst_query->execute();
$instructor_result = $inst_query->get_result();

$result = null;
if ($instructor_result->num_rows > 0) {
    $instructor_data  = $instructor_result->fetch_assoc();
    $instructor_db_id = $instructor_data['id'];

    $students_query = $conn->prepare("
        SELECT km.id, km.full_name, km.current_khan_level, km.khan_color, km.status, km.photo_path,
               u.email, u.phone
        FROM khan_members km
        LEFT JOIN users u ON km.user_id = u.id
        WHERE km.instructor_id = ?
        ORDER BY km.current_khan_level DESC, km.full_name ASC
    ");
    $students_query->bind_param("i", $instructor_db_id);
    $students_query->execute();
    $result = $students_query->get_result();
}

include 'includes/user_header.php';
?>

<div class="dashboard-container">

    <div>
        <h1 style="margin-bottom:0.25rem;">My Students</h1>
        <p style="color:var(--text-muted);font-size:0.9rem;margin:0;">
            <?php echo $result ? $result->num_rows : 0; ?> student<?php echo ($result && $result->num_rows !== 1) ? 's' : ''; ?> assigned to you
        </p>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Contact</th>
                    <th>Rank</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="cell-avatar">
                                <?php if (!empty($student['photo_path'])): ?>
                                    <img src="<?php echo SITE_URL.'/'.$student['photo_path']; ?>">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($student['full_name'],0,1)); ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="cell-name"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                <?php if ($student['khan_color']): ?>
                                <div style="font-size:0.75rem;color:var(--text-muted);"><?php echo htmlspecialchars($student['khan_color']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($student['email']): ?>
                            <div class="contact-item"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($student['email']); ?></div>
                        <?php endif; ?>
                        <?php if ($student['phone']): ?>
                            <div class="contact-item"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($student['phone']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-khan" style="font-size:0.75rem;">Khan <?php echo $student['current_khan_level']; ?></span>
                    </td>
                    <td>
                        <span class="status-dot <?php echo $student['status']; ?>"></span>
                        <?php echo ucfirst($student['status']); ?>
                    </td>
                    <td style="text-align:right;">
                        <a href="instructor_student_history.php?student_id=<?php echo $student['id']; ?>" class="btn btn-outline btn-sm">
                            <i class="fas fa-history"></i> View History
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="dashboard-section">
        <div class="empty-state">
            <i class="fas fa-user-friends"></i>
            <h3>No Students Found</h3>
            <p>You currently have no students assigned to you.</p>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include 'includes/user_footer.php'; ?>