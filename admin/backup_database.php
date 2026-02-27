<?php
if (!defined('DB_HOST')) {
    require_once '../config/database.php';
}
requireAdmin();

$page_title = 'Backup Database';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle backup download request
if (isset($_POST['action']) && $_POST['action'] === 'download_backup') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, 'oma_database');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');

    $filename = 'oma_database_backup_' . date('Y-m-d_H-i-s') . '.sql';

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "-- ============================================================\n";
    echo "-- Oriental Muay Boran Academy - Database Backup\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Database: oma_database\n";
    echo "-- ============================================================\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n";
    echo "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
    echo "SET NAMES utf8mb4;\n";
    echo "CREATE DATABASE IF NOT EXISTS `oma_database` DEFAULT CHARACTER SET utf8mb4;\n";
    echo "USE `oma_database`;\n\n";

    // Get all tables
    $tables_result = $conn->query("SHOW TABLES");
    if (!$tables_result) {
        echo "-- ERROR: Could not retrieve tables: " . $conn->error . "\n";
        exit;
    }

    $tables = [];
    while ($row = $tables_result->fetch_array()) {
        $tables[] = $row[0];
    }

    foreach ($tables as $table) {
        echo "-- ------------------------------------------------------------\n";
        echo "-- Table: `$table`\n";
        echo "-- ------------------------------------------------------------\n\n";
        echo "DROP TABLE IF EXISTS `$table`;\n";

        $create_result = $conn->query("SHOW CREATE TABLE `$table`");
        if ($create_result) {
            $create_row = $create_result->fetch_assoc();
            echo $create_row['Create Table'] . ";\n\n";
        }

        $data_result = $conn->query("SELECT * FROM `$table`");
        if ($data_result && $data_result->num_rows > 0) {
            echo "-- Data for table `$table`\n";
            while ($data_row = $data_result->fetch_array(MYSQLI_NUM)) {
                $values = [];
                foreach ($data_row as $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . $conn->real_escape_string($value) . "'";
                    }
                }
                echo "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
            }
            echo "\n";
        } else {
            echo "-- (no data)\n\n";
        }
    }

    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    echo "\n-- ============================================================\n";
    echo "-- Backup complete: " . count($tables) . " tables\n";
    echo "-- ============================================================\n";

    $conn->close();
    exit;
}

// --- Page Display ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, 'oma_database');
$table_data = [];
$db_size = '0.00';
$connect_error = '';

if ($conn->connect_error) {
    $connect_error = $conn->connect_error;
} else {
    $conn->set_charset('utf8mb4');

    $tables_result = $conn->query("SHOW TABLES");
    if ($tables_result) {
        while ($row = $tables_result->fetch_array()) {
            $table_name = $row[0];
            $count_result = $conn->query("SELECT COUNT(*) as cnt FROM `$table_name`");
            $count = $count_result ? $count_result->fetch_assoc()['cnt'] : 0;
            $table_data[] = ['name' => $table_name, 'rows' => $count];
        }
    }

    $size_result = $conn->query("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
        FROM information_schema.tables
        WHERE table_schema = 'oma_database'
    ");
    if ($size_result) {
        $db_size = $size_result->fetch_assoc()['size_mb'] ?? '0.00';
    }

    $conn->close();
}

include 'includes/admin_header.php';
?>

<style>
    .backup-stat-card {
        background: var(--admin-card);
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: var(--admin-shadow);
    }
    .backup-stat-icon {
        border-radius: 50%;
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.3rem;
    }
    .backup-stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--admin-text);
        line-height: 1.1;
    }
    .backup-stat-label {
        color: var(--admin-text-muted);
        font-size: 0.82rem;
        margin-top: 2px;
    }
    .backup-card {
        background: var(--admin-card);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: var(--admin-shadow);
        margin-bottom: 1.5rem;
    }
    .backup-card h3 {
        margin: 0 0 0.5rem 0;
        color: var(--admin-text);
        font-size: 1.1rem;
    }
    .backup-card p {
        color: var(--admin-text-muted);
        font-size: 0.88rem;
        margin-bottom: 1.5rem;
    }
    .backup-btn {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        transition: opacity 0.2s, transform 0.1s;
    }
    .backup-btn:hover { opacity: 0.9; transform: translateY(-1px); }
    .backup-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .backup-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
    .backup-table th {
        text-align: left;
        padding: 0.6rem 1rem;
        color: var(--admin-text-muted);
        font-weight: 600;
        border-bottom: 2px solid var(--admin-border);
    }
    .backup-table td {
        padding: 0.65rem 1rem;
        border-bottom: 1px solid var(--admin-border);
        color: var(--admin-text);
    }
    .badge-blue {
        background: rgba(59,130,246,0.12);
        color: #3b82f6;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .error-box {
        background: rgba(239,68,68,0.1);
        border: 1px solid rgba(239,68,68,0.3);
        color: #ef4444;
        border-radius: 8px;
        padding: 1rem 1.2rem;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }
