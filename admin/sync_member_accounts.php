<?php
/**
 * sync_member_accounts.php
 * ─────────────────────────────────────────────────────────────────
 * ONE-TIME migration script.
 * Finds every khan_member that has no linked user account and
 * creates one automatically.
 *
 * HOW TO USE:
 *   1. Upload to your /admin/ folder
 *   2. Open it in your browser while logged in as admin
 *   3. Review the preview, then click "Run Sync"
 *   4. Done — delete the file afterward for security
 *
 * WHAT IT DOES PER MEMBER:
 *   - Generates a unique serial number  (OMA-017, OMA-018, …)
 *   - Uses the member's existing email if it looks real,
 *     otherwise generates  firstname.lastname@oma.local
 *   - Sets default password:  oma + firstname + lastname
 *     (e.g.  omajuancruz)  — member can change after first login
 *   - Links the new user back to the khan_members row (user_id)
 *   - Skips any member whose email already exists in users table
 * ─────────────────────────────────────────────────────────────────
 */

if (!defined('DB_HOST')) {
    require_once '../config/database.php';
}
requireAdmin();

$conn = getDbConnection();

// ── Helper: next serial number ─────────────────────────────────────
function nextSerial($conn) {
    $res  = $conn->query("SELECT serial_number FROM users WHERE serial_number LIKE 'OMA-%' ORDER BY serial_number DESC LIMIT 1");
    $last = $res ? $res->fetch_assoc() : null;
    $next = 1;
    if ($last && preg_match('/OMA-0*(\d+)/', $last['serial_number'], $m)) {
        $next = (int)$m[1] + 1;
    }
    return 'OMA-' . str_pad($next, 3, '0', STR_PAD_LEFT);
}

// ── Helper: unique safe email ──────────────────────────────────────
function safeEmail($conn, $fullName, $existingEmail) {
    // If the existing email looks real (not @oma.com / @archive.local auto-generated), use it
    $isAutoEmail = preg_match('/@oma\.com$|@oma\.local$|@archive\.local$/', $existingEmail)
                   || preg_match('/_\d{10}\d{3}@/', $existingEmail); // old slug+timestamp format

    if (!$isAutoEmail && filter_var($existingEmail, FILTER_VALIDATE_EMAIL)) {
        // Check if this real email is already taken by another user
        $chk = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($existingEmail) . "' LIMIT 1");
        if (!$chk || $chk->num_rows === 0) {
            return $existingEmail; // safe to use
        }
    }

    // Generate from name
    $slug  = preg_replace('/[^a-z0-9]+/', '.', strtolower(trim($fullName)));
    $slug  = trim($slug, '.');
    $email = $slug . '@oma.local';
    $i     = 2;
    while ($conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "' LIMIT 1")->num_rows > 0) {
        $email = $slug . $i . '@oma.local';
        $i++;
    }
    return $email;
}

// ── Helper: default password ───────────────────────────────────────
function defaultPassword($fullName) {
    $parts = array_values(array_filter(explode(' ', strtolower(trim($fullName)))));
    $first = $parts[0] ?? 'member';
    $last  = count($parts) > 1 ? $parts[count($parts) - 1] : '';
    return 'oma' . $first . $last;
}

