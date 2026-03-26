<?php
$page_title = "Khan Members";
$extra_head = '<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">';
require_once '../config/database.php';

$conn = getDbConnection();
$sql = "SELECT km.*, u.name as user_name, i.name as instructor_name, i.photo_path as instructor_photo
        FROM khan_members km
        LEFT JOIN users u ON km.user_id = u.id
        LEFT JOIN instructors i ON km.instructor_id = i.id
        ORDER BY
            CASE km.status
                WHEN 'active'    THEN 1
                WHEN 'inactive'  THEN 2
                WHEN 'graduated' THEN 3
            END,
            km.current_khan_level DESC,
            km.full_name ASC";
$result = $conn->query($sql);
$members = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) $members[] = $row;
}

$khan_colors = [
    1  => ['color'=>'#FFFFFF',                                          'text'=>'#1a1a1a','name'=>'White Khan',   'desc'=>'Beginner'],
    2  => ['color'=>'#FFEB3B',                                          'text'=>'#1a1a1a','name'=>'Yellow Khan',  'desc'=>'Novice'],
    3  => ['color'=>'linear-gradient(135deg,#FFEB3B 50%,#FFFFFF 50%)', 'text'=>'#1a1a1a','name'=>'Yellow-White', 'desc'=>'Advanced Novice'],
    4  => ['color'=>'#4CAF50',                                          'text'=>'#fff',   'name'=>'Green Khan',   'desc'=>'Intermediate'],
    5  => ['color'=>'linear-gradient(135deg,#4CAF50 50%,#FFFFFF 50%)', 'text'=>'#1a1a1a','name'=>'Green-White',  'desc'=>'Advanced Intermediate'],
    6  => ['color'=>'#2196F3',                                          'text'=>'#fff',   'name'=>'Blue Khan',    'desc'=>'Skilled Practitioner'],
    7  => ['color'=>'linear-gradient(135deg,#2196F3 50%,#FFFFFF 50%)', 'text'=>'#1a1a1a','name'=>'Blue-White',   'desc'=>'Advanced Practitioner'],
    8  => ['color'=>'#795548',                                          'text'=>'#fff',   'name'=>'Brown Khan',   'desc'=>'Senior Practitioner'],
    9  => ['color'=>'linear-gradient(135deg,#795548 50%,#FFFFFF 50%)', 'text'=>'#1a1a1a','name'=>'Brown-White',  'desc'=>'Advanced Senior'],
    10 => ['color'=>'#D32F2F',                                          'text'=>'#fff',   'name'=>'Red Khan',     'desc'=>'Instructor Level'],
    11 => ['color'=>'linear-gradient(135deg,#D32F2F 50%,#FFFFFF 50%)', 'text'=>'#1a1a1a','name'=>'Red-White',    'desc'=>'Advanced Instructor'],
    12 => ['color'=>'linear-gradient(135deg,#D32F2F 50%,#FFEB3B 50%)', 'text'=>'#1a1a1a','name'=>'Red-Yellow',   'desc'=>'Master Level'],
    13 => ['color'=>'linear-gradient(135deg,#D32F2F 50%,#C0C0C0 50%)', 'text'=>'#1a1a1a','name'=>'Red-Silver',   'desc'=>'Senior Master'],
    14 => ['color'=>'linear-gradient(135deg,#C0C0C0,#E8E8E8,#C0C0C0)','text'=>'#1a1a1a','name'=>'Silver Khan',  'desc'=>'Grandmaster Level'],
    15 => ['color'=>'linear-gradient(135deg,#C0C0C0 50%,#FFD700 50%)', 'text'=>'#1a1a1a','name'=>'Silver-Gold',  'desc'=>'Advanced Grandmaster'],
    16 => ['color'=>'linear-gradient(135deg,#FFD700,#FFF9C4,#FFD700)',  'text'=>'#1a1a1a','name'=>'Gold Khan',    'desc'=>'Supreme Grandmaster'],
];

$all_instructors = [];
$all_locations   = [];
foreach ($members as $m) {
    if (!empty($m['instructor_name']) && !in_array($m['instructor_name'], $all_instructors))
        $all_instructors[] = $m['instructor_name'];
    if (!empty($m['training_location']) && !in_array($m['training_location'], $all_locations))
        $all_locations[] = $m['training_location'];
}
sort($all_instructors);
sort($all_locations);

include '../includes/header.php';
?>

<style>
/* ============================================================
   DESIGN TOKENS — mirrors index
   ============================================================ */
:root {
    --gold:         #D4AF37;
    --gold-light:   #F0D060;
    --gold-dark:    #A07C10;
    --red:          #ca1313;
    --black:        #0a0a0a;
    --dark:         #111;
    --mid:          #1a1a1a;
    --white:        #fff;
    --muted:        rgba(255,255,255,0.65);
    --border-gold:  rgba(212,175,55,0.15);
    --font-display: 'Cinzel', serif;
    --font-body:    'Cormorant Garamond', serif;
    --font-ui:      'Rajdhani', sans-serif;
    --ease:         0.25s cubic-bezier(0.4,0,0.2,1);
}

