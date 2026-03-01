<?php
$page_title = "Manage Course Materials";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

// ── PAGINATION HELPER ─────────────────────────────────────────────────
function buildPaginationBar($total, $per_page, $current_page, $extra_params = []) {
    $total_pages = max(1, ceil($total / $per_page));
    $makeUrl = function($p) use ($per_page, $extra_params) {
        $params = array_merge($extra_params, ['page' => $p]);
        if ($per_page !== 10) $params['per_page'] = $per_page;
        return '?' . http_build_query($params);
    };
    $btnBase   = 'display:inline-block;padding:.35rem .7rem;border-radius:5px;border:1px solid #ddd;font-size:.85rem;text-decoration:none;color:#333;background:#fff;';
    $btnActive  = 'background:#007bff;color:#fff;border-color:#007bff;font-weight:600;';
    $btnDis    = 'opacity:.45;pointer-events:none;';
    ob_start(); ?>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-top:1rem;padding:.8rem 1rem;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;">
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <span style="color:#666;font-size:.88rem;">
                Showing <strong><?= min(($current_page-1)*$per_page+1,$total) ?>–<?= min($current_page*$per_page,$total) ?></strong>
                of <strong><?= $total ?></strong>
            </span>
            <form method="GET" style="display:flex;align-items:center;gap:.4rem;">
                <?php foreach($extra_params as $k=>$v): ?><input type="hidden" name="<?=$k?>" value="<?=htmlspecialchars($v)?>"><?php endforeach; ?>
                <input type="hidden" name="page" value="1">
                <label style="font-size:.85rem;color:#666;">Rows:</label>
                <select name="per_page" onchange="this.form.submit()" style="padding:.3rem .5rem;border:1px solid #ddd;border-radius:5px;font-size:.85rem;cursor:pointer;">
                    <?php foreach([10,25,50,100] as $opt): ?>
                        <option value="<?=$opt?>" <?=$per_page==$opt?'selected':''?>><?=$opt?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php if($total_pages>1): ?>
        <div style="display:flex;gap:.3rem;align-items:center;">
            <?php
            $pd=$current_page<=1?$btnDis:'';
            echo "<a href='{$makeUrl($current_page-1)}' style='{$btnBase}{$pd}'>&laquo;</a>";
            $rng=2;$sp=max(1,$current_page-$rng);$ep=min($total_pages,$current_page+$rng);
            if($ep-$sp<$rng*2){$sp=max(1,$ep-$rng*2);$ep=min($total_pages,$sp+$rng*2);}
            if($sp>1){echo "<a href='{$makeUrl(1)}' style='{$btnBase}'>1</a>";if($sp>2)echo "<span style='padding:.35rem .5rem;color:#999;font-size:.85rem;'>…</span>";}
            for($p=$sp;$p<=$ep;$p++){$a=$p===$current_page?$btnActive:'';echo "<a href='{$makeUrl($p)}' style='{$btnBase}{$a}'>{$p}</a>";}
            if($ep<$total_pages){if($ep<$total_pages-1)echo "<span style='padding:.35rem .5rem;color:#999;font-size:.85rem;'>…</span>";echo "<a href='{$makeUrl($total_pages)}' style='{$btnBase}'>{$total_pages}</a>";}
            $nd=$current_page>=$total_pages?$btnDis:'';
            echo "<a href='{$makeUrl($current_page+1)}' style='{$btnBase}{$nd}'>&raquo;</a>";
            ?>
        </div>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}
// ─────────────────────────────────────────────────────────────────────


