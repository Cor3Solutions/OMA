<?php
$page_title = "Admin Dashboard";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();

// 1. Total Counts
$total_users       = $conn->query("SELECT COUNT(*) as t FROM users WHERE role != 'admin'")->fetch_assoc()['t'];
$total_members     = $conn->query("SELECT COUNT(*) as t FROM khan_members WHERE status = 'active'")->fetch_assoc()['t'];
$total_instructors = $conn->query("SELECT COUNT(*) as t FROM instructors WHERE status = 'active'")->fetch_assoc()['t'];
$unread_messages   = $conn->query("SELECT COUNT(*) as t FROM contact_messages WHERE status = 'new'")->fetch_assoc()['t'];
$total_affiliates  = $conn->query("SELECT COUNT(*) as t FROM affiliates WHERE status = 'active'")->fetch_assoc()['t'];
$total_materials   = $conn->query("SELECT COUNT(*) as t FROM course_materials WHERE status = 'published'")->fetch_assoc()['t'];
$total_events      = $conn->query("SELECT COUNT(*) as t FROM event_gallery WHERE status = 'active'")->fetch_assoc()['t'];

// 2. Khan Level Distribution (bar)
$q = $conn->query("SELECT current_khan_level, COUNT(*) as count FROM khan_members GROUP BY current_khan_level ORDER BY current_khan_level ASC");
$khan_labels = []; $khan_data = [];
while ($r = $q->fetch_assoc()) { $khan_labels[] = "Khan " . $r['current_khan_level']; $khan_data[] = (int)$r['count']; }

// 3. Yearly Memberships by date_joined (line)
$q = $conn->query("SELECT YEAR(date_joined) as yr, COUNT(*) as count FROM khan_members WHERE date_joined IS NOT NULL GROUP BY yr ORDER BY yr ASC");
$year_labels = []; $year_data = [];
while ($r = $q->fetch_assoc()) { $year_labels[] = (string)$r['yr']; $year_data[] = (int)$r['count']; }

// 4. Members by Training Location (horizontal bar, top 8)
$q = $conn->query("SELECT training_location, COUNT(*) as count FROM khan_members WHERE training_location IS NOT NULL AND training_location != '' GROUP BY training_location ORDER BY count DESC LIMIT 8");
$loc_labels = []; $loc_data = [];
while ($r = $q->fetch_assoc()) { $loc_labels[] = $r['training_location']; $loc_data[] = (int)$r['count']; }

// 5. Recent members
$recent_members = $conn->query("SELECT full_name, current_khan_level, status, date_joined FROM khan_members ORDER BY date_joined DESC LIMIT 6");

// 6. Recent messages
$recent_messages = $conn->query("SELECT name, subject, status, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 6");

include 'includes/admin_header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.dash-wrap { width: 100%; }

/* Hero */
.dash-hero {
    background: linear-gradient(135deg, var(--admin-primary-dark) 0%, var(--admin-primary) 60%, var(--admin-primary-light) 100%);
    border-radius: var(--radius-xl);
    padding: 2rem 2.2rem;
    margin-bottom: 1.8rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(139,0,0,0.2);
}
.dash-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,0.07);
    border-radius: 50%;
    pointer-events: none;
}
.dash-hero::after {
    content: '';
    position: absolute;
    bottom: -40px; left: 38%;
    width: 150px; height: 150px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
    pointer-events: none;
}
.dash-hero h1 {
    font-family: 'Syne', sans-serif;
    font-size: 1.75rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 0.2rem;
    letter-spacing: -0.5px;
}
.dash-hero p { color: rgba(255,255,255,0.75); font-size: 0.9rem; margin: 0; }
.live-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.07em;
}
.live-dot {
    width: 7px; height: 7px;
    background: #4ade80;
    border-radius: 50%;
    animation: blink 1.4s infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }

