<?php
$page_title = "Admin Dashboard";
require_once '../config/database.php';
requireAdmin();

// Get statistics
$conn = getDbConnection();

/**
 * --- DATABASE QUERIES ---
 */

// 1. Total Counts
$total_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'")->fetch_assoc()['total'];
$total_members = $conn->query("SELECT COUNT(*) as total FROM khan_members WHERE status = 'active'")->fetch_assoc()['total'];
$total_instructors = $conn->query("SELECT COUNT(*) as total FROM instructors WHERE status = 'active'")->fetch_assoc()['total'];
$unread_messages = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'new'")->fetch_assoc()['total'];
$total_affiliates = $conn->query("SELECT COUNT(*) as total FROM affiliates WHERE status = 'active'")->fetch_assoc()['total'];
$total_materials = $conn->query("SELECT COUNT(*) as total FROM course_materials WHERE status = 'published'")->fetch_assoc()['total'];
$total_events = $conn->query("SELECT COUNT(*) as total FROM event_gallery WHERE status = 'active'")->fetch_assoc()['total'];

// 2. Khan Level Distribution (Bar Chart)
$khan_levels_query = $conn->query("SELECT current_khan_level, COUNT(*) as count FROM khan_members GROUP BY current_khan_level ORDER BY current_khan_level ASC");
$khan_labels = [];
$khan_data = [];
while ($row = $khan_levels_query->fetch_assoc()) {
    $khan_labels[] = "Khan " . $row['current_khan_level'];
    $khan_data[] = $row['count'];
}

// 3. Member Registration Growth - Last 6 Months (Line Chart)
$growth_query = $conn->query("
    SELECT DATE_FORMAT(created_at, '%M') as month_name, COUNT(*) as count 
    FROM khan_members 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
    ORDER BY created_at ASC
");
$growth_labels = [];
$growth_data = [];
while ($row = $growth_query->fetch_assoc()) {
    $growth_labels[] = $row['month_name'];
    $growth_data[] = $row['count'];
}

// 4. Active vs Inactive Distribution (Doughnut Chart)
$status_query = $conn->query("SELECT status, COUNT(*) as count FROM khan_members GROUP BY status");
$status_labels = [];
$status_data = [];
while ($row = $status_query->fetch_assoc()) {
    $status_labels[] = ucfirst($row['status']);
    $status_data[] = $row['count'];
}

include 'includes/admin_header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1>Dashboard Overview</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e3f2fd;"><svg width="32" height="32" fill="#1976d2" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" /></svg></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($total_users); ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #fff3e0;"><svg width="32" height="32" fill="#f57c00" viewBox="0 0 24 24"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z" /></svg></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($total_members); ?></div>
                <div class="stat-label">Active Members</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #e8f5e9;"><svg width="32" height="32" fill="#388e3c" viewBox="0 0 24 24"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z" /></svg></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($total_instructors); ?></div>
                <div class="stat-label">Instructors</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #fce4ec;"><svg width="32" height="32" fill="#c2185b" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" /></svg></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($unread_messages); ?></div>
                <div class="stat-label">New Messages</div>
            </div>
        </div>
    </div>

    <div class="dashboard-content" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 25px;">
        
        <div class="dashboard-section">
            <div class="section-header"><h2>Khan Level Distribution</h2></div>
            <div style="position: relative; height: 300px;"><canvas id="khanLevelChart"></canvas></div>
        </div>

        <div class="dashboard-section">
            <div class="section-header"><h2>Member Growth (6 Months)</h2></div>
            <div style="position: relative; height: 300px;"><canvas id="registrationChart"></canvas></div>
        </div>

        <div class="dashboard-section">
            <div class="section-header"><h2>Membership Status</h2></div>
            <div style="position: relative; height: 300px;"><canvas id="statusChart"></canvas></div>
        </div>

    </div>
</div>

<script>
    // 1. Khan Level Distribution
    new Chart(document.getElementById('khanLevelChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($khan_labels); ?>,
            datasets: [{
                label: 'Members',
                data: <?php echo json_encode($khan_data); ?>,
                backgroundColor: 'rgba(211, 47, 47, 0.8)',
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 2
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // 2. Member Growth
    new Chart(document.getElementById('registrationChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($growth_labels); ?>,
            datasets: [{
                label: 'New Registrations',
                data: <?php echo json_encode($growth_data); ?>,
                backgroundColor: 'rgba(255, 193, 7, 0.2)',
                borderColor: 'rgba(211, 47, 47, 1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // 3. Status Distribution
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($status_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($status_data); ?>,
                backgroundColor: [
                    'rgba(46, 125, 50, 0.8)', // Green
                    'rgba(211, 47, 47, 0.8)', // Red
                    'rgba(255, 193, 7, 0.8)'  // Yellow
                ],
                borderWidth: 2
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

<?php include 'includes/admin_footer.php'; ?>