// --- HANDLE FORM SUBMISSIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. SAVE (ADD OR UPDATE)
    if (isset($_POST['save_course'])) {
        $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
        $is_update = ($course_id > 0);

        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $category = $_POST['category'];
        
        // Khan Level Logic
        if (isset($_POST['use_range_toggle'])) {
            $khan_level_min = (int)$_POST['khan_min'];
            $khan_level_max = (int)$_POST['khan_max'];
            if ($khan_level_min > $khan_level_max) {
                $temp = $khan_level_min; $khan_level_min = $khan_level_max; $khan_level_max = $temp;
            }
        } else {
            $khan_level_min = (int)$_POST['khan_single'];
            $khan_level_max = (int)$_POST['khan_single'];
        }

        $video_url = sanitize($_POST['video_url']);
        $duration_minutes = !empty($_POST['duration_minutes']) ? (int)$_POST['duration_minutes'] : null;
        $display_order = (int)$_POST['display_order'];
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        $status = $_POST['status'];
        $user_id = $_SESSION['user_id'];
        
        // Get current files if updating
        $current_file = '';
        $current_thumb = '';
        if ($is_update) {
            $stmt = $conn->prepare("SELECT file_path, thumbnail_path FROM course_materials WHERE id = ?");
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $stmt->bind_result($current_file, $current_thumb);
            $stmt->fetch();
            $stmt->close();
        }

        // --- HANDLE MAIN FILE UPLOAD ---
        $file_path = $is_update ? $current_file : ''; 
        $file_type = isset($_POST['existing_file_type']) ? $_POST['existing_file_type'] : '';

        if (!empty($_FILES['file']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'video/mp4', 'application/zip'];
            $upload = uploadFile($_FILES['file'], UPLOAD_DIR . 'courses/', $allowed_types);
            
            if ($upload['success']) {
                $file_path = 'assets/uploads/courses/' . $upload['filename'];
                $file_type = $_FILES['file']['type'];
                // Delete old file if replacing
                if ($is_update && !empty($current_file) && file_exists('../' . $current_file)) {
                    unlink('../' . $current_file);
                }
            } else {
                $error = $upload['message'];
            }
        }
        
        // --- HANDLE THUMBNAIL UPLOAD ---
        $thumbnail_path = $is_update ? $current_thumb : ''; 
        if (!empty($_FILES['thumbnail']['name'])) {
            $upload = uploadFile($_FILES['thumbnail'], UPLOAD_DIR . 'courses/', ['image/jpeg', 'image/png', 'image/webp']);
            if ($upload['success']) {
                $thumbnail_path = 'assets/uploads/courses/' . $upload['filename'];
                if ($is_update && !empty($current_thumb) && file_exists('../' . $current_thumb)) {
                    unlink('../' . $current_thumb);
                }
            }
        }
        
        // --- DATABASE INSERT / UPDATE ---
        if (empty($error)) {
            if ($is_update) {
                $stmt = $conn->prepare("UPDATE course_materials SET title=?, description=?, category=?, khan_level_min=?, khan_level_max=?, file_path=?, file_type=?, video_url=?, thumbnail_path=?, duration_minutes=?, display_order=?, is_public=?, status=? WHERE id=?");
                $stmt->bind_param("sssiissssiiisi", $title, $description, $category, $khan_level_min, $khan_level_max, $file_path, $file_type, $video_url, $thumbnail_path, $duration_minutes, $display_order, $is_public, $status, $course_id);
                $msg = 'Course updated successfully!';
            logActivity($conn, 'edit', 'course_materials', $course_id??$id,
                $title??'Course',
                'Course material updated. Status: ' . ($status??'N/A'));;
            } else {
                $stmt = $conn->prepare("INSERT INTO course_materials (title, description, category, khan_level_min, khan_level_max, file_path, file_type, video_url, thumbnail_path, duration_minutes, display_order, is_public, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssiissssiiisi", $title, $description, $category, $khan_level_min, $khan_level_max, $file_path, $file_type, $video_url, $thumbnail_path, $duration_minutes, $display_order, $is_public, $status, $user_id);
                $msg = 'Course added successfully!';
            logActivity($conn, 'create', 'course_materials', $conn->insert_id, $title??'New Course',
                'Course material created. Category: ' . ($category??'N/A') .
                ' | Khan Level: ' . ($khan_level_min??'').'-'.($khan_level_max??'') .
                ' | Status: ' . ($status??'N/A'));;
            }
            
            if ($stmt->execute()) {
                $success = $msg;
            } else {
                $error = 'Failed to save course material: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
    
    // 2. ARCHIVE COURSE (Quick Action)
    elseif (isset($_POST['archive_course'])) {
        $id = (int)$_POST['id'];
        if ($conn->query("UPDATE course_materials SET status = 'archived' WHERE id = $id")) {
            $success = "Course material archived successfully.";
        } else {
            $error = "Failed to archive course.";
        }
    }

    // 3. DELETE COURSE
    elseif (isset($_POST['delete_course'])) {
        $id = (int)$_POST['id'];
        // Archive before delete
        require_once 'includes/activity_helper.php';
        $fullRow = $conn->query("SELECT * FROM course_materials WHERE id = $id")->fetch_assoc();
        if ($fullRow) {
            archiveRecord($conn, 'course_materials', $id, $fullRow['title'], $fullRow);
            logActivity($conn, 'delete', 'course_materials', $id, $fullRow['title'], 'Category: '.$fullRow['category']);
        }
        $result = $conn->query("SELECT file_path, thumbnail_path FROM course_materials WHERE id = $id");
        if ($course = $result->fetch_assoc()) {
            if (!empty($course['file_path']) && file_exists('../' . $course['file_path'])) unlink('../' . $course['file_path']);
            if (!empty($course['thumbnail_path']) && file_exists('../' . $course['thumbnail_path'])) unlink('../' . $course['thumbnail_path']);
            
            if ($conn->query("DELETE FROM course_materials WHERE id = $id")) {
                $success = 'Course material deleted successfully!';
            } else {
                $error = 'Failed to delete course material';
            }
        }
    }
}

// Paginated courses
$_per_page  = isset($_GET['per_page']) ? max(10, min(100, (int)$_GET['per_page'])) : 10;
$_cur_page  = isset($_GET['page'])     ? max(1, (int)$_GET['page']) : 1;
$_offset    = ($_cur_page - 1) * $_per_page;
$_total_courses = $conn->query("SELECT COUNT(*) as c FROM course_materials")->fetch_assoc()['c'];
if ($_cur_page > max(1, ceil($_total_courses / $_per_page))) { $_cur_page = max(1, ceil($_total_courses / $_per_page)); $_offset = ($_cur_page-1)*$_per_page; }
$courses = $conn->query("SELECT * FROM course_materials ORDER BY category, display_order ASC LIMIT $_per_page OFFSET $_offset");
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
        <button class="btn btn-primary" onclick="openModal()">+ Add New Material</button>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Target Level</th>
                    <th>Type</th>
                    <th>Access</th>
                    <th>Status</th>
                    <th width="200">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($course = $courses->fetch_assoc()): ?>
                <tr style="<?php echo $course['status'] === 'archived' ? 'opacity:0.6; background:#f9f9f9;' : ''; ?>">
                    <td>
                        <strong><?php echo htmlspecialchars($course['title']); ?></strong><br>
                        <small><?php echo htmlspecialchars(substr($course['description'], 0, 60)); ?>...</small>
                    </td>
                    <td><?php echo ucfirst($course['category']); ?></td>
                    <td>
                        <?php if ($course['khan_level_min'] == $course['khan_level_max']): ?>
                            <span class="badge" style="background:#2196F3; color:white;">Khan <?php echo $course['khan_level_min']; ?></span>
                        <?php else: ?>
                            <span class="badge" style="background:#673AB7; color:white;">Khan <?php echo $course['khan_level_min']; ?> - <?php echo $course['khan_level_max']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($course['video_url'])): ?>
                            <i class="fas fa-video"></i> Link
                        <?php elseif (!empty($course['file_path'])): ?>
                            <i class="fas fa-file"></i> <?php echo strtoupper(pathinfo($course['file_path'], PATHINFO_EXTENSION)); ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><span class="badge" style="background: <?php echo $course['is_public'] ? '#388e3c' : '#f57c00'; ?>; color: white;"><?php echo $course['is_public'] ? 'Public' : 'Members Only'; ?></span></td>
                    <td>
                        <span class="badge badge-<?php echo $course['status'] === 'published' ? 'active' : 'inactive'; ?>">
                            <?php echo ucfirst($course['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick='editCourse(<?php echo json_encode($course, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>

                            <?php if($course['status'] !== 'archived'): ?>
                            <form method="POST" onsubmit="return confirm('Archive this course? It will be hidden from users.');">
                                <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                                <button type="submit" name="archive_course" class="btn btn-sm btn-warning" title="Archive">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <form method="POST" onsubmit="return confirm('Permanently delete this course material?');">
                                <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                                <button type="submit" name="delete_course" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php echo buildPaginationBar($_total_courses, $_per_page, $_cur_page); ?>
</div>

<div id="courseModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Add Course Material</h2>
        
        <form method="POST" enctype="multipart/form-data" id="courseForm">
            <input type="hidden" name="course_id" id="course_id" value="0">
            <input type="hidden" name="existing_file_type" id="existing_file_type">

            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" id="title" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" id="description" class="form-textarea"></textarea>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                        <option value="instructor">Instructor</option>
                        <option value="weapon">Weapon (Krabi Krabong)</option>
                    </select>
                </div>
                
                <div class="form-group" style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; background: #fafafa;">
                    <label class="form-label">Target Khan Level</label>
                    <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 0.9em; cursor: pointer;">
                        <input type="checkbox" name="use_range_toggle" id="use_range_toggle" onchange="toggleRangeInputs()"> 
                        <span>Apply to a range of levels (e.g. 1-3)</span>
                    </label>

                    <div id="single_level_input">
                        <select name="khan_single" id="khan_single" class="form-select">
                            <?php for($i=1; $i<=16; $i++): echo "<option value='$i'>Khan $i</option>"; endfor; ?>
                        </select>
                    </div>

                    <div id="range_level_input" style="display:none; gap: 0.5rem; align-items: center;">
                        <select name="khan_min" id="khan_min" class="form-select" style="flex:1;">
                            <?php for($i=1; $i<=16; $i++): echo "<option value='$i'>From Khan $i</option>"; endfor; ?>
                        </select>
                        <span>to</span>
                        <select name="khan_max" id="khan_max" class="form-select" style="flex:1;">
                            <?php for($i=1; $i<=16; $i++): echo "<option value='$i' ".($i==16?'selected':'').">To Khan $i</option>"; endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group" style="background: #f0f7ff; padding: 15px; border-radius: 8px; border: 1px solid #cce5ff;">
                <label class="form-label">Digital File (PDF, Image, etc.)</label>
                
                <div id="current_file_preview" style="margin-bottom: 10px; font-size: 0.9rem;"></div>

                <input type="file" name="file" class="form-input">
                <small style="color: #666;">Upload to replace current file.</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">OR Video URL (YouTube, Vimeo)</label>
                <input type="url" name="video_url" id="video_url" class="form-input">
            </div>
            
            <div class="form-group">
                <label class="form-label">Thumbnail Image</label>
                <input type="file" name="thumbnail" class="form-input" accept="image/*">
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" id="duration_minutes" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" id="display_order" class="form-input" value="0">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_public" id="is_public" style="width: auto;">
                        <span>Make publicly accessible</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="published" selected>Published</option>
                        <option value="draft">Draft</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="submit" name="save_course" class="btn btn-primary">Save Material</button>
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
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

<script>
// --- JAVASCRIPT LOGIC ---

function toggleRangeInputs() {
    const isRange = document.getElementById('use_range_toggle').checked;
    const singleDiv = document.getElementById('single_level_input');
    const rangeDiv = document.getElementById('range_level_input');
    
    if (isRange) {
        singleDiv.style.display = 'none';
        rangeDiv.style.display = 'flex';
    } else {
        singleDiv.style.display = 'block';
        rangeDiv.style.display = 'none';
    }
}

function openModal() {
    // RESET FORM for "Add New"
    document.getElementById('courseForm').reset();
    document.getElementById('course_id').value = "0";
    document.getElementById('modalTitle').innerText = "Add Course Material";
    document.getElementById('use_range_toggle').checked = false;
    
    // RESET STATUS TO PUBLISHED
    document.getElementById('status').value = 'published';
    
    // Clear File Preview
    document.getElementById('current_file_preview').innerHTML = '';
    
    toggleRangeInputs();
    document.getElementById('courseModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('courseModal').style.display = 'none';
}

function editCourse(data) {
    // POPULATE FORM with existing data
    document.getElementById('course_id').value = data.id;
    document.getElementById('title').value = data.title;
    document.getElementById('description').value = data.description;
    document.getElementById('category').value = data.category;
    document.getElementById('video_url').value = data.video_url;
    document.getElementById('duration_minutes').value = data.duration_minutes;
    document.getElementById('display_order').value = data.display_order;
    document.getElementById('status').value = data.status;
    document.getElementById('is_public').checked = (data.is_public == 1);
    document.getElementById('existing_file_type').value = data.file_type;
    
    // Khan Level Logic
    if (data.khan_level_min == data.khan_level_max) {
        document.getElementById('use_range_toggle').checked = false;
        document.getElementById('khan_single').value = data.khan_level_min;
    } else {
        document.getElementById('use_range_toggle').checked = true;
        document.getElementById('khan_min').value = data.khan_level_min;
        document.getElementById('khan_max').value = data.khan_level_max;
    }
    toggleRangeInputs();

    document.getElementById('modalTitle').innerText = "Edit Course Material";
    
    // SHOW CURRENT FILE
    const previewDiv = document.getElementById('current_file_preview');
    if(data.file_path) {
        const fileName = data.file_path.split('/').pop();
        previewDiv.innerHTML = `
            <strong>Current File:</strong> 
            <a href="../${data.file_path}" target="_blank" style="color:#2196F3; text-decoration:underline;">${fileName}</a>
            <br><span style="color:green; font-size:0.85em;">(Upload below to replace this file)</span>
        `;
    } else {
        previewDiv.innerHTML = '<span style="color:#999;">No file currently uploaded.</span>';
    }
    
    document.getElementById('courseModal').style.display = 'block';
}

// Close modal if clicked outside
window.onclick = function(event) {
    var modal = document.getElementById('courseModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>