</style>

<?php if ($connect_error): ?>
    <div class="error-box">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Database connection failed:</strong> <?php echo htmlspecialchars($connect_error); ?>
        <br><small>Check that DB_HOST, DB_USER, DB_PASS are defined correctly in your config.</small>
    </div>
<?php else: ?>

<!-- Stats Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.2rem; margin-bottom: 1.5rem;">
    <div class="backup-stat-card">
        <div class="backup-stat-icon" style="background: rgba(59,130,246,0.12);">
            <i class="fas fa-table" style="color: #3b82f6;"></i>
        </div>
        <div>
            <div class="backup-stat-value"><?php echo count($table_data); ?></div>
            <div class="backup-stat-label">Tables</div>
        </div>
    </div>
    <div class="backup-stat-card">
        <div class="backup-stat-icon" style="background: rgba(16,185,129,0.12);">
            <i class="fas fa-hdd" style="color: #10b981;"></i>
        </div>
        <div>
            <div class="backup-stat-value"><?php echo ($db_size && $db_size > 0) ? $db_size : '< 1'; ?> MB</div>
            <div class="backup-stat-label">Database Size</div>
        </div>
    </div>
    <div class="backup-stat-card">
        <div class="backup-stat-icon" style="background: rgba(245,158,11,0.12);">
            <i class="fas fa-server" style="color: #f59e0b;"></i>
        </div>
        <div>
            <div class="backup-stat-value" style="font-size: 1rem;">oma_database</div>
            <div class="backup-stat-label">Database Name</div>
        </div>
    </div>
</div>

<!-- Download Card -->
<div class="backup-card">
    <h3><i class="fas fa-download" style="color: #3b82f6; margin-right: 6px;"></i> Download SQL Backup</h3>
    <p>
        Generates a full <code>.sql</code> dump of <strong>oma_database</strong> — all tables, structure, and data.
        You can restore this file directly in phpMyAdmin under the <em>Import</em> tab.
    </p>
    <form method="POST" action="backup_database.php" id="backupForm">
        <input type="hidden" name="action" value="download_backup">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <button type="submit" class="backup-btn" id="backupBtn">
            <i class="fas fa-download" id="backupIcon"></i>
            <span id="backupText">Download Backup</span>
        </button>
    </form>
</div>

<!-- Tables Overview with Pagination -->
<div class="backup-card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 1.2rem; flex-wrap: wrap; gap: 0.5rem;">
        <h3 style="margin:0;"><i class="fas fa-list" style="color: #10b981; margin-right: 6px;"></i> Tables in Backup</h3>
        <div style="display:flex; align-items:center; gap: 0.6rem;">
            <input type="text" id="tableSearch" placeholder="Search tables..." 
                style="padding: 0.4rem 0.8rem; border: 1px solid var(--admin-border); border-radius: 6px; background: var(--admin-card); color: var(--admin-text); font-size: 0.83rem; width: 180px;">
            <span id="tableCount" style="color: var(--admin-text-muted); font-size: 0.82rem;"></span>
        </div>
    </div>
    <?php if (empty($table_data)): ?>
        <p style="color: var(--admin-text-muted);">No tables found in oma_database.</p>
    <?php else: ?>
    <div style="overflow-x: auto;">
        <table class="backup-table" id="tablesTable">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Table Name</th>
                    <th style="text-align:right;">Rows</th>
                </tr>
            </thead>
            <tbody id="tablesBody">
                <?php foreach ($table_data as $i => $t): ?>
                <tr data-name="<?php echo htmlspecialchars(strtolower($t['name'])); ?>">
                    <td style="color: var(--admin-text-muted);"><?php echo $i + 1; ?></td>
                    <td><code><?php echo htmlspecialchars($t['name']); ?></code></td>
                    <td style="text-align:right;"><span class="badge-blue"><?php echo number_format($t['rows']); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <div id="tablePagination" style="display:flex; align-items:center; justify-content:space-between; margin-top: 1rem; flex-wrap:wrap; gap:0.5rem;">
        <span id="tablePageInfo" style="color: var(--admin-text-muted); font-size: 0.83rem;"></span>
        <div style="display:flex; gap:0.4rem;" id="tablePageBtns"></div>
    </div>
    <?php endif; ?>
