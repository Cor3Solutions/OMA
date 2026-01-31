<?php
$page_title = "My Students";
require_once '../config/database.php';
requireLogin();

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Security: Only allow instructors
if ($user_role !== 'instructor') {
    header('Location: dashboard.php');
    exit;
}

// 1. Get the Instructor's ID based on the logged-in User ID
$inst_query = $conn->prepare("SELECT id FROM instructors WHERE user_id = ?");
$inst_query->bind_param("i", $user_id);
$inst_query->execute();
$instructor_result = $inst_query->get_result();

if ($instructor_result->num_rows === 0) {
    // Handle error: User is marked as instructor but not found in instructors table
    $error = "Instructor profile not found.";
} else {
    $instructor_data = $instructor_result->fetch_assoc();
    $instructor_db_id = $instructor_data['id'];

    // 2. Fetch Students assigned to this Instructor
    $students_query = $conn->prepare("
        SELECT 
            km.id, 
            km.full_name, 
            km.current_khan_level, 
            km.khan_color, 
            km.status, 
            km.photo_path,
            u.email,
            u.phone
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
    <div class="section-header">
        <h2><i class="fas fa-users"></i> My Students</h2>
    </div>

    <?php if (isset($result) && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Contact</th>
                        <th>Rank</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="cell-avatar">
                                    <?php if (!empty($student['photo_path'])): ?>
                                        <img src="<?php echo SITE_URL . '/' . $student['photo_path']; ?>" alt="Profile">
                                    <?php else: ?>
                                        <span><?php echo strtoupper(substr($student['full_name'], 0, 1)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="cell-info">
                                    <span class="cell-name"><?php echo htmlspecialchars($student['full_name']); ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="contact-cell">
                                <?php if ($student['email']): ?>
                                    <div class="contact-item"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($student['email']); ?></div>
                                <?php endif; ?>
                                <?php if ($student['phone']): ?>
                                    <div class="contact-item"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($student['phone']); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-khan">Khan <?php echo $student['current_khan_level']; ?></span>
                            <?php if($student['khan_color']): ?>
                                <small style="display:block; color:#666; font-size:0.8em; margin-top:2px;"><?php echo $student['khan_color']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-dot <?php echo $student['status']; ?>"></span>
                            <?php echo ucfirst($student['status']); ?>
                        </td>
                        <td>
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
        <div class="empty-state">
            <i class="fas fa-user-friends"></i>
            <h3>No Students Found</h3>
            <p>You currently do not have any students assigned to you.</p>
        </div>
    <?php endif; ?>
</div>

<style>
/* Table Styles */
.table-responsive {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th, .data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.data-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #444;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cell-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    overflow: hidden;
}

.cell-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cell-name {
    font-weight: 500;
}

.contact-item {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 2px;
}

.contact-item i {
    width: 16px;
    color: #999;
}

.badge-khan {
    background: #e3f2fd;
    color: #1565c0;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 5px;
}

.status-dot.active { background: #4caf50; }
.status-dot.inactive { background: #9e9e9e; }
.status-dot.suspended { background: #f44336; }
</style>

<?php include 'includes/user_footer.php'; ?>