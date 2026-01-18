<?php
$page_title = "Admin Dashboard";
require_once '../config/database.php';
requireAdmin();

// Get statistics
$conn = getDbConnection();

// Count total users
$users_result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
$total_users = $users_result->fetch_assoc()['total'];

// Count total khan members
$members_result = $conn->query("SELECT COUNT(*) as total FROM khan_members WHERE status = 'active'");
$total_members = $members_result->fetch_assoc()['total'];

// Count total instructors
$instructors_result = $conn->query("SELECT COUNT(*) as total FROM instructors WHERE status = 'active'");
$total_instructors = $instructors_result->fetch_assoc()['total'];

// Count unread messages
$messages_result = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'new'");
$unread_messages = $messages_result->fetch_assoc()['total'];

// Count affiliates
$affiliates_result = $conn->query("SELECT COUNT(*) as total FROM affiliates WHERE status = 'active'");
$total_affiliates = $affiliates_result->fetch_assoc()['total'];

// Count course materials
$materials_result = $conn->query("SELECT COUNT(*) as total FROM course_materials WHERE status = 'published'");
$total_materials = $materials_result->fetch_assoc()['total'];

// Count events
$events_result = $conn->query("SELECT COUNT(*) as total FROM event_gallery WHERE status = 'active'");
$total_events = $events_result->fetch_assoc()['total'];

// Get recent members
$recent_members = $conn->query("SELECT * FROM khan_members ORDER BY created_at DESC LIMIT 5");

// Get recent messages
$recent_messages = $conn->query("SELECT * FROM contact_messages WHERE status = 'new' ORDER BY created_at DESC LIMIT 5");

include 'includes/admin_header.php';
?>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1>Dashboard Overview</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e3f2fd;">
                <svg width="32" height="32" fill="#1976d2" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff3e0;">
                <svg width="32" height="32" fill="#f57c00" viewBox="0 0 24 24">
                    <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $total_members; ?></div>
                <div class="stat-label">Khan Members</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #e8f5e9;">
                <svg width="32" height="32" fill="#388e3c" viewBox="0 0 24 24">
                    <path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $total_instructors; ?></div>
                <div class="stat-label">Instructors</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #fce4ec;">
                <svg width="32" height="32" fill="#c2185b" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $unread_messages; ?></div>
                <div class="stat-label">New Messages</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #f3e5f5;">
                <svg width="32" height="32" fill="#7b1fa2" viewBox="0 0 24 24">
                    <path d="M12 2l-5.5 9h11z M12 22l5.5-9h-11z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $total_affiliates; ?></div>
                <div class="stat-label">Affiliates</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #e0f2f1;">
                <svg width="32" height="32" fill="#00796b" viewBox="0 0 24 24">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $total_materials; ?></div>
                <div class="stat-label">Course Materials</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff9c4;">
                <svg width="32" height="32" fill="#f9a825" viewBox="0 0 24 24">
                    <path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $total_events; ?></div>
                <div class="stat-label">Events</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #ffebee;">
                <svg width="32" height="32" fill="#c62828" viewBox="0 0 24 24">
                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo date('H:i'); ?></div>
                <div class="stat-label">Current Time</div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-content">
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Recent Khan Members</h2>
                <a href="khan_members.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Khan Level</th>
                            <th>Date Joined</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($member = $recent_members->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                            <td>Khan <?php echo $member['current_khan_level']; ?></td>
                            <td><?php echo formatDate($member['date_joined']); ?></td>
                            <td><span class="badge badge-<?php echo $member['status']; ?>"><?php echo ucfirst($member['status']); ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Recent Messages</h2>
                <a href="messages.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($message = $recent_messages->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($message['name']); ?></td>
                            <td><?php echo htmlspecialchars($message['subject']); ?></td>
                            <td><?php echo formatDateTime($message['created_at']); ?></td>
                            <td>
                                <a href="messages.php?view=<?php echo $message['id']; ?>" class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
