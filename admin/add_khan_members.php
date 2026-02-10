<?php
$page_title = "Add Khan Member";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';
$warning = '';
$duplicate_check = [];

/**
 * Check for duplicate members in the database
 * Returns array of potential duplicates with match details
 */
function checkForDuplicates($conn, $full_name, $email, $phone, $exclude_id = null) {
    $duplicates = [];
    
    // Normalize inputs for comparison
    $name_clean = trim(strtolower($full_name));
    $email_clean = trim(strtolower($email));
    $phone_clean = preg_replace('/[^0-9]/', '', $phone); // Remove non-numeric characters
    
    // Query to find potential duplicates
    $query = "SELECT id, full_name, email, phone, current_khan_level, status, date_joined 
              FROM khan_members 
              WHERE 1=1";
    
    if ($exclude_id) {
        $query .= " AND id != " . (int)$exclude_id;
    }
    
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $match_score = 0;
        $match_reasons = [];
        
        // Check exact name match
        if (strtolower(trim($row['full_name'])) === $name_clean) {
            $match_score += 50;
            $match_reasons[] = "Exact name match";
        }
        // Check similar name (for typos)
        elseif (levenshtein(strtolower(trim($row['full_name'])), $name_clean) <= 3) {
            $match_score += 30;
            $match_reasons[] = "Similar name";
        }
        
        // Check exact email match
        if (!empty($email_clean) && !empty($row['email'])) {
            if (strtolower(trim($row['email'])) === $email_clean) {
                $match_score += 40;
                $match_reasons[] = "Exact email match";
            }
        }
        
        // Check phone match (only numbers)
        if (!empty($phone_clean) && !empty($row['phone'])) {
            $db_phone_clean = preg_replace('/[^0-9]/', '', $row['phone']);
            if ($phone_clean === $db_phone_clean) {
                $match_score += 40;
                $match_reasons[] = "Phone number match";
            }
            // Check last 7 digits (in case of different country codes)
            elseif (strlen($phone_clean) >= 7 && strlen($db_phone_clean) >= 7) {
                if (substr($phone_clean, -7) === substr($db_phone_clean, -7)) {
                    $match_score += 25;
                    $match_reasons[] = "Phone number (last 7 digits) match";
                }
            }
        }
        
        // If match score is significant, add to duplicates array
        if ($match_score >= 40) {
            $duplicates[] = [
                'id' => $row['id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'current_khan_level' => $row['current_khan_level'],
                'status' => $row['status'],
                'date_joined' => $row['date_joined'],
                'match_score' => $match_score,
                'match_reasons' => $match_reasons
            ];
        }
    }
    
    // Sort by match score (highest first)
    usort($duplicates, function($a, $b) {
        return $b['match_score'] - $a['match_score'];
    });
    
    return $duplicates;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $current_khan_level = (int)$_POST['current_khan_level'];
    $khan_color = isset($_POST['khan_color']) ? sanitize($_POST['khan_color']) : '';
    $date_joined = $_POST['date_joined'];
    $date_promoted = !empty($_POST['date_promoted']) ? $_POST['date_promoted'] : null;
    $instructor_id = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
    $training_location = isset($_POST['training_location']) ? sanitize($_POST['training_location']) : '';
    $status = $_POST['status'];
    $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';
    $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    
    // Check if user wants to force add despite duplicates
    $force_add = isset($_POST['force_add']) && $_POST['force_add'] === '1';
    
    // Validate required fields
    if (empty($full_name)) {
        $error = 'Full name is required';
    } elseif (empty($email)) {
        $error = 'Email is required';
    } elseif (empty($date_joined)) {
        $error = 'Date joined is required';
    } else {
        // Check for duplicates
        $duplicate_check = checkForDuplicates($conn, $full_name, $email, $phone);
        
        if (!empty($duplicate_check) && !$force_add) {
            // Show warning with duplicate details
            $warning = 'Potential duplicate member(s) found! Please review before adding.';
        } else {
            // No duplicates or user forced the add - proceed with insertion
            $stmt = $conn->prepare("INSERT INTO khan_members (user_id, full_name, email, phone, current_khan_level, khan_color, date_joined, date_promoted, instructor_id, training_location, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssississss", $user_id, $full_name, $email, $phone, $current_khan_level, $khan_color, $date_joined, $date_promoted, $instructor_id, $training_location, $status, $notes);
            
            if ($stmt->execute()) {
                $success = 'Khan member added successfully!';
                // Clear form data after successful submission
                $_POST = [];
            } else {
                $error = 'Failed to add khan member: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Get instructors for dropdown
$instructors = $conn->query("SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name");

// Get available users for dropdown
$available_users = $conn->query("SELECT id, name, email FROM users WHERE role = 'member' ORDER BY name");

// Get khan colors for automatic mapping
$khan_colors = $conn->query("SELECT khan_level, color_name, hex_color FROM khan_colors ORDER BY khan_level ASC");

include 'includes/admin_header.php';
?>

<style>
.duplicate-warning-box {
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.duplicate-item {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.duplicate-item:last-child {
    margin-bottom: 0;
}

.match-badge {
    display: inline-block;
    padding: 0.25rem 0.6rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-right: 0.5rem;
}

.match-high {
    background: #f44336;
    color: white;
}

.match-medium {
    background: #ff9800;
    color: white;
}

.match-low {
    background: #ffc107;
    color: #000;
}

.form-section {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
}

.required::after {
    content: ' *';
    color: #f44336;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.btn {
    padding: 0.7rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.btn-primary {
    background: #1976d2;
    color: white;
}

.btn-primary:hover {
    background: #1565c0;
}

.btn-success {
    background: #4caf50;
    color: white;
}

.btn-success:hover {
    background: #388e3c;
}

.btn-warning {
    background: #ff9800;
    color: white;
}

.btn-warning:hover {
    background: #f57c00;
}

.btn-outline {
    background: white;
    color: #666;
    border: 1px solid #ddd;
}

.btn-outline:hover {
    background: #f5f5f5;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.info-box {
    background: #e3f2fd;
    border-left: 4px solid #1976d2;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
}
</style>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($warning && !empty($duplicate_check)): ?>
    <div class="duplicate-warning-box">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
            <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #ff9800;"></i>
            <div>
                <h3 style="margin: 0; color: #856404;">⚠️ Potential Duplicate Detected</h3>
                <p style="margin: 0.5rem 0 0 0; color: #856404;">
                    Found <?php echo count($duplicate_check); ?> similar member(s) in the database. Please review carefully.
                </p>
            </div>
        </div>
        
        <?php foreach ($duplicate_check as $dup): ?>
            <div class="duplicate-item">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                    <div>
                        <?php
                            $badge_class = 'match-low';
                            $badge_text = 'Low Match';
                            if ($dup['match_score'] >= 80) {
                                $badge_class = 'match-high';
                                $badge_text = 'High Match';
                            } elseif ($dup['match_score'] >= 60) {
                                $badge_class = 'match-medium';
                                $badge_text = 'Medium Match';
                            }
                        ?>
                        <span class="match-badge <?php echo $badge_class; ?>">
                            <?php echo $badge_text; ?> (<?php echo $dup['match_score']; ?>%)
                        </span>
                        <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($dup['full_name']); ?></strong>
                    </div>
                    <a href="khan_members.php#member-<?php echo $dup['id']; ?>" 
                       class="btn btn-outline" 
                       style="padding: 0.4rem 0.8rem; font-size: 0.85rem;"
                       target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Record
                    </a>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem; font-size: 0.9rem;">
                    <div>
                        <strong>📧 Email:</strong><br>
                        <span style="color: #666;"><?php echo htmlspecialchars($dup['email']); ?></span>
                    </div>
                    <div>
                        <strong>📞 Phone:</strong><br>
                        <span style="color: #666;"><?php echo htmlspecialchars($dup['phone'] ?: 'N/A'); ?></span>
                    </div>
                    <div>
                        <strong>🥋 Khan Level:</strong><br>
                        <span style="color: #666;">Khan <?php echo $dup['current_khan_level']; ?></span>
                    </div>
                    <div>
                        <strong>📅 Joined:</strong><br>
                        <span style="color: #666;"><?php echo formatDate($dup['date_joined']); ?></span>
                    </div>
                    <div>
                        <strong>Status:</strong><br>
                        <span class="badge badge-<?php echo $dup['status']; ?>" style="padding: 0.2rem 0.5rem; border-radius: 3px;">
                            <?php echo ucfirst($dup['status']); ?>
                        </span>
                    </div>
                </div>
                
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                    <strong style="color: #f57c00;">Match Reasons:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem; color: #666;">
                        <?php foreach ($dup['match_reasons'] as $reason): ?>
                            <li><?php echo htmlspecialchars($reason); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid #ffc107;">
            <p style="margin-bottom: 1rem; font-weight: 600; color: #856404;">
                Are you sure this is a new member and not a duplicate?
            </p>
            <form method="POST" style="display: inline;">
                <?php
                    // Re-populate form fields
                    foreach ($_POST as $key => $value) {
                        if ($key !== 'add_member' && $key !== 'force_add') {
                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                        }
                    }
                ?>
                <input type="hidden" name="force_add" value="1">
                <button type="submit" name="add_member" class="btn btn-warning">
                    <i class="fas fa-user-plus"></i> Yes, Add Anyway
                </button>
                <button type="button" class="btn btn-outline" onclick="window.location.href='add_khan_member.php'">
                    <i class="fas fa-times"></i> Cancel & Review Form
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="admin-section">
    <div class="section-header">
        <h2><i class="fas fa-user-plus"></i> Add New Khan Member</h2>
        <a href="khan_members.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Members List
        </a>
    </div>
    
    <div class="info-box">
        <strong><i class="fas fa-info-circle"></i> Important:</strong>
        <ul style="margin: 0.5rem 0 0 1.5rem;">
            <li>All fields marked with <span style="color: #f44336;">*</span> are required</li>
            <li>The system will automatically check for duplicate entries based on name, email, and phone</li>
            <li>If a potential duplicate is found, you'll be asked to confirm before adding</li>
        </ul>
    </div>

    <form method="POST" class="form-section">
        <h3 style="margin-top: 0; padding-bottom: 1rem; border-bottom: 2px solid #f0f0f0;">
            <i class="fas fa-user"></i> Personal Information
        </h3>
        
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label required">Full Name</label>
                <input type="text" 
                       name="full_name" 
                       class="form-input" 
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                       placeholder="Enter full name"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label required">Email</label>
                <input type="email" 
                       name="email" 
                       class="form-input"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       placeholder="email@example.com"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" 
                       name="phone" 
                       class="form-input"
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                       placeholder="+63 912 345 6789">
            </div>

            <div class="form-group">
                <label class="form-label">Link to User Account</label>
                <select name="user_id" class="form-select">
                    <option value="">No linked account</option>
                    <?php 
                    $available_users->data_seek(0); // Reset pointer
                    while ($user = $available_users->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $user['id']; ?>"
                                <?php echo (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <small style="color: #666; display: block; margin-top: 0.25rem;">
                    Optional: Link this member to an existing user account
                </small>
            </div>
        </div>

        <h3 style="margin-top: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #f0f0f0;">
            <i class="fas fa-graduation-cap"></i> Training Information
        </h3>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label required">Current Khan Level</label>
                <select name="current_khan_level" id="current_khan_level" class="form-select" required onchange="updateKhanColor()">
                    <?php for ($i = 1; $i <= 16; $i++): ?>
                        <option value="<?php echo $i; ?>"
                                <?php echo (isset($_POST['current_khan_level']) && $_POST['current_khan_level'] == $i) ? 'selected' : ($i == 1 ? 'selected' : ''); ?>>
                            Khan <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Khan Color/Belt (Auto-filled)</label>
                <div style="position: relative;">
                    <input type="text" 
                           name="khan_color" 
                           id="khan_color_display"
                           class="form-input"
                           value="<?php echo isset($_POST['khan_color']) ? htmlspecialchars($_POST['khan_color']) : 'White'; ?>"
                           readonly
                           style="padding-left: 45px; background-color: #f5f5f5; cursor: not-allowed;">
                    <div id="color_indicator" 
                         style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 25px; height: 25px; border-radius: 50%; border: 2px solid #ddd; background-color: #FFFFFF;">
                    </div>
                </div>
                <small style="color: #666; display: block; margin-top: 0.25rem;">
                    Color is automatically assigned based on Khan Level
                </small>
            </div>

            <div class="form-group">
                <label class="form-label required">Date Joined</label>
                <input type="date" 
                       name="date_joined" 
                       class="form-input"
                       value="<?php echo isset($_POST['date_joined']) ? $_POST['date_joined'] : date('Y-m-d'); ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Date Promoted to Current Level</label>
                <input type="date" 
                       name="date_promoted" 
                       class="form-input"
                       value="<?php echo isset($_POST['date_promoted']) ? $_POST['date_promoted'] : ''; ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Instructor</label>
                <select name="instructor_id" class="form-select">
                    <option value="">Select Instructor</option>
                    <?php 
                    $instructors->data_seek(0); // Reset pointer
                    while ($instructor = $instructors->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $instructor['id']; ?>"
                                <?php echo (isset($_POST['instructor_id']) && $_POST['instructor_id'] == $instructor['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($instructor['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Training Location</label>
                <input type="text" 
                       name="training_location" 
                       class="form-input"
                       value="<?php echo isset($_POST['training_location']) ? htmlspecialchars($_POST['training_location']) : ''; ?>"
                       placeholder="e.g., Quezon City, Manila, etc.">
            </div>

            <div class="form-group">
                <label class="form-label required">Status</label>
                <select name="status" class="form-select" required>
                    <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : 'selected'; ?>>Active</option>
                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    <option value="refresher" <?php echo (isset($_POST['status']) && $_POST['status'] == 'refresher') ? 'selected' : ''; ?>>Needs Refresher</option>
                    <option value="graduated" <?php echo (isset($_POST['status']) && $_POST['status'] == 'graduated') ? 'selected' : ''; ?>>Graduated</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Additional Notes</label>
            <textarea name="notes" 
                      class="form-textarea" 
                      placeholder="Any additional information about this member..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
        </div>

        <div class="action-buttons">
            <button type="submit" name="add_member" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Khan Member
            </button>
            <a href="khan_members.php" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
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
    echo implode(",\n    ", $color_map);
    ?>
};

// Update khan color when level changes
function updateKhanColor() {
    const level = document.getElementById('current_khan_level').value;
    const colorDisplay = document.getElementById('khan_color_display');
    const colorIndicator = document.getElementById('color_indicator');
    
    if (khanColorMap[level]) {
        colorDisplay.value = khanColorMap[level].name;
        colorIndicator.style.backgroundColor = khanColorMap[level].hex;
        
        // Add border for light colors for better visibility
        if (['#FFFFFF', '#FFFACD', '#90EE90', '#87CEEB', '#D2B48C', '#FFB6C1'].includes(khanColorMap[level].hex)) {
            colorIndicator.style.border = '2px solid #999';
        } else {
            colorIndicator.style.border = '2px solid #ddd';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateKhanColor();
});

// Real-time duplicate checking (optional enhancement)
let duplicateCheckTimeout;

function checkDuplicatesRealtime() {
    clearTimeout(duplicateCheckTimeout);
    
    const fullName = document.querySelector('input[name="full_name"]').value;
    const email = document.querySelector('input[name="email"]').value;
    const phone = document.querySelector('input[name="phone"]').value;
    
    // Only check if at least name or email is filled
    if (fullName.length >= 3 || email.length >= 5) {
        duplicateCheckTimeout = setTimeout(() => {
            // You could implement an AJAX call here to check for duplicates in real-time
            // For now, we rely on the server-side check on form submission
            console.log('Checking for duplicates...', {fullName, email, phone});
        }, 500);
    }
}

// Attach event listeners (optional)
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.querySelector('input[name="full_name"]');
    const emailInput = document.querySelector('input[name="email"]');
    const phoneInput = document.querySelector('input[name="phone"]');
    
    if (nameInput) nameInput.addEventListener('input', checkDuplicatesRealtime);
    if (emailInput) emailInput.addEventListener('input', checkDuplicatesRealtime);
    if (phoneInput) phoneInput.addEventListener('input', checkDuplicatesRealtime);
});
</script>

<?php include 'includes/admin_footer.php'; ?>