/* Stat cards */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
    gap: 1rem;
    margin-bottom: 1.8rem;
}
.scard {
    background: var(--admin-surface);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--radius-lg);
    padding: 1.2rem 1.1rem 1rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.18s, box-shadow 0.18s;
    box-shadow: var(--shadow-sm);
}
.scard:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.scard::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
}
.scard.c-red::after    { background: var(--admin-primary); }
.scard.c-gold::after   { background: #f59e0b; }
.scard.c-green::after  { background: var(--admin-success); }
.scard.c-blue::after   { background: var(--admin-info); }
.scard.c-purple::after { background: #a855f7; }
.scard.c-orange::after { background: var(--admin-secondary); }
.scard.c-teal::after   { background: #14b8a6; }

.scard-emoji { font-size: 1.6rem; margin-bottom: 0.6rem; line-height: 1; }
.scard-val {
    font-family: 'Syne', sans-serif;
    font-size: 2rem;
    font-weight: 800;
    color: var(--admin-text);
    line-height: 1;
    margin-bottom: 0.25rem;
}
.scard-label {
    font-size: 0.72rem;
    color: var(--admin-text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* Charts */
.chart-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 1.2rem;
    margin-bottom: 1.2rem;
}
.cbox   { grid-column: span 6; }
.cbox-4 { grid-column: span 4; }
.cbox-8 { grid-column: span 8; }
.cbox-12{ grid-column: span 12; }

@media (max-width: 1100px) {
    .cbox, .cbox-8, .cbox-4 { grid-column: span 12; }
}
@media (max-width: 640px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); }
}

.chart-panel {
    background: var(--admin-surface);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--radius-lg);
    padding: 1.4rem 1.4rem 1.2rem;
    box-shadow: var(--shadow-sm);
}
.chart-panel-title {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--admin-text-light);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
.chart-panel-title .accent {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.chart-canvas-wrap { position: relative; }
.h-260 { height: 260px; }
.h-300 { height: 300px; }
.h-320 { height: 320px; }

/* Tables */
.table-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.2rem;
    margin-bottom: 1.2rem;
}
@media (max-width: 900px) { .table-grid { grid-template-columns: 1fr; } }

.table-panel {
    background: var(--admin-surface);
    border: 1px solid var(--admin-border-light);
    border-radius: var(--radius-lg);
    padding: 1.4rem;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.table-panel table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
.table-panel th {
    text-align: left;
    padding: 0.45rem 0.7rem;
    color: var(--admin-text-muted);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    border-bottom: 2px solid var(--admin-border-light);
    font-weight: 700;
}
.table-panel td {
    padding: 0.55rem 0.7rem;
    border-bottom: 1px solid var(--admin-border-light);
    color: var(--admin-text);
    vertical-align: middle;
}
.table-panel tr:last-child td { border-bottom: none; }

.spill {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: capitalize;
}
.spill.active    { background: rgba(16,185,129,.12); color: var(--admin-success); }
.spill.inactive  { background: rgba(239,68,68,.12);  color: var(--admin-danger); }
.spill.refresher { background: rgba(245,158,11,.12); color: var(--admin-warning); }
.spill.new       { background: rgba(59,130,246,.12); color: var(--admin-info); }
.spill.read      { background: rgba(148,163,184,.12);color: var(--admin-text-muted); }
.spill.replied   { background: rgba(16,185,129,.12); color: var(--admin-success); }
.spill.archived  { background: rgba(100,116,139,.12);color: var(--admin-text-muted); }
</style>

<div class="dash-wrap">

    <!-- Hero -->
    <div class="dash-hero">
        <div>
            <h1>Dashboard Overview</h1>
            <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> — Oriental Muay Boran Academy</p>
        </div>
        <div class="live-pill"><span class="live-dot"></span> Live Data</div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-row">
        <div class="scard c-red">
            <div class="scard-emoji">👥</div>
            <div class="scard-val"><?php echo number_format($total_users); ?></div>
            <div class="scard-label">Total Users</div>
        </div>
        <div class="scard c-gold">
            <div class="scard-emoji">🥋</div>
            <div class="scard-val"><?php echo number_format($total_members); ?></div>
            <div class="scard-label">Active Members</div>
        </div>
        <div class="scard c-green">
            <div class="scard-emoji">🧑‍🏫</div>
            <div class="scard-val"><?php echo number_format($total_instructors); ?></div>
            <div class="scard-label">Instructors</div>
        </div>
        <div class="scard c-blue">
            <div class="scard-emoji">✉️</div>
            <div class="scard-val"><?php echo number_format($unread_messages); ?></div>
            <div class="scard-label">New Messages</div>
        </div>
        <div class="scard c-purple">
            <div class="scard-emoji">🤝</div>
            <div class="scard-val"><?php echo number_format($total_affiliates); ?></div>
            <div class="scard-label">Affiliates</div>
        </div>
        <div class="scard c-orange">
            <div class="scard-emoji">📚</div>
            <div class="scard-val"><?php echo number_format($total_materials); ?></div>
            <div class="scard-label">Materials</div>
        </div>
        <div class="scard c-teal">
            <div class="scard-emoji">🖼️</div>
            <div class="scard-val"><?php echo number_format($total_events); ?></div>
            <div class="scard-label">Events</div>
        </div>
    </div>

    <!-- Charts: Khan Level (left) + Yearly line (right) -->
    <div class="chart-grid">

        <div class="chart-panel cbox">
            <div class="chart-panel-title">
                <span class="accent" style="background:var(--admin-primary);"></span>
                Khan Level Distribution
            </div>
            <div class="chart-canvas-wrap h-300"><canvas id="khanChart"></canvas></div>
        </div>

        <div class="chart-panel cbox">
            <div class="chart-panel-title">
                <span class="accent" style="background:var(--admin-success);"></span>
                Yearly Memberships — by Date Joined
            </div>
            <div class="chart-canvas-wrap h-300"><canvas id="yearlyChart"></canvas></div>
        </div>

        <!-- Training Location — full width -->
        <div class="chart-panel cbox-12">
            <div class="chart-panel-title">
                <span class="accent" style="background:#14b8a6;"></span>
                Members by Training Location
            </div>
            <div class="chart-canvas-wrap h-260"><canvas id="locChart"></canvas></div>
        </div>

    </div>

    <!-- Recent Tables -->
    <div class="table-grid">
        <div class="table-panel">
            <div class="chart-panel-title" style="margin-bottom:1rem;">
                <span class="accent" style="background:#f59e0b;"></span>
                Latest Members (by Date Joined)
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Khan Level</th>
                        <th>Status</th>
                        <th>Date Joined</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($m = $recent_members->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($m['full_name']); ?></td>
                        <td style="font-weight:700;color:var(--admin-primary);">Khan <?php echo $m['current_khan_level']; ?></td>
                        <td><span class="spill <?php echo $m['status']; ?>"><?php echo $m['status']; ?></span></td>
                        <td style="color:var(--admin-text-light);white-space:nowrap;"><?php echo date('M d, Y', strtotime($m['date_joined'])); ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="table-panel">
            <div class="chart-panel-title" style="margin-bottom:1rem;">
                <span class="accent" style="background:var(--admin-info);"></span>
                Recent Messages
            </div>
            <table>
                <thead>
                    <tr>
                        <th>From</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $has_msgs = false;
                while ($msg = $recent_messages->fetch_assoc()):
                    $has_msgs = true;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($msg['name']); ?></td>
                        <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($msg['subject'] ?? '—'); ?></td>
                        <td><span class="spill <?php echo $msg['status']; ?>"><?php echo $msg['status']; ?></span></td>
                        <td style="color:var(--admin-text-light);white-space:nowrap;"><?php echo date('M d', strtotime($msg['created_at'])); ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php if (!$has_msgs): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--admin-text-muted);padding:1.5rem 0;">No messages yet</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
const G = 'rgba(0,0,0,0.06)';
const T = '#94a3b8';
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = T;

// 1. Khan Level — gradient bar
const kCtx = document.getElementById('khanChart').getContext('2d');
const kGrad = kCtx.createLinearGradient(0, 0, 0, 300);
kGrad.addColorStop(0, 'rgba(139,0,0,0.9)');
kGrad.addColorStop(1, 'rgba(245,158,11,0.5)');
new Chart(kCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($khan_labels); ?>,
        datasets: [{
            label: 'Members',
            data: <?php echo json_encode($khan_data); ?>,
            backgroundColor: kGrad,
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: T } },
            y: { grid: { color: G }, ticks: { color: T, precision: 0 }, beginAtZero: true }
        }
    }
});

// 2. Yearly Memberships — line
new Chart(document.getElementById('yearlyChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($year_labels ?: ['No data']); ?>,
        datasets: [{
            label: 'Members Joined',
            data: <?php echo json_encode($year_data ?: [0]); ?>,
            backgroundColor: 'rgba(16,185,129,0.12)',
            borderColor: 'rgba(16,185,129,1)',
            pointBackgroundColor: 'rgba(245,158,11,1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 9,
            fill: true,
            tension: 0.35,
            borderWidth: 2.5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: G }, ticks: { color: T } },
            y: { grid: { color: G }, ticks: { color: T, precision: 0 }, beginAtZero: true }
        }
    }
});

// 3. Training Location — horizontal bar (full width, so more room)
new Chart(document.getElementById('locChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($loc_labels ?: ['No data']); ?>,
        datasets: [{
            label: 'Members',
            data: <?php echo json_encode($loc_data ?: [0]); ?>,
            backgroundColor: 'rgba(20,184,166,0.75)',
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: G }, ticks: { color: T, precision: 0 }, beginAtZero: true },
            y: { grid: { display: false }, ticks: { color: T, font: { size: 12 } } }
        }
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>