<?php
$page_title = "Activity Log & Archive";
require_once '../config/database.php';
requireAdmin();
require_once 'includes/activity_helper.php';

$conn = getDbConnection();
$success = '';
$error   = '';

// ── Handle Restore ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_archive'])) {
    $archive_id = (int)$_POST['archive_id'];

    $row = $conn->query("SELECT * FROM archives WHERE id = $archive_id AND is_restored = 0")->fetch_assoc();

    if ($row) {
        $data   = json_decode($row['record_data'], true);
        $module = $row['module'];
        $restored = false;

        // Remove primary key so INSERT gets a new one
        unset($data['id']);

        if ($module === 'khan_members') {
            $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
            $vals = implode(', ', array_map(fn($v) => $v === null ? 'NULL' : "'".$conn->real_escape_string($v)."'", array_values($data)));
            $restored = $conn->query("INSERT INTO khan_members ($cols) VALUES ($vals)");
        } elseif ($module === 'instructors') {
            $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
            $vals = implode(', ', array_map(fn($v) => $v === null ? 'NULL' : "'".$conn->real_escape_string($v)."'", array_values($data)));
            $restored = $conn->query("INSERT INTO instructors ($cols) VALUES ($vals)");
        } elseif ($module === 'affiliates') {
            $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
            $vals = implode(', ', array_map(fn($v) => $v === null ? 'NULL' : "'".$conn->real_escape_string($v)."'", array_values($data)));
            $restored = $conn->query("INSERT INTO affiliates ($cols) VALUES ($vals)");
        } elseif ($module === 'users') {
            $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
            $vals = implode(', ', array_map(fn($v) => $v === null ? 'NULL' : "'".$conn->real_escape_string($v)."'", array_values($data)));
            $restored = $conn->query("INSERT INTO users ($cols) VALUES ($vals)");
        } elseif ($module === 'contact_messages') {
            $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
            $vals = implode(', ', array_map(fn($v) => $v === null ? 'NULL' : "'".$conn->real_escape_string($v)."'", array_values($data)));
            $restored = $conn->query("INSERT INTO contact_messages ($cols) VALUES ($vals)");
        } elseif ($module === 'course_materials') {
            $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
            $vals = implode(', ', array_map(fn($v) => $v === null ? 'NULL' : "'".$conn->real_escape_string($v)."'", array_values($data)));
            $restored = $conn->query("INSERT INTO course_materials ($cols) VALUES ($vals)");
        } elseif ($module === 'event_gallery') {
            $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
            $vals = implode(', ', array_map(fn($v) => $v === null ? 'NULL' : "'".$conn->real_escape_string($v)."'", array_values($data)));
            $restored = $conn->query("INSERT INTO event_gallery ($cols) VALUES ($vals)");
        }

        if ($restored) {
            $admin_name = $_SESSION['user_name'] ?? 'Unknown';
            $conn->query("UPDATE archives SET is_restored=1, restored_at=NOW(), restored_by_name='".$conn->real_escape_string($admin_name)."' WHERE id=$archive_id");
            logActivity($conn, 'restore', $module, $row['original_id'], $row['record_label'], 'Restored from archive');
            $success = "Record \"" . htmlspecialchars($row['record_label']) . "\" has been restored successfully.";
        } else {
            $error = "Restore failed: " . $conn->error;
        }
    } else {
        $error = "Archive record not found or already restored.";
    }
}

// ── Filters ───────────────────────────────────────────────────────────────────
$tab      = $_GET['tab']    ?? 'log';       // log | archive
$module_f = $_GET['module'] ?? '';
$action_f = $_GET['action'] ?? '';
$search   = trim($_GET['search'] ?? '');
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 25;
$offset   = ($page_num - 1) * $per_page;

// ── Activity Log ──────────────────────────────────────────────────────────────
$log_where = "WHERE 1=1";
if ($module_f) $log_where .= " AND module = '" . $conn->real_escape_string($module_f) . "'";
if ($action_f) $log_where .= " AND action = '" . $conn->real_escape_string($action_f) . "'";
if ($search)   $log_where .= " AND (admin_name LIKE '%" . $conn->real_escape_string($search) . "%' OR record_label LIKE '%" . $conn->real_escape_string($search) . "%' OR details LIKE '%" . $conn->real_escape_string($search) . "%')";

$log_total = $conn->query("SELECT COUNT(*) as c FROM activity_log $log_where")->fetch_assoc()['c'];
$log_pages = ceil($log_total / $per_page);
$logs      = $conn->query("SELECT * FROM activity_log $log_where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");