body { background: var(--dark); color: var(--white); }

.container {
    width: 90%;
    max-width: 1280px;
    margin: 0 auto;
}

/* ============================================================
   HERO
   ============================================================ */
.km-hero {
    position: relative;
    background: var(--black);
    padding: 100px 0 80px;
    text-align: center;
    overflow: hidden;
}
.km-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 70% 60% at 70% 120%, rgba(202,19,19,0.2) 0%, transparent 65%),
        radial-gradient(ellipse 50% 40% at 20% -10%, rgba(212,175,55,0.08) 0%, transparent 60%);
    pointer-events: none;
}
/* Corner ornaments */
.hero-corner {
    position: absolute;
    width: 72px; height: 72px;
    z-index: 3; opacity: 0.5;
}
.hero-corner--tl { top: 24px; left: 24px;    border-top: 2px solid var(--gold); border-left:  2px solid var(--gold); }
.hero-corner--tr { top: 24px; right: 24px;   border-top: 2px solid var(--gold); border-right: 2px solid var(--gold); }
.hero-corner--bl { bottom: 24px; left: 24px;  border-bottom: 2px solid var(--gold); border-left:  2px solid var(--gold); }
.hero-corner--br { bottom: 24px; right: 24px; border-bottom: 2px solid var(--gold); border-right: 2px solid var(--gold); }

.km-hero-content { position: relative; z-index: 1; }

.hero-eyebrow {
    display: inline-block;
    font-family: var(--font-ui);
    font-size: 0.72rem; font-weight: 700;
    letter-spacing: 6px; text-transform: uppercase;
    color: var(--gold);
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.3);
    padding: 6px 18px; border-radius: 2px;
    margin-bottom: 22px;
}
.km-hero h1 {
    font-family: var(--font-display);
    font-size: 3.8rem; font-weight: 900;
    color: var(--white); text-transform: uppercase;
    letter-spacing: 3px; line-height: 1.05;
    margin: 0 0 10px;
    text-shadow: 0 4px 30px rgba(0,0,0,0.9);
}
.km-hero h1 span { color: var(--gold); }

.hero-divider {
    display: flex; align-items: center; justify-content: center;
    gap: 14px; margin: 0 auto 20px; max-width: 440px;
}
.hero-divider-line      { flex: 1; height: 1px; }
.hero-divider-line.l    { background: linear-gradient(to left,  var(--gold), transparent); }
.hero-divider-line.r    { background: linear-gradient(to right, var(--gold), transparent); }
.hero-divider-diamond   { width: 7px; height: 7px; background: var(--gold); transform: rotate(45deg); flex-shrink: 0; }

.km-hero-sub {
    font-family: var(--font-body);
    font-size: 1.2rem; color: var(--muted);
    font-style: italic; font-weight: 300;
    max-width: 520px; margin: 0 auto 2.5rem;
    line-height: 1.75;
}

/* Stats row */
.km-hero-stats {
    display: inline-flex;
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.km-hero-stat {
    padding: 0.8rem 2rem;
    border-right: 1px solid rgba(212,175,55,0.15);
    text-align: center;
    transition: background var(--ease);
}
.km-hero-stat:last-child { border-right: none; }
.km-hero-stat:hover { background: rgba(212,175,55,0.05); }
.km-hero-stat strong {
    display: block;
    font-family: var(--font-display);
    font-size: 1.8rem; font-weight: 900;
    color: var(--gold);
    text-shadow: 0 0 20px rgba(212,175,55,0.3);
    line-height: 1;
    margin-bottom: 4px;
}
.km-hero-stat span {
    font-family: var(--font-ui);
    font-size: 0.65rem; letter-spacing: 3px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.45);
}

.km-levels-btn {
    display: inline-flex;
    align-items: center; gap: 8px;
    font-family: var(--font-ui);
    font-size: 0.75rem; font-weight: 700;
    letter-spacing: 3px; text-transform: uppercase;
    color: var(--gold);
    border: 1px solid rgba(212,175,55,0.3);
    background: rgba(212,175,55,0.06);
    padding: 9px 22px; border-radius: 2px;
    cursor: pointer;
    transition: background var(--ease), border-color var(--ease);
}
.km-levels-btn:hover { background: rgba(212,175,55,0.12); border-color: rgba(212,175,55,0.55); }

/* ============================================================
   LEVEL RAIL
   ============================================================ */