// ── Fetch members without a user account ──────────────────────────
$members = $conn->query("
    SELECT id, full_name, email, phone, current_khan_level, khan_color, status
    FROM khan_members
    WHERE user_id IS NULL
    ORDER BY full_name ASC
");

$pending = [];
while ($m = $members->fetch_assoc()) {
    $email = safeEmail($conn, $m['full_name'], $m['email']);
    $pw    = defaultPassword($m['full_name']);
    $pending[] = array_merge($m, ['new_email' => $email, 'default_pw' => $pw]);
}

// ── RUN SYNC ──────────────────────────────────────────────────────
$results = [];
$did_run = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_sync'])) {
    $did_run = true;
    $conn->begin_transaction();

    try {
        foreach ($pending as $m) {
            $serial   = nextSerial($conn);
            $email    = $m['new_email'];
            $pw_plain = $m['default_pw'];
            $pw_hash  = password_hash($pw_plain, PASSWORD_DEFAULT);
            $name     = $m['full_name'];
            $phone    = $m['phone'] ?? '';
            $status   = $m['status'] === 'active' ? 'active' : 'inactive';
            $klabel   = 'Khan ' . $m['current_khan_level'];

            // Insert user
            $ustmt = $conn->prepare("
                INSERT INTO users (serial_number, name, email, phone, password, role, status, khan_level)
                VALUES (?, ?, ?, ?, ?, 'member', ?, ?)
            ");
            $ustmt->bind_param("sssssss", $serial, $name, $email, $phone, $pw_hash, $status, $klabel);

            if ($ustmt->execute()) {
                $uid = $conn->insert_id;
                $ustmt->close();

                // Link back to khan_members
                $conn->query("UPDATE khan_members SET user_id = $uid WHERE id = " . (int)$m['id']);

                $results[] = [
                    'success' => true,
                    'name'    => $name,
                    'serial'  => $serial,
                    'email'   => $email,
                    'pw'      => $pw_plain,
                    'uid'     => $uid,
                ];
            } else {
                $ustmt->close();
                $results[] = [
                    'success' => false,
                    'name'    => $name,
                    'error'   => $conn->error,
                ];
            }
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $fatal = $e->getMessage();
    }
}

include 'includes/admin_header.php';
?>

<style>
.sync-wrap { max-width: 960px; margin: 0 auto; }
.sync-card {
    background: var(--admin-surface, #fff);
    border: 1px solid var(--admin-border-light, #e5e7eb);
    border-radius: 12px;
    padding: 1.8rem 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.sync-title { font-size: 1.4rem; font-weight: 800; color: var(--admin-text,#111); margin: 0 0 0.4rem 0; }
.sync-sub   { color: var(--admin-text-muted, #6b7280); font-size: 0.9rem; }
.info-banner {
    background: #eff6ff; border-left: 4px solid #3b82f6;
    border-radius: 8px; padding: 1rem 1.2rem;
    font-size: 0.88rem; color: #1e40af; margin-bottom: 1.4rem;
    line-height: 1.6;
}
.warn-banner {
    background: #fef3c7; border-left: 4px solid #f59e0b;
    border-radius: 8px; padding: 1rem 1.2rem;
    font-size: 0.88rem; color: #92400e; margin-bottom: 1.4rem;
}
.sync-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
.sync-table th {
    text-align: left; padding: 0.55rem 0.9rem;
    background: var(--admin-bg, #f9fafb);
    font-size: 0.72rem; text-transform: uppercase;
    letter-spacing: 0.06em; color: var(--admin-text-muted, #6b7280);
    border-bottom: 2px solid var(--admin-border-light, #e5e7eb);
}
.sync-table td {
    padding: 0.6rem 0.9rem;
    border-bottom: 1px solid var(--admin-border-light, #e5e7eb);
    color: var(--admin-text, #111);
    vertical-align: middle;
}
.sync-table tr:last-child td { border-bottom: none; }
.sync-table tr:hover td { background: rgba(0,0,0,0.015); }
.pw-code {
    font-family: monospace; font-size: 0.85rem;
    background: #f3f4f6; border-radius: 5px;
    padding: 2px 8px; color: #374151;
    border: 1px solid #d1d5db;
}
.pill-ok   { background:#dcfce7; color:#166534; padding:2px 9px; border-radius:999px; font-size:0.75rem; font-weight:700; }
.pill-fail { background:#fee2e2; color:#991b1b; padding:2px 9px; border-radius:999px; font-size:0.75rem; font-weight:700; }
.run-btn {
    background: linear-gradient(135deg, #1d4ed8, #3b82f6);
    color: #fff; border: none; padding: 0.85rem 2.2rem;
    border-radius: 8px; font-size: 1rem; font-weight: 700;
    cursor: pointer; transition: opacity 0.2s;
    display: inline-flex; align-items: center; gap: 0.6rem;
}
.run-btn:hover { opacity: 0.9; }
.empty-state { text-align: center; padding: 2.5rem; color: var(--admin-text-muted, #9ca3af); }
.empty-state i { font-size: 2.5rem; margin-bottom: 0.6rem; }
</style>

<div class="sync-wrap">

    <div class="sync-card">
        <h1 class="sync-title"><i class="fas fa-link" style="color:#3b82f6;"></i> Sync Member Accounts</h1>
        <p class="sync-sub">Creates a <em>users</em> table account for every Khan member that doesn't have one yet.</p>
    </div>

    <?php if (isset($fatal)): ?>
        <div class="sync-card" style="border-color:#f87171;">
            <p style="color:#dc2626; font-weight:700;"><i class="fas fa-exclamation-triangle"></i> Fatal error — all changes rolled back.</p>
            <p><?php echo htmlspecialchars($fatal); ?></p>
        </div>

    <?php elseif ($did_run): ?>
        <!-- ── RESULTS ── -->
        <?php
        $ok_count   = count(array_filter($results, fn($r) => $r['success']));
        $fail_count = count($results) - $ok_count;
        ?>
        <div class="sync-card">
            <h2 style="margin:0 0 0.5rem;font-size:1.1rem;"><i class="fas fa-check-circle" style="color:#22c55e;"></i> Sync Complete</h2>
            <p style="margin:0; color:var(--admin-text-muted,#6b7280);">
                <strong style="color:#22c55e;"><?php echo $ok_count; ?> accounts created</strong>
                <?php if ($fail_count): ?> · <strong style="color:#ef4444;"><?php echo $fail_count; ?> failed</strong><?php endif; ?>
            </p>
        </div>

        <?php if ($ok_count > 0): ?>
        <div class="sync-card">
            <div class="warn-banner">
                <i class="fas fa-key"></i> <strong>Save this list!</strong>
                These are the generated default passwords. Members can change them after their first login.
                Once you leave this page, the plaintext passwords are gone.
            </div>
            <div style="overflow-x:auto;">
            <table class="sync-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Serial</th>
                        <th>Login Email</th>
                        <th>Default Password</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php $n = 1; foreach ($results as $r): ?>
                    <tr>
                        <td style="color:var(--admin-text-muted,#6b7280);"><?php echo $n++; ?></td>
                        <td style="font-weight:600;"><?php echo htmlspecialchars($r['name']); ?></td>
                        <?php if ($r['success']): ?>
                            <td><code><?php echo htmlspecialchars($r['serial']); ?></code></td>
                            <td><small><?php echo htmlspecialchars($r['email']); ?></small></td>
                            <td><span class="pw-code"><?php echo htmlspecialchars($r['pw']); ?></span></td>
                            <td><span class="pill-ok"><i class="fas fa-check"></i> Created</span></td>
                        <?php else: ?>
                            <td colspan="3" style="color:#ef4444; font-size:0.82rem;"><?php echo htmlspecialchars($r['error'] ?? ''); ?></td>
                            <td><span class="pill-fail"><i class="fas fa-times"></i> Failed</span></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <div style="margin-top:1.5rem; display:flex; gap:1rem; flex-wrap:wrap;">
                <a href="manage_users_centralized.php" class="btn btn-primary">
                    <i class="fas fa-users-cog"></i> Go to Manage Users
                </a>
                <a href="khan_members.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Khan Members
                </a>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- ── PREVIEW ── -->
        <?php if (empty($pending)): ?>
        <div class="sync-card">
            <div class="empty-state">
                <i class="fas fa-check-circle" style="color:#22c55e;"></i>
                <p style="font-size:1rem; font-weight:700; margin:0.5rem 0 0;">All members already have user accounts!</p>
                <p style="margin:0.4rem 0 1.2rem;">Nothing to sync.</p>
                <a href="manage_users_centralized.php" class="btn btn-primary">Go to Manage Users</a>
            </div>
        </div>
        <?php else: ?>

        <div class="sync-card">
            <div class="info-banner">
                <i class="fas fa-info-circle"></i>
                Found <strong><?php echo count($pending); ?> member(s)</strong> without a user account.
                Review the preview below, then click <strong>Run Sync</strong> to create their accounts.<br><br>
                <strong>Default password format:</strong> <code>oma</code> + first name + last name (lowercase, no spaces).<br>
                Example: <em>Juan Cruz</em> → password is <code>omajuancruz</code><br><br>
                Members with a real email address will keep it. Auto-generated emails (@oma.local) are
                used for members who only had a placeholder email from manual encoding.
            </div>

            <div style="overflow-x:auto; margin-bottom:1.5rem;">
            <table class="sync-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Member Name</th>
                        <th>Khan Level</th>
                        <th>Login Email (will use)</th>
                        <th>Default Password</th>
                        <th>Email Source</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pending as $i => $m): 
                    $isReal = !preg_match('/@oma\.com$|@oma\.local$|@archive\.local$/', $m['new_email'])
                              && filter_var($m['new_email'], FILTER_VALIDATE_EMAIL);
                ?>
                    <tr>
                        <td style="color:var(--admin-text-muted,#6b7280);"><?php echo $i + 1; ?></td>
                        <td style="font-weight:600;"><?php echo htmlspecialchars($m['full_name']); ?></td>
                        <td><strong>Khan <?php echo htmlspecialchars($m['current_khan_level']); ?></strong></td>
                        <td><small><?php echo htmlspecialchars($m['new_email']); ?></small></td>
                        <td><span class="pw-code"><?php echo htmlspecialchars($m['default_pw']); ?></span></td>
                        <td>
                            <?php if ($isReal): ?>
                                <span style="color:#166534;font-size:0.78rem;font-weight:600;"><i class="fas fa-check"></i> Real email</span>
                            <?php else: ?>
                                <span style="color:#92400e;font-size:0.78rem;font-weight:600;"><i class="fas fa-robot"></i> Generated</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <form method="POST" onsubmit="return confirm('Create <?php echo count($pending); ?> user account(s) now? This cannot be undone automatically.');">
                <button type="submit" name="run_sync" class="run-btn">
                    <i class="fas fa-bolt"></i> Run Sync — Create <?php echo count($pending); ?> Account(s)
                </button>
                <a href="manage_users_centralized.php" class="btn btn-outline" style="margin-left:0.75rem;">Cancel</a>
            </form>
        </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php include 'includes/admin_footer.php'; ?>