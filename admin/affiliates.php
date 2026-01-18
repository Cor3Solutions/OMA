<?php
$page_title = "Manage Affiliates";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_affiliate'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $website_url = sanitize($_POST['website_url']);
        $facebook_url = sanitize($_POST['facebook_url']);
        $contact_email = sanitize($_POST['contact_email']);
        $contact_phone = sanitize($_POST['contact_phone']);
        $display_order = (int)$_POST['display_order'];
        $status = $_POST['status'];
        
        // Handle logo upload
        $logo_path = '';
        if (!empty($_FILES['logo']['name'])) {
            $upload = uploadFile($_FILES['logo'], UPLOAD_DIR . 'affiliates/', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
            if ($upload['success']) {
                $logo_path = 'assets/uploads/affiliates/' . $upload['filename'];
            } else {
                $error = $upload['message'];
            }
        }
        
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO affiliates (name, logo_path, description, website_url, facebook_url, contact_email, contact_phone, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssis", $name, $logo_path, $description, $website_url, $facebook_url, $contact_email, $contact_phone, $display_order, $status);
            
            if ($stmt->execute()) {
                $success = 'Affiliate added successfully!';
            } else {
                $error = 'Failed to add affiliate';
            }
            $stmt->close();
        }
    }
    
    elseif (isset($_POST['edit_affiliate'])) {
        $id = (int)$_POST['id'];
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $website_url = sanitize($_POST['website_url']);
        $facebook_url = sanitize($_POST['facebook_url']);
        $contact_email = sanitize($_POST['contact_email']);
        $contact_phone = sanitize($_POST['contact_phone']);
        $display_order = (int)$_POST['display_order'];
        $status = $_POST['status'];
        
        // Get current logo
        $current = $conn->query("SELECT logo_path FROM affiliates WHERE id = $id")->fetch_assoc();
        $logo_path = $current['logo_path'];
        
        // Handle new logo upload
        if (!empty($_FILES['logo']['name'])) {
            // Delete old logo
            if (!empty($logo_path) && file_exists($logo_path)) {
                deleteFile($logo_path);
            }
            
            $upload = uploadFile($_FILES['logo'], UPLOAD_DIR . 'affiliates/', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
            if ($upload['success']) {
                $logo_path = 'assets/uploads/affiliates/' . $upload['filename'];
            } else {
                $error = $upload['message'];
            }
        }
        
        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE affiliates SET name=?, logo_path=?, description=?, website_url=?, facebook_url=?, contact_email=?, contact_phone=?, display_order=?, status=? WHERE id=?");
            $stmt->bind_param("sssssssisi", $name, $logo_path, $description, $website_url, $facebook_url, $contact_email, $contact_phone, $display_order, $status, $id);
            
            if ($stmt->execute()) {
                $success = 'Affiliate updated successfully!';
            } else {
                $error = 'Failed to update affiliate';
            }
            $stmt->close();
        }
    }
    
    elseif (isset($_POST['delete_affiliate'])) {
        $id = (int)$_POST['id'];
        
        // Get logo path before deleting
        $result = $conn->query("SELECT logo_path FROM affiliates WHERE id = $id");
        if ($affiliate = $result->fetch_assoc()) {
            // Delete logo file
            if (!empty($affiliate['logo_path']) && file_exists($affiliate['logo_path'])) {
                deleteFile($affiliate['logo_path']);
            }
            
            // Delete record
            if ($conn->query("DELETE FROM affiliates WHERE id = $id")) {
                $success = 'Affiliate deleted successfully!';
            } else {
                $error = 'Failed to delete affiliate';
            }
        }
    }
}

