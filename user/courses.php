<?php
$page_title = "My Courses";
require_once '../config/database.php';
requireLogin();

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// FETCH COURSES
$courses = [];
if ($user_role === 'member') {
    $m = $conn->query("SELECT current_khan_level FROM khan_members WHERE user_id = $user_id")->fetch_assoc();
    $lvl = $m['current_khan_level'] ?? 1;
    $q = "SELECT * FROM course_materials WHERE status='published' AND (is_public=1 OR (khan_level_min <= $lvl AND khan_level_max >= $lvl)) ORDER BY display_order";
} else {
    $q = "SELECT * FROM course_materials WHERE status='published' ORDER BY category, display_order";
}

$res = $conn->query($q);
while ($row = $res->fetch_assoc()) {
    $courses[] = $row;
}

include 'includes/user_header.php';
?>

<style>
    :root {
        --primary: #2c3e50;
        --bg: #f4f6f9;
        --card: #ffffff;
        --radius: 12px;
    }

    body {
        background-color: var(--bg);
        font-family: -apple-system, sans-serif;
        color: #333;
        margin: 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-header h2 {
        margin: 0;
        color: var(--primary);
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .course-card {
        background: var(--card);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
    }

    .course-img {
        height: 180px;
        background: #eee;
        position: relative;
    }

    .course-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .play-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .course-card:hover .play-overlay {
        opacity: 1;
    }

    .course-body {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .badge {
        display: inline-block;
        padding: 4px 8px;
        background: #f0f2f5;
        font-size: 0.75rem;
        border-radius: 4px;
        margin-bottom: 0.5rem;
        color: #666;
    }

    .btn-view {
        margin-top: auto;
        padding: 12px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    /* MODAL STYLES (Mobile Optimized) */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        z-index: 9999;
        flex-direction: column;
    }

    .modal-header {
        padding: 15px;
        background: #1a1a1a;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .close-btn {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        padding: 10px;
    }

    .modal-scroll-area {
        flex: 1;
        overflow-y: auto;
        padding: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        -webkit-overflow-scrolling: touch;
    }

    .video-container {
        width: 100%;
        max-width: 1000px;
        background: black;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    video {
        width: 100%;
        max-height: 50vh;
        display: block;
    }

    .materials-container {
        width: 100%;
        max-width: 1000px;
        padding: 20px;
        box-sizing: border-box;
        background: #222;
        min-height: 100%;
    }

    .material-item {
        background: #333;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 15px;
        color: white;
    }

    .mat-icon {
        font-size: 24px;
        color: #3498db;
    }

    .mat-info h4 {
        margin: 0 0 5px 0;
        font-size: 1rem;
    }

    .mat-info p {
        margin: 0;
        font-size: 0.8rem;
        color: #aaa;
    }

    .btn-download {
        margin-left: auto;
        padding: 8px 15px;
        background: #444;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    /* Phone specific */
    @media (max-width: 768px) {
        .course-grid {
            grid-template-columns: 1fr;
        }

        .course-card {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .play-overlay {
            opacity: 1;
            background: rgba(0, 0, 0, 0.1);
        }

        /* Always show play icon slightly on phone */
        .btn-view {
            padding: 15px;
            font-size: 1rem;
        }

        /* Larger touch target */
    }
</style>

<div class="container">
    <div class="page-header">
        <h2><i class="fas fa-graduation-cap"></i> Training Library</h2>
    </div>

    <?php if (empty($courses)): ?>
        <div style="text-align:center; padding: 3rem; background:white; border-radius:12px;">
            <h3>No materials available</h3>
            <p>Training materials will appear here when assigned to your level.</p>
        </div>
    <?php else: ?>
        <div class="course-grid">
            <?php foreach ($courses as $c): ?>
                <div class="course-card">
                    <div class="course-img">
                        <?php if ($c['thumbnail_path']): ?>
                            <img src="<?php echo SITE_URL . '/' . $c['thumbnail_path']; ?>">
                        <?php endif; ?>
                        <div class="play-overlay"><i class="fas fa-play-circle fa-3x"
                                style="color:white; drop-shadow:0 2px 4px rgba(0,0,0,0.5);"></i></div>
                    </div>
                    <div class="course-body">
                        <span class="badge"><?php echo ucfirst($c['category']); ?></span>
                        <h3 style="margin:0 0 10px 0;"><?php echo htmlspecialchars($c['title']); ?></h3>
                        <p style="color:#666; font-size:0.9rem; line-height:1.4; flex:1; margin-bottom:15px;">
                            <?php echo htmlspecialchars(substr($c['description'], 0, 80)) . '...'; ?>
                        </p>
                        <a href="view_course.php?id=<?php echo $c['id']; ?>" class="btn-view" style="text-decoration:none;">
                            Start Module
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="courseModal" class="modal-overlay">
    <div class="modal-header">
        <h3 id="mTitle"
            style="margin:0; font-size:1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:80%;">
            Title</h3>
        <button class="close-btn" onclick="closeViewer()">&times;</button>
    </div>

    <div class="modal-scroll-area">
        <div id="videoSection" class="video-container" style="display:none;">
            <video id="mainVideo" controls controlsList="nodownload" oncontextmenu="return false;">
                Your browser does not support video.
            </video>
        </div>

        <div class="materials-container">
            <h4 style="color:#eee; margin-top:0; border-bottom:1px solid #444; padding-bottom:10px;">
                <i class="fas fa-folder-open"></i> Course Materials
            </h4>

            <div id="materialsList">
            </div>
        </div>
    </div>
</div>

<script>
    function openViewer(course) {
        document.getElementById('mTitle').textContent = course.title;
        const vidSec = document.getElementById('videoSection');
        const vidPlayer = document.getElementById('mainVideo');
        const matList = document.getElementById('materialsList');

        // Setup Video
        if (course.video_url) {
            vidSec.style.display = 'block';
            vidPlayer.src = course.video_url;
        } else {
            vidSec.style.display = 'none';
            vidPlayer.pause();
            vidPlayer.src = "";
        }

        // Setup Materials
        matList.innerHTML = '';

        // 1. Add the main file if exists (PDF/Image)
        if (course.file_path) {
            const ext = course.file_path.split('.').pop().toLowerCase();
            let icon = 'fa-file-alt';
            if (ext === 'pdf') icon = 'fa-file-pdf';
            if (['jpg', 'png'].includes(ext)) icon = 'fa-file-image';

            // Check if it's displayable inline or download
            let actionBtn = '';
            if (ext === 'pdf' || ['jpg', 'png', 'jpeg'].includes(ext)) {
                // For simple view, we just link to it or embed. 
                // Since mobile PDF embedding is tricky, a new tab or dedicated view is safer.
                actionBtn = `<a href="${course.file_path}" target="_blank" class="btn-download">View ${ext.toUpperCase()}</a>`;
            } else {
                actionBtn = `<span style="color:#777; font-size:0.8rem;">Preview unavailable</span>`;
            }

            const html = `
            <div class="material-item">
                <i class="fas ${icon} mat-icon"></i>
                <div class="mat-info">
                    <h4>Course Reference Document</h4>
                    <p>Primary material for this module</p>
                </div>
                ${actionBtn}
            </div>
            
            ${['jpg', 'jpeg', 'png'].includes(ext) ? `<img src="${course.file_path}" style="width:100%; border-radius:8px; margin-top:10px;">` : ''}
        `;
            matList.innerHTML += html;
        }

        if (!course.video_url && !course.file_path) {
            matList.innerHTML = '<p style="color:#777; text-align:center;">No digital assets found for this course.</p>';
        }

        document.getElementById('courseModal').style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Lock scroll
    }

    function closeViewer() {
        const vidPlayer = document.getElementById('mainVideo');
        vidPlayer.pause();
        vidPlayer.src = "";
        document.getElementById('courseModal').style.display = 'none';
        document.body.style.overflow = 'auto'; // Unlock scroll
    }
</script>

<?php include 'includes/user_footer.php'; ?>