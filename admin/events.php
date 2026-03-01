<?php
$page_title = "Manage Events Gallery";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
require_once 'includes/activity_helper.php';
$success = '';
$error = '';

// === AJAX HANDLER FOR DELETING INDIVIDUAL PHOTOS ===
// This block runs when JavaScript sends a delete request
if (isset($_POST['action']) && $_POST['action'] === 'delete_single_photo') {
    $photo_id = (int)$_POST['photo_id'];
    
    // 1. Get file path
    $q = $conn->query("SELECT image_path, event_id FROM event_photos WHERE id = $photo_id");
    if ($row = $q->fetch_assoc()) {
        // 2. Delete physical file
        if (file_exists('../' . $row['image_path'])) { // Adjust path relative to admin folder
            unlink('../' . $row['image_path']); 
        }
        
        // 3. Delete DB Record
        $conn->query("DELETE FROM event_photos WHERE id = $photo_id");
        
        // 4. (Optional) If this was the main cover, update event_gallery table
        // We simply check if the main table points to this deleted file
        $conn->query("UPDATE event_gallery SET image_path = '' WHERE image_path = '".$row['image_path']."'");
        
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Photo not found']);
    }
    exit; // Stop script execution here so we don't load the HTML
}

// === REGULAR FORM SUBMISSIONS ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. ADD NEW EVENT
    if (isset($_POST['add_event'])) {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
        $location = sanitize($_POST['location']);
        $category = sanitize($_POST['category']);
        $display_order = (int)$_POST['display_order'];
        $status = $_POST['status'];
        
        if (empty($_FILES['images']['name'][0])) {
            $error = 'At least one event image is required';
        } 
        
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO event_gallery (title, description, event_date, location, image_path, category, display_order, status) VALUES (?, ?, ?, ?, '', ?, ?, ?)");
            $stmt->bind_param("sssssis", $title, $description, $event_date, $location, $category, $display_order, $status);
            
            if ($stmt->execute()) {
                $event_id = $stmt->insert_id;
                handleImageUploads($conn, $event_id, $_FILES['images']);
                $success = "Event added successfully!";
            logActivity($conn, 'create', 'event_gallery', $conn->insert_id, $title,
                'Event added. Date: ' . ($event_date??'N/A') . ' | Location: ' . $location .
                ' | Category: ' . $category . ' | Status: ' . $status);
            } else {
                $error = 'Failed to create event record.';
            }
            $stmt->close();
        }
    }

    // 2. EDIT EVENT DETAILS
    elseif (isset($_POST['edit_event'])) {
        $event_id = (int)$_POST['event_id'];
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
        $location = sanitize($_POST['location']);
        $category = sanitize($_POST['category']);
        $display_order = (int)$_POST['display_order'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE event_gallery SET title=?, description=?, event_date=?, location=?, category=?, display_order=?, status=? WHERE id=?");
        $stmt->bind_param("sssssisi", $title, $description, $event_date, $location, $category, $display_order, $status, $event_id);

        if ($stmt->execute()) {
            // Upload NEW images if any
            if (!empty($_FILES['images']['name'][0])) {
                handleImageUploads($conn, $event_id, $_FILES['images']);
                $success = "Event updated and new photos added!";
                logActivity($conn, 'edit', 'event_gallery', $event_id, $title,
                    'Event updated with new photos. Location: ' . $location . ' | Status: ' . $status);
            } else {
                $success = "Event details updated successfully!";
                logActivity($conn, 'edit', 'event_gallery', $event_id, $title,
                    'Event details updated. Category: ' . $category . ' | Status: ' . $status);
            }
        } else {
            $error = "Failed to update event.";
        }
        $stmt->close();
    }
    
    // 3. DELETE WHOLE EVENT
    elseif (isset($_POST['delete_event'])) {
        $id = (int)$_POST['id'];
        $del_evt = $conn->query("SELECT * FROM event_gallery WHERE id = $id")->fetch_assoc();
        if ($del_evt) {
            archiveRecord($conn, 'event_gallery', $id, $del_evt['title'], $del_evt);
            logActivity($conn, 'delete', 'event_gallery', $id, $del_evt['title'],
                'Event deleted. Date: ' . ($del_evt['event_date']??'N/A') .
                ' | Category: ' . $del_evt['category'] . ' | Status was: ' . $del_evt['status']);
        }
        
        $photos_res = $conn->query("SELECT image_path FROM event_photos WHERE event_id = $id");
        while ($p = $photos_res->fetch_assoc()) {
             if (!empty($p['image_path']) && file_exists('../'.$p['image_path'])) {
                 unlink('../'.$p['image_path']);
             }
        }
        
        $conn->query("DELETE FROM event_photos WHERE event_id = $id");
        $conn->query("DELETE FROM event_gallery WHERE id = $id");
        $success = 'Event deleted successfully!';
    }
}

