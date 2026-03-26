<?php
$page_title = "Training Library";
require_once '../config/database.php';
requireLogin();

$conn      = getDbConnection();
$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

$courses = [];
if ($user_role === 'member') {
    $m   = $conn->query("SELECT current_khan_level FROM khan_members WHERE user_id = $user_id")->fetch_assoc();
    $lvl = $m['current_khan_level'] ?? 1;
    $q   = "SELECT * FROM course_materials WHERE status='published' AND (is_public=1 OR khan_level_min <= $lvl) ORDER BY khan_level_min ASC, display_order ASC";
} else {
    $q = "SELECT * FROM course_materials WHERE status='published' ORDER BY category, display_order";
}

$res = $conn->query($q);
while ($row = $res->fetch_assoc()) { $courses[] = $row; }

// Group by category
$grouped = [];
foreach ($courses as $c) {
    $grouped[$c['category']][] = $c;
}

include 'includes/user_header.php';
?>

<div class="dashboard-container">

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:0.5rem;">
        <div>
            <h1 style="margin-bottom:0.25rem;">Training Library</h1>
            <p style="color:var(--text-muted);font-size:0.9rem;margin:0;font-family:'DM Sans',sans-serif;">
                <?php echo count($courses); ?> module<?php echo count($courses) !== 1 ? 's' : ''; ?> available
                <?php if ($user_role === 'member'): ?> · up to Khan <?php echo $lvl ?? 1; ?><?php endif; ?>
            </p>
        </div>
    </div>

    <?php if (empty($courses)): ?>
    <div class="dashboard-section">
        <div class="empty-state">
            <i class="fas fa-graduation-cap"></i>
            <h3>No Materials Available</h3>
            <p>Training materials will appear here when assigned to your Khan level.</p>
        </div>
    </div>
    <?php else: ?>

    <?php foreach ($grouped as $category => $items): ?>
    <div class="dashboard-section">
        <div class="section-header">
            <h2>
                <i class="fas <?php
                    $icons = ['technique'=>'fa-fist-raised','theory'=>'fa-book','history'=>'fa-landmark','conditioning'=>'fa-dumbbell','general'=>'fa-scroll'];
                    echo $icons[strtolower($category)] ?? 'fa-layer-group';
                ?>"></i>
                <?php echo ucfirst($category); ?>
            </h2>
            <span style="font-size:0.8rem;color:var(--text-muted);font-weight:600;"><?php echo count($items); ?> module<?php echo count($items)!==1?'s':''; ?></span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.25rem;">
            <?php foreach ($items as $c): ?>
            <div style="background:var(--light);border-radius:10px;overflow:hidden;border:1px solid var(--border);display:flex;flex-direction:column;transition:all 0.25s;"
                 onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 32px rgba(0,0,0,0.12)';this.style.borderColor='var(--gold)'"
                 onmouseout="this.style.transform='none';this.style.boxShadow='none';this.style.borderColor='var(--border)'">

                <div style="height:150px;background:linear-gradient(135deg,var(--crimson-dark),var(--ink));position:relative;overflow:hidden;">
                    <?php if ($c['thumbnail_path']): ?>
                        <img src="<?php echo SITE_URL.'/'.$c['thumbnail_path']; ?>" style="width:100%;height:100%;object-fit:cover;opacity:0.8;">
                    <?php else: ?>
                        <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:3rem;color:rgba(201,168,76,0.2);">
                            <i class="fas fa-scroll"></i>
                        </div>
                    <?php endif; ?>
                    <!-- Level badge -->
                    <div style="position:absolute;bottom:10px;right:10px;">
                        <span style="background:rgba(0,0,0,0.7);color:var(--gold);padding:3px 10px;border-radius:50px;font-size:0.68rem;font-weight:700;letter-spacing:0.5px;backdrop-filter:blur(4px);">
                            KL <?php echo $c['khan_level_min']; ?>
                            <?php if($c['khan_level_max'] > $c['khan_level_min']): ?>–<?php echo $c['khan_level_max']; ?><?php endif; ?>
                        </span>
                    </div>
                </div>

                <div style="padding:1.25rem;flex:1;display:flex;flex-direction:column;">
                    <div style="font-family:'Cinzel',serif;font-weight:700;font-size:0.9rem;color:var(--ink);margin-bottom:0.5rem;line-height:1.35;">
                        <?php echo htmlspecialchars($c['title']); ?>
                    </div>
                    <p style="font-size:0.82rem;color:var(--text-muted);line-height:1.5;flex:1;margin-bottom:1rem;">
                        <?php echo htmlspecialchars(mb_substr($c['description'],0,90)); ?>...
                    </p>
                    <div style="display:flex;gap:0.625rem;">
                        <?php if(!empty($c['video_url'])): ?>
                            <span style="font-size:0.72rem;color:var(--crimson);background:#fef2f2;padding:2px 8px;border-radius:50px;font-weight:600;"><i class="fas fa-play-circle"></i> Video</span>
                        <?php endif; ?>
                        <?php if(!empty($c['file_path'])): ?>
                            <span style="font-size:0.72rem;color:var(--success);background:#f0fdf4;padding:2px 8px;border-radius:50px;font-weight:600;"><i class="fas fa-file"></i> Document</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="padding:0 1.25rem 1.25rem;">
                    <a href="view_course.php?id=<?php echo $c['id']; ?>" class="btn btn-primary btn-block btn-sm">
                        <i class="fas fa-play"></i> Start Module
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include 'includes/user_footer.php'; ?>