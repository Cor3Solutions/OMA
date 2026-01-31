<?php
$page_title = "Student History";
require_once '../config/database.php';
requireLogin();

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];

// Security: Only allow instructors
if ($_SESSION['user_role'] !== 'instructor') {
    header('Location: dashboard.php');
    exit;
}

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if (!$student_id) {
    header('Location: instructor_students.php');
    exit;
}

// 1. Verify this student belongs to the logged-in instructor
$inst_query = $conn->query("SELECT id FROM instructors WHERE user_id = $user_id");
$instructor_data = $inst_query->fetch_assoc();

if (!$instructor_data) {
    die("Error: Instructor profile not found.");
}

$instructor_id = $instructor_data['id'];

// Check relationship and get student info
$check_query = $conn->prepare("SELECT * FROM khan_members WHERE id = ? AND instructor_id = ?");
$check_query->bind_param("ii", $student_id, $instructor_id);
$check_query->execute();
$student_result = $check_query->get_result();

if ($student_result->num_rows === 0) {
    die("Access Denied: This student is not assigned to you.");
}

$student_info = $student_result->fetch_assoc();

// 2. Fetch History
$history_query = $conn->prepare("
    SELECT * FROM khan_training_history 
    WHERE member_id = ? 
    ORDER BY training_date DESC
");
$history_query->bind_param("i", $student_id);
$history_query->execute();
$history_data = $history_query->get_result();

include 'includes/user_header.php';
?>

<div class="dashboard-container">
    <!-- Back Button & Title -->
    <div class="page-header">
        <a href="instructor_students.php" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Students
        </a>
    </div>

    <!-- Student Info Card -->
    <div class="student-info-card">
        <div class="student-avatar-section">
            <?php if (!empty($student_info['photo_path'])): ?>
                <img src="<?php echo SITE_URL . '/' . $student_info['photo_path']; ?>" alt="Student Photo" class="student-avatar-large">
            <?php else: ?>
                <div class="student-avatar-large">
                    <?php echo strtoupper(substr($student_info['full_name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="student-details">
            <h1><?php echo htmlspecialchars($student_info['full_name']); ?></h1>
            <div class="student-meta">
                <span class="meta-item">
                    <i class="fas fa-award"></i>
                    Khan Level <?php echo $student_info['current_khan_level']; ?>
                </span>
                <?php if ($student_info['khan_color']): ?>
                <span class="meta-item">
                    <i class="fas fa-palette"></i>
                    <?php echo htmlspecialchars($student_info['khan_color']); ?>
                </span>
                <?php endif; ?>
                <span class="meta-item">
                    <i class="fas fa-history"></i>
                    <?php echo $history_data->num_rows; ?> Training Records
                </span>
            </div>
        </div>
    </div>

    <!-- Training History Timeline -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-timeline"></i> Training History</h2>
        </div>

        <div class="timeline-container">
            <?php if ($history_data->num_rows > 0): ?>
                <div class="timeline">
                    <?php while ($record = $history_data->fetch_assoc()): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker <?php echo $record['status']; ?>"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="timeline-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('F j, Y', strtotime($record['training_date'])); ?>
                                    </span>
                                    <span class="badge badge-<?php echo $record['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $record['status'])); ?>
                                    </span>
                                </div>
                                
                                <h3 class="timeline-title">
                                    <i class="fas fa-award"></i>
                                    Khan Level <?php echo $record['khan_level']; ?>
                                </h3>
                                
                                <?php if (!empty($record['location'])): ?>
                                    <p class="timeline-loc">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo htmlspecialchars($record['location']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($record['notes'])): ?>
                                    <div class="timeline-notes">
                                        <strong>Training Notes:</strong>
                                        <p><?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($record['certified_date'])): ?>
                                    <div class="timeline-cert">
                                        <i class="fas fa-certificate"></i> 
                                        Certified: <?php echo date('F j, Y', strtotime($record['certified_date'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h3>No Training History</h3>
                    <p>No training records found for this student yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Page Header */
.page-header {
    margin-bottom: 1.5rem;
}

/* Student Info Card */
.student-info-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    border-radius: var(--radius-lg);
    padding: 2rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 2rem;
    box-shadow: var(--shadow-lg);
    position: relative;
    overflow: hidden;
}

.student-info-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.student-avatar-section {
    position: relative;
    z-index: 1;
}

.student-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid rgba(255, 255, 255, 0.3);
    box-shadow: var(--shadow-xl);
    object-fit: cover;
}

.student-avatar-large:not(img) {
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 700;
}

.student-details {
    flex: 1;
    position: relative;
    z-index: 1;
}

.student-details h1 {
    color: white;
    font-size: 2rem;
    margin-bottom: 1rem;
}

.student-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    opacity: 0.95;
}

.meta-item i {
    font-size: 1.125rem;
}

/* Timeline Container */
.timeline-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 1rem 0;
}

.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 12px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(to bottom, var(--primary) 0%, var(--border) 100%);
    border-radius: 2px;
}

.timeline-item {
    position: relative;
    margin-bottom: 2.5rem;
}

.timeline-marker {
    position: absolute;
    left: -40px;
    top: 8px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: white;
    border: 4px solid var(--border);
    z-index: 2;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.timeline-marker.completed,
.timeline-marker.certified {
    border-color: var(--success);
    background: var(--success);
}

.timeline-marker.in_progress {
    border-color: var(--warning);
    background: white;
}

.timeline-content {
    background: white;
    padding: 1.75rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: var(--transition);
}

.timeline-content:hover {
    box-shadow: var(--shadow-lg);
    transform: translateX(4px);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.timeline-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-light);
    font-size: 0.9375rem;
    font-weight: 500;
}

.timeline-date i {
    color: var(--primary);
}

.timeline-title {
    margin: 0 0 1rem 0;
    color: var(--primary);
    font-size: 1.375rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.timeline-loc {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9375rem;
    color: var(--text-light);
    margin-bottom: 1rem;
}

.timeline-loc i {
    color: var(--primary);
}

.timeline-notes {
    background: var(--light);
    padding: 1.25rem;
    border-radius: var(--radius-sm);
    margin-bottom: 1rem;
    border-left: 3px solid var(--primary);
}

.timeline-notes strong {
    display: block;
    color: var(--text);
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.timeline-notes p {
    color: var(--text);
    font-size: 0.9375rem;
    line-height: 1.6;
    margin: 0;
}

.timeline-cert {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9375rem;
    color: var(--success);
    font-weight: 600;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}

.timeline-cert i {
    font-size: 1.125rem;
}

/* Badge Variants */
.badge-certified {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    color: #166534;
}

.badge-completed {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
}

.badge-in_progress {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
}

/* Responsive Design */
@media (max-width: 768px) {
    .student-info-card {
        flex-direction: column;
        text-align: center;
        padding: 1.5rem;
    }
    
    .student-details h1 {
        font-size: 1.5rem;
    }
    
    .student-meta {
        justify-content: center;
        gap: 1rem;
    }
    
    .timeline {
        padding-left: 30px;
    }
    
    .timeline::before {
        left: 8px;
    }
    
    .timeline-marker {
        left: -30px;
        width: 20px;
        height: 20px;
        border-width: 3px;
    }
    
    .timeline-content {
        padding: 1.25rem;
    }
    
    .timeline-title {
        font-size: 1.125rem;
    }
    
    .timeline-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php include 'includes/user_footer.php'; ?>