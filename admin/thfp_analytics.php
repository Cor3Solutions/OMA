<?php
// ================================================================
// thfp_analytics.php — Team Medal Tally & Analytics (Fiery Theme)
// ================================================================
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['thfp_admin'])) {
    header('Location: thfpadmin.php'); exit;
}

if (file_exists('../config/database.php')) { require_once '../config/database.php'; $conn = getDbConnection(); }
else { $conn = new mysqli('localhost','root','','oma_database'); }
$conn->set_charset('utf8mb4');

$selected_event = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$events_list    = $conn->query("SELECT id, event_number, name FROM thfp_events ORDER BY event_number ASC");

$event_filter = $selected_event ? "AND b.event_id = $selected_event" : '';

// Medal logic
$medal_sql = "
    SELECT
        f.gym,
        SUM(CASE WHEN b.bout_type = 'final' AND b.winner_id = f.id THEN 1 ELSE 0 END) AS gold,
        SUM(CASE
            WHEN b.bout_type = 'final'
             AND b.winner_id IS NOT NULL
             AND b.winner_id <> f.id
             AND (b.red_fighter_id = f.id OR b.blue_fighter_id = f.id)
            THEN 1 ELSE 0 END) AS silver,
        SUM(CASE
            WHEN b.bout_type IN ('prelim','co_main','amateur')
             AND b.winner_id = f.id
             AND EXISTS (
                 SELECT 1 FROM thfp_bouts fin
                 WHERE fin.event_id = b.event_id
                   AND fin.bout_type = 'final'
                   AND fin.weight_class = b.weight_class
                   AND fin.gender = b.gender
                   AND fin.age_category = b.age_category
             )
             AND NOT EXISTS (
                 SELECT 1 FROM thfp_bouts fin2
                 WHERE fin2.event_id = b.event_id
                   AND fin2.bout_type = 'final'
                   AND fin2.weight_class = b.weight_class
                   AND fin2.gender = b.gender
                   AND fin2.age_category = b.age_category
                   AND (fin2.red_fighter_id = f.id OR fin2.blue_fighter_id = f.id)
             )
            THEN 1 ELSE 0 END) AS bronze
    FROM thfp_fighters f
    JOIN thfp_bouts b ON (b.red_fighter_id = f.id OR b.blue_fighter_id = f.id)
    WHERE f.gym != '' AND f.gym IS NOT NULL $event_filter
    GROUP BY f.gym
    HAVING (gold + silver + bronze) > 0
    ORDER BY gold DESC, silver DESC, bronze DESC, f.gym ASC
";
$medal_res = $conn->query($medal_sql);
$medals = [];
while ($r = $medal_res->fetch_assoc()) $medals[] = $r;

// Fight stats by gym
$stats_sql = "
    SELECT
        f.gym,
        COUNT(DISTINCT f.id) AS fighters,
        COUNT(b.id) AS bouts,
        SUM(CASE WHEN b.winner_id = f.id THEN 1 ELSE 0 END) AS wins,
        SUM(CASE WHEN b.winner_id IS NOT NULL AND b.winner_id <> f.id
                  AND (b.red_fighter_id=f.id OR b.blue_fighter_id=f.id)
             THEN 1 ELSE 0 END) AS losses,
        SUM(CASE WHEN b.winner_id = f.id
                  AND b.result_method IN ('KO','TKO','RSC','SUB')
             THEN 1 ELSE 0 END) AS finishes
    FROM thfp_fighters f
    JOIN thfp_bouts b ON (b.red_fighter_id = f.id OR b.blue_fighter_id = f.id)
    WHERE f.gym != '' AND f.gym IS NOT NULL $event_filter
    GROUP BY f.gym
    ORDER BY wins DESC
";
$stats_res = $conn->query($stats_sql);
$stats = [];
while ($r = $stats_res->fetch_assoc()) $stats[] = $r;

