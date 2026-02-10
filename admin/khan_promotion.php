<?php
$page_title = "Khan Level Promotion";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

// Get member_id from URL
$member_id = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;

// Fetch member details
$member = null;
if ($member_id > 0) {
    $stmt = $conn->prepare("SELECT km.*, u.name as user_name FROM khan_members km LEFT JOIN users u ON km.user_id = u.id WHERE km.id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();
    $stmt->close();
}

if (!$member) {
    header('Location: khan_members.php');
    exit;
}

// Handle promotion submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_member'])) {
    $new_khan_level = (int)$_POST['new_khan_level'];
    $promotion_date = $_POST['promotion_date'];
    $promoted_by = isset($_POST['promoted_by']) ? (int)$_POST['promoted_by'] : null;
    $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';
    
    // Get new khan color from database
    $color_result = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $new_khan_level");
    $new_khan_color = '';
    if ($color_result && $color_row = $color_result->fetch_assoc()) {
        $new_khan_color = $color_row['color_name'];
    }
    
    // Validate that new level is higher than current
    if ($new_khan_level <= $member['current_khan_level']) {
        $error = "New Khan level must be higher than current level (Khan {$member['current_khan_level']})";
    } else {
        $conn->begin_transaction();
        
        try {
            // Insert into training history
            $stmt = $conn->prepare("INSERT INTO member_training_history (member_id, khan_level_achieved, khan_color, promotion_date, promoted_by_instructor_id, notes, created_by_admin_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $admin_id = $_SESSION['user_id'];
            $stmt->bind_param("iissisi", $member_id, $new_khan_level, $new_khan_color, $promotion_date, $promoted_by, $notes, $admin_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to record promotion history');
            }
            $stmt->close();
            
            // Update member's current level
            $stmt = $conn->prepare("UPDATE khan_members SET current_khan_level = ?, khan_color = ?, date_promoted = ? WHERE id = ?");
            $stmt->bind_param("issi", $new_khan_level, $new_khan_color, $promotion_date, $member_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update member level');
            }
            $stmt->close();
            
            $conn->commit();
            $success = "Member successfully promoted to Khan $new_khan_level!";
            
            // Refresh member data
            $stmt = $conn->prepare("SELECT km.*, u.name as user_name FROM khan_members km LEFT JOIN users u ON km.user_id = u.id WHERE km.id = ?");
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $member = $result->fetch_assoc();
            $stmt->close();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

// Handle adding historical/backdated khan levels
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_historical_level'])) {
    $khan_level = (int)$_POST['khan_level'];
    $promotion_date = $_POST['promotion_date'];
    $promoted_by = isset($_POST['promoted_by']) ? (int)$_POST['promoted_by'] : null;
    $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';
    
    // Get khan color from database
    $color_result = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $khan_level");
    $khan_color = '';
    if ($color_result && $color_row = $color_result->fetch_assoc()) {
        $khan_color = $color_row['color_name'];
    }
    
    $conn->begin_transaction();
    
    try {
        // Insert into training history
        $stmt = $conn->prepare("INSERT INTO member_training_history (member_id, khan_level_achieved, khan_color, promotion_date, promoted_by_instructor_id, notes, created_by_admin_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $admin_id = $_SESSION['user_id'];
        $stmt->bind_param("iissisi", $member_id, $khan_level, $khan_color, $promotion_date, $promoted_by, $notes, $admin_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to add historical record');
        }
        $stmt->close();
        
        $conn->commit();
        $success = "Historical Khan $khan_level record added successfully!";
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Handle deletion of history record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_history'])) {
    $history_id = (int)$_POST['history_id'];
    
    if ($conn->query("DELETE FROM member_training_history WHERE id = $history_id AND member_id = $member_id")) {
        $success = 'History record deleted successfully!';
    } else {
        $error = 'Failed to delete history record';
    }
}

// Fetch training history
$history = $conn->query("
    SELECT mth.*, i.name as instructor_name, u.name as admin_name
    FROM member_training_history mth
    LEFT JOIN instructors i ON mth.promoted_by_instructor_id = i.id
    LEFT JOIN users u ON mth.created_by_admin_id = u.id
    WHERE mth.member_id = $member_id
    ORDER BY mth.khan_level_achieved ASC, mth.promotion_date ASC
");

// Get instructors for dropdown
$instructors = $conn->query("SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name");

// Get khan colors for mapping
$khan_colors = $conn->query("SELECT khan_level, color_name, hex_color FROM khan_colors ORDER BY khan_level ASC");

include 'includes/admin_header.php';
?>

<style>
.promotion-card {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.member-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.current-level {
    font-size: 3rem;
    font-weight: bold;
    text-align: center;
    margin: 1rem 0;
}

.timeline {
    position: relative;
    padding: 2rem 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #ddd;
}

.timeline-item {
    position: relative;
    padding-left: 100px;
    margin-bottom: 2rem;
    min-height: 80px;
}

.timeline-marker {
    position: absolute;
    left: 35px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.timeline-content {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s;
}

.modal-content {
    background-color: #fefefe;
    margin: 3% auto;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 700px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    animation: slideDown 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideDown {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.modal-close:hover {
    color: #000;
}
</style>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="admin-section">
    <div class="section-header">
        <h2><i class="fas fa-medal"></i> Khan Level Promotion</h2>
        <a href="khan_members.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Members
        </a>
    </div>

    <div class="member-info">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div>
                <h3 style="margin: 0; font-size: 1.8rem;"><?php echo htmlspecialchars($member['full_name']); ?></h3>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">
                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($member['email']); ?>
                    <?php if ($member['phone']): ?>
                        | <i class="fas fa-phone"></i> <?php echo htmlspecialchars($member['phone']); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="current-level">
                Khan <?php echo $member['current_khan_level']; ?>
            </div>
        </div>
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.3);">
            <strong>Current Color:</strong> <?php echo htmlspecialchars($member['khan_color']); ?> | 
            <strong>Date Joined:</strong> <?php echo formatDate($member['date_joined']); ?> | 
            <strong>Last Promoted:</strong> <?php echo $member['date_promoted'] ? formatDate($member['date_promoted']) : 'N/A'; ?>
        </div>
    </div>

    <div class="promotion-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid #f0f0f0; padding-bottom: 1rem;">
            <h3 style="margin: 0;"><i class="fas fa-trophy"></i> Promote to Next Level</h3>
            <button class="btn btn-success" onclick="document.getElementById('promoteModal').style.display='block'">
                <i class="fas fa-arrow-up"></i> Promote Member
            </button>
        </div>
        
        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 6px; border-left: 4px solid #388e3c;">
            <p style="margin: 0;"><strong><i class="fas fa-info-circle"></i> Promotion Guidelines:</strong></p>
            <ul style="margin: 0.5rem 0 0 1.5rem;">
                <li>Member will be promoted from <strong>Khan <?php echo $member['current_khan_level']; ?></strong> to the next level</li>
                <li>All promotion records are permanently stored in the training history</li>
                <li>You can also add historical records for past promotions below</li>
            </ul>
        </div>
    </div>

    <div class="promotion-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid #f0f0f0; padding-bottom: 1rem;">
            <h3 style="margin: 0;"><i class="fas fa-history"></i> Training History & Progression</h3>
            <button class="btn btn-primary" onclick="document.getElementById('historicalModal').style.display='block'">
                <i class="fas fa-plus-circle"></i> Add Historical Record
            </button>
        </div>

        <?php if ($history && $history->num_rows > 0): ?>
            <div class="timeline">
                <?php 
                $khan_colors->data_seek(0);
                $color_map = [];
                while($kc = $khan_colors->fetch_assoc()) {
                    $color_map[$kc['khan_level']] = $kc['hex_color'];
                }
                
                while ($record = $history->fetch_assoc()): 
                    $marker_color = isset($color_map[$record['khan_level_achieved']]) ? $color_map[$record['khan_level_achieved']] : '#cccccc';
                ?>
                    <div class="timeline-item">
                        <div class="timeline-marker" style="background: <?php echo $marker_color; ?>">
                            <div style="position: absolute; left: -15px; top: 50%; transform: translateY(-50%); background: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: bold; font-size: 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); white-space: nowrap;">
                                <?php echo $record['khan_level_achieved']; ?>
                            </div>
                        </div>
                        <div class="timeline-content">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 0.5rem 0; color: #1976d2;">
                                        <i class="fas fa-medal"></i> Khan <?php echo $record['khan_level_achieved']; ?> - <?php echo htmlspecialchars($record['khan_color']); ?>
                                    </h4>
                                    <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">
                                        <i class="fas fa-calendar"></i> <strong>Promoted:</strong> <?php echo formatDate($record['promotion_date']); ?>
                                    </div>
                                    <?php if ($record['instructor_name']): ?>
                                        <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">
                                            <i class="fas fa-user-tie"></i> <strong>By:</strong> <?php echo htmlspecialchars($record['instructor_name']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($record['notes']): ?>
                                        <div style="margin-top: 0.5rem; padding: 0.5rem; background: #f8f9fa; border-radius: 4px; font-size: 0.9rem;">
                                            <i class="fas fa-sticky-note"></i> <?php echo nl2br(htmlspecialchars($record['notes'])); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="font-size: 0.8rem; color: #999; margin-top: 0.5rem;">
                                        <i class="fas fa-user-shield"></i> Recorded by: <?php echo htmlspecialchars($record['admin_name'] ?: 'System'); ?>
                                    </div>
                                </div>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this history record?');">
                                    <input type="hidden" name="history_id" value="<?php echo $record['id']; ?>">
                                    <button type="submit" name="delete_history" class="btn btn-sm btn-danger" title="Delete Record">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; color: #999;">
                <i class="fas fa-history" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                <h3 style="margin: 0;">No Training History Yet</h3>
                <p>Start by adding historical records or promoting the member to the next level.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Promote Modal -->
<div id="promoteModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('promoteModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-arrow-up"></i> Promote to Next Khan Level</h2>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">New Khan Level *</label>
                <select name="new_khan_level" id="promote_khan_level" class="form-select" required onchange="updatePromoteColor()">
                    <?php for ($i = $member['current_khan_level'] + 1; $i <= 16; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == ($member['current_khan_level'] + 1) ? 'selected' : ''; ?>>
                            Khan <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">New Khan Color (Auto-filled)</label>
                <div style="position: relative;">
                    <input type="text" 
                           id="promote_khan_color_display"
                           class="form-input"
                           readonly
                           style="padding-left: 45px; background-color: #f5f5f5; cursor: not-allowed;">
                    <div id="promote_color_indicator" 
                         style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 25px; height: 25px; border-radius: 50%; border: 2px solid #999;">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Promotion Date *</label>
                <input type="date" name="promotion_date" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Promoted By (Instructor)</label>
                <select name="promoted_by" class="form-select">
                    <option value="">-- Select Instructor --</option>
                    <?php 
                    $instructors->data_seek(0);
                    while ($instructor = $instructors->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $instructor['id']; ?>">
                            <?php echo htmlspecialchars($instructor['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Add any notes about this promotion..."></textarea>
            </div>

            <div class="action-buttons">
                <button type="submit" name="promote_member" class="btn btn-success">
                    <i class="fas fa-check"></i> Confirm Promotion
                </button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('promoteModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Historical Record Modal -->
<div id="historicalModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('historicalModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-history"></i> Add Historical Khan Level Record</h2>
        
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
            <strong><i class="fas fa-info-circle"></i> Note:</strong> Use this to backfill historical promotions (e.g., Khan 2, 3, 4) that occurred before the current level.
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Khan Level Achieved *</label>
                <select name="khan_level" id="historical_khan_level" class="form-select" required onchange="updateHistoricalColor()">
                    <?php for ($i = 1; $i <= 16; $i++): ?>
                        <option value="<?php echo $i; ?>">Khan <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Khan Color (Auto-filled)</label>
                <div style="position: relative;">
                    <input type="text" 
                           id="historical_khan_color_display"
                           class="form-input"
                           readonly
                           style="padding-left: 45px; background-color: #f5f5f5; cursor: not-allowed;">
                    <div id="historical_color_indicator" 
                         style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 25px; height: 25px; border-radius: 50%; border: 2px solid #999;">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Promotion Date *</label>
                <input type="date" name="promotion_date" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Promoted By (Instructor)</label>
                <select name="promoted_by" class="form-select">
                    <option value="">-- Select Instructor --</option>
                    <?php 
                    $instructors->data_seek(0);
                    while ($instructor = $instructors->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $instructor['id']; ?>">
                            <?php echo htmlspecialchars($instructor['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Add any notes about this historical record..."></textarea>
            </div>

            <div class="action-buttons">
                <button type="submit" name="add_historical_level" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Historical Record
                </button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('historicalModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Khan color mapping from database
    const khanColorMap = {
        <?php 
        $khan_colors->data_seek(0);
        $color_map = [];
        while($kc = $khan_colors->fetch_assoc()) {
            $color_map[] = $kc['khan_level'] . ": { name: '" . addslashes($kc['color_name']) . "', hex: '" . $kc['hex_color'] . "' }";
        }
        echo implode(",\n        ", $color_map);
        ?>
    };

    function updateColor(levelId, displayId, indicatorId) {
        const level = document.getElementById(levelId).value;
        const colorDisplay = document.getElementById(displayId);
        const colorIndicator = document.getElementById(indicatorId);
        
        if (khanColorMap[level]) {
            colorDisplay.value = khanColorMap[level].name;
            colorIndicator.style.backgroundColor = khanColorMap[level].hex;
            
            // Add border for light colors
            if (['#FFFFFF', '#FFFACD', '#90EE90', '#87CEEB', '#D2B48C', '#FFB6C1'].includes(khanColorMap[level].hex)) {
                colorIndicator.style.border = '2px solid #999';
            } else {
                colorIndicator.style.border = '2px solid #ddd';
            }
        }
    }

    function updatePromoteColor() {
        updateColor('promote_khan_level', 'promote_khan_color_display', 'promote_color_indicator');
    }

    function updateHistoricalColor() {
        updateColor('historical_khan_level', 'historical_khan_color_display', 'historical_color_indicator');
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updatePromoteColor();
        updateHistoricalColor();
    });

    // Close modals
    window.onclick = function(event) {
        const promoteModal = document.getElementById('promoteModal');
        const historicalModal = document.getElementById('historicalModal');
        
        if (event.target == promoteModal) {
            promoteModal.style.display = 'none';
        }
        if (event.target == historicalModal) {
            historicalModal.style.display = 'none';
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('promoteModal').style.display = 'none';
            document.getElementById('historicalModal').style.display = 'none';
        }
    });
</script>

<?php include 'includes/admin_footer.php'; ?>