.km-rail-wrap {
    background: var(--mid);
    border-top: 1px solid var(--border-gold);
    border-bottom: 1px solid var(--border-gold);
    padding: 20px 0;
}
.km-rail-title {
    font-family: var(--font-ui);
    font-size: 0.6rem; letter-spacing: 4px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.25);
    text-align: center;
    margin-bottom: 12px;
}
.km-rail-scroller { overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none; }
.km-rail-scroller::-webkit-scrollbar { display: none; }
.km-rail {
    display: flex; gap: 4px;
    min-width: max-content;
    padding: 4px 24px 8px;
    justify-content: center;
}
.km-rail-item {
    display: flex; flex-direction: column;
    align-items: center; gap: 5px;
    cursor: pointer;
    padding: 8px 10px;
    border-radius: 4px;
    border: 1px solid transparent;
    transition: background var(--ease), border-color var(--ease);
}
.km-rail-item:hover { background: rgba(212,175,55,0.06); border-color: rgba(212,175,55,0.2); }
.km-rail-dot {
    width: 28px; height: 28px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.15);
    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
    flex-shrink: 0;
}
.km-rail-num {
    font-family: var(--font-display);
    font-size: 0.62rem; font-weight: 700;
    color: rgba(255,255,255,0.5);
}
.km-rail-label {
    font-family: var(--font-ui);
    font-size: 0.52rem; letter-spacing: 1px;
    color: rgba(255,255,255,0.3);
    text-align: center;
    max-width: 52px; line-height: 1.3;
}

/* ============================================================
   FILTER BAR
   ============================================================ */
.km-filter-wrap {
    background: var(--dark);
    border-bottom: 1px solid var(--border-gold);
    position: sticky; top: 0; z-index: 100;
    box-shadow: 0 4px 24px rgba(0,0,0,0.5);
}
.km-filter-inner {
    max-width: 1280px; margin: 0 auto;
    padding: 14px 24px;
    display: flex; align-items: center;
    gap: 10px; flex-wrap: wrap;
}
.km-search-wrap { position: relative; flex: 1; min-width: 200px; }
.km-search-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.3); font-size: 0.8rem; pointer-events: none; }
.km-search {
    width: 100%;
    padding: 9px 14px 9px 38px;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border-gold);
    border-radius: 2px;
    font-family: var(--font-ui);
    font-size: 0.85rem; font-weight: 500;
    color: var(--white);
    outline: none;
    transition: border-color var(--ease), background var(--ease);
}
.km-search::placeholder { color: rgba(255,255,255,0.3); }
.km-search:focus { border-color: rgba(212,175,55,0.5); background: rgba(212,175,55,0.04); }

.km-select {
    padding: 9px 32px 9px 14px;
    background: rgba(255,255,255,0.04) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23D4AF37'/%3E%3C/svg%3E") no-repeat right 12px center;
    border: 1px solid var(--border-gold);
    border-radius: 2px;
    font-family: var(--font-ui);
    font-size: 0.8rem; font-weight: 500;
    color: var(--muted);
    outline: none; cursor: pointer;
    appearance: none; -webkit-appearance: none;
    transition: border-color var(--ease);
    white-space: nowrap;
}
.km-select:focus { border-color: rgba(212,175,55,0.5); }
.km-select option { background: var(--dark); color: var(--white); }

.km-filter-chips { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
.km-chip {
    padding: 6px 14px;
    border-radius: 2px;
    font-family: var(--font-ui);
    font-size: 0.72rem; font-weight: 700;
    letter-spacing: 1px; text-transform: uppercase;
    cursor: pointer;
    border: 1px solid var(--border-gold);
    background: transparent;
    color: rgba(255,255,255,0.45);
    transition: all var(--ease);
    white-space: nowrap; user-select: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.km-chip:hover { border-color: rgba(212,175,55,0.4); color: var(--white); }
.km-chip.active { background: rgba(212,175,55,0.12); border-color: var(--gold); color: var(--gold); }

.km-filter-count {
    margin-left: auto;
    font-family: var(--font-ui);
    font-size: 0.72rem; letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.3);
    white-space: nowrap;
}
.km-filter-count span { color: var(--gold); font-weight: 700; }

/* ============================================================
   SORT + VIEW BAR
   ============================================================ */
.km-sort-bar {
    max-width: 1280px; margin: 0 auto;
    padding: 16px 24px 8px;
    display: flex; align-items: center;
    justify-content: space-between;
    gap: 12px; flex-wrap: wrap;
}
.km-sort-label {
    font-family: var(--font-ui);
    font-size: 0.68rem; letter-spacing: 3px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.3);
}
.km-sort-btns { display: flex; gap: 4px; flex-wrap: wrap; }
.km-sort-btn {
    padding: 5px 14px;
    border-radius: 2px;
    font-family: var(--font-ui);
    font-size: 0.72rem; font-weight: 600;
    letter-spacing: 1px;
    cursor: pointer;
    border: 1px solid var(--border-gold);
    background: transparent;
    color: rgba(255,255,255,0.4);
    transition: all var(--ease);
}
.km-sort-btn:hover { border-color: rgba(212,175,55,0.4); color: var(--white); }
.km-sort-btn.active { background: rgba(212,175,55,0.1); border-color: var(--gold); color: var(--gold); }

.km-view-btns { display: flex; gap: 4px; }
.km-view-btn {
    width: 32px; height: 32px;
    border-radius: 2px;
    border: 1px solid var(--border-gold);
    background: transparent;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.3);
    transition: all var(--ease);
    font-size: 0.8rem;
}
.km-view-btn:hover { border-color: rgba(212,175,55,0.4); color: var(--white); }
.km-view-btn.active { background: rgba(212,175,55,0.1); border-color: var(--gold); color: var(--gold); }

/* ============================================================
   MEMBER GRID
   ============================================================ */