// Results by weight class
$wc_sql = "
    SELECT b.weight_class, b.gender, b.age_category, b.bout_type, b.bout_number,
           b.result_method, b.decision_type, b.result_round, b.result_time,
           rf.name AS red_name, rf.gym AS red_gym,
           bf.name AS blue_name, bf.gym AS blue_gym,
           wf.name AS winner_name, wf.gym AS winner_gym,
           e.event_number
    FROM thfp_bouts b
    LEFT JOIN thfp_fighters rf ON b.red_fighter_id = rf.id
    LEFT JOIN thfp_fighters bf ON b.blue_fighter_id = bf.id
    LEFT JOIN thfp_fighters wf ON b.winner_id = wf.id
    LEFT JOIN thfp_events e ON b.event_id = e.id
    WHERE 1=1 " . ($selected_event ? "AND b.event_id = $selected_event" : '') . "
    ORDER BY FIELD(b.bout_type,'final','main_event','co_main','prelim','amateur'), b.weight_class, b.bout_order
";
$wc_res = $conn->query($wc_sql);
$by_wc = [];
while ($r = $wc_res->fetch_assoc()) {
    $key = trim($r['weight_class'].' '.$r['age_category'].' '.$r['gender']);
    $by_wc[$key][] = $r;
}

// Top performers
$ef = $selected_event ? "WHERE b.event_id = $selected_event" : '';
$top_sql = "
    SELECT f.name, f.gym, f.photo_path,
           COUNT(b.id) AS win_count,
           SUM(CASE WHEN b.result_method IN ('KO','TKO','RSC','SUB') THEN 1 ELSE 0 END) AS finishes
    FROM thfp_fighters f
    JOIN thfp_bouts b ON b.winner_id = f.id
    $ef
    GROUP BY f.id
    ORDER BY win_count DESC, finishes DESC
    LIMIT 5
";
$top_res = $conn->query($top_sql);
$top_winners = [];
while ($r = $top_res->fetch_assoc()) $top_winners[] = $r;

// Method breakdown
$m_ef = $selected_event ? "AND event_id = $selected_event" : '';
$method_sql = "
    SELECT result_method, COUNT(*) AS cnt
    FROM thfp_bouts
    WHERE result_method != '' AND result_method IS NOT NULL $m_ef
    GROUP BY result_method
    ORDER BY cnt DESC
";
$method_res = $conn->query($method_sql);
$methods = []; $total_methods = 0;
while ($r = $method_res->fetch_assoc()) { $methods[] = $r; $total_methods += $r['cnt']; }

$total_gold   = array_sum(array_column($medals, 'gold'));
$total_silver = array_sum(array_column($medals, 'silver'));
$total_bronze = array_sum(array_column($medals, 'bronze'));
$total_gyms   = count($medals);

