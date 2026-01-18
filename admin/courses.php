<?php
$page_title = "Manage Course Materials";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_course'])) {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $category = $_POST['category'];
        $khan_level_min = (int)$_POST['khan_level_min'];
        $khan_level_max = (int)$_POST['khan_level_max'];
        $video_url = sanitize($_POST['video_url']);
        $duration_minutes = !empty($_POST['duration_minutes']) ? (int)$_POST['duration_minutes'] : null;
        $display_order = (int)$_POST['display_order'];
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        $status = $_POST['status'];
        $created_by = $_SESSION['user_id'];
        
        // Handle file upload
        $file_path = '';
        $file_type = '';
        if (!empty($_FILES['file']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'video/mp4', 'application/zip'];
            $upload = uploadFile($_FILES['file'], UPLOAD_DIR . 'courses/', $allowed_types);
            if ($upload['success']) {
                $file_path = 'assets/uploads/courses/' . $upload['filename'];
                $file_type = $_FILES['file']['type'];
            } else {
                $error = $upload['message'];
            }
        }
        
        // Handle thumbnail upload
        $thumbnail_path = '';
        if (!empty($_FILES['thumbnail']['name'])) {
            $upload = uploadFile($_FILES['thumbnail'], UPLOAD_DIR . 'courses/', ['image/jpeg', 'image/png', 'image/webp']);
            if ($upload['success']) {
                $thumbnail_path = 'assets/uploads/courses/' . $upload['filename'];
            }
        }
        
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO course_materials (title, description, category, khan_level_min, khan_level_max, file_path, file_type, video_url, thumbnail_path, duration_minutes, display_order, is_public, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiissssiiisi", $title, $description, $category, $khan_level_min, $khan_level_max, $file_path, $file_type, $video_url, $thumbnail_path, $duration_minutes, $display_order, $is_public, $status, $created_by);
            
            if ($stmt->execute()) {
                $success = 'Course material added successfully!';
            } else {
                $error = 'Failed to add course material';
            }
            $stmt->close();
        }
    }
    
    elseif (isset($_POST['delete_course'])) {
        $id = (int)$_POST['id'];
        
        $result = $conn->query("SELECT file_path, thumbnail_path FROM course_materials WHERE id = $id");
        if ($course = $result->fetch_assoc()) {
            if (!empty($course['file_path']) && file_exists($course['file_path'])) {
                deleteFile($course['file_path']);
            }
            if (!empty($course['thumbnail_path']) && file_exists($course['thumbnail_path'])) {
                deleteFile($course['thumbnail_path']);
            }
            
            if ($conn->query("DELETE FROM course_materials WHERE id = $id")) {
                $success = 'Course material deleted successfully!';
            } else {
                $error = 'Failed to delete course material';
            }
        }
    }
}

$courses = $conn->query("SELECT * FROM course_materials ORDER BY category, display_order ASC");

include 'includes/admin_header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="admin-section">
    <div class="section-header">
        <h2>Course Materials Management</h2>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">
            Add New Material
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Khan Level</th>
                    <th>Type</th>
                    <th>Duration</th>
                    <th>Access</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($course = $courses->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($course['title']); ?></strong><br>
                        <small><?php echo htmlspecialchars(substr($course['description'], 0, 60)); ?>...</small>
                    </td>
                    <td><?php echo ucfirst($course['category']); ?></td>
                    <td>Khan <?php echo $course['khan_level_min']; ?>-<?php echo $course['khan_level_max']; ?></td>
                    <td>
                        <?php if (!empty($course['video_url'])): ?>
                            Video
                        <?php elseif (!empty($course['file_path'])): ?>
                            <?php echo strtoupper(pathinfo($course['file_path'], PATHINFO_EXTENSION)); ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?php echo $course['duration_minutes'] ? $course['duration_minutes'] . ' min' : 'N/A'; ?></td>
                    <td><span class="badge" style="background: <?php echo $course['is_public'] ? '#388e3c' : '#f57c00'; ?>; color: white;"><?php echo $course['is_public'] ? 'Public' : 'Members Only'; ?></span></td>
                    <td><span class="badge badge-<?php echo $course['status'] === 'published' ? 'active' : 'inactive'; ?>"><?php echo ucfirst($course['status']); ?></span></td>
                    <td>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this course material?');">
                            <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                            <button type="submit" name="delete_course" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Course Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2>Add Course Material</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea"></textarea>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-select" required>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                        <option value="instructor">Instructor</option>
                        <option value="weapon">Weapon (Krabi Krabong)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Khan Level Range</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="number" name="khan_level_min" class="form-input" min="1" max="16" value="1" style="flex: 1;">
                        <span>to</span>
                        <input type="number" name="khan_level_max" class="form-input" min="1" max="16" value="16" style="flex: 1;">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Upload File (PDF, Video, ZIP)</label>
                <input type="file" name="file" class="form-input">
            </div>
            
            <div class="form-group">
                <label class="form-label">OR Video URL (YouTube, Vimeo)</label>
                <input type="url" name="video_url" class="form-input">
            </div>
            
            <div class="form-group">
                <label class="form-label">Thumbnail Image</label>
                <input type="file" name="thumbnail" class="form-input" accept="image/*">
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-input" value="0">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_public" style="width: auto;">
                        <span>Make publicly accessible</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="submit" name="add_course" class="btn btn-primary">Add Material</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
.modal-content { background-color: #fefefe; margin: 3% auto; padding: 2rem; border-radius: 8px; width: 90%; max-width: 900px; max-height: 90vh; overflow-y: auto; }
.modal-close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
.modal-close:hover { color: #000; }
</style>

<?php include 'includes/admin_footer.php'; ?>