.km-grid-wrap {
    max-width: 1280px; margin: 0 auto;
    padding: 16px 24px 80px;
}
.km-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}
.km-grid.list-view { grid-template-columns: 1fr; gap: 8px; }

/* ---- Card ---- */
.km-card {
    background: rgba(255,255,255,0.025);
    border: 1px solid var(--border-gold);
    border-radius: 4px;
    overflow: hidden;
    position: relative;
    transition: border-color var(--ease), box-shadow var(--ease), transform var(--ease);
}
.km-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(to right, var(--red), var(--gold));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
    z-index: 2;
}
.km-card:hover { border-color: rgba(212,175,55,0.38); box-shadow: 0 16px 48px rgba(0,0,0,0.5), 0 0 20px rgba(212,175,55,0.05); transform: translateY(-4px); }
.km-card:hover::before { transform: scaleX(1); }

.km-card-photo {
    width: 100%; height: 200px;
    position: relative; overflow: hidden;
}
.km-card-photo img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
    transition: transform 0.4s ease;
    filter: brightness(0.9) contrast(1.05);
}
.km-card:hover .km-card-photo img { transform: scale(1.05); }
.km-card-initial {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-display);
    font-size: 3.5rem; font-weight: 700;
}
.km-card-level-pill {
    position: absolute; bottom: 10px; left: 10px;
    padding: 4px 12px;
    border-radius: 2px;
    font-family: var(--font-ui);
    font-size: 0.62rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    box-shadow: 0 2px 10px rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(4px);
}
.km-card-status-dot {
    position: absolute; top: 10px; right: 10px;
    width: 9px; height: 9px;
    border-radius: 50%;
    border: 2px solid rgba(0,0,0,0.3);
    box-shadow: 0 1px 4px rgba(0,0,0,0.3);
}

.km-card-body { padding: 1.2rem 1.4rem 1rem; }
.km-card-name {
    font-family: var(--font-display);
    font-size: 0.95rem; font-weight: 700;
    color: var(--white); letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 10px; line-height: 1.3;
}
.km-card-meta { display: flex; flex-direction: column; gap: 6px; }
.km-card-meta-row {
    display: flex; align-items: center; gap: 8px;
    font-family: var(--font-body);
    font-size: 0.95rem; color: var(--muted);
}
.km-card-meta-row i { width: 13px; color: rgba(212,175,55,0.4); flex-shrink: 0; font-size: 0.7rem; }

.km-card-footer {
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 10px 1.4rem;
    border-top: 1px solid var(--border-gold);
    background: rgba(0,0,0,0.2);
}
.km-card-status-badge {
    font-family: var(--font-ui);
    font-size: 0.6rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 2px;
    padding: 4px 10px; border-radius: 2px;
}
.km-card-since {
    font-family: var(--font-ui);
    font-size: 0.65rem; letter-spacing: 1px;
    color: rgba(255,255,255,0.25);
    text-transform: uppercase;
}

