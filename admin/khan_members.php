<?php
$page_title = "Manage Khan Members";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();

// Pending refresher requests count (for badge)
$pending_refresher_count = (int)$conn->query(
    "SELECT COUNT(*) c FROM refresher_requests WHERE status = 'pending'"
)->fetch_assoc()['c'];
require_once 'includes/activity_helper.php';
$success = '';
$error   = '';

// ── SERIAL NUMBER HELPER ──────────────────────────────────────────────────────
function nextMemberSerial($conn) {
    $res  = $conn->query("
        SELECT serial_number FROM users
        WHERE serial_number REGEXP '^OMA-[0-9]+$'
        ORDER BY CAST(SUBSTRING(serial_number, 5) AS UNSIGNED) DESC
        LIMIT 1
    ");
    $last = $res ? $res->fetch_assoc() : null;
    $next = 1;
    if ($last && preg_match('/^OMA-0*(\d+)$/', $last['serial_number'], $m)) {
        $next = (int)$m[1] + 1;
    }
    return 'OMA-' . str_pad($next, 3, '0', STR_PAD_LEFT);
}

// ── AUTO STATUS ESCALATION ────────────────────────────────────────────────────
$cutoff_3m = date('Y-m-d', strtotime('-3 months'));
$cutoff_6m = date('Y-m-d', strtotime('-6 months'));

$to_refresher = $conn->query("
    SELECT km.id, km.full_name, km.current_khan_level,
           km.date_promoted, km.date_joined,
           MAX(kth.training_date) AS last_training
    FROM khan_members km
    LEFT JOIN khan_training_history kth ON kth.member_id = km.id
    WHERE km.status = 'active'
    GROUP BY km.id
    HAVING (last_training IS NULL OR last_training < '$cutoff_3m')
       AND (km.date_promoted IS NULL OR km.date_promoted < '$cutoff_3m')
       AND (km.date_joined   IS NULL OR km.date_joined   < '$cutoff_3m')
");

$auto_refreshed = 0;
if ($to_refresher && $to_refresher->num_rows > 0) {
    while ($s = $to_refresher->fetch_assoc()) {
        $sid = (int)$s['id'];
        $conn->query("UPDATE khan_members SET status = 'refresher' WHERE id = $sid AND status = 'active'");
        if ($conn->affected_rows > 0) {
            $last = $s['last_training'] ?? $s['date_promoted'] ?? $s['date_joined'] ?? 'unknown';
            logActivity($conn, 'refresher', 'khan_members', $sid, $s['full_name'],
                'Auto-flagged: No promotion or training in the last 3 months. ' .
                'Last activity: ' . $last . '. Khan Level: ' . $s['current_khan_level'] . '.', [], []);
            $auto_refreshed++;
        }
    }
}

$to_inactive = $conn->query("
    SELECT km.id, km.full_name, km.current_khan_level,
           km.date_promoted, km.date_joined,
           MAX(kth.training_date) AS last_training
    FROM khan_members km
    LEFT JOIN khan_training_history kth ON kth.member_id = km.id
    WHERE km.status = 'refresher'
    GROUP BY km.id
    HAVING (last_training IS NULL OR last_training < '$cutoff_6m')
       AND (km.date_promoted IS NULL OR km.date_promoted < '$cutoff_6m')
       AND (km.date_joined   IS NULL OR km.date_joined   < '$cutoff_6m')
");

$auto_inactivated = 0;
if ($to_inactive && $to_inactive->num_rows > 0) {
    while ($s = $to_inactive->fetch_assoc()) {
        $sid = (int)$s['id'];
        $conn->query("UPDATE khan_members SET status = 'inactive' WHERE id = $sid AND status = 'refresher'");
        if ($conn->affected_rows > 0) {
            $last = $s['last_training'] ?? $s['date_promoted'] ?? $s['date_joined'] ?? 'unknown';
            logActivity($conn, 'edit', 'khan_members', $sid, $s['full_name'],
                'Auto-inactivated: No promotion or training in the last 6 months (was Refresher). ' .
                'Last activity: ' . $last . '. Khan Level: ' . $s['current_khan_level'] . '.', [], []);
            $auto_inactivated++;
        }
    }
}

$auto_msgs = [];
if ($auto_refreshed   > 0) $auto_msgs[] = $auto_refreshed   . ' member(s) flagged as Needs Refresher (no activity in 3 months).';
if ($auto_inactivated > 0) $auto_msgs[] = $auto_inactivated . ' member(s) set to Inactive (no activity in 6 months).';
if (!empty($auto_msgs)) $success = implode(' ', $auto_msgs);

// ── PAGINATION HELPER ─────────────────────────────────────────────────────────
function buildPaginationBar($total, $per_page, $current_page, $extra_params = []) {
    $total_pages = max(1, ceil($total / $per_page));
    $makeUrl = function($p) use ($per_page, $extra_params) {
        $params = array_merge($extra_params, ['page' => $p]);
        if ($per_page !== 10) $params['per_page'] = $per_page;
        return '?' . http_build_query($params);
    };
    $btnBase  = 'display:inline-block;padding:.35rem .7rem;border-radius:5px;border:1px solid #ddd;font-size:.85rem;text-decoration:none;color:#333;background:#fff;';
    $btnActive = 'background:#007bff;color:#fff;border-color:#007bff;font-weight:600;';
    $btnDis   = 'opacity:.45;pointer-events:none;';
    ob_start(); ?>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-top:1rem;padding:.8rem 1rem;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;">
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <span style="color:#666;font-size:.88rem;">
                Showing <strong><?= min(($current_page-1)*$per_page+1,$total) ?>–<?= min($current_page*$per_page,$total) ?></strong>
                of <strong><?= $total ?></strong>
            </span>
            <form method="GET" style="display:flex;align-items:center;gap:.4rem;">
                <?php foreach($extra_params as $k=>$v): ?><input type="hidden" name="<?=$k?>" value="<?=htmlspecialchars($v)?>"><?php endforeach; ?>
                <input type="hidden" name="page" value="1">
                <label style="font-size:.85rem;color:#666;">Rows:</label>
                <select name="per_page" onchange="this.form.submit()" style="padding:.3rem .5rem;border:1px solid #ddd;border-radius:5px;font-size:.85rem;cursor:pointer;">
                    <?php foreach([10,25,50,100] as $opt): ?>
                        <option value="<?=$opt?>" <?=$per_page==$opt?'selected':''?>><?=$opt?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php if($total_pages>1): ?>
        <div style="display:flex;gap:.3rem;align-items:center;">
            <?php
            $pd=$current_page<=1?$btnDis:'';
            echo "<a href='{$makeUrl($current_page-1)}' style='{$btnBase}{$pd}'>&laquo;</a>";
            $rng=2;$sp=max(1,$current_page-$rng);$ep=min($total_pages,$current_page+$rng);
            if($ep-$sp<$rng*2){$sp=max(1,$ep-$rng*2);$ep=min($total_pages,$sp+$rng*2);}
            if($sp>1){echo "<a href='{$makeUrl(1)}' style='{$btnBase}'>1</a>";if($sp>2)echo "<span style='padding:.35rem .5rem;color:#999;font-size:.85rem;'>…</span>";}
            for($p=$sp;$p<=$ep;$p++){$a=$p===$current_page?$btnActive:'';echo "<a href='{$makeUrl($p)}' style='{$btnBase}{$a}'>{$p}</a>";}
            if($ep<$total_pages){if($ep<$total_pages-1)echo "<span style='padding:.35rem .5rem;color:#999;font-size:.85rem;'>…</span>";echo "<a href='{$makeUrl($total_pages)}' style='{$btnBase}'>{$total_pages}</a>";}
            $nd=$current_page>=$total_pages?$btnDis:'';
            echo "<a href='{$makeUrl($current_page+1)}' style='{$btnBase}{$nd}'>&raquo;</a>";
            ?>
        </div>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}

// ══════════════════════════════════════════════════════════════════════════════
// HANDLE FORM SUBMISSIONS — POST/REDIRECT/GET pattern prevents duplicate submits
// and guarantees the member list is always re-fetched fresh after any action.
// ══════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── ADD MEMBER ────────────────────────────────────────────────────────────
    if (isset($_POST['add_member'])) {
        $full_name         = sanitize($_POST['full_name']);
        $email             = sanitize($_POST['email']);
        $phone             = sanitize($_POST['phone'] ?? '');
        $current_khan_level = (int)$_POST['current_khan_level'];
        $date_joined       = $_POST['date_joined'];
        $date_promoted     = !empty($_POST['date_promoted']) ? $_POST['date_promoted'] : null;
        $instructor_id     = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
        $training_location = sanitize($_POST['training_location'] ?? '');
        $status            = $_POST['status'];
        $notes             = sanitize($_POST['notes'] ?? '');

        // Get khan color from database
        $color_result = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $current_khan_level");
        $khan_color   = ($color_result && $cr = $color_result->fetch_assoc()) ? $cr['color_name'] : '';

        // ── Auto-create or link user account ──────────────────────────────────
        $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $serial  = '';

        if (!$user_id && !empty($email)) {
            $ucheck = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "' LIMIT 1");
            if ($ucheck && $ucheck->num_rows > 0) {
                $user_id = (int)$ucheck->fetch_assoc()['id'];
            } else {
                $serial = nextMemberSerial($conn);
                $parts  = array_values(array_filter(explode(' ', strtolower(trim($full_name)))));
                $first  = $parts[0] ?? 'member';
                $last   = count($parts) > 1 ? $parts[count($parts) - 1] : '';
                $default_password = password_hash('oma' . $first . $last, PASSWORD_DEFAULT);
                $khan_level_label = 'Khan ' . $current_khan_level;

                $ustmt = $conn->prepare("INSERT INTO users (serial_number, name, email, phone, password, role, status, khan_level) VALUES (?, ?, ?, ?, ?, 'member', ?, ?)");
                $ustmt->bind_param("sssssss", $serial, $full_name, $email, $phone, $default_password, $status, $khan_level_label);
                if ($ustmt->execute()) {
                    $user_id = $conn->insert_id;
                } else {
                    // User creation failed (e.g. duplicate email) — log but continue without linking
                    $serial = ''; // no serial was created
                }
                $ustmt->close();
            }
        }

        // ── Insert khan_members record ────────────────────────────────────────
        // FIX: Use NULL binding for optional integer/date fields to avoid type errors
        $stmt = $conn->prepare("
            INSERT INTO khan_members
                (user_id, full_name, email, phone, current_khan_level, khan_color,
                 date_joined, date_promoted, instructor_id, training_location, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Type string (12 params):
        //  i=user_id  s=full_name  s=email  s=phone  i=current_khan_level  s=khan_color
        //  s=date_joined  s=date_promoted  i=instructor_id  s=training_location  s=status  s=notes
        $stmt->bind_param(
            "isssisssisss",
            $user_id, $full_name, $email, $phone,
            $current_khan_level, $khan_color,
            $date_joined, $date_promoted,
            $instructor_id, $training_location,
            $status, $notes
        );

        if ($stmt->execute()) {
            $new_member_id = $conn->insert_id;
            $serial_msg = $serial ? ' User account created with serial <strong>' . htmlspecialchars($serial) . '</strong>.' : '';
            logActivity(
                $conn, 'create', 'khan_members', $new_member_id,
                $full_name,
                'Added new Khan member. Level: Khan ' . $current_khan_level .
                ' (' . $khan_color . ') | Email: ' . $email .
                ' | Phone: ' . ($phone ?: 'N/A') .
                ' | Location: ' . ($training_location ?: 'N/A') .
                ' | Instructor ID: ' . ($instructor_id ?? 'None') .
                ' | Status: ' . $status .
                ' | Joined: ' . $date_joined .
                ($date_promoted ? ' | Promoted: ' . $date_promoted : '') .
                ($notes ? ' | Notes: ' . $notes : '')
            );
            // ★ PRG: redirect so refresh doesn't re-submit the form
            session_start_if_needed(); // helper below
            $_SESSION['flash_success'] = 'Khan member <strong>' . htmlspecialchars($full_name) . '</strong> added successfully!' . $serial_msg;
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?page=1');
            exit;
        } else {
            $error = 'Failed to add khan member: ' . htmlspecialchars($conn->error);
        }
        $stmt->close();

    // ── EDIT MEMBER ───────────────────────────────────────────────────────────
    } elseif (isset($_POST['edit_member'])) {
        $id                = (int)$_POST['id'];
        $before_row        = $conn->query("SELECT * FROM khan_members WHERE id = $id")->fetch_assoc();
        $user_id           = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $full_name         = sanitize($_POST['full_name']);
        $email             = sanitize($_POST['email']);
        $phone             = sanitize($_POST['phone'] ?? '');
        $current_khan_level = (int)$_POST['current_khan_level'];
        $date_joined       = $_POST['date_joined'];
        $date_promoted     = !empty($_POST['date_promoted']) ? $_POST['date_promoted'] : null;
        $instructor_id     = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
        $training_location = sanitize($_POST['training_location'] ?? '');
        $status            = $_POST['status'];
        $notes             = sanitize($_POST['notes'] ?? '');

        $color_result = $conn->query("SELECT color_name FROM khan_colors WHERE khan_level = $current_khan_level");
        $khan_color   = ($color_result && $cr = $color_result->fetch_assoc()) ? $cr['color_name'] : '';

        $stmt = $conn->prepare("
            UPDATE khan_members
            SET user_id=?, full_name=?, email=?, phone=?,
                current_khan_level=?, khan_color=?,
                date_joined=?, date_promoted=?,
                instructor_id=?, training_location=?,
                status=?, notes=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "isssississssi",
            $user_id, $full_name, $email, $phone,
            $current_khan_level, $khan_color,
            $date_joined, $date_promoted,
            $instructor_id, $training_location,
            $status, $notes, $id
        );

        if ($stmt->execute()) {
            // Password change
            $new_password = trim($_POST['new_password'] ?? '');
            if ($new_password !== '') {
                $linked_uid = $user_id;
                if (!$linked_uid) {
                    $ue = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "' LIMIT 1");
                    if ($ue && $ue->num_rows > 0) $linked_uid = (int)$ue->fetch_assoc()['id'];
                }
                if ($linked_uid) {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $conn->query("UPDATE users SET password = '" . $conn->real_escape_string($hashed) . "' WHERE id = $linked_uid");
                    logActivity($conn, 'edit', 'users', $linked_uid, $full_name, 'Password changed by admin.');
                }
            }

            // Diff log
            $after_row = [
                'full_name' => $full_name, 'email' => $email, 'phone' => $phone,
                'current_khan_level' => $current_khan_level, 'khan_color' => $khan_color,
                'date_joined' => $date_joined, 'date_promoted' => $date_promoted,
                'instructor_id' => $instructor_id, 'training_location' => $training_location,
                'status' => $status, 'notes' => $notes
            ];
            $changes = [];
            foreach (['full_name','email','phone','current_khan_level','date_promoted',
                      'instructor_id','training_location','status','notes'] as $f) {
                if ((string)($before_row[$f] ?? '') !== (string)($after_row[$f] ?? '')) {
                    $changes[] = $f . ': [' . ($before_row[$f] ?? '') . ' → ' . ($after_row[$f] ?? '') . ']';
                }
            }
            logActivity($conn, 'edit', 'khan_members', $id, $full_name,
                empty($changes) ? 'No field changes detected.' : 'Fields changed: ' . implode(' | ', $changes));

            session_start_if_needed();
            $_SESSION['flash_success'] = 'Khan member <strong>' . htmlspecialchars($full_name) . '</strong> updated successfully!';
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query(array_filter($_GET)));
            exit;
        } else {
            $error = 'Failed to update khan member: ' . htmlspecialchars($conn->error);
        }
        $stmt->close();

    // ── DELETE MEMBER ─────────────────────────────────────────────────────────
    } elseif (isset($_POST['delete_member'])) {
        $id      = (int)$_POST['id'];
        // Fetch the raw khan_members row only (no JOINs) so the archive JSON
        // contains only actual table columns — prevents restore errors.
        $del_row = $conn->query("SELECT * FROM khan_members WHERE id = $id")->fetch_assoc();
        // Fetch instructor name separately just for the log message
        $del_instructor = '';
        if (!empty($del_row['instructor_id'])) {
            $ir = $conn->query("SELECT name FROM instructors WHERE id = " . (int)$del_row['instructor_id'] . " LIMIT 1");
            if ($ir && $ir->num_rows > 0) $del_instructor = $ir->fetch_assoc()['name'];
        }

        if ($del_row) {
            archiveRecord($conn, 'khan_members', $id, $del_row['full_name'], $del_row);
            logActivity(
                $conn, 'delete', 'khan_members', $id,
                $del_row['full_name'],
                'Permanently deleted Khan member. Khan Level: ' . $del_row['current_khan_level'] .
                ' | Email: ' . $del_row['email'] .
                ' | Location: ' . ($del_row['training_location'] ?: 'N/A') .
                ' | Instructor: ' . ($del_instructor ?: 'None') .
                ' | Status was: ' . $del_row['status']
            );
        }

        if ($conn->query("DELETE FROM khan_members WHERE id = $id")) {
            session_start_if_needed();
            $_SESSION['flash_success'] = 'Khan member deleted and archived successfully.';
        } else {
            session_start_if_needed();
            $_SESSION['flash_error'] = 'Failed to delete khan member: ' . htmlspecialchars($conn->error);
        }
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?page=1');
        exit;
    }
}

// ── Pick up flash messages from session (set after redirect) ──────────────────
function session_start_if_needed() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}
session_start_if_needed();
if (!empty($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (!empty($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// ── SERVER-SIDE SEARCH + FILTER + PAGINATION ─────────────────────────────────
$_per_page    = isset($_GET['per_page']) ? max(10, min(100, (int)$_GET['per_page'])) : 10;
$_cur_page    = isset($_GET['page'])     ? max(1, (int)$_GET['page']) : 1;
$_search      = trim($_GET['search'] ?? '');
$_lvl_filter  = isset($_GET['level'])  && $_GET['level']  !== '' ? (int)$_GET['level']  : 0;
$_stat_filter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : '';

// Build WHERE clause
$_where_parts = [];
if ($_search !== '') {
    $s = $conn->real_escape_string($_search);
    $_where_parts[] = "(km.full_name LIKE '%$s%' OR km.email LIKE '%$s%' OR km.phone LIKE '%$s%' OR km.training_location LIKE '%$s%' OR i.name LIKE '%$s%')";
}
if ($_lvl_filter > 0)    $_where_parts[] = "km.current_khan_level = $_lvl_filter";
if ($_stat_filter !== '') $_where_parts[] = "km.status = '" . $conn->real_escape_string($_stat_filter) . "'";
$_where = $_where_parts ? 'WHERE ' . implode(' AND ', $_where_parts) : '';

// Count with filters applied
$_total_members = (int)$conn->query("
    SELECT COUNT(*) as c
    FROM khan_members km
    LEFT JOIN instructors i ON km.instructor_id = i.id
    $_where
")->fetch_assoc()['c'];

$_max_page = max(1, (int)ceil($_total_members / $_per_page));
if ($_cur_page > $_max_page) { $_cur_page = $_max_page; }
$_offset = ($_cur_page - 1) * $_per_page;

$members = $conn->query("
    SELECT km.*, i.name as instructor_name, u.serial_number, u.email as user_email
    FROM khan_members km
    LEFT JOIN instructors i ON km.instructor_id = i.id
    LEFT JOIN users u ON km.user_id = u.id
    $_where
    ORDER BY km.id DESC
    LIMIT $_per_page OFFSET $_offset
");

if (!$members) {
    $error .= ' [DB fetch error: ' . htmlspecialchars($conn->error) . ']';
}

// Extra params for pagination links (preserve active filters)
$_pagination_params = array_filter([
    'search' => $_search,
    'level'  => $_lvl_filter  ?: '',
    'status' => $_stat_filter ?: '',
    'per_page' => $_per_page !== 10 ? $_per_page : '',
]);

// Get instructors for dropdown
$instructors = $conn->query("SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name");
$available_users = $conn->query("SELECT id, name, email FROM users WHERE role = 'member' ORDER BY name");
$khan_colors = $conn->query("SELECT khan_level, color_name, hex_color FROM khan_colors ORDER BY khan_level ASC");

include 'includes/admin_header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php
// ★ DEBUG HELPER — remove once confirmed working
// Shows row count directly so you can confirm DB has data
$debug_count = $conn->query("SELECT COUNT(*) as c FROM khan_members")->fetch_assoc()['c'];
if ($debug_count === 0):
?>
<div class="alert" style="background:#fff3cd; border-left:4px solid #ffc107; padding:1rem; margin-bottom:1rem; border-radius:4px;">
    <strong>⚠ No Khan Members in the database yet.</strong>
    Use the "Encode Members" button or the Add modal to add your first member.
</div>
<?php endif; ?>

<div class="admin-section">
    <div class="section-header">
        <h2><i class="fas fa-user-graduate"></i> Khan Members Management
            <small style="font-size:0.8rem; font-weight:400; color:#888; margin-left:8px;">
                <?php echo number_format($_total_members); ?> total member<?php echo $_total_members !== 1 ? 's' : ''; ?>
            </small>
        </h2>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
            <a href="refresher_requests.php" class="btn btn-outline" style="position:relative;">
                <i class="fas fa-sync-alt"></i> Refresher Requests
                <?php if ($pending_refresher_count > 0): ?>
                <span style="position:absolute;top:-6px;right:-6px;background:#f44336;color:white;font-size:0.7rem;font-weight:700;min-width:18px;height:18px;border-radius:999px;display:flex;align-items:center;justify-content:center;padding:0 4px;">
                    <?php echo $pending_refresher_count; ?>
                </span>
                <?php endif; ?>
            </a>
            <button class="btn btn-success" onclick="document.getElementById('addModal').style.display='block'">
                <i class="fas fa-user-plus"></i> Add Member
            </button>
            <a href="manual_encode.php" class="btn btn-primary">
                <i class="fas fa-keyboard"></i> Encode Members
            </a>
        </div>
    </div>

    <form method="GET" id="filterForm" style="display:flex;gap:1rem;margin-bottom:1.5rem;align-items:center;flex-wrap:wrap;">
        <div style="flex:1;min-width:250px;position:relative;">
            <input type="text" name="search" id="searchInput"
                   placeholder="Search name, email, phone, location…"
                   value="<?php echo htmlspecialchars($_search); ?>"
                   style="width:100%;padding-right:2.2rem;"
                   autocomplete="off">
            <?php if ($_search !== ''): ?>
            <a href="?<?php echo http_build_query(array_filter(['level'=>$_lvl_filter?:'',' status'=>$_stat_filter])); ?>"
               title="Clear search"
               style="position:absolute;right:8px;top:50%;transform:translateY(-50%);text-decoration:none;color:#999;font-size:1.1rem;line-height:1;">×</a>
            <?php endif; ?>
        </div>
        <select name="level" class="form-select" style="width:180px;" onchange="this.form.submit()">
            <option value="">All Levels</option>
            <?php for ($i = 1; $i <= 16; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo $_lvl_filter === $i ? 'selected' : ''; ?>>Khan <?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        <select name="status" class="form-select" style="width:180px;" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="active"    <?php echo $_stat_filter === 'active'    ? 'selected' : ''; ?>>Active</option>
            <option value="inactive"  <?php echo $_stat_filter === 'inactive'  ? 'selected' : ''; ?>>Inactive</option>
            <option value="refresher" <?php echo $_stat_filter === 'refresher' ? 'selected' : ''; ?>>Needs Refresher</option>
        </select>
        <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if ($_search !== '' || $_lvl_filter || $_stat_filter !== ''): ?>
        <a href="?" class="btn btn-outline" style="white-space:nowrap;">
            <i class="fas fa-times"></i> Clear
        </a>
        <?php endif; ?>
        <?php if ($_per_page !== 10): ?>
        <input type="hidden" name="per_page" value="<?php echo $_per_page; ?>">
        <?php endif; ?>
    </form>

    <?php if ($_search !== '' || $_lvl_filter || $_stat_filter !== ''): ?>
    <div style="margin-bottom:1rem;font-size:0.88rem;color:#666;">
        Found <strong><?php echo number_format($_total_members); ?></strong> result<?php echo $_total_members !== 1 ? 's' : ''; ?>
        <?php if ($_search !== ''): ?> for "<strong><?php echo htmlspecialchars($_search); ?></strong>"<?php endif; ?>
        <?php if ($_lvl_filter): ?> · Khan <?php echo $_lvl_filter; ?><?php endif; ?>
        <?php if ($_stat_filter !== ''): ?> · <?php echo ucfirst($_stat_filter); ?><?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="data-table" id="membersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>User Account</th>
                    <th>Email/Phone</th>
                    <th>Khan Level</th>
                    <th>Color</th>
                    <th>Instructor</th>
                    <th>Location</th>
                    <th>Date Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // ★ FIX: check if $members is valid before looping
                if ($members && $members->num_rows > 0):
                    while ($member = $members->fetch_assoc()):
                ?>
                <tr data-level="<?php echo $member['current_khan_level']; ?>"
                    data-status="<?php echo $member['status']; ?>">
                    <td><?php echo $member['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($member['full_name']); ?></strong></td>
                    <td>
                        <?php if ($member['user_id']): ?>
                            <span style="display:inline-block;background:#e3f2fd;color:#1976d2;padding:0.25rem 0.5rem;border-radius:4px;font-size:0.85rem;">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($member['serial_number'] ?? '—'); ?>
                            </span><br>
                            <small style="color:#666;"><?php echo htmlspecialchars($member['user_email'] ?? ''); ?></small>
                        <?php else: ?>
                            <span style="color:#999;font-style:italic;">No account</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <small><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($member['email']); ?></small><br>
                        <small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($member['phone'] ?: 'N/A'); ?></small>
                    </td>
                    <td><strong style="color:#388e3c;">Khan <?php echo $member['current_khan_level']; ?></strong></td>
                    <td>
                        <?php if ($member['khan_color']): ?>
                            <span style="display:inline-block;background:#f5f5f5;padding:0.25rem 0.5rem;border-radius:4px;font-size:0.85rem;">
                                <?php echo htmlspecialchars($member['khan_color']); ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#999;">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($member['instructor_name'] ?: 'Not assigned'); ?></td>
                    <td><?php echo htmlspecialchars($member['training_location'] ?: 'N/A'); ?></td>
                    <td><small><?php echo formatDate($member['date_joined']); ?></small></td>
                    <td>
                        <span class="badge badge-<?php echo $member['status']; ?>"
                              style="padding:0.3rem 0.6rem;border-radius:4px;font-size:0.85rem;">
                            <?php echo ucfirst($member['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary"
                                onclick="editMember(<?php echo htmlspecialchars(json_encode($member), ENT_QUOTES, 'UTF-8'); ?>)"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display:inline;"
                                  onsubmit="return confirm('Are you sure you want to delete this member?');">
                                <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                <button type="submit" name="delete_member" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <a href="member_training_history.php?member_id=<?php echo $member['id']; ?>"
                               class="btn btn-sm btn-success" title="View Training History">
                                <i class="fas fa-history"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="11" style="text-align:center;padding:2rem;color:#999;">
                        <?php if ($_total_members > 0): ?>
                            No members found for the current page. <a href="?page=1">Go to page 1</a>
                        <?php else: ?>
                            No members added yet. Click <strong>Add Member</strong> or <strong>Encode Members</strong> to get started.
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo buildPaginationBar($_total_members, $_per_page, $_cur_page, $_pagination_params); ?>
</div>

<!-- Add Member Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-user-plus"></i> Add Khan Member</h2>

        <div class="alert" style="background:#e3f2fd;border-left:4px solid #1976d2;padding:1rem;margin-bottom:1.5rem;border-radius:4px;">
            <strong><i class="fas fa-lightbulb"></i> Tip:</strong> For bulk encoding use
            <a href="manual_encode.php" style="color:#1976d2;font-weight:bold;">Encode Members</a>.
        </div>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Link to User Account (Optional)</label>
                <select name="user_id" class="form-select">
                    <option value="">-- Auto-create / detect by email --</option>
                    <?php
                    if ($available_users) { $available_users->data_seek(0); }
                    while ($available_users && $user = $available_users->fetch_assoc()):
                    ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-input" placeholder="09XX XXX XXXX">
                </div>
                <div class="form-group">
                    <label class="form-label">Current Khan Level *</label>
                    <select name="current_khan_level" id="add_current_khan_level" class="form-select" required
                            onchange="updateKhanColor('add')">
                        <?php for ($i = 1; $i <= 16; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i === 1 ? 'selected' : ''; ?>>Khan <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Khan Color/Band (Auto-filled)</label>
                    <div style="position:relative;">
                        <input type="text" name="khan_color" id="add_khan_color_display" class="form-input"
                               value="White" readonly style="padding-left:45px;background:#f5f5f5;cursor:not-allowed;">
                        <div id="add_color_indicator" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:25px;height:25px;border-radius:50%;border:2px solid #999;background:#FFFFFF;"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Joined *</label>
                    <input type="date" name="date_joined" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Date Promoted (Optional)</label>
                    <input type="date" name="date_promoted" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Instructor/Kru</label>
                    <select name="instructor_id" class="form-select">
                        <option value="">-- No Instructor --</option>
                        <?php
                        if ($instructors) { $instructors->data_seek(0); }
                        while ($instructors && $instructor = $instructors->fetch_assoc()):
                        ?>
                            <option value="<?php echo $instructor['id']; ?>"><?php echo htmlspecialchars($instructor['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Training Location</label>
                    <input type="text" name="training_location" class="form-input" placeholder="e.g., Quezon City">
                </div>
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="refresher">Needs Refresher</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Additional notes…"></textarea>
            </div>

            <div class="action-buttons">
                <button type="submit" name="add_member" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Member
                </button>
                <button type="button" class="btn btn-outline"
                        onclick="document.getElementById('addModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Member Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h2><i class="fas fa-user-edit"></i> Edit Khan Member</h2>
        <form method="POST" id="editForm">
            <input type="hidden" name="id" id="edit_id">

            <div class="form-group">
                <label class="form-label">Link to User Account (Optional)</label>
                <select name="user_id" id="edit_user_id" class="form-select">
                    <option value="">-- No User Account --</option>
                    <?php
                    if ($available_users) { $available_users->data_seek(0); }
                    while ($available_users && $user = $available_users->fetch_assoc()):
                    ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" id="edit_full_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" id="edit_email" class="form-input" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" id="edit_phone" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Current Khan Level *</label>
                    <select name="current_khan_level" id="edit_current_khan_level" class="form-select" required
                            onchange="updateKhanColor('edit')">
                        <?php for ($i = 1; $i <= 16; $i++): ?>
                            <option value="<?php echo $i; ?>">Khan <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Khan Color/Band (Auto-filled)</label>
                    <div style="position:relative;">
                        <input type="text" name="khan_color" id="edit_khan_color_display" class="form-input"
                               readonly style="padding-left:45px;background:#f5f5f5;cursor:not-allowed;">
                        <div id="edit_color_indicator" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:25px;height:25px;border-radius:50%;border:2px solid #999;"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Joined *</label>
                    <input type="date" name="date_joined" id="edit_date_joined" class="form-input" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Date Promoted (Optional)</label>
                    <input type="date" name="date_promoted" id="edit_date_promoted" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Instructor/Kru</label>
                    <select name="instructor_id" id="edit_instructor_id" class="form-select">
                        <option value="">-- No Instructor --</option>
                        <?php
                        if ($instructors) { $instructors->data_seek(0); }
                        while ($instructors && $instructor = $instructors->fetch_assoc()):
                        ?>
                            <option value="<?php echo $instructor['id']; ?>"><?php echo htmlspecialchars($instructor['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Training Location</label>
                    <input type="text" name="training_location" id="edit_training_location" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" id="edit_status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="refresher">Needs Refresher</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" id="edit_notes" class="form-textarea" rows="3"></textarea>
            </div>

            <div style="border-top:2px dashed #e0e0e0;margin:1.5rem 0;padding-top:1.5rem;">
                <h4 style="margin:0 0 0.75rem;color:#555;font-size:0.95rem;">
                    <i class="fas fa-lock"></i> Change Password
                    <span style="font-weight:400;color:#999;">(leave blank to keep current)</span>
                </h4>
                <div class="form-grid">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">New Password</label>
                        <div style="position:relative;">
                            <input type="password" name="new_password" id="edit_new_password"
                                   class="form-input" placeholder="Enter new password" style="padding-right:2.5rem;">
                            <button type="button" onclick="togglePwVisibility()"
                                    style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#888;">
                                <i class="fas fa-eye" id="pwToggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Default Password Hint</label>
                        <div style="background:#f5f5f5;border-radius:4px;padding:0.55rem 0.75rem;font-size:0.85rem;color:#666;border:1px solid #ddd;">
                            Format: <code style="color:#D32F2F;">oma</code> + first name + last name
                            <br><small id="pwHint" style="color:#888;"></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <button type="submit" name="edit_member" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Member
                </button>
                <button type="button" class="btn btn-outline"
                        onclick="document.getElementById('editModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal { display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background:rgba(0,0,0,0.5); }
.modal-content { background:#fefefe;margin:3% auto;padding:2rem;border-radius:8px;width:90%;max-width:900px;max-height:90vh;overflow-y:auto;box-shadow:0 4px 20px rgba(0,0,0,0.3);animation:slideDown .3s; }
@keyframes slideDown { from{transform:translateY(-50px);opacity:0}to{transform:translateY(0);opacity:1} }
.modal-close { color:#aaa;float:right;font-size:28px;font-weight:bold;cursor:pointer;transition:color .3s; }
.modal-close:hover { color:#000; }
.badge-active   { background:#4caf50;color:white; }
.badge-inactive { background:#757575;color:white; }
.badge-refresher{ background:#ff9800;color:white; }
</style>

<script>
const khanColorMap = {
    <?php
    if ($khan_colors) { $khan_colors->data_seek(0); }
    $color_map = [];
    while ($khan_colors && $kc = $khan_colors->fetch_assoc()) {
        $color_map[] = $kc['khan_level'] . ": { name: '" . addslashes($kc['color_name']) . "', hex: '" . $kc['hex_color'] . "' }";
    }
    echo implode(",\n        ", $color_map);
    ?>
};

function updateKhanColor(prefix) {
    const level = document.getElementById(prefix + '_current_khan_level').value;
    const display   = document.getElementById(prefix + '_khan_color_display');
    const indicator = document.getElementById(prefix + '_color_indicator');
    if (khanColorMap[level]) {
        display.value = khanColorMap[level].name;
        indicator.style.backgroundColor = khanColorMap[level].hex;
        const lightColors = ['#FFFFFF','#FFFACD','#90EE90','#87CEEB','#D2B48C','#FFB6C1'];
        indicator.style.border = lightColors.includes(khanColorMap[level].hex)
            ? '2px solid #999' : '2px solid #ddd';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    updateKhanColor('add');
});

function editMember(member) {
    document.getElementById('edit_id').value                  = member.id;
    document.getElementById('edit_user_id').value             = member.user_id || '';
    document.getElementById('edit_full_name').value           = member.full_name;
    document.getElementById('edit_email').value               = member.email;
    document.getElementById('edit_phone').value               = member.phone || '';
    document.getElementById('edit_current_khan_level').value  = member.current_khan_level;
    document.getElementById('edit_date_joined').value         = member.date_joined;
    document.getElementById('edit_date_promoted').value       = member.date_promoted || '';
    document.getElementById('edit_instructor_id').value       = member.instructor_id || '';
    document.getElementById('edit_training_location').value   = member.training_location || '';
    document.getElementById('edit_status').value              = member.status;
    document.getElementById('edit_notes').value               = member.notes || '';
    document.getElementById('edit_new_password').value        = '';

    const parts = member.full_name.trim().toLowerCase().split(/\s+/).filter(Boolean);
    const first = parts[0] || '';
    const last  = parts.length > 1 ? parts[parts.length - 1] : '';
    document.getElementById('pwHint').textContent = 'Default: oma' + first + last;

    updateKhanColor('edit');
    document.getElementById('editModal').style.display = 'block';
}

// Search submits on Enter key
document.getElementById('searchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('filterForm').submit(); }
});

window.onclick = function(e) {
    ['addModal','editModal'].forEach(id => {
        if (e.target === document.getElementById(id))
            document.getElementById(id).style.display = 'none';
    });
};

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('addModal').style.display  = 'none';
        document.getElementById('editModal').style.display = 'none';
    }
});

function togglePwVisibility() {
    const input = document.getElementById('edit_new_password');
    const icon  = document.getElementById('pwToggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye','fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash','fa-eye');
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>