// Function handles physical upload and DB insert
function handleImageUploads($conn, $event_id, $files) {
    $uploaded_count = 0;
    $first_image_path = '';
    $file_count = count($files['name']);
    
    for ($i = 0; $i < $file_count; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $file_array = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i]
            ];

            // Assuming uploadFile is in your config/database.php
            $upload = uploadFile($file_array, UPLOAD_DIR . 'events/', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
            
            if ($upload['success']) {
                $path = 'assets/uploads/events/' . $upload['filename'];
                $conn->query("INSERT INTO event_photos (event_id, image_path) VALUES ($event_id, '$path')");
                if ($uploaded_count === 0) $first_image_path = $path;
                $uploaded_count++;
            }
        }
    }

    // Ensure main table has a cover image
    if ($first_image_path) {
        $check = $conn->query("SELECT image_path FROM event_gallery WHERE id = $event_id")->fetch_assoc();
        if (empty($check['image_path'])) {
            $conn->query("UPDATE event_gallery SET image_path = '$first_image_path' WHERE id = $event_id");
        }
    }
}

$events = $conn->query("SELECT * FROM event_gallery ORDER BY display_order ASC, event_date DESC");

include 'includes/admin_header.php';
?>

<style>
    /* EXISTING CSS */
    .photo-grid { display: flex; gap: 5px; margin-top: 10px; overflow-x: auto; padding-bottom: 5px; }
    .photo-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
    .more-count { display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: #eee; border-radius: 4px; font-size: 0.8rem; color: #666; font-weight: bold; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px); }
    .modal-content { background-color: #fff; margin: 2% auto; padding: 2rem; border-radius: 12px; width: 95%; max-width: 800px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    .modal-close { float: right; font-size: 28px; font-weight: bold; cursor: pointer; color: #888; transition: 0.2s; }
    .modal-close:hover { color: #333; }

    /* NEW CSS FOR EDIT GALLERY GRID */
    .edit-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
        margin-top: 10px;
        max-height: 200px;
        overflow-y: auto;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #eee;
    }
    .edit-photo-item {
        position: relative;
        height: 100px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #ddd;
    }
    .edit-photo-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .btn-delete-photo {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: 0.2s;
    }
    .btn-delete-photo:hover {
        background: #c82333;
        transform: scale(1.1);
    }
</style>

<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

<div class="admin-section">
    <div class="section-header">
        <h2>Events Gallery Management</h2>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Add New Event
        </button>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        <?php while ($event = $events->fetch_assoc()): ?>
            <?php 
                $eid = $event['id'];
                
                // 1. Get Preview Photos (Limit 5)
                $preview_photos = $conn->query("SELECT image_path FROM event_photos WHERE event_id = $eid LIMIT 5");
                $photo_count = $conn->query("SELECT count(*) as c FROM event_photos WHERE event_id = $eid")->fetch_assoc()['c'];
                
                // 2. Get ALL Photos for the Edit Modal (ID and Path)
                $all_photos_query = $conn->query("SELECT id, image_path FROM event_photos WHERE event_id = $eid");
                $all_photos = [];
                while($p = $all_photos_query->fetch_assoc()) { $all_photos[] = $p; }

                // Encode both event data and the full photo list
                $event_json = htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8');
                $photos_json = htmlspecialchars(json_encode($all_photos), ENT_QUOTES, 'UTF-8');
            ?>
        <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            <div style="position: relative; height: 200px; background: #eee;">
                <?php if (!empty($event['image_path'])): ?>
                    <img src="<?php echo SITE_URL . '/' . $event['image_path']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #aaa;">No Image</div>
                <?php endif; ?>
                <span class="badge badge-<?php echo $event['status']; ?>" style="position: absolute; top: 10px; right: 10px;">
                    <?php echo ucfirst($event['status']); ?>
                </span>
            </div>

            <div style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column;">
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.25rem;"><?php echo htmlspecialchars($event['title']); ?></h3>
                <div style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">
                    <?php if ($event['event_date']): ?>
                        <div style="margin-bottom: 4px;"><i class="far fa-calendar-alt"></i> <?php echo formatDate($event['event_date']); ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($photo_count > 0): ?>
                <div class="photo-grid">
                    <?php $shown = 0; while($p = $preview_photos->fetch_assoc()): if($shown < 4): ?>
                        <img src="<?php echo SITE_URL . '/' . $p['image_path']; ?>" class="photo-thumb">
                    <?php $shown++; endif; endwhile; ?>
                    <?php if($photo_count > 4): ?><div class="more-count">+<?php echo $photo_count - 4; ?></div><?php endif; ?>
                </div>
                <?php endif; ?>
                
                <p style="margin: 1rem 0; font-size: 0.9rem; color: #555; flex: 1;">
                    <?php echo htmlspecialchars(substr($event['description'], 0, 90)) . (strlen($event['description'])>90?'...':''); ?>
                </p>

                <div style="border-top: 1px solid #eee; padding-top: 1rem; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-sm btn-primary" onclick='openEditModal(<?php echo $event_json; ?>, <?php echo $photos_json; ?>)'>
                        <i class="fas fa-edit"></i> Edit
                    </button>

                    <form method="POST" onsubmit="return confirm('WARNING: Delete this entire event?');">
                        <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                        <button type="submit" name="delete_event" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2>Add New Event</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Description *</label><textarea name="description" class="form-textarea" required rows="4"></textarea></div>
            <div class="form-group" style="background:#f9f9f9; padding:15px; border-radius:8px; border:1px dashed #ccc;">
                <label class="form-label">Photos *</label>
                <input type="file" name="images[]" class="form-input" accept="image/*" multiple required>
            </div>
            <div class="form-grid">
                <div class="form-group"><label class="form-label">Date</label><input type="date" name="event_date" class="form-input"></div>
                <div class="form-group"><label class="form-label">Location</label><input type="text" name="location" class="form-input"></div>
            </div>
            <div class="form-grid">
                <div class="form-group"><label class="form-label">Category</label><input type="text" name="category" class="form-input"></div>
                <div class="form-group"><label class="form-label">Order</label><input type="number" name="display_order" class="form-input" value="0"></div>
            </div>
            <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <button type="submit" name="add_event" class="btn btn-primary" style="margin-top:15px; width:100%;">Create Event</button>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h2>Edit Event</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="event_id" id="edit_event_id">
            
            <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" id="edit_title" class="form-input" required></div>
            
            <div class="form-group">
                <label class="form-label">Manage Current Photos</label>
                <div id="edit_photos_container" class="edit-gallery-grid">
                    </div>
                <small style="color:#666;">Click the Red X to delete a photo immediately.</small>
            </div>

            <div class="form-group" style="background:#e8f4fd; padding:15px; border-radius:8px; border:1px dashed #2196F3; margin-top:15px;">
                <label class="form-label" style="color:#0d47a1;">Upload New Photos</label>
                <input type="file" name="images[]" class="form-input" accept="image/*" multiple>
            </div>

            <div class="form-group"><label class="form-label">Description</label><textarea name="description" id="edit_description" class="form-textarea" required rows="4"></textarea></div>
            
            <div class="form-grid">
                <div class="form-group"><label class="form-label">Date</label><input type="date" name="event_date" id="edit_event_date" class="form-input"></div>
                <div class="form-group"><label class="form-label">Location</label><input type="text" name="location" id="edit_location" class="form-input"></div>
            </div>
            <div class="form-grid">
                <div class="form-group"><label class="form-label">Category</label><input type="text" name="category" id="edit_category" class="form-input"></div>
                <div class="form-group"><label class="form-label">Order</label><input type="number" name="display_order" id="edit_display_order" class="form-input"></div>
            </div>
            <div class="form-group"><label class="form-label">Status</label><select name="status" id="edit_status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            
            <button type="submit" name="edit_event" class="btn btn-primary" style="margin-top:20px; width:100%;">Save Changes</button>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addModal').style.display = 'block';
    }

    // Updated function accepts photo list
    function openEditModal(eventData, photoList) {
        document.getElementById('edit_event_id').value = eventData.id;
        document.getElementById('edit_title').value = eventData.title;
        document.getElementById('edit_description').value = eventData.description;
        document.getElementById('edit_event_date').value = eventData.event_date;
        document.getElementById('edit_location').value = eventData.location;
        document.getElementById('edit_category').value = eventData.category;
        document.getElementById('edit_display_order').value = eventData.display_order;
        document.getElementById('edit_status').value = eventData.status;

        // Render the gallery grid
        const galleryContainer = document.getElementById('edit_photos_container');
        galleryContainer.innerHTML = ''; // Clear previous

        if(photoList.length === 0) {
            galleryContainer.innerHTML = '<p style="padding:10px; color:#999; text-align:center;">No photos found.</p>';
        } else {
            photoList.forEach(photo => {
                const div = document.createElement('div');
                div.className = 'edit-photo-item';
                div.id = 'photo-card-' + photo.id;
                
                // Construct HTML with Delete Button
                div.innerHTML = `
                    <img src="<?php echo SITE_URL; ?>/${photo.image_path}">
                    <button type="button" class="btn-delete-photo" onclick="deletePhoto(${photo.id})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                galleryContainer.appendChild(div);
            });
        }

        document.getElementById('editModal').style.display = 'block';
    }

    // AJAX Function to delete photo immediately
    function deletePhoto(photoId) {
        if(!confirm("Are you sure you want to delete this photo? It cannot be undone.")) return;

        const formData = new FormData();
        formData.append('action', 'delete_single_photo');
        formData.append('photo_id', photoId);

        fetch('', { // Post to current page
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                // Remove the image from the modal grid
                document.getElementById('photo-card-' + photoId).remove();
            } else {
                alert('Error deleting photo: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('A network error occurred.');
        });
    }

    // Close modal handling
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = "none";
        }
    }
</script>

<?php include 'includes/admin_footer.php'; ?>