/* Status colors */
.status-active    { background: rgba(34,197,94,0.12);  color: #4ade80; border: 1px solid rgba(34,197,94,0.25); }
.status-inactive  { background: rgba(156,163,175,0.1); color: #9ca3af; border: 1px solid rgba(156,163,175,0.2); }
.status-graduated { background: rgba(59,130,246,0.12); color: #60a5fa; border: 1px solid rgba(59,130,246,0.25); }
.dot-active    { background: #22c55e; }
.dot-inactive  { background: #6b7280; }
.dot-graduated { background: #3b82f6; }

/* ---- List view overrides ---- */
.km-grid.list-view .km-card { display: flex; align-items: stretch; }
.km-grid.list-view .km-card-photo { width: 80px; height: auto; min-height: 80px; flex-shrink: 0; }
.km-grid.list-view .km-card-initial { font-size: 1.8rem; }
.km-grid.list-view .km-card-level-pill { font-size: 0.55rem; bottom: 5px; left: 5px; padding: 3px 7px; }
.km-grid.list-view .km-card-status-dot { top: 6px; right: 6px; width: 7px; height: 7px; }
.km-grid.list-view .km-card-body { flex: 1; display: flex; align-items: center; gap: 1.5rem; padding: 1rem 1.4rem; }
.km-grid.list-view .km-card-name { margin-bottom: 0; min-width: 160px; font-size: 0.85rem; }
.km-grid.list-view .km-card-meta { flex-direction: row; gap: 1rem; flex-wrap: wrap; }
.km-grid.list-view .km-card-footer { flex-direction: column; justify-content: center; align-items: flex-end; gap: 4px; border-top: none; border-left: 1px solid var(--border-gold); padding: 1rem; min-width: 110px; }

/* ---- Empty state ---- */
.km-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 5rem 2rem;
}
.km-empty i { font-size: 2.5rem; margin-bottom: 1rem; display: block; color: rgba(212,175,55,0.2); }
.km-empty p {
    font-family: var(--font-body);
    font-size: 1.1rem;
    color: var(--muted); font-style: italic;
    line-height: 1.7;
}

/* ============================================================
   LEVELS MODAL
   ============================================================ */
.km-modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.75);
    z-index: 1000;
    backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
    padding: 24px;
}
.km-modal-overlay.open { display: flex; }
.km-modal {
    background: var(--mid);
    border: 1px solid var(--border-gold);
    border-radius: 4px;
    width: 100%; max-width: 820px;
    max-height: 88vh; overflow-y: auto;
    box-shadow: 0 32px 80px rgba(0,0,0,0.7);
    animation: modalIn 0.25s ease;
}
@keyframes modalIn { from { transform: translateY(20px) scale(0.97); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }

.km-modal-head {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--border-gold);
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; background: var(--mid); z-index: 1;
}
.km-modal-head h2 {
    font-family: var(--font-display);
    color: var(--white);
    font-size: 1.1rem; letter-spacing: 2px;
    text-transform: uppercase;
}
.km-modal-close {
    width: 32px; height: 32px;
    border-radius: 2px;
    border: 1px solid var(--border-gold);
    background: transparent;
    color: var(--muted);
    cursor: pointer;
    font-size: 0.9rem;
    display: flex; align-items: center; justify-content: center;
    transition: all var(--ease);
}
.km-modal-close:hover { border-color: rgba(212,175,55,0.5); color: var(--white); background: rgba(212,175,55,0.08); }

.km-modal-body { padding: 1.5rem 2rem 2rem; }
.km-modal-body p {
    font-family: var(--font-body);
    color: var(--muted); font-size: 1rem;
    font-style: italic; margin-bottom: 1.5rem;
}

.km-levels-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 8px; }
.km-level-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border-gold);
    border-radius: 4px;
    padding: 12px 14px;
    display: flex; align-items: center; gap: 12px;
    transition: background var(--ease), border-color var(--ease);
    cursor: pointer;
}
.km-level-card:hover { background: rgba(212,175,55,0.06); border-color: rgba(212,175,55,0.3); }
.km-level-swatch { width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; border: 2px solid rgba(255,255,255,0.15); box-shadow: 0 2px 8px rgba(0,0,0,0.4); }
.km-level-info { flex: 1; min-width: 0; }
.km-level-num {
    font-family: var(--font-ui);
    color: var(--gold); font-size: 0.62rem;
    font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase;
}
.km-level-name { font-family: var(--font-display); color: var(--white); font-size: 0.8rem; font-weight: 600; line-height: 1.2; }
.km-level-desc { font-family: var(--font-body); color: var(--muted); font-size: 0.78rem; margin-top: 1px; font-style: italic; }

/* ============================================================
   CTA
   ============================================================ */
.km-cta-section {
    background: linear-gradient(135deg, #0d0d0d 0%, #1a0a0a 50%, #0d0d0d 100%);
    border-top: 1px solid rgba(202,19,19,0.2);
    padding: 80px 0;
}
.km-cta-box {
    max-width: 760px; margin: 0 auto;
    text-align: center;
    border: 1px solid rgba(212,175,55,0.18);
    border-radius: 4px; padding: 4.5rem 3rem;
    position: relative; overflow: hidden;
}
.km-cta-box::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse at center, rgba(212,175,55,0.04) 0%, transparent 70%); }
.km-cta-box h2 { font-family: var(--font-display); font-size: 2rem; color: var(--white); letter-spacing: 2px; text-transform: uppercase; margin: 0 0 1rem; position: relative; }
.km-cta-box p  { font-family: var(--font-body); font-size: 1.15rem; color: var(--muted); max-width: 480px; margin: 0 auto 2.5rem; line-height: 1.75; font-style: italic; position: relative; }

