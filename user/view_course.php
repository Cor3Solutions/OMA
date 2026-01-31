<?php
$page_title = "Course Viewer";
require_once '../config/database.php';
requireLogin();

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
// Use email or ID for the watermark text
$user_identity = $_SESSION['email'] ?? "User ID: $user_id";

// 1. Get Course ID securely
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Fetch Course with Permission Check
if ($user_role === 'member') {
    // Check member level permissions
    $m = $conn->query("SELECT current_khan_level FROM khan_members WHERE user_id = $user_id")->fetch_assoc();
    $lvl = $m['current_khan_level'] ?? 1;
    
    $stmt = $conn->prepare("SELECT * FROM course_materials WHERE id = ? AND status='published' AND (is_public=1 OR (khan_level_min <= ? AND khan_level_max >= ?))");
    $stmt->bind_param("iii", $course_id, $lvl, $lvl);
} else {
    // Admins can view anything
    $stmt = $conn->prepare("SELECT * FROM course_materials WHERE id = ?");
    $stmt->bind_param("i", $course_id);
}

$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// Redirect if not found or no permission
if (!$course) {
    header("Location: courses.php");
    exit;
}

// 3. Determine File Type
$file_ext = '';
if($course['file_path']) {
    $file_ext = strtolower(pathinfo($course['file_path'], PATHINFO_EXTENSION));
}

include 'includes/user_header.php'; 
?>

<style>
    /* Prevent text selection */
    body {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-color: #f4f6f9;
    }

    /* Privacy Curtain (Hidden by default) */
    #privacy-curtain {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: #000;
        z-index: 99999; /* Super high z-index */
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
    }

    /* Print Cloaking: Hide everything when printing */
    @media print {
        body * { display: none !important; }
        body:after {
            content: "Security Warning: Printing is strictly prohibited. Your attempt has been logged.";
            display: block;
            text-align: center; font-size: 18px; padding-top: 50px; color: red;
        }
    }

    /* Layout Styles */
    .viewer-container { position: relative; max-width: 1000px; margin: 2rem auto; padding: 0 15px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #555; text-decoration: none; margin-bottom: 20px; font-weight: 600; }
    .content-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); position: relative; min-height: 500px; }

    /* Watermark Overlay */
    .security-overlay {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        z-index: 50; /* Above content, below curtain */
        pointer-events: none; /* Allow clicking through */
        overflow: hidden;
        opacity: 0.15; /* Visibility of watermark */
        display: flex; flex-wrap: wrap; align-content: flex-start;
    }
    .watermark-text {
        width: 250px; height: 150px;
        display: flex; align-items: center; justify-content: center;
        transform: rotate(-30deg);
        font-size: 14px; font-weight: bold; color: #333;
    }

    /* Video & File Wrappers */
    .video-wrapper { background: black; text-align: center; width: 100%; }
    video { width: 100%; max-height: 500px; outline: none; }
    
    .file-viewer { position: relative; background: #333; min-height: 600px; }
    iframe { width: 100%; height: 800px; border: none; display: block; }
    .img-view { width: 100%; height: auto; display: block; pointer-events: none; }
</style>

<div id="privacy-curtain">
    <i class="fas fa-eye-slash fa-4x" style="margin-bottom: 20px; color: #e74c3c;"></i>
    <h2 style="margin: 0;">Content Hidden</h2>
    <p style="color: #aaa;">Security protection active. Click to resume.</p>
</div>

<div class="viewer-container">
    <a href="courses.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Library</a>

    <div class="content-card">
        
        <div class="security-overlay">
            <?php 
            // Tile the user's email 60 times across the screen
            for($i=0; $i<60; $i++) {
                echo "<div class='watermark-text'>$user_identity <br> DO NOT SHARE</div>";
            }
            ?>
        </div>

        <?php if (!empty($course['video_url'])): ?>
        <div class="video-wrapper">
            <video controls controlsList="nodownload" oncontextmenu="return false;">
                <source src="<?php echo htmlspecialchars($course['video_url']); ?>" type="video/mp4">
            </video>
        </div>
        <?php endif; ?>

        <div style="padding: 2rem; border-bottom: 1px solid #eee; position: relative; z-index: 1;">
            <span class="badge" style="background:#eee; color:#333; padding:4px 8px; border-radius:4px; font-size:0.8rem;">
                <?php echo ucfirst($course['category']); ?>
            </span>
            <h1 style="margin: 10px 0; color: #2c3e50;"><?php echo htmlspecialchars($course['title']); ?></h1>
            <p style="color: #666; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
        </div>

        <?php if (!empty($course['file_path'])): ?>
            <?php $full_file_url = SITE_URL . '/' . $course['file_path']; ?>
            
            <div class="file-viewer">
                <?php if ($file_ext === 'pdf'): ?>
                    <iframe src="<?php echo $full_file_url; ?>#toolbar=0&navpanes=0&scrollbar=0" style="position: relative; z-index: 1;"></iframe>
                
                <?php elseif (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                    <div style="padding: 20px; text-align: center;">
                        <img src="<?php echo $full_file_url; ?>" class="img-view">
                    </div>
                
                <?php else: ?>
                    <div style="padding: 50px; text-align: center; color: white;">
                        <i class="fas fa-lock fa-3x"></i>
                        <p>This file type is protected and cannot be previewed online.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    const curtain = document.getElementById('privacy-curtain');

    // 1. PRIVACY CURTAIN (Anti-Snipping Tool / Win+Shift+S)
    // When the browser loses focus (like when Snipping Tool opens), hide everything.
    window.addEventListener('blur', function() {
        curtain.style.display = 'flex';
        document.title = "Protected Content";
    });

    // When focus returns, remove the curtain
    window.addEventListener('focus', function() {
        // Slight delay prevents rapid screenshotting immediately on click
        setTimeout(() => {
            curtain.style.display = 'none';
            document.title = "<?php echo htmlspecialchars($course['title']); ?>";
        }, 300);
    });

    // 2. DISABLE RIGHT CLICK
    document.addEventListener('contextmenu', event => event.preventDefault());

    // 3. KEYBOARD BLOCKING
    document.addEventListener('keydown', function(e) {
        // Block Ctrl+S, Ctrl+P, Ctrl+U
        if (e.ctrlKey && (e.key === 's' || e.key === 'p' || e.key === 'u')) {
            e.preventDefault();
            alert('Security Alert: Saving and Printing are disabled.');
            return false;
        }
        // Block F12 (Dev Tools) - Optional
        if (e.key === 'F12') {
            e.preventDefault();
            return false;
        }
    });

    // 4. ANTI-PRINTSCREEN
    document.addEventListener('keyup', (e) => {
        if (e.key === 'PrintScreen' || e.keyCode === 44) {
            // Attempt to clear clipboard
            navigator.clipboard.writeText('');
            alert('Screenshots are disabled on this platform.');
            // Flash curtain
            curtain.style.display = 'flex';
        }
    });
</script>

<?php include 'includes/user_footer.php'; ?>