<?php
/**
 * user/refresher_request.php
 * Member submits a refresher validation request.
 * Only accessible when status = 'refresher'.
 */
$page_title = "Refresher Validation Request";
require_once '../config/database.php';
requireLogin();

$conn    = getDbConnection();
$user_id = $_SESSION['user_id'];

// Only members
if ($_SESSION['user_role'] !== 'member') {
    header('Location: dashboard.php'); exit;
}

// Load member record
$member = $conn->query("
    SELECT km.*, i.name as instructor_name
    FROM khan_members km
    LEFT JOIN instructors i ON km.instructor_id = i.id
    WHERE km.user_id = $user_id
")->fetch_assoc();

if (!$member) {
    header('Location: dashboard.php'); exit;
}

// Flag whether member currently needs a refresher
$needs_refresher = ($member['status'] === 'refresher');

$member_id = (int)$member['id'];

// Check for existing pending request
$existing = $conn->query("
    SELECT * FROM refresher_requests
    WHERE member_id = $member_id AND status = 'pending'
    LIMIT 1
")->fetch_assoc();

// Fetch all past requests (most recent first)
$past = $conn->query("
    SELECT * FROM refresher_requests
    WHERE member_id = $member_id
    ORDER BY submitted_at DESC
");

$success = '';
$error   = '';

// ── HANDLE SUBMIT ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    if (!$needs_refresher) {
        $error = 'You can only submit a request when your status is Needs Refresher.';
    } elseif ($existing) {
        $error = 'You already have a pending request. Please wait for the admin to review it.';
    } else {
        $message = sanitize($_POST['message'] ?? '');
        if (strlen(trim($message)) < 10) {
            $error = 'Please provide a brief explanation (at least 10 characters).';
        } else {
            $msg_esc = $conn->real_escape_string($message);
            $conn->query("
                INSERT INTO refresher_requests (member_id, user_id, message)
                VALUES ($member_id, $user_id, '$msg_esc')
            ");
            $success = 'Your request has been submitted! The admin will review it shortly.';
            // Reload existing
            $existing = $conn->query("
                SELECT * FROM refresher_requests
                WHERE member_id = $member_id AND status = 'pending'
                LIMIT 1
            ")->fetch_assoc();
        }
    }
}

include 'includes/user_header.php';
?>

<div style="max-width:700px; margin:0 auto;">

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<!-- ── Status Banner ── -->
<?php if ($needs_refresher): ?>
<div style="background:linear-gradient(135deg,#ff6b00,#e65100);color:white;border-radius:12px;padding:1.75rem 2rem;margin-bottom:2rem;display:flex;align-items:center;gap:1.5rem;box-shadow:0 4px 16px rgba(230,81,0,0.25);">
    <div style="width:56px;height:56px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div>
        <h2 style="margin:0 0 0.25rem;font-size:1.2rem;">Your status is: Needs Refresher</h2>
        <p style="margin:0;opacity:0.9;font-size:0.9rem;">
            No training or promotion was recorded in the last 3 months. Submit a request below to be validated as active again.
        </p>
    </div>
</div>
<?php elseif ($member['status'] === 'inactive'): ?>
<div style="background:linear-gradient(135deg,#555,#333);color:white;border-radius:12px;padding:1.75rem 2rem;margin-bottom:2rem;display:flex;align-items:center;gap:1.5rem;box-shadow:0 4px 16px rgba(0,0,0,0.2);">
    <div style="width:56px;height:56px;background:rgba(255,255,255,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;">
        <i class="fas fa-user-slash"></i>
    </div>
    <div>
        <h2 style="margin:0 0 0.25rem;font-size:1.2rem;">Your status is: Inactive</h2>
        <p style="margin:0;opacity:0.9;font-size:0.9rem;">
            Your account has been set to inactive due to 6 months of no training or promotion. Please contact your instructor or the admin to have your status restored.
        </p>
    </div>
</div>
<?php else: ?>
<div style="background:linear-gradient(135deg,#2e7d32,#1b5e20);color:white;border-radius:12px;padding:1.75rem 2rem;margin-bottom:2rem;display:flex;align-items:center;gap:1.5rem;box-shadow:0 4px 16px rgba(27,94,32,0.25);">
    <div style="width:56px;height:56px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;">
        <i class="fas fa-check-circle"></i>
    </div>
    <div>
        <h2 style="margin:0 0 0.25rem;font-size:1.2rem;">Your status is: Active</h2>
        <p style="margin:0;opacity:0.9;font-size:0.9rem;">
            You're in good standing. A refresher request is only needed if your status changes to <strong>Needs Refresher</strong>.
        </p>
    </div>
</div>
<?php endif; ?>

<?php if ($needs_refresher): ?>

<!-- ── Member Info ── -->
<div style="background:white;border-radius:12px;padding:1.5rem 2rem;margin-bottom:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.07);display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap;">
    <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#8b0000,#5c0000);color:white;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:700;flex-shrink:0;">
        <?php echo strtoupper(mb_substr($member['full_name'] ?? '?', 0, 1)); ?>
    </div>
    <div style="flex:1;">
        <div style="font-weight:700;font-size:1.05rem;"><?php echo htmlspecialchars($member['full_name'] ?? ''); ?></div>
        <div style="color:#666;font-size:0.875rem;margin-top:2px;">
            Khan <?php echo $member['current_khan_level'] ?? ''; ?> &bull;
            <?php echo htmlspecialchars($member['khan_color'] ?? 'N/A'); ?>
            <?php if (!empty($member['instructor_name'])): ?>
                &bull; Kru <?php echo htmlspecialchars($member['instructor_name']); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($existing): ?>
<!-- ── Pending Request Notice ── -->
<div style="background:#fff8e1;border:2px solid #ffb300;border-radius:12px;padding:1.5rem 2rem;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;">
        <i class="fas fa-clock" style="color:#ff8f00;font-size:1.2rem;"></i>
        <strong style="color:#e65100;font-size:1rem;">Request Pending Review</strong>
    </div>
    <p style="margin:0 0 0.5rem;color:#555;font-size:0.9rem;">
        <strong>Submitted:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($existing['submitted_at'])); ?>
    </p>
    <p style="margin:0;color:#555;font-size:0.9rem;">
        <strong>Your message:</strong> <?php echo nl2br(htmlspecialchars($existing['message'])); ?>
    </p>
</div>
<?php else: ?>
<!-- ── Submit Form ── -->
<div style="background:white;border-radius:12px;padding:2rem;margin-bottom:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.07);">
    <h3 style="margin:0 0 0.5rem;font-size:1.1rem;color:#333;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-paper-plane" style="color:#8b0000;"></i> Submit Validation Request
    </h3>
    <p style="margin:0 0 1.5rem;color:#666;font-size:0.9rem;">
        Explain to the admin what you have been doing — training sessions attended, personal practice, or anything relevant to your continued participation.
    </p>
    <form method="POST">
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-weight:600;margin-bottom:0.5rem;color:#333;">
                Your Message / Explanation *
            </label>
            <textarea name="message" rows="5" required minlength="10"
                style="width:100%;padding:0.85rem 1rem;border:2px solid #e0e0e0;border-radius:8px;font-size:0.95rem;font-family:inherit;resize:vertical;box-sizing:border-box;transition:border-color 0.2s;"
                onfocus="this.style.borderColor='#8b0000'" onblur="this.style.borderColor='#e0e0e0'"
                placeholder="e.g. I have been training regularly with my group but no grading event was held in the past months. I am ready to continue and would like my active status restored..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
            <small style="color:#888;font-size:0.8rem;">Minimum 10 characters.</small>
        </div>
        <button type="submit" name="submit_request"
                style="background:linear-gradient(135deg,#8b0000,#5c0000);color:white;border:none;padding:0.9rem 2rem;border-radius:8px;font-size:0.95rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:0.5rem;transition:opacity 0.2s;"
                onmouseover="this.style.opacity='0.88'" onmouseout="this.style.opacity='1'">
            <i class="fas fa-paper-plane"></i> Submit Request
        </button>
    </form>
</div>
<?php endif; ?>

<?php endif; // needs_refresher ?>

<!-- ── Past Requests ── -->
<?php
$past->data_seek(0);
$past_count = $past->num_rows;
?>
<?php if ($past_count > 0): ?>
<div style="background:white;border-radius:12px;padding:1.75rem 2rem;box-shadow:0 2px 8px rgba(0,0,0,0.07);">
    <h3 style="margin:0 0 1.25rem;font-size:1rem;color:#555;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-history"></i> Past Requests
    </h3>
    <?php while ($req = $past->fetch_assoc()): ?>
    <?php
        $bg    = ['pending'=>'#fff8e1','approved'=>'#e8f5e9','rejected'=>'#ffebee'][$req['status']] ?? '#f5f5f5';
        $color = ['pending'=>'#f57f17','approved'=>'#2e7d32','rejected'=>'#c62828'][$req['status']] ?? '#333';
        $icon  = ['pending'=>'fa-clock','approved'=>'fa-check-circle','rejected'=>'fa-times-circle'][$req['status']] ?? 'fa-circle';
    ?>
    <div style="background:<?php echo $bg; ?>;border-radius:8px;padding:1rem 1.25rem;margin-bottom:0.75rem;border-left:4px solid <?php echo $color; ?>;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.5rem;">
            <span style="font-weight:600;color:<?php echo $color; ?>;font-size:0.9rem;">
                <i class="fas <?php echo $icon; ?>"></i> <?php echo ucfirst($req['status']); ?>
            </span>
            <span style="font-size:0.8rem;color:#888;">
                <?php echo date('M j, Y g:i A', strtotime($req['submitted_at'])); ?>
            </span>
        </div>
        <p style="margin:0 0 0.35rem;font-size:0.875rem;color:#555;">
            <strong>Your message:</strong> <?php echo nl2br(htmlspecialchars($req['message'])); ?>
        </p>
        <?php if ($req['admin_notes']): ?>
        <p style="margin:0;font-size:0.875rem;color:#555;">
            <strong>Admin response:</strong> <?php echo nl2br(htmlspecialchars($req['admin_notes'])); ?>
        </p>
        <?php endif; ?>
        <?php if ($req['reviewed_by'] && $req['reviewed_at']): ?>
        <p style="margin:0.4rem 0 0;font-size:0.78rem;color:#888;">
            Reviewed by <?php echo htmlspecialchars($req['reviewed_by']); ?> on <?php echo date('M j, Y', strtotime($req['reviewed_at'])); ?>
        </p>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<div style="margin-top:1.5rem;text-align:center;">
    <a href="student_history.php" style="color:#666;font-size:0.9rem;text-decoration:none;">
        <i class="fas fa-arrow-left"></i> Back to Training History
    </a>
</div>
</div>

<style>
.alert { padding:1rem 1.25rem; border-radius:8px; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem; }
.alert-success { background:#e8f5e9; color:#2e7d32; border-left:4px solid #4caf50; }
.alert-error   { background:#ffebee; color:#c62828; border-left:4px solid #f44336; }
</style>

<?php include 'includes/user_footer.php'; ?>