// ── Archive ───────────────────────────────────────────────────────────────────
$arc_where = "WHERE 1=1";
if ($module_f) $arc_where .= " AND module = '" . $conn->real_escape_string($module_f) . "'";
if ($search)   $arc_where .= " AND (deleted_by_name LIKE '%" . $conn->real_escape_string($search) . "%' OR record_label LIKE '%" . $conn->real_escape_string($search) . "%')";

$arc_filter_restored = $_GET['restored'] ?? 'no';
if ($arc_filter_restored === 'yes') {
    $arc_where .= " AND is_restored = 1";
} else {
    $arc_where .= " AND is_restored = 0";
}

$arc_total = $conn->query("SELECT COUNT(*) as c FROM archives $arc_where")->fetch_assoc()['c'];
$arc_pages = ceil($arc_total / $per_page);
$archives  = $conn->query("SELECT * FROM archives $arc_where ORDER BY deleted_at DESC LIMIT $per_page OFFSET $offset");

// Stats
$stats = [
    'total_logs'    => $conn->query("SELECT COUNT(*) as c FROM activity_log")->fetch_assoc()['c'],
    'total_deleted' => $conn->query("SELECT COUNT(*) as c FROM archives WHERE is_restored=0")->fetch_assoc()['c'],
    'total_restored'=> $conn->query("SELECT COUNT(*) as c FROM archives WHERE is_restored=1")->fetch_assoc()['c'],
    'today_actions' => $conn->query("SELECT COUNT(*) as c FROM activity_log WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'],
];

include 'includes/admin_header.php';

$modules = ['khan_members','instructors','affiliates','users','contact_messages','course_materials','event_gallery'];
$module_labels = [
    'khan_members'    => 'Khan Members',
    'instructors'     => 'Instructors',
    'affiliates'      => 'Affiliates',
    'users'           => 'Users',
    'contact_messages'=> 'Messages',
    'course_materials'=> 'Courses',
    'event_gallery'   => 'Events',
];
$action_colors = [
    'create'  => '#22c55e',
    'edit'    => '#3b82f6',
    'delete'  => '#ef4444',
    'restore' => '#a855f7',
    'login'   => '#f59e0b',
    'logout'  => '#94a3b8',
    'view'    => '#64748b',
    'archive' => '#f97316',
];
$action_icons = [
    'create'  => 'fa-plus-circle',
    'edit'    => 'fa-pen',
    'delete'  => 'fa-trash',
    'restore' => 'fa-undo',
    'login'   => 'fa-sign-in-alt',
    'logout'  => 'fa-sign-out-alt',
    'view'    => 'fa-eye',
    'archive' => 'fa-archive',
];

function buildQuery($overrides = []) {
    $params = array_merge($_GET, $overrides);
    unset($params['p']);
    return '?' . http_build_query(array_filter($params, fn($v) => $v !== ''));
}
?>

<style>
/* ── Tab Bar ── */
.log-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid var(--admin-border);
    margin-bottom: 1.5rem;
}
.log-tab {
    padding: 0.75rem 1.6rem;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--admin-text-muted);
    text-decoration: none;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: all 0.18s;
    display: flex;
    align-items: center;
    gap: 7px;
}
.log-tab:hover { color: var(--admin-text); }
.log-tab.active {
    color: var(--admin-primary);
    border-bottom-color: var(--admin-primary);
}
.tab-badge {
    background: var(--admin-primary);
    color: #fff;
    font-size: 0.68rem;
    padding: 1px 7px;
    border-radius: 999px;
    font-weight: 700;
}
.tab-badge.orange { background: #f97316; }

/* ── Stat Row ── */
.log-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px,1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.log-stat {
    background: var(--admin-surface);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--radius-lg);
    padding: 1.1rem 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.9rem;
    box-shadow: var(--shadow-sm);
}
.log-stat-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.log-stat-val { font-size: 1.55rem; font-weight: 800; color: var(--admin-text); line-height: 1; }
.log-stat-label { font-size: 0.72rem; color: var(--admin-text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; }

/* ── Filter Bar ── */
.filter-bar {
    background: var(--admin-surface);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--radius-lg);
    padding: 1rem 1.2rem;
    display: flex;
    gap: 0.8rem;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 1.2rem;
    box-shadow: var(--shadow-sm);
}
.filter-bar input,
.filter-bar select {
    padding: 0.5rem 0.8rem;
    border: 1px solid var(--admin-border);
    border-radius: var(--radius-md);
    font-size: 0.85rem;
    color: var(--admin-text);
    background: var(--admin-bg);
    min-width: 130px;
}
.filter-bar input { min-width: 200px; }
.filter-bar button {
    padding: 0.5rem 1.1rem;
    background: var(--admin-primary);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}