// Get all affiliates
$affiliates = $conn->query("SELECT * FROM affiliates ORDER BY display_order ASC, name ASC");

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
        <h2>Affiliate Organizations</h2>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">
            Add New Affiliate
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Display Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($affiliate = $affiliates->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if (!empty($affiliate['logo_path'])): ?>
                            <img src="<?php echo SITE_URL . '/' . $affiliate['logo_path']; ?>" alt="Logo" style="width: 60px; height: 60px; object-fit: contain; border-radius: 4px;">
                        <?php else: ?>
                            <div style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px;">No Logo</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($affiliate['name']); ?></strong><br>
                        <small><?php echo htmlspecialchars(substr($affiliate['description'], 0, 60)); ?>...</small>
                    </td>
                    <td>
                        <?php if ($affiliate['contact_email']): ?>
                            <a href="mailto:<?php echo htmlspecialchars($affiliate['contact_email']); ?>"><?php echo htmlspecialchars($affiliate['contact_email']); ?></a><br>
                        <?php endif; ?>
                        <?php if ($affiliate['contact_phone']): ?>
                            <span><?php echo htmlspecialchars($affiliate['contact_phone']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $affiliate['display_order']; ?></td>
                    <td><span class="badge badge-<?php echo $affiliate['status']; ?>"><?php echo ucfirst($affiliate['status']); ?></span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary" onclick="editAffiliate(<?php echo htmlspecialchars(json_encode($affiliate)); ?>)">Edit</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this affiliate?');">
                                <input type="hidden" name="id" value="<?php echo $affiliate['id']; ?>">
                                <button type="submit" name="delete_affiliate" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Affiliate Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
        <h2>Add New Affiliate</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Organization Name *</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-input" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Logo Image</label>
                <input type="file" name="logo" class="form-input" accept="image/*">
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea"></textarea>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Website URL</label>
                    <input type="url" name="website_url" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" class="form-input">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contact Phone</label>
                    <input type="tel" name="contact_phone" class="form-input">
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
                <button type="submit" name="add_affiliate" class="btn btn-primary">Add Affiliate</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Affiliate Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h2>Edit Affiliate</h2>
        <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Organization Name *</label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" id="edit_display_order" class="form-input">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Logo Image (leave empty to keep current)</label>
                <input type="file" name="logo" class="form-input" accept="image/*">
                <div id="current_logo" style="margin-top: 10px;"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" id="edit_description" class="form-textarea"></textarea>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Website URL</label>
                    <input type="url" name="website_url" id="edit_website_url" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" id="edit_facebook_url" class="form-input">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" id="edit_contact_email" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contact Phone</label>
                    <input type="tel" name="contact_phone" id="edit_contact_phone" class="form-input">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" id="edit_status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="action-buttons">
                <button type="submit" name="edit_affiliate" class="btn btn-primary">Update Affiliate</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    max-height: 85vh;
    overflow-y: auto;
}

.modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.modal-close:hover {
    color: #000;
}
</style>

<script>
function editAffiliate(affiliate) {
    document.getElementById('edit_id').value = affiliate.id;
    document.getElementById('edit_name').value = affiliate.name;
    document.getElementById('edit_description').value = affiliate.description || '';
    document.getElementById('edit_website_url').value = affiliate.website_url || '';
    document.getElementById('edit_facebook_url').value = affiliate.facebook_url || '';
    document.getElementById('edit_contact_email').value = affiliate.contact_email || '';
    document.getElementById('edit_contact_phone').value = affiliate.contact_phone || '';
    document.getElementById('edit_display_order').value = affiliate.display_order;
    document.getElementById('edit_status').value = affiliate.status;
    
    const currentLogo = document.getElementById('current_logo');
    if (affiliate.logo_path) {
        currentLogo.innerHTML = '<strong>Current logo:</strong><br><img src="<?php echo SITE_URL; ?>/' + affiliate.logo_path + '" style="max-width: 150px; margin-top: 10px; border-radius: 4px;">';
    } else {
        currentLogo.innerHTML = '';
    }
    
    document.getElementById('editModal').style.display = 'block';
}
</script>

<?php include 'includes/admin_footer.php'; ?>