define('PHOTO_DIR', __DIR__ . '/uploads/thfp_fighters/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>THFP Analytics</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
    --fire:#E8341A;--fire-light:#FF5A3C;--fire-dark:#C42A12;
    --fire-dim:rgba(232,52,26,.1);--fire-border:rgba(232,52,26,.28);
    --ember:#FF8C00;--ember-dim:rgba(255,140,0,.1);--ember-border:rgba(255,140,0,.25);
    --gold:#D4AF37;--gold-light:#F0D060;
    --gold-dim:rgba(212,175,55,.1);--gold-border:rgba(212,175,55,.22);
    --black:#080808;--dark:#0d0d0d;--surface:#161616;--card:#1a1a1a;
    --white:#fff;--muted:rgba(255,255,255,.55);--dim:rgba(255,255,255,.26);--border:rgba(255,255,255,.08);
    --font-disp:'Cinzel',serif;--font-ui:'Rajdhani',sans-serif;
}
html,body{background:var(--dark);color:var(--white);font-family:var(--font-ui);min-height:100vh;-webkit-font-smoothing:antialiased;}
.layout{display:flex;min-height:100vh;}
.sidebar{width:220px;flex-shrink:0;background:var(--black);border-right:1px solid var(--fire-border);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;}
.sb-logo{padding:1.5rem 1.3rem 1.1rem;border-bottom:1px solid var(--fire-border);display:flex;flex-direction:column;align-items:center;gap:.55rem;text-align:center;background:linear-gradient(180deg,rgba(232,52,26,.06) 0%,transparent 100%);}
.sb-emblem{position:relative;width:42px;height:42px;display:flex;align-items:center;justify-content:center;}
.sb-emblem-diamond{width:42px;height:42px;border:1.5px solid var(--fire);transform:rotate(45deg);position:absolute;background:var(--fire-dim);}
.sb-emblem span{position:relative;z-index:1;font-family:var(--font-disp);font-size:.62rem;font-weight:900;color:var(--fire);}
.sb-title{font-family:var(--font-disp);font-size:.78rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;}
.sb-sub{font-size:.58rem;letter-spacing:3px;text-transform:uppercase;color:var(--fire);opacity:.7;}
.sb-nav{padding:.5rem 0;flex:1;}
.sb-section{font-size:.54rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:var(--dim);padding:.75rem 1.2rem .25rem;}
.sb-link{display:flex;align-items:center;gap:.7rem;padding:.62rem 1.2rem;font-size:.82rem;font-weight:600;color:var(--muted);text-decoration:none;border-left:2px solid transparent;transition:all .18s;}
.sb-link:hover{background:var(--fire-dim);color:var(--white);}
.sb-link.active{color:var(--fire-light);border-left-color:var(--fire);background:var(--fire-dim);}
.sb-link svg{width:15px;height:15px;opacity:.65;flex-shrink:0;}
.sb-footer{padding:1rem 1.2rem;border-top:1px solid var(--fire-border);}
.sb-signout{background:none;border:1px solid var(--border);border-radius:3px;color:var(--dim);cursor:pointer;font-family:var(--font-ui);font-size:.72rem;letter-spacing:1px;padding:.4rem .9rem;width:100%;transition:all .2s;}
.sb-signout:hover{border-color:var(--fire-border);color:var(--fire-light);}
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
.topbar{background:var(--black);border-bottom:1px solid var(--fire-border);padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;position:relative;}
.topbar::after{content:'';position:absolute;bottom:0;left:0;right:0;height:1px;background:linear-gradient(to right,var(--fire),var(--ember),transparent);}
.topbar-title{font-family:var(--font-disp);font-size:1.1rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;}
.content{padding:2rem;overflow-y:auto;flex:1;}
.ev-filter{display:flex;align-items:center;gap:.75rem;margin-bottom:2rem;flex-wrap:wrap;background:var(--surface);border:1px solid var(--fire-border);border-radius:4px;padding:1rem 1.2rem;}
.ev-filter label{font-size:.62rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--fire);white-space:nowrap;}
.ev-filter select{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:3px;color:var(--white);font-family:var(--font-ui);font-size:.88rem;padding:.5rem .9rem;outline:none;flex:1;max-width:420px;}
.ev-filter select:focus{border-color:var(--fire);}
.ev-filter select option{background:#1a1a1a;}
.btn-filter{background:var(--fire);color:var(--white);border:none;border-radius:3px;font-family:var(--font-ui);font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:.5rem 1rem;cursor:pointer;transition:background .2s;white-space:nowrap;}
.btn-filter:hover{background:var(--fire-light);}
.strip{display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:1px;background:var(--fire-border);border:1px solid var(--fire-border);border-radius:4px;overflow:hidden;margin-bottom:2rem;}
.strip-box{background:var(--surface);padding:1rem 1.2rem;text-align:center;position:relative;overflow:hidden;}
.strip-box::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
.s-white::before{background:linear-gradient(to right,var(--fire),var(--ember));}
.s-gold::before{background:#FFD700;}
.s-silver::before{background:#C0C0C0;}
.s-bronze::before{background:#CD7F32;}
.s-total::before{background:linear-gradient(to right,var(--fire),var(--gold));}
.strip-val{font-family:var(--font-disp);font-size:2rem;font-weight:900;line-height:1;display:block;}
.strip-lbl{font-size:.58rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--muted);margin-top:4px;display:block;}
.c-fire{color:var(--fire-light);}.c-gold{color:#FFD700;}.c-silver{color:#C0C0C0;}.c-bronze{color:#CD7F32;}.c-white{color:var(--white);}
.sec-eyebrow{font-size:.62rem;font-weight:700;letter-spacing:5px;text-transform:uppercase;color:var(--fire);margin-bottom:.25rem;}
.sec-title{font-family:var(--font-disp);font-size:1.25rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:1.2rem;line-height:1;}
.panel{background:var(--surface);border:1px solid var(--fire-border);border-radius:4px;overflow:hidden;margin-bottom:2.5rem;}
.panel-top{background:var(--black);border-bottom:1px solid var(--fire-border);padding:.9rem 1.5rem;display:flex;align-items:center;justify-content:space-between;position:relative;}
.panel-top::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--fire),var(--ember),transparent);}
.panel-top h2{font-family:var(--font-disp);font-size:.85rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--fire-light);}
.medal-tbl{width:100%;border-collapse:collapse;}
.medal-tbl th{font-size:.6rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--dim);padding:.65rem 1rem;border-bottom:1px solid var(--fire-border);text-align:left;background:rgba(232,52,26,.04);}
.medal-tbl th.center{text-align:center;}
.medal-tbl td{padding:.75rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle;}
.medal-tbl tr:last-child td{border-bottom:none;}
.medal-tbl tr:hover td{background:var(--fire-dim);}
.rank-num{font-family:var(--font-disp);font-size:1.1rem;font-weight:900;color:var(--dim);width:32px;}
.rank-num.r1{color:#FFD700;}.rank-num.r2{color:#C0C0C0;}.rank-num.r3{color:#CD7F32;}
.gym-name{font-weight:600;font-size:.9rem;font-family:var(--font-ui);}
.medal-cell{text-align:center;}
.medal-count{display:inline-block;min-width:36px;padding:4px 10px;border-radius:2px;font-family:var(--font-disp);font-size:.95rem;font-weight:900;text-align:center;}
.mc-gold{background:rgba(255,215,0,.12);color:#FFD700;border:1px solid rgba(255,215,0,.3);}
.mc-silver{background:rgba(192,192,192,.1);color:#C0C0C0;border:1px solid rgba(192,192,192,.25);}
.mc-bronze{background:rgba(205,127,50,.12);color:#CD7F32;border:1px solid rgba(205,127,50,.3);}
.mc-zero{color:var(--dim);font-size:.85rem;}
.bar-wrap{display:flex;align-items:center;gap:4px;min-width:100px;}
.bar-seg{height:8px;border-radius:1px;}
.bar-g{background:#FFD700;}.bar-s{background:#C0C0C0;}.bar-b{background:#CD7F32;}
.analytics-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2.5rem;}
.stat-card{background:var(--surface);border:1px solid var(--fire-border);border-radius:4px;overflow:hidden;}
.sc-head{background:var(--black);border-bottom:1px solid var(--fire-border);padding:.65rem 1rem;font-family:var(--font-ui);font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--fire-light);}
.top-row{display:flex;align-items:center;gap:.85rem;padding:.72rem 1rem;border-bottom:1px solid var(--border);transition:background .15s;}
.top-row:last-child{border-bottom:none;}
.top-row:hover{background:var(--fire-dim);}
.top-rank{font-family:var(--font-disp);font-size:1.1rem;font-weight:900;color:var(--dim);min-width:22px;}
.top-rank.r1{color:var(--fire-light);}
.top-photo{width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--fire-border);flex-shrink:0;}
.top-init{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-disp);font-size:.85rem;font-weight:900;color:var(--fire-light);background:var(--fire-dim);border:2px solid var(--fire-border);flex-shrink:0;}
.top-info{flex:1;min-width:0;}
.top-name{font-size:.88rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.top-gym{font-size:.7rem;color:var(--muted);margin-top:1px;}
.top-stat{text-align:right;flex-shrink:0;}
.top-stat-num{font-family:var(--font-disp);font-size:1.2rem;font-weight:900;color:var(--fire-light);line-height:1;}
.top-stat-lbl{font-size:.58rem;color:var(--dim);letter-spacing:1px;text-transform:uppercase;}
.method-body{padding:.75rem 1rem;}
.method-bar{margin-bottom:.65rem;}
.method-bar:last-child{margin-bottom:0;}
.method-bar-label{display:flex;justify-content:space-between;font-size:.72rem;margin-bottom:4px;}
.method-bar-label .name{color:var(--white);font-weight:600;}
.method-bar-label .cnt{color:var(--muted);}
.method-bar-track{height:8px;background:rgba(255,255,255,.06);border-radius:4px;overflow:hidden;}
.method-bar-fill{height:100%;border-radius:4px;}
.fill-ko{background:var(--fire);}
.fill-tko{background:var(--fire-light);}
.fill-rsc{background:var(--ember);}
.fill-sub{background:#8b5cf6;}
.fill-dec{background:#3b82f6;}
.fill-draw{background:#64748b;}
.fill-nc{background:#475569;}
.fill-default{background:var(--gold);}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(265px,1fr));gap:1rem;margin-bottom:2.5rem;}
.team-card{background:var(--surface);border:1px solid var(--fire-border);border-radius:4px;overflow:hidden;}
.tc-head{background:var(--black);border-bottom:1px solid var(--fire-border);padding:.65rem 1rem;font-family:var(--font-ui);font-size:.76rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;}
.tc-body{padding:.4rem 0;}
.tc-row{display:flex;justify-content:space-between;align-items:center;padding:.45rem 1rem;border-bottom:1px solid var(--border);font-size:.82rem;}
.tc-row:last-child{border-bottom:none;}
.tc-row:hover{background:var(--fire-dim);}
.tc-lbl{color:var(--muted);}
.tc-val{font-family:var(--font-disp);font-weight:700;color:var(--fire-light);}
.wc-section{margin-bottom:1.5rem;}
.wc-header{background:var(--black);border:1px solid var(--fire-border);border-bottom:none;padding:.6rem 1rem;font-family:var(--font-ui);font-size:.68rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--fire-light);border-radius:4px 4px 0 0;position:relative;}
.wc-header::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--fire),var(--ember),transparent);border-radius:4px 4px 0 0;}
.wc-body{background:var(--surface);border:1px solid var(--fire-border);border-top:none;border-radius:0 0 4px 4px;overflow:hidden;}
.wc-bout{display:flex;align-items:center;padding:.62rem 1rem;border-bottom:1px solid var(--border);gap:.75rem;font-size:.82rem;transition:background .15s;}
.wc-bout:last-child{border-bottom:none;}
.wc-bout:hover{background:var(--fire-dim);}
.wc-type{font-size:.55rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:2px 7px;border-radius:2px;white-space:nowrap;flex-shrink:0;}
.t-final{background:rgba(212,175,55,.2);color:var(--gold-light);border:1px solid rgba(212,175,55,.4);}
.t-prelim{background:rgba(255,255,255,.05);color:var(--muted);border:1px solid var(--border);}
.t-comain{background:var(--ember-dim);color:var(--ember);border:1px solid var(--ember-border);}
.t-main{background:var(--fire-dim);color:var(--fire-light);border:1px solid var(--fire-border);}
.wc-fighter{flex:1;min-width:0;}
.wc-winner{font-weight:600;color:var(--fire-light);}
.wc-loser{color:var(--muted);}
.wc-gym{font-size:.68rem;color:var(--dim);}
.wc-vs{color:var(--dim);font-size:.68rem;font-weight:700;letter-spacing:2px;flex-shrink:0;padding:0 .4rem;}
.wc-result{text-align:right;flex-shrink:0;min-width:80px;}
.wc-method{font-size:.65rem;font-weight:700;letter-spacing:1px;}
.wc-dec{font-size:.65rem;color:var(--dim);}
.no-data{text-align:center;padding:2.5rem;color:var(--dim);font-style:italic;font-size:.9rem;}
@media(max-width:900px){.sidebar{display:none;}.analytics-grid{grid-template-columns:1fr;}.strip{grid-template-columns:1fr 1fr;}.stats-grid{grid-template-columns:1fr;}.content{padding:1.25rem;}}
</style>
</head>
<body>
<div class="layout">
<nav class="sidebar">
    <div class="sb-logo">
        <div class="sb-emblem"><div class="sb-emblem-diamond"></div><span>TH</span></div>
        <div class="sb-title">THFP</div>
        <div class="sb-sub">Admin Portal</div>
    </div>
    <div class="sb-nav">
        <div class="sb-section">Manage</div>
        <a href="thfpadmin.php?section=events" class="sb-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Events</a>
        <a href="thfpadmin.php?section=bouts" class="sb-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>Bouts</a>
        <a href="thfpadmin.php?section=fighters" class="sb-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>Fighters</a>
        <div class="sb-section" style="margin-top:.5rem;">Analytics</div>
        <a href="thfp_analytics.php" class="sb-link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>Medal Tally</a>
        <div class="sb-section" style="margin-top:.5rem;">Site</div>
        <a href="thfp.php" target="_blank" class="sb-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>View Public Page</a>
    </div>
    <div class="sb-footer"><form method="POST" action="thfpadmin.php"><button type="submit" name="thfp_logout" class="sb-signout">&#8592; Sign Out</button></form></div>