.km-cta-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; position: relative; }
.btn-cta-gold {
    font-family: var(--font-ui); font-weight: 700;
    font-size: 0.8rem; letter-spacing: 3px; text-transform: uppercase;
    background: var(--gold); color: #000;
    padding: 16px 36px; text-decoration: none;
    border-radius: 2px; display: inline-flex; align-items: center; gap: 8px;
    transition: background 0.25s, box-shadow 0.25s, transform 0.2s;
    box-shadow: 0 4px 20px rgba(212,175,55,0.3);
}
.btn-cta-gold:hover { background: var(--gold-light); box-shadow: 0 6px 28px rgba(212,175,55,0.5); transform: translateY(-2px); }
.btn-cta-outline {
    font-family: var(--font-ui); font-weight: 700;
    font-size: 0.8rem; letter-spacing: 3px; text-transform: uppercase;
    border: 1.5px solid rgba(255,255,255,0.3); color: var(--muted);
    padding: 16px 36px; text-decoration: none;
    border-radius: 2px; display: inline-flex; align-items: center; gap: 8px;
    background: transparent;
    transition: border-color 0.25s, color 0.25s, transform 0.2s;
}
.btn-cta-outline:hover { border-color: var(--gold); color: var(--gold); transform: translateY(-2px); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 860px) {
    .km-hero h1 { font-size: 2.6rem; }
    .km-grid { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
    .km-hero-stats { flex-wrap: wrap; border-radius: 4px; }
    .km-hero-stat { border-right: none; border-bottom: 1px solid rgba(212,175,55,0.15); }
    .km-hero-stat:last-child { border-bottom: none; }
    .km-rail { justify-content: flex-start; }
}
@media (max-width: 640px) {
    .km-hero h1     { font-size: 1.9rem; }
    .hero-corner    { width: 48px; height: 48px; }
    .km-grid        { grid-template-columns: 1fr; }
    .km-sort-bar    { flex-direction: column; align-items: flex-start; }
    .km-cta-box     { padding: 2.5rem 1.5rem; }
    .km-cta-btns    { flex-direction: column; align-items: center; }
    .btn-cta-gold,
    .btn-cta-outline { width: 100%; justify-content: center; }
    .km-grid.list-view .km-card-body { flex-wrap: wrap; }
}
</style>

<!-- ============================================================
     HERO
     ============================================================ -->
<div class="km-hero">
    <div class="hero-corner hero-corner--tl"></div>
    <div class="hero-corner hero-corner--tr"></div>
    <div class="hero-corner hero-corner--bl"></div>
    <div class="hero-corner hero-corner--br"></div>

    <div class="km-hero-content">
        <span class="hero-eyebrow">Muayboran Certification</span>
        <h1>Khan <span>Members</span></h1>
        <div class="hero-divider">
            <div class="hero-divider-line l"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-line r"></div>
        </div>
        <p class="km-hero-sub">Certified practitioners advancing through the Khan grading system of traditional Muay Boran</p>

        <?php
        $active_count = count(array_filter($members, fn($m) => $m['status'] === 'active'));
        $grad_count   = count(array_filter($members, fn($m) => $m['status'] === 'graduated'));
        ?>
        <div class="km-hero-stats">
            <div class="km-hero-stat"><strong><?= count($members) ?></strong><span>Total</span></div>
            <div class="km-hero-stat"><strong><?= $active_count ?></strong><span>Active</span></div>
            <div class="km-hero-stat"><strong><?= $grad_count ?></strong><span>Graduated</span></div>
            <div class="km-hero-stat"><strong><?= count($all_instructors) ?></strong><span>Instructors</span></div>
        </div>

        <div style="margin-top: 1.5rem;">
            <button class="km-levels-btn" onclick="document.getElementById('levelsModal').classList.add('open')">
                <i class="fas fa-layer-group"></i> Explore All 16 Khan Levels
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
     LEVEL RAIL
     ============================================================ -->
<div class="km-rail-wrap">
    <p class="km-rail-title">Quick Level Filter — click any level</p>
    <div class="km-rail-scroller">
        <div class="km-rail">
            <?php foreach ($khan_colors as $level => $info): ?>
            <div class="km-rail-item" onclick="filterByLevel(<?= $level ?>)" title="Khan <?= $level ?> — <?= $info['name'] ?>">
                <div class="km-rail-dot" style="background:<?= $info['color'] ?>;"></div>
                <span class="km-rail-num"><?= $level ?></span>
                <span class="km-rail-label"><?= $info['name'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ============================================================
     FILTER BAR (sticky)
     ============================================================ -->
<div class="km-filter-wrap">
    <div class="km-filter-inner">
        <div class="km-search-wrap">
            <i class="fas fa-search"></i>
            <input class="km-search" type="text" id="kmSearch" placeholder="Search by name or location…" oninput="applyFilters()">
        </div>

        <div class="km-filter-chips">
            <button class="km-chip active" data-status="all"       onclick="setStatus(this,'all')">All</button>
            <button class="km-chip"        data-status="active"    onclick="setStatus(this,'active')">
                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#22c55e;"></span>Active
            </button>
            <button class="km-chip"        data-status="graduated" onclick="setStatus(this,'graduated')">
                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#3b82f6;"></span>Graduated
            </button>
            <button class="km-chip"        data-status="inactive"  onclick="setStatus(this,'inactive')">
                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#6b7280;"></span>Inactive
            </button>
        </div>

        <select class="km-select" id="kmLevelMin" onchange="applyFilters()">
            <option value="">Min Level</option>
            <?php for($l=1;$l<=16;$l++): ?><option value="<?=$l?>"><?=$l?></option><?php endfor; ?>
        </select>
        <select class="km-select" id="kmLevelMax" onchange="applyFilters()">
            <option value="">Max Level</option>
            <?php for($l=1;$l<=16;$l++): ?><option value="<?=$l?>"><?=$l?></option><?php endfor; ?>
        </select>

        <?php if (!empty($all_instructors)): ?>
        <select class="km-select" id="kmInstructor" onchange="applyFilters()">
            <option value="">All Instructors</option>
            <?php foreach($all_instructors as $ins): ?>
            <option value="<?=htmlspecialchars($ins)?>"><?=htmlspecialchars($ins)?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>

        <?php if (!empty($all_locations)): ?>
        <select class="km-select" id="kmLocation" onchange="applyFilters()">
            <option value="">All Locations</option>
            <?php foreach($all_locations as $loc): ?>
            <option value="<?=htmlspecialchars($loc)?>"><?=htmlspecialchars($loc)?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>

        <button class="km-chip" id="kmReset" onclick="resetFilters()" style="display:none;border-color:rgba(202,19,19,0.4);color:#f87171;">
            <i class="fas fa-times" style="font-size:0.6rem;"></i> Clear
        </button>

        <span class="km-filter-count" id="kmCount"><span>0</span> members</span>
    </div>
</div>

<!-- ============================================================
     SORT + VIEW BAR
     ============================================================ -->
<div class="km-sort-bar">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <span class="km-sort-label">Sort:</span>
        <div class="km-sort-btns">
            <button class="km-sort-btn active" data-sort="level-desc" onclick="setSort(this,'level-desc')">Highest Level</button>
            <button class="km-sort-btn"        data-sort="level-asc"  onclick="setSort(this,'level-asc')">Lowest Level</button>
            <button class="km-sort-btn"        data-sort="name-asc"   onclick="setSort(this,'name-asc')">Name A–Z</button>
            <button class="km-sort-btn"        data-sort="recent"     onclick="setSort(this,'recent')">Newest First</button>
        </div>
    </div>
    <div class="km-view-btns">
        <button class="km-view-btn active" id="viewGrid" onclick="setView('grid')" title="Grid view"><i class="fas fa-th-large"></i></button>
        <button class="km-view-btn"        id="viewList" onclick="setView('list')" title="List view"><i class="fas fa-list"></i></button>
    </div>
</div>

<!-- ============================================================
     MEMBER GRID
     ============================================================ -->
<div class="km-grid-wrap">
    <div class="km-grid" id="kmGrid">
        <?php if (empty($members)): ?>
        <div class="km-empty">
            <i class="fas fa-user-graduate"></i>
            <p>Our Khan members will be showcased here soon.</p>
        </div>
        <?php else: ?>
        <?php foreach ($members as $member):
            $kl        = (int)$member['current_khan_level'];
            $ci        = $khan_colors[$kl] ?? ['color'=>'#555','text'=>'#fff','name'=>'Khan '.$kl,'desc'=>''];
            $status    = $member['status'] ?? 'inactive';
            $has_photo = !empty($member['photo_path']) && file_exists('../'.$member['photo_path']);
            $initial   = strtoupper(substr($member['full_name'], 0, 1));
            $since     = !empty($member['date_joined'])    ? date('M Y', strtotime($member['date_joined']))    : '';
            $promoted  = (!empty($member['date_promoted']) && $member['date_promoted'] !== '0000-00-00')
                         ? date('M Y', strtotime($member['date_promoted'])) : '';
        ?>
        <div class="km-card"
             data-name="<?= htmlspecialchars(strtolower($member['full_name'])) ?>"
             data-status="<?= htmlspecialchars($status) ?>"
             data-level="<?= $kl ?>"
             data-instructor="<?= htmlspecialchars(strtolower($member['instructor_name'] ?? '')) ?>"
             data-location="<?= htmlspecialchars(strtolower($member['training_location'] ?? '')) ?>"
             data-joined="<?= htmlspecialchars($member['date_joined'] ?? '') ?>">

            <div class="km-card-photo" style="background:<?= $ci['color'] ?>;">
                <?php if ($has_photo): ?>
                    <img src="<?= htmlspecialchars('../'.$member['photo_path']) ?>"
                         alt="<?= htmlspecialchars($member['full_name']) ?>">
                <?php else: ?>
                    <div class="km-card-initial" style="color:<?= $ci['text'] ?>;opacity:0.4;"><?= $initial ?></div>
                <?php endif; ?>
                <div class="km-card-level-pill" style="background:<?= $ci['color'] ?>;color:<?= $ci['text'] ?>;">
                    KL <?= $kl ?> &middot; <?= htmlspecialchars($ci['name']) ?>
                </div>
                <div class="km-card-status-dot dot-<?= $status ?>"></div>
            </div>

            <div class="km-card-body">
                <div class="km-card-name"><?= htmlspecialchars($member['full_name']) ?></div>
                <div class="km-card-meta">
                    <?php if (!empty($member['instructor_name'])): ?>
                    <div class="km-card-meta-row">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span><?= htmlspecialchars($member['instructor_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($member['training_location'])): ?>
                    <div class="km-card-meta-row">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($member['training_location']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($since): ?>
                    <div class="km-card-meta-row">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Since <?= $since ?><?= $promoted ? ' &middot; Promoted '.$promoted : '' ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="km-card-footer">
                <span class="km-card-status-badge status-<?= $status ?>"><?= ucfirst($status) ?></span>
                <?php if ($since): ?><span class="km-card-since"><?= $since ?></span><?php endif; ?>
            </div>

        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="km-empty" id="kmEmpty" style="display:none;">
            <i class="fas fa-search"></i>
            <p>No members match your filters.<br><em>Try adjusting your search or clearing all filters.</em></p>
        </div>
    </div>
</div>

<!-- ============================================================
     CTA
     ============================================================ -->
<section class="km-cta-section">
    <div class="container">
        <div class="km-cta-box">
            <h2>Begin Your Khan Journey</h2>
            <p>Join our community of dedicated practitioners and start your path to mastery.</p>
            <div class="km-cta-btns">
                <a href="khan-grading.php" class="btn-cta-gold"><i class="fas fa-layer-group"></i> View Grading Structure</a>
                <a href="contact.php"      class="btn-cta-outline"><i class="fas fa-pen"></i> Submit Membership Inquiry</a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     LEVELS MODAL
     ============================================================ -->
<div class="km-modal-overlay" id="levelsModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="km-modal">
        <div class="km-modal-head">
            <h2><i class="fas fa-layer-group" style="color:var(--gold);margin-right:10px;"></i>16 Khan Levels of Mastery</h2>
            <button class="km-modal-close" onclick="document.getElementById('levelsModal').classList.remove('open')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="km-modal-body">
            <p>Click any level to filter the member list to that rank.</p>
            <div class="km-levels-grid">
                <?php foreach ($khan_colors as $level => $info): ?>
                <div class="km-level-card" onclick="filterByLevel(<?= $level ?>);document.getElementById('levelsModal').classList.remove('open');">
                    <div class="km-level-swatch" style="background:<?= $info['color'] ?>;"></div>
                    <div class="km-level-info">
                        <div class="km-level-num">Khan <?= $level ?></div>
                        <div class="km-level-name"><?= $info['name'] ?></div>
                        <div class="km-level-desc"><?= $info['desc'] ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
let currentStatus = 'all';
let currentSort   = 'level-desc';

const getCards = () => [...document.querySelectorAll('#kmGrid .km-card')];

function filterByLevel(level) {
    document.getElementById('kmLevelMin').value = level;
    document.getElementById('kmLevelMax').value = level;
    applyFilters();
    document.querySelector('.km-filter-wrap').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function setStatus(btn, status) {
    currentStatus = status;
    document.querySelectorAll('.km-chip[data-status]').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

function setSort(btn, sort) {
    currentSort = sort;
    document.querySelectorAll('.km-sort-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

function setView(v) {
    document.getElementById('kmGrid').classList.toggle('list-view', v === 'list');
    document.getElementById('viewGrid').classList.toggle('active', v === 'grid');
    document.getElementById('viewList').classList.toggle('active', v === 'list');
}

function resetFilters() {
    document.getElementById('kmSearch').value = '';
    document.getElementById('kmLevelMin').value = '';
    document.getElementById('kmLevelMax').value = '';
    const ii = document.getElementById('kmInstructor');
    const li = document.getElementById('kmLocation');
    if (ii) ii.value = '';
    if (li) li.value = '';
    currentStatus = 'all';
    document.querySelectorAll('.km-chip[data-status]').forEach(c => c.classList.remove('active'));
    document.querySelector('.km-chip[data-status="all"]').classList.add('active');
    applyFilters();
}

function applyFilters() {
    const search     = (document.getElementById('kmSearch').value || '').toLowerCase().trim();
    const levelMin   = parseInt(document.getElementById('kmLevelMin').value) || 1;
    const levelMax   = parseInt(document.getElementById('kmLevelMax').value) || 16;
    const ii         = document.getElementById('kmInstructor');
    const li         = document.getElementById('kmLocation');
    const instructor = ii ? ii.value.toLowerCase() : '';
    const location   = li ? li.value.toLowerCase() : '';

    let visible = [];
    getCards().forEach(card => {
        const name  = card.dataset.name       || '';
        const stat  = card.dataset.status     || '';
        const level = parseInt(card.dataset.level) || 0;
        const inst  = card.dataset.instructor || '';
        const loc   = card.dataset.location   || '';

        const ok = (!search     || name.includes(search) || loc.includes(search))
                && (currentStatus === 'all' || stat === currentStatus)
                && (level >= levelMin && level <= levelMax)
                && (!instructor || inst.includes(instructor))
                && (!location   || loc.includes(location));

        card.style.display = ok ? '' : 'none';
        if (ok) visible.push(card);
    });

    // Sort
    const grid = document.getElementById('kmGrid');
    visible.sort((a, b) => {
        if (currentSort === 'level-desc') return parseInt(b.dataset.level) - parseInt(a.dataset.level);
        if (currentSort === 'level-asc')  return parseInt(a.dataset.level) - parseInt(b.dataset.level);
        if (currentSort === 'name-asc')   return a.dataset.name.localeCompare(b.dataset.name);
        if (currentSort === 'recent') {
            const da = a.dataset.joined || '';
            const db = b.dataset.joined || '';
            return db.localeCompare(da);
        }
        return 0;
    });
    visible.forEach(c => grid.appendChild(c));

    document.getElementById('kmEmpty').style.display = visible.length === 0 ? 'block' : 'none';
    document.querySelector('#kmCount span').textContent = visible.length;

    const dirty = search || currentStatus !== 'all' || levelMin > 1 || levelMax < 16 || instructor || location;
    document.getElementById('kmReset').style.display = dirty ? 'inline-flex' : 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    applyFilters();
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') document.getElementById('levelsModal').classList.remove('open');
    });
});
</script>

<?php
$conn->close();
include '../includes/footer.php';
?>