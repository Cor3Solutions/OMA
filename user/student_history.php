<?php
$page_title = "My Training History";
require_once '../config/database.php';
requireLogin();

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Redirect admins
if ($user_role === 'admin') {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

// Get member info
$member_info = null;
$history_data = [];

if ($user_role === 'member') {
    // Get member details
    $member_query = $conn->prepare("
        SELECT km.*, i.name as instructor_name 
        FROM khan_members km 
        LEFT JOIN instructors i ON km.instructor_id = i.id 
        WHERE km.user_id = ?
    ");
    $member_query->bind_param("i", $user_id);
    $member_query->execute();
    $member_info = $member_query->get_result()->fetch_assoc();
    
    if ($member_info) {
        // Fetch training history
        $history_query = $conn->prepare("
            SELECT * FROM khan_training_history 
            WHERE member_id = ? 
            ORDER BY training_date DESC
        ");
        $history_query->bind_param("i", $member_info['id']);
        $history_query->execute();
        $result = $history_query->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $history_data[] = $row;
        }
    }
} elseif ($user_role === 'instructor') {
    // Instructors see a different view - redirect to their students page
    header('Location: instructor_students.php');
    exit;
}

include 'includes/user_header.php';
?>

<div class="dashboard-container">
    <!-- Current Status Card -->
    <div class="status-card">
        <div class="status-header">
            <div class="status-icon">
                <i class="fas fa-medal"></i>
            </div>
            <div class="status-info">
                <h2>My Current Status</h2>
                <p>Track your Muayboran journey and progression</p>
            </div>
        </div>
        
        <div class="status-grid">
            <div class="status-item">
                <span class="status-label">Current Khan Level</span>
                <span class="status-value"><?php echo $member_info['current_khan_level'] ?? 'N/A'; ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Khan Color</span>
                <span class="status-value"><?php echo htmlspecialchars($member_info['khan_color'] ?? 'Not Assigned'); ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Instructor</span>
                <span class="status-value"><?php echo htmlspecialchars($member_info['instructor_name'] ?? 'Not Assigned'); ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Total Trainings</span>
                <span class="status-value"><?php echo count($history_data); ?></span>
            </div>
        </div>
    </div>

    <!-- Training History Timeline -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-history"></i> Training History</h2>
        </div>

        <div class="timeline-container">
            <?php if (count($history_data) > 0): ?>
                <div class="timeline">
                    <?php foreach ($history_data as $record): ?>
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
                                        <strong>Notes:</strong>
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
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h3>No Training History Yet</h3>
                    <p>Your training history will appear here as you progress through your Khan levels.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Status Card */
.status-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    border-radius: var(--radius-lg);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-lg);
    position: relative;
    overflow: hidden;
}

.status-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.status-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    position: relative;
    z-index: 1;
}

.status-icon {
    width: 72px;
    height: 72px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
}

.status-info h2 {
    color: white;
    margin-bottom: 0.25rem;
    font-size: 1.75rem;
}

.status-info p {
    opacity: 0.9;
    font-size: 1rem;
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    position: relative;
    z-index: 1;
}

.status-item {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    padding: 1.5rem;
    border-radius: var(--radius);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
}

.status-item:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-4px);
}

.status-label {
    display: block;
    font-size: 0.875rem;
    opacity: 0.8;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.status-value {
    display: block;
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1;
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
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(245, 158, 11, 0);
    }
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
.badge {
    display: inline-block;
    padding: 0.375rem 0.875rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.8125rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

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
    .status-card {
        padding: 1.5rem;
    }
    
    .status-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .status-info h2 {
        font-size: 1.5rem;
    }
    
    .status-grid {
        grid-template-columns: 1fr 1fr;
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

@media (max-width: 480px) {
    .status-grid {
        grid-template-columns: 1fr;
    }
    
    .status-value {
        font-size: 1.5rem;
    }
}
</style>

<?php include 'includes/user_footer.php'; ?>