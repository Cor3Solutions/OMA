<?php
$page_title = "Manage Events Gallery";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event'])) {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
        $location = sanitize($_POST['location']);
        $category = sanitize($_POST['category']);
        $display_order = (int)$_POST['display_order'];
        $status = $_POST['status'];
        
        $image_path = '';
        if (!empty($_FILES['image']['name'])) {
            $upload = uploadFile($_FILES['image'], UPLOAD_DIR . 'events/', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
            if ($upload['success']) {
                $image_path = 'assets/uploads/events/' . $upload['filename'];
            } else {
                $error = $upload['message'];
            }
        } else {
            $error = 'Event image is required';
        }
        
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO event_gallery (title, description, event_date, location, image_path, category, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $title, $description, $event_date, $location, $image_path, $category, $display_order, $status);
            
            if ($stmt->execute()) {
                $success = 'Event added successfully!';
            } else {
                $error = 'Failed to add event';
            }
            $stmt->close();
        }
    }
    
    elseif (isset($_POST['delete_event'])) {
        $id = (int)$_POST['id'];
        
        $result = $conn->query("SELECT image_path FROM event_gallery WHERE id = $id");
        if ($event = $result->fetch_assoc()) {
            if (!empty($event['image_path']) && file_exists($event['image_path'])) {
                deleteFile($event['image_path']);
            }
            
            if ($conn->query("DELETE FROM event_gallery WHERE id = $id")) {
                $success = 'Event deleted successfully!';
            } else {
                $error = 'Failed to delete event';
            }
        }
    }
}

$events = $conn->query("SELECT * FROM event_gallery ORDER BY display_order ASC, event_date DESC");

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
        <h2>Events Gallery Management</h2>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">
            Add New Event
        </button>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        <?php while ($event = $events->fetch_assoc()): ?>
        <div class="card" style="padding: 0; overflow: hidden;">
            <?php if (!empty($event['image_path'])): ?>
                <img src="<?php echo SITE_URL . '/' . $event['image_path']; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
            <?php endif; ?>
            <div style="padding: 1rem;">
                <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($event['title']); ?></h3>
                <?php if ($event['event_date']): ?>
                    <p style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem;">📅 <?php echo formatDate($event['event_date']); ?></p>
                <?php endif; ?>
                <?php if ($event['location']): ?>
                    <p style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem;">📍 <?php echo htmlspecialchars($event['location']); ?></p>
                <?php endif; ?>
                <p style="font-size: 0.875rem; margin-bottom: 1rem;"><?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>...</p>
                <div style="display: flex; gap: 0.5rem; justify-content: space-between; align-items: center;">
                    <span class="badge badge-<?php echo $event['status']; ?>"><?php echo ucfirst($event['status']); ?></span>
                    <form method="POST" onsubmit="return confirm('Delete this event?');">
                        <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                        <button type="submit" name="delete_event" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Add Event Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2>Add New Event</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Event Title *</label>
                <input type="text" name="title" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-textarea" required></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Event Image *</label>
                <input type="file" name="image" class="form-input" accept="image/*" required>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Event Date</label>
                    <input type="date" name="event_date" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-input">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-input" placeholder="e.g., Training, Seminar, Competition">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-input" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="action-buttons">
                <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
.modal-content { background-color: #fefefe; margin: 5% auto; padding: 2rem; border-radius: 8px; width: 90%; max-width: 800px; max-height: 85vh; overflow-y: auto; }
.modal-close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
.modal-close:hover { color: #000; }
</style>

<?php include 'includes/admin_footer.php'; ?>
