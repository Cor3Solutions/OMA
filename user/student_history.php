<?php
$page_title = "My Training History";
require_once '../config/database.php';
requireLogin();

$conn      = getDbConnection();
$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($user_role === 'admin') {
    header('Location: ' . SITE_URL . '/admin/index.php'); exit;
}
if ($user_role === 'instructor') {
    header('Location: instructor_students.php'); exit;
}

$member_info  = null;
$history_data = [];

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
    $hq = $conn->prepare("SELECT * FROM khan_training_history WHERE member_id = ? ORDER BY training_date DESC");
    $hq->bind_param("i", $member_info['id']);
    $hq->execute();
    $result = $hq->get_result();
    while ($row = $result->fetch_assoc()) { $history_data[] = $row; }
}

include 'includes/user_header.php';
?>

<div class="dashboard-container">

<?php if ($member_info && $member_info['status'] === 'refresher'):
    $pending_req = $conn->query("
        SELECT id FROM refresher_requests
        WHERE member_id = " . (int)$member_info['id'] . " AND status = 'pending' LIMIT 1
    ")->fetch_assoc();
?>
<div style="background:linear-gradient(135deg,#7c2d12,#431407);color:white;border-radius:12px;padding:1.25rem 1.75rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;box-shadow:0 4px 20px rgba(124,45,18,0.35);border:1px solid rgba(255,200,100,0.2);">
    <div style="display:flex;align-items:center;gap:1rem;">
        <div style="width:42px;height:42px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-exclamation-triangle" style="color:#fbbf24;font-size:1rem;"></i>
        </div>
        <div>
            <strong style="display:block;font-family:'Cinzel',serif;font-size:0.875rem;margin-bottom:0.15rem;letter-spacing:0.03em;">Status: Needs Refresher</strong>
            <span style="font-size:0.79rem;opacity:0.8;">No training or promotion in 3 months. Submit a request to restore Active status.</span>
        </div>
    </div>
    <?php if ($pending_req): ?>
        <div style="background:rgba(255,255,255,0.12);padding:0.5rem 1.1rem;border-radius:8px;font-size:0.8rem;font-weight:600;white-space:nowrap;">
            <i class="fas fa-clock"></i> Request Pending Review
        </div>
    <?php else: ?>
        <a href="refresher_request.php" style="background:rgba(255,255,255,0.9);color:#7c2d12;padding:0.5rem 1.2rem;border-radius:8px;font-size:0.82rem;font-weight:700;text-decoration:none;white-space:nowrap;">
            <i class="fas fa-paper-plane"></i> Submit Request
        </a>
    <?php endif; ?>
</div>
<?php endif; ?>

    <!-- Status Hero -->
    <div style="background:linear-gradient(135deg,var(--crimson-dark) 0%,#1a0000 100%);color:white;border-radius:var(--radius-lg);padding:2.25rem 2.5rem;box-shadow:var(--shadow-lg);position:relative;overflow:hidden;border:1px solid rgba(201,168,76,0.18);">
        <div style="position:absolute;top:-60%;right:-5%;width:350px;height:350px;background:radial-gradient(circle,rgba(201,168,76,0.1) 0%,transparent 65%);border-radius:50%;pointer-events:none;"></div>
        <div style="position:absolute;bottom:-80px;left:-40px;width:220px;height:220px;background:radial-gradient(circle,rgba(139,0,0,0.25) 0%,transparent 60%);border-radius:50%;pointer-events:none;"></div>

        <div style="position:relative;z-index:1;display:flex;align-items:center;gap:1.5rem;margin-bottom:2rem;flex-wrap:wrap;">
            <div style="width:64px;height:64px;background:rgba(255,255,255,0.1);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:2rem;border:1px solid rgba(201,168,76,0.2);">
                <i class="fas fa-medal" style="color:var(--gold);"></i>
            </div>
            <div>
                <h2 style="color:white;font-size:1.5rem;margin-bottom:0.2rem;">My Training Record</h2>
                <p style="opacity:0.7;font-size:0.875rem;margin:0;font-family:'DM Sans',sans-serif;">Your Muayboran progression journey</p>
            </div>
        </div>

        <div style="position:relative;z-index:1;display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;">
            <?php
            $stats = [
                ['Khan Level', $member_info['current_khan_level'] ?? 'N/A', 'fa-award'],
                ['Khan Color', $member_info['khan_color'] ?? 'N/A', 'fa-palette'],
                ['Instructor', $member_info['instructor_name'] ?? 'N/A', 'fa-user-tie'],
                ['Trainings', count($history_data), 'fa-history'],
            ];
            foreach ($stats as $st):
            ?>
            <div style="background:rgba(255,255,255,0.08);backdrop-filter:blur(8px);padding:1.25rem;border-radius:var(--radius);border:1px solid rgba(255,255,255,0.1);">
                <div style="font-size:0.7rem;opacity:0.7;margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.6px;font-weight:600;">
                    <i class="fas <?php echo $st[2]; ?>" style="color:var(--gold);margin-right:4px;"></i><?php echo $st[0]; ?>
                </div>
                <div style="font-family:'Cinzel',serif;font-size:1.375rem;font-weight:700;line-height:1;">
                    <?php echo htmlspecialchars((string)$st[1]); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Timeline -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2><i class="fas fa-history"></i> Training History</h2>
            <span style="font-size:0.8rem;color:var(--text-muted);font-weight:600;"><?php echo count($history_data); ?> records</span>
        </div>

        <?php if (count($history_data) > 0): ?>
        <div style="position:relative;padding-left:2.5rem;">
            <!-- Vertical line -->
            <div style="position:absolute;left:10px;top:0;bottom:0;width:2px;background:linear-gradient(to bottom,var(--crimson),var(--border));border-radius:2px;"></div>

            <?php foreach ($history_data as $i => $record): ?>
            <?php
            $dot_color = match($record['status']) {
                'certified' => 'var(--gold)',
                'completed' => 'var(--success)',
                'in_progress' => 'var(--warning)',
                default => 'var(--border)'
            };
            $badge_styles = [
                'certified'   => 'background:#fdf6e3;color:#92400e;',
                'completed'   => 'background:#f0fdf4;color:#166534;',
                'in_progress' => 'background:#fffbeb;color:#92400e;',
            ];
            $bstyle = $badge_styles[$record['status']] ?? 'background:var(--fog);color:var(--ash);';
            ?>
            <div style="position:relative;margin-bottom:1.75rem;">
                <!-- Dot -->
                <div style="position:absolute;left:-2.5rem;top:1.1rem;width:22px;height:22px;border-radius:50%;background:<?php echo $dot_color; ?>;border:3px solid white;box-shadow:0 0 0 2px <?php echo $dot_color; ?>;z-index:2;"></div>

                <div style="background:var(--pearl);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;transition:var(--transition);"
                     onmouseover="this.style.boxShadow='var(--shadow-md)';this.style.transform='translateX(4px)'"
                     onmouseout="this.style.boxShadow='none';this.style.transform='none'">

                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.875rem;">
                        <span style="display:flex;align-items:center;gap:0.5rem;color:var(--text-muted);font-size:0.85rem;font-weight:500;">
                            <i class="fas fa-calendar" style="color:var(--crimson);"></i>
                            <?php echo date('F j, Y', strtotime($record['training_date'])); ?>
                        </span>
                        <span style="padding:3px 12px;border-radius:50px;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;<?php echo $bstyle; ?>">
                            <?php echo ucfirst(str_replace('_',' ',$record['status'])); ?>
                        </span>
                    </div>

                    <h3 style="font-size:1.1rem;color:var(--ink);display:flex;align-items:center;gap:0.5rem;margin-bottom:<?php echo empty($record['location']) && empty($record['notes']) && empty($record['certified_date']) ? '0' : '0.875rem'; ?>;">
                        <i class="fas fa-award" style="color:var(--gold);font-size:1rem;"></i>
                        Khan Level <?php echo $record['khan_level']; ?>
                    </h3>

                    <?php if (!empty($record['location'])): ?>
                    <p style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;color:var(--text-muted);margin-bottom:0.75rem;">
                        <i class="fas fa-map-marker-alt" style="color:var(--crimson);"></i>
                        <?php echo htmlspecialchars($record['location']); ?>
                    </p>
                    <?php endif; ?>

                    <?php if (!empty($record['notes'])): ?>
                    <div style="background:white;padding:1rem 1.125rem;border-radius:var(--radius-sm);border-left:3px solid var(--crimson);margin-bottom:0.75rem;">
                        <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:0.375rem;">Notes</div>
                        <p style="font-size:0.875rem;color:var(--text);margin:0;line-height:1.6;"><?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($record['certified_date'])): ?>
                    <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;color:var(--gold);font-weight:600;padding-top:0.75rem;border-top:1px solid var(--fog);">
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
            <p>Your training records will appear here as you progress through your Khan levels.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php include 'includes/user_footer.php'; ?>