.filter-bar .reset-btn {
    background: transparent;
    color: var(--admin-text-muted);
    border: 1px solid var(--admin-border);
}

/* ── Table Panel ── */
.log-panel {
    background: var(--admin-surface);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.2rem;
}
.log-panel table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
.log-panel thead tr { background: var(--admin-bg); border-bottom: 2px solid var(--admin-border-light); }
.log-panel th {
    padding: 0.65rem 1rem;
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--admin-text-muted);
    font-weight: 700;
    white-space: nowrap;
}
.log-panel td {
    padding: 0.6rem 1rem;
    border-bottom: 1px solid var(--admin-border-light);
    color: var(--admin-text);
    vertical-align: middle;
}
.log-panel tr:last-child td { border-bottom: none; }
.log-panel tr:hover td { background: rgba(0,0,0,0.015); }

/* ── Action Badge ── */
.action-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.71rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

/* ── Module Badge ── */
.module-pill {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 999px;
    font-size: 0.71rem;
    font-weight: 700;
    background: rgba(59,130,246,0.1);
    color: #3b82f6;
    white-space: nowrap;
}

/* ── Archive record pill ── */
.arc-restored { background: rgba(34,197,94,0.1); color: #22c55e; }
.arc-deleted  { background: rgba(239,68,68,0.1);  color: #ef4444; }

/* ── Admin name chip ── */
.admin-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    color: var(--admin-text);
}
.admin-avatar-sm {
    width: 26px; height: 26px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
    color: #fff;
    font-size: 0.68rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

/* ── Pagination ── */
.pagination {
    display: flex;
    gap: 0.4rem;
    align-items: center;
    flex-wrap: wrap;
    padding: 1rem 1.2rem;
    border-top: 1px solid var(--admin-border-light);
}
.pg-btn {
    padding: 0.35rem 0.75rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--admin-border);
    background: var(--admin-surface);
    color: var(--admin-text);
    font-size: 0.82rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s;
}
.pg-btn:hover { background: var(--admin-bg); }
.pg-btn.active { background: var(--admin-primary); color: #fff; border-color: var(--admin-primary); }
.pg-info { font-size: 0.8rem; color: var(--admin-text-muted); margin-left: auto; }

/* ── Details expand ── */
.details-cell { max-width: 220px; }
.details-text { 
    color: var(--admin-text-muted); 
    font-size: 0.8rem; 
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 220px;
    display: block;
    cursor: pointer;
}
.details-text:hover { white-space: normal; }

/* ── Empty state ── */
.empty-state {
    padding: 3rem;
    text-align: center;
    color: var(--admin-text-muted);
}
.empty-state i { font-size: 2.5rem; margin-bottom: 0.8rem; opacity: 0.3; }
.empty-state p { font-size: 0.9rem; }
</style>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="log-stats">
    <div class="log-stat">
        <div class="log-stat-icon" style="background:rgba(59,130,246,0.1);">
            <i class="fas fa-list" style="color:#3b82f6;"></i>
        </div>
        <div>
            <div class="log-stat-val"><?php echo number_format($stats['total_logs']); ?></div>
            <div class="log-stat-label">Total Actions</div>
        </div>
    </div>
    <div class="log-stat">
        <div class="log-stat-icon" style="background:rgba(245,158,11,0.1);">
            <i class="fas fa-bolt" style="color:#f59e0b;"></i>
        </div>
        <div>
            <div class="log-stat-val"><?php echo number_format($stats['today_actions']); ?></div>
            <div class="log-stat-label">Today</div>
        </div>
    </div>
    <div class="log-stat">
        <div class="log-stat-icon" style="background:rgba(239,68,68,0.1);">
            <i class="fas fa-archive" style="color:#ef4444;"></i>
        </div>
        <div>
            <div class="log-stat-val"><?php echo number_format($stats['total_deleted']); ?></div>
            <div class="log-stat-label">Archived</div>
        </div>
    </div>
    <div class="log-stat">
        <div class="log-stat-icon" style="background:rgba(168,85,247,0.1);">
            <i class="fas fa-undo" style="color:#a855f7;"></i>
        </div>
        <div>
            <div class="log-stat-val"><?php echo number_format($stats['total_restored']); ?></div>
            <div class="log-stat-label">Restored</div>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="log-tabs">
    <a href="<?php echo buildQuery(['tab'=>'log','p'=>1]); ?>" class="log-tab <?php echo $tab==='log'?'active':''; ?>">
        <i class="fas fa-history"></i> Activity Log
        <span class="tab-badge"><?php echo number_format($stats['total_logs']); ?></span>
    </a>
    <a href="<?php echo buildQuery(['tab'=>'archive','p'=>1,'restored'=>'no']); ?>" class="log-tab <?php echo $tab==='archive'?'active':''; ?>">
        <i class="fas fa-archive"></i> Archive
        <?php if ($stats['total_deleted'] > 0): ?>
        <span class="tab-badge orange"><?php echo number_format($stats['total_deleted']); ?></span>
        <?php endif; ?>
    </a>
</div>

<?php if ($tab === 'log'): ?>
<!-- ═══════════════════════════════════════════════════════════════
     ACTIVITY LOG TAB
════════════════════════════════════════════════════════════════ -->

<form method="GET" class="filter-bar">
    <input type="hidden" name="tab" value="log">
    <input type="text" name="search" placeholder="🔍  Search admin, record, details…" value="<?php echo htmlspecialchars($search); ?>">
    <select name="module">
        <option value="">All Modules</option>
        <?php foreach ($module_labels as $k => $v): ?>
            <option value="<?php echo $k; ?>" <?php echo $module_f===$k?'selected':''; ?>><?php echo $v; ?></option>
        <?php endforeach; ?>
    </select>
    <select name="action">
        <option value="">All Actions</option>
        <?php foreach (array_keys($action_colors) as $a): ?>
            <option value="<?php echo $a; ?>" <?php echo $action_f===$a?'selected':''; ?>><?php echo ucfirst($a); ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit"><i class="fas fa-filter"></i> Filter</button>
    <a href="?tab=log" class="filter-bar reset-btn" style="text-decoration:none;padding:0.5rem 1rem;border-radius:var(--radius-md);font-size:0.85rem;">Reset</a>
</form>

<div class="log-panel">
    <?php if ($log_total === 0): ?>
        <div class="empty-state">
            <i class="fas fa-history"></i>
            <p>No activity logged yet. Actions taken by admins will appear here.</p>
        </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>When</th>
                <th>Admin</th>
                <th>Action</th>
                <th>Module</th>
                <th>Record</th>
                <th>Details</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($log = $logs->fetch_assoc()):
            $color = $action_colors[$log['action']] ?? '#64748b';
            $icon  = $action_icons[$log['action']]  ?? 'fa-circle';
        ?>
            <tr>
                <td style="white-space:nowrap;color:var(--admin-text-muted);font-size:0.8rem;">
                    <?php echo date('M d, Y', strtotime($log['created_at'])); ?><br>
                    <span style="font-size:0.72rem;"><?php echo date('h:i A', strtotime($log['created_at'])); ?></span>
                </td>
                <td>
                    <div class="admin-chip">
                        <div class="admin-avatar-sm"><?php echo strtoupper(substr($log['admin_name'],0,1)); ?></div>
                        <?php echo htmlspecialchars($log['admin_name']); ?>
                    </div>
                </td>
                <td>
                    <span class="action-pill" style="background:<?php echo $color; ?>1a;color:<?php echo $color; ?>;">
                        <i class="fas <?php echo $icon; ?>"></i>
                        <?php echo ucfirst($log['action']); ?>
                    </span>
                </td>
                <td><span class="module-pill"><?php echo $module_labels[$log['module']] ?? htmlspecialchars($log['module']); ?></span></td>
                <td style="font-weight:500;"><?php echo htmlspecialchars($log['record_label'] ?? '—'); ?></td>
                <td class="details-cell">
                    <span class="details-text" title="<?php echo htmlspecialchars($log['details'] ?? ''); ?>">
                        <?php echo htmlspecialchars($log['details'] ?? '—'); ?>
                    </span>
                </td>
                <td style="color:var(--admin-text-muted);font-size:0.78rem;font-family:monospace;"><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($log_pages > 1): ?>
    <div class="pagination">
        <?php if ($page_num > 1): ?>
            <a class="pg-btn" href="<?php echo buildQuery(['tab'=>'log','p'=>$page_num-1]); ?>"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($i = max(1,$page_num-2); $i <= min($log_pages,$page_num+2); $i++): ?>
            <a class="pg-btn <?php echo $i===$page_num?'active':''; ?>" href="<?php echo buildQuery(['tab'=>'log','p'=>$i]); ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($page_num < $log_pages): ?>
            <a class="pg-btn" href="<?php echo buildQuery(['tab'=>'log','p'=>$page_num+1]); ?>"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
        <span class="pg-info">Showing <?php echo number_format(($offset+1)); ?>–<?php echo number_format(min($offset+$per_page,$log_total)); ?> of <?php echo number_format($log_total); ?></span>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- ═══════════════════════════════════════════════════════════════
     ARCHIVE TAB
════════════════════════════════════════════════════════════════ -->

<!-- Toggle restored / deleted -->
<div style="display:flex;gap:0.6rem;margin-bottom:1rem;flex-wrap:wrap;align-items:center;">
    <a href="<?php echo buildQuery(['tab'=>'archive','restored'=>'no','p'=>1]); ?>"
       class="btn <?php echo $arc_filter_restored==='no'?'btn-primary':'btn-outline'; ?> btn-sm">
        <i class="fas fa-trash"></i> Deleted Records
    </a>
    <a href="<?php echo buildQuery(['tab'=>'archive','restored'=>'yes','p'=>1]); ?>"
       class="btn <?php echo $arc_filter_restored==='yes'?'btn-success':'btn-outline'; ?> btn-sm">
        <i class="fas fa-undo"></i> Restored Records
    </a>
</div>

<form method="GET" class="filter-bar">
    <input type="hidden" name="tab" value="archive">
    <input type="hidden" name="restored" value="<?php echo htmlspecialchars($arc_filter_restored); ?>">
    <input type="text" name="search" placeholder="🔍  Search name, admin…" value="<?php echo htmlspecialchars($search); ?>">
    <select name="module">
        <option value="">All Modules</option>
        <?php foreach ($module_labels as $k => $v): ?>
            <option value="<?php echo $k; ?>" <?php echo $module_f===$k?'selected':''; ?>><?php echo $v; ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit"><i class="fas fa-filter"></i> Filter</button>
    <a href="?tab=archive&restored=<?php echo $arc_filter_restored; ?>" class="filter-bar reset-btn" style="text-decoration:none;padding:0.5rem 1rem;border-radius:var(--radius-md);font-size:0.85rem;">Reset</a>
</form>

<div class="log-panel">
    <?php if ($arc_total === 0): ?>
        <div class="empty-state">
            <i class="fas fa-archive"></i>
            <p><?php echo $arc_filter_restored==='yes' ? 'No restored records yet.' : 'No archived records yet. Deleted items will appear here.'; ?></p>
        </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Deleted At</th>
                <th>Deleted By</th>
                <th>Module</th>
                <th>Record</th>
                <th>Status</th>
                <?php if ($arc_filter_restored==='yes'): ?>
                    <th>Restored By</th>
                    <th>Restored At</th>
                <?php else: ?>
                    <th>Preview</th>
                    <th>Restore</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php while ($arc = $archives->fetch_assoc()):
            $data = json_decode($arc['record_data'], true);
        ?>
            <tr>
                <td style="white-space:nowrap;color:var(--admin-text-muted);font-size:0.8rem;">
                    <?php echo date('M d, Y', strtotime($arc['deleted_at'])); ?><br>
                    <span style="font-size:0.72rem;"><?php echo date('h:i A', strtotime($arc['deleted_at'])); ?></span>
                </td>
                <td>
                    <div class="admin-chip">
                        <div class="admin-avatar-sm" style="background:linear-gradient(135deg,#ef4444,#f97316);"><?php echo strtoupper(substr($arc['deleted_by_name'],0,1)); ?></div>
                        <?php echo htmlspecialchars($arc['deleted_by_name']); ?>
                    </div>
                </td>
                <td><span class="module-pill"><?php echo $module_labels[$arc['module']] ?? htmlspecialchars($arc['module']); ?></span></td>
                <td style="font-weight:600;"><?php echo htmlspecialchars($arc['record_label']); ?></td>
                <td>
                    <?php if ($arc['is_restored']): ?>
                        <span class="action-pill arc-restored"><i class="fas fa-check-circle"></i> Restored</span>
                    <?php else: ?>
                        <span class="action-pill arc-deleted"><i class="fas fa-trash"></i> Deleted</span>
                    <?php endif; ?>
                </td>

                <?php if ($arc_filter_restored==='yes'): ?>
                    <td>
                        <div class="admin-chip">
                            <div class="admin-avatar-sm" style="background:linear-gradient(135deg,#22c55e,#14b8a6);"><?php echo strtoupper(substr($arc['restored_by_name']??'?',0,1)); ?></div>
                            <?php echo htmlspecialchars($arc['restored_by_name'] ?? '—'); ?>
                        </div>
                    </td>
                    <td style="color:var(--admin-text-muted);font-size:0.8rem;white-space:nowrap;">
                        <?php echo $arc['restored_at'] ? date('M d, Y h:i A', strtotime($arc['restored_at'])) : '—'; ?>
                    </td>
                <?php else: ?>
                    <td>
                        <button class="btn btn-sm btn-outline" style="font-size:0.75rem;" onclick='showPreview(<?php echo json_encode($data); ?>, <?php echo json_encode($arc['module']); ?>)'>
                            <i class="fas fa-eye"></i> Preview
                        </button>
                    </td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Restore \"<?php echo addslashes($arc['record_label']); ?>\" back to the system?');">
                            <input type="hidden" name="archive_id" value="<?php echo $arc['id']; ?>">
                            <button type="submit" name="restore_archive" class="btn btn-sm btn-success" style="font-size:0.75rem;">
                                <i class="fas fa-undo"></i> Restore
                            </button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($arc_pages > 1): ?>
    <div class="pagination">
        <?php if ($page_num > 1): ?>
            <a class="pg-btn" href="<?php echo buildQuery(['tab'=>'archive','p'=>$page_num-1]); ?>"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($i = max(1,$page_num-2); $i <= min($arc_pages,$page_num+2); $i++): ?>
            <a class="pg-btn <?php echo $i===$page_num?'active':''; ?>" href="<?php echo buildQuery(['tab'=>'archive','p'=>$i]); ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($page_num < $arc_pages): ?>
            <a class="pg-btn" href="<?php echo buildQuery(['tab'=>'archive','p'=>$page_num+1]); ?>"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
        <span class="pg-info">Showing <?php echo number_format(($offset+1)); ?>–<?php echo number_format(min($offset+$per_page,$arc_total)); ?> of <?php echo number_format($arc_total); ?></span>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Preview Modal -->
<div id="previewModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:600px;">
        <span class="modal-close" onclick="document.getElementById('previewModal').style.display='none'">&times;</span>
        <h2 style="margin-top:0;font-size:1.2rem;"><i class="fas fa-eye" style="color:var(--admin-info);"></i> Archived Record Preview</h2>
        <div id="previewBody"></div>
    </div>
</div>

<script>
function showPreview(data, module) {
    const labels = {
        full_name:'Full Name', name:'Name', email:'Email', phone:'Phone',
        current_khan_level:'Khan Level', khan_color:'Khan Color',
        date_joined:'Date Joined', date_promoted:'Date Promoted',
        training_location:'Training Location', status:'Status',
        title:'Title', location:'Location', specialization:'Specialization',
        bio:'Bio', website_url:'Website', contact_email:'Contact Email',
        subject:'Subject', message:'Message', role:'Role',
        created_at:'Created', updated_at:'Updated'
    };
    const skip = ['id','user_id','instructor_id','photo_path','logo_path','file_path','thumbnail_path','password','admin_notes'];

    let html = '<div style="background:var(--admin-bg);border-radius:var(--radius-lg);padding:1.2rem;font-size:0.86rem;">';
    html += '<table style="width:100%;border-collapse:collapse;">';
    for (const [k, v] of Object.entries(data)) {
        if (skip.includes(k) || v === null || v === '') continue;
        const label = labels[k] || k.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase());
        html += `<tr style="border-bottom:1px solid var(--admin-border-light);">
            <td style="padding:0.5rem 0.7rem;color:var(--admin-text-muted);font-weight:600;white-space:nowrap;width:40%;">${label}</td>
            <td style="padding:0.5rem 0.7rem;color:var(--admin-text);">${String(v).replace(/</g,'&lt;')}</td>
        </tr>`;
    }
    html += '</table></div>';
    document.getElementById('previewBody').innerHTML = html;
    document.getElementById('previewModal').style.display = 'block';
}

window.onclick = e => {
    if (e.target === document.getElementById('previewModal'))
        document.getElementById('previewModal').style.display = 'none';
};
</script>

<?php include 'includes/admin_footer.php'; ?>