</nav>

<div class="main">
    <div class="topbar">
        <div class="topbar-title">Medal Tally &amp; Analytics</div>
        <a href="thfpadmin.php" style="font-family:var(--font-ui);font-size:.75rem;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--muted);text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--fire-light)'" onmouseout="this.style.color='var(--muted)'">&#8592; Back</a>
    </div>
    <div class="content">

        <form method="GET" class="ev-filter">
            <label>Filter by Event:</label>
            <select name="event_id" onchange="this.form.submit()">
                <option value="0">All Events (Combined)</option>
                <?php while ($ev = $events_list->fetch_assoc()): ?>
                <option value="<?php echo $ev['id']; ?>" <?php echo $selected_event === $ev['id'] ? 'selected' : ''; ?>>
                    Combat <?php echo $ev['event_number']; ?> — <?php echo htmlspecialchars($ev['name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn-filter">Apply</button>
        </form>

        <div class="strip">
            <div class="strip-box s-white"><span class="strip-val c-fire"><?php echo $total_gyms; ?></span><span class="strip-lbl">Teams</span></div>
            <div class="strip-box s-gold"><span class="strip-val c-gold"><?php echo $total_gold; ?></span><span class="strip-lbl">Gold</span></div>
            <div class="strip-box s-silver"><span class="strip-val c-silver"><?php echo $total_silver; ?></span><span class="strip-lbl">Silver</span></div>
            <div class="strip-box s-bronze"><span class="strip-val c-bronze"><?php echo $total_bronze; ?></span><span class="strip-lbl">Bronze</span></div>
            <div class="strip-box s-total"><span class="strip-val c-white"><?php echo $total_gold+$total_silver+$total_bronze; ?></span><span class="strip-lbl">Total</span></div>
        </div>

        <div class="sec-eyebrow">Team Rankings</div>
        <div class="sec-title">Medal Tally</div>
        <div class="panel">
            <div class="panel-top">
                <h2>Official Standings</h2>
                <span style="font-size:.72rem;color:var(--dim);"><?php echo $total_gyms; ?> team<?php echo $total_gyms !== 1 ? 's' : ''; ?></span>
            </div>
            <table class="medal-tbl">
                <thead><tr>
                    <th style="width:40px;">#</th><th>Team / Gym</th>
                    <th class="center">🥇 Gold</th><th class="center">🥈 Silver</th><th class="center">🥉 Bronze</th>
                    <th class="center">Total</th><th>Distribution</th>
                </tr></thead>
                <tbody>
                <?php
                $max_t = 1;
                foreach ($medals as $m) $max_t = max($max_t, $m['gold']+$m['silver']+$m['bronze']);
                foreach ($medals as $i => $m):
                    $rank  = $i + 1;
                    $total = $m['gold']+$m['silver']+$m['bronze'];
                    $rc    = $rank===1?'r1':($rank===2?'r2':($rank===3?'r3':''));
                    $gw    = $max_t>0?round(($m['gold']/$max_t)*110):0;
                    $sw    = $max_t>0?round(($m['silver']/$max_t)*110):0;
                    $bw    = $max_t>0?round(($m['bronze']/$max_t)*110):0;
                ?>
                <tr>
                    <td><div class="rank-num <?php echo $rc; ?>"><?php echo $rank; ?></div></td>
                    <td><div class="gym-name"><?php echo htmlspecialchars($m['gym']); ?></div></td>
                    <td class="medal-cell"><?php echo $m['gold']>0?"<span class='medal-count mc-gold'>{$m['gold']}</span>":"<span class='mc-zero'>—</span>"; ?></td>
                    <td class="medal-cell"><?php echo $m['silver']>0?"<span class='medal-count mc-silver'>{$m['silver']}</span>":"<span class='mc-zero'>—</span>"; ?></td>
                    <td class="medal-cell"><?php echo $m['bronze']>0?"<span class='medal-count mc-bronze'>{$m['bronze']}</span>":"<span class='mc-zero'>—</span>"; ?></td>
                    <td class="medal-cell" style="font-family:var(--font-disp);font-weight:900;font-size:1.1rem;"><?php echo $total; ?></td>
                    <td>
                        <div class="bar-wrap">
                            <?php if($m['gold']>0):?><div class="bar-seg bar-g" style="width:<?php echo max($gw,4);?>px"></div><?php endif;?>
                            <?php if($m['silver']>0):?><div class="bar-seg bar-s" style="width:<?php echo max($sw,4);?>px"></div><?php endif;?>
                            <?php if($m['bronze']>0):?><div class="bar-seg bar-b" style="width:<?php echo max($bw,4);?>px"></div><?php endif;?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($medals)):?><tr><td colspan="7" class="no-data">No medal data yet. Bouts with type "Final" generate the tally.</td></tr><?php endif;?>
                </tbody>
            </table>
        </div>

        <div class="sec-eyebrow">Performance Insights</div>
        <div class="sec-title">Fight Analytics</div>
        <div class="analytics-grid">
            <div class="stat-card">
                <div class="sc-head">&#9733; Top Winners</div>
                <?php if(empty($top_winners)):?><div class="no-data">No results recorded yet.</div><?php endif;?>
                <?php foreach($top_winners as $idx=>$tw):
                    $po=!empty($tw['photo_path'])&&file_exists(PHOTO_DIR.basename($tw['photo_path']));
                ?>
                <div class="top-row">
                    <div class="top-rank <?php echo $idx===0?'r1':'';?>"><?php echo $idx+1;?></div>
                    <?php if($po):?><img src="<?php echo htmlspecialchars($tw['photo_path']);?>" class="top-photo" alt="">
                    <?php else:?><div class="top-init"><?php echo strtoupper(substr(trim($tw['name']),0,1));?></div><?php endif;?>
                    <div class="top-info">
                        <div class="top-name"><?php echo htmlspecialchars($tw['name']);?></div>
                        <div class="top-gym"><?php echo htmlspecialchars($tw['gym']?:'Unaffiliated');?></div>
                    </div>
                    <div class="top-stat">
                        <div class="top-stat-num"><?php echo $tw['win_count'];?></div>
                        <div class="top-stat-lbl"><?php echo $tw['win_count']==1?'Win':'Wins';?></div>
                    </div>
                </div>
                <?php endforeach;?>
            </div>
            <div class="stat-card">
                <div class="sc-head">&#127937; Result Methods</div>
                <div class="method-body">
                    <?php if(empty($methods)):?><div class="no-data" style="padding:1rem 0;">No results yet.</div><?php endif;?>
                    <?php $fills=['KO'=>'fill-ko','TKO'=>'fill-tko','RSC'=>'fill-rsc','SUB'=>'fill-sub','DEC'=>'fill-dec','DRAW'=>'fill-draw','NC'=>'fill-nc'];
                    foreach($methods as $m):
                        $pct=$total_methods>0?round(($m['cnt']/$total_methods)*100):0;
                        $fill=$fills[$m['result_method']]??'fill-default';
                    ?>
                    <div class="method-bar">
                        <div class="method-bar-label">
                            <span class="name"><?php echo $m['result_method'];?></span>
                            <span class="cnt"><?php echo $m['cnt'];?> (<?php echo $pct;?>%)</span>
                        </div>
                        <div class="method-bar-track"><div class="method-bar-fill <?php echo $fill;?>" style="width:<?php echo $pct;?>%"></div></div>
                    </div>
                    <?php endforeach;?>
                    <?php if($total_methods>0):?>
                    <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;font-size:.78rem;">
                        <span style="color:var(--muted);">Total Decided Bouts</span>
                        <span style="font-family:var(--font-disp);color:var(--fire-light);font-weight:700;"><?php echo $total_methods;?></span>
                    </div>
                    <?php endif;?>
                </div>
            </div>
        </div>

        <div class="sec-eyebrow">By Gym</div>
        <div class="sec-title">Team Fight Stats</div>
        <div class="stats-grid">
        <?php foreach($stats as $s):
            $wr=$s['bouts']>0?round(($s['wins']/$s['bouts'])*100):0;
            $fr=$s['wins']>0?round(($s['finishes']/$s['wins'])*100):0;
        ?>
        <div class="team-card">
            <div class="tc-head"><?php echo htmlspecialchars($s['gym']);?></div>
            <div class="tc-body">
                <div class="tc-row"><span class="tc-lbl">Fighters Competed</span><span class="tc-val"><?php echo $s['fighters'];?></span></div>
                <div class="tc-row"><span class="tc-lbl">Total Bouts</span><span class="tc-val"><?php echo $s['bouts'];?></span></div>
                <div class="tc-row"><span class="tc-lbl">Wins — Losses</span>
                    <span style="font-family:var(--font-disp);font-weight:700;">
                        <span style="color:#4ade80;"><?php echo $s['wins'];?></span>
                        <span style="color:var(--dim);margin:0 4px;">—</span>
                        <span style="color:#f87171;"><?php echo $s['losses'];?></span>
                    </span>
                </div>
                <div class="tc-row"><span class="tc-lbl">Win Rate</span><span class="tc-val" style="color:<?php echo $wr>=50?'#4ade80':'#f87171';?>"><?php echo $wr;?>%</span></div>
                <div class="tc-row"><span class="tc-lbl">Finish Rate</span><span class="tc-val"><?php echo $fr;?>%</span></div>
            </div>
        </div>
        <?php endforeach;?>
        <?php if(empty($stats)):?><div class="no-data" style="grid-column:1/-1;">No team fight data yet.</div><?php endif;?>
        </div>

        <div class="sec-eyebrow">Breakdown</div>
        <div class="sec-title">Results by Division</div>
        <?php foreach($by_wc as $div=>$bouts):?>
        <div class="wc-section">
            <div class="wc-header"><?php echo htmlspecialchars($div);?></div>
            <div class="wc-body">
            <?php foreach($bouts as $b):
                $tc=['final'=>'t-final','co_main'=>'t-comain','prelim'=>'t-prelim','amateur'=>'t-prelim','main_event'=>'t-main'][$b['bout_type']]??'t-prelim';
                $tl=['main_event'=>'Main','co_main'=>'Co-Main','prelim'=>'Prelim','amateur'=>'Amateur','final'=>'Final'][$b['bout_type']]??ucfirst($b['bout_type']);
                $rw=!empty($b['winner_name'])&&$b['winner_name']===$b['red_name'];
                $bw=!empty($b['winner_name'])&&$b['winner_name']===$b['blue_name'];
                $mc=['KO'=>'color:var(--fire-light)','TKO'=>'color:#fca5a5','RSC'=>'color:var(--ember)','DEC'=>'color:#93c5fd','SUB'=>'color:#c4b5fd','DRAW'=>'color:#94a3b8','NC'=>'color:#64748b'][$b['result_method']]??'';
            ?>
            <div class="wc-bout">
                <span class="wc-type <?php echo $tc;?>"><?php echo $tl;?></span>
                <div class="wc-fighter">
                    <div class="<?php echo $rw?'wc-winner':'wc-loser';?>"><?php echo htmlspecialchars($b['red_name']??'TBA');?></div>
                    <div class="wc-gym"><?php echo htmlspecialchars($b['red_gym']??'');?></div>
                </div>
                <span class="wc-vs">VS</span>
                <div class="wc-fighter" style="text-align:right;">
                    <div class="<?php echo $bw?'wc-winner':'wc-loser';?>"><?php echo htmlspecialchars($b['blue_name']??'TBA');?></div>
                    <div class="wc-gym"><?php echo htmlspecialchars($b['blue_gym']??'');?></div>
                </div>
                <div class="wc-result">
                    <?php if($b['result_method']):?>
                        <div class="wc-method" style="<?php echo $mc;?>"><?php echo $b['result_method'];?><?php echo $b['result_round']?' R'.$b['result_round']:'';?></div>
                        <div class="wc-dec"><?php echo $b['decision_type'];?><?php echo $b['result_time']?' '.$b['result_time']:'';?></div>
                    <?php else:?><div class="wc-dec">Scheduled</div><?php endif;?>
                </div>
            </div>
            <?php endforeach;?>
            </div>
        </div>
        <?php endforeach;?>
        <?php if(empty($by_wc)):?><div class="no-data">No bout data yet.</div><?php endif;?>

    </div>
</div>
</div>
</body>
</html>