</div>

<script>
(function() {
    const ROWS_PER_PAGE = 10;
    const tbody = document.getElementById('tablesBody');
    const pageInfo = document.getElementById('tablePageInfo');
    const pageBtns = document.getElementById('tablePageBtns');
    const searchInput = document.getElementById('tableSearch');
    const tableCount = document.getElementById('tableCount');

    let allRows = Array.from(tbody.querySelectorAll('tr'));
    let filteredRows = [...allRows];
    let currentPage = 1;

    function renderPage() {
        const total = filteredRows.length;
        const totalPages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * ROWS_PER_PAGE;
        const end = start + ROWS_PER_PAGE;

        allRows.forEach(r => r.style.display = 'none');
        filteredRows.slice(start, end).forEach(r => r.style.display = '');

        pageInfo.textContent = total === 0 ? 'No results' :
            `Showing ${start + 1}–${Math.min(end, total)} of ${total} tables`;
        tableCount.textContent = `(${total} total)`;

        // Build page buttons
        pageBtns.innerHTML = '';
        if (totalPages <= 1) return;

        const btnStyle = (active) => `
            padding: 0.3rem 0.7rem; border-radius: 5px; border: 1px solid var(--admin-border);
            background: ${active ? '#3b82f6' : 'var(--admin-card)'}; 
            color: ${active ? '#fff' : 'var(--admin-text)'}; cursor: pointer; font-size: 0.82rem;
            font-weight: ${active ? '600' : '400'};
        `;

        // Prev
        const prev = document.createElement('button');
        prev.innerHTML = '&laquo;';
        prev.style.cssText = btnStyle(false);
        prev.disabled = currentPage === 1;
        prev.onclick = () => { currentPage--; renderPage(); };
        pageBtns.appendChild(prev);

        // Page numbers (show max 5 around current)
        let startP = Math.max(1, currentPage - 2);
        let endP = Math.min(totalPages, startP + 4);
        if (endP - startP < 4) startP = Math.max(1, endP - 4);

        for (let p = startP; p <= endP; p++) {
            const btn = document.createElement('button');
            btn.textContent = p;
            btn.style.cssText = btnStyle(p === currentPage);
            btn.onclick = ((pg) => () => { currentPage = pg; renderPage(); })(p);
            pageBtns.appendChild(btn);
        }

        // Next
        const next = document.createElement('button');
        next.innerHTML = '&raquo;';
        next.style.cssText = btnStyle(false);
        next.disabled = currentPage === totalPages;
        next.onclick = () => { currentPage++; renderPage(); };
        pageBtns.appendChild(next);
    }

    searchInput && searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        filteredRows = allRows.filter(r => r.dataset.name.includes(q));
        currentPage = 1;
        renderPage();
    });

    renderPage();
})();
</script>

<?php endif; ?>

<script>
document.getElementById('backupForm') && document.getElementById('backupForm').addEventListener('submit', function () {
    const btn = document.getElementById('backupBtn');
    const icon = document.getElementById('backupIcon');
    const text = document.getElementById('backupText');
    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';
    text.textContent = 'Preparing backup...';
    setTimeout(() => {
        btn.disabled = false;
        icon.className = 'fas fa-download';
        text.textContent = 'Download Backup';
    }, 5000);
});
</script>

<?php include 'includes/admin_footer.php'; ?>