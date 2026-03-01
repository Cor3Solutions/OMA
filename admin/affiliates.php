<?php
$page_title = "Manage Affiliates";
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


// --- PHP FORM HANDLING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Add Affiliate
    if (isset($_POST['add_affiliate'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $website_url = sanitize($_POST['website_url']);
        $facebook_url = sanitize($_POST['facebook_url']);
        $contact_email = sanitize($_POST['contact_email']);
        $contact_phone = sanitize($_POST['contact_phone']);
        $display_order = (int)$_POST['display_order'];
        $status = $_POST['status'];
        
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
            logActivity($conn, 'create', 'affiliates', $conn->insert_id, $name,
                'New affiliate added. Contact: ' . ($contact_email??'N/A') .
                ' | Phone: ' . ($phone??'N/A') . ' | Status: ' . $status);;
            } else {
                $error = 'Failed to add affiliate';
            }
            $stmt->close();
        }
    }
    
    // 2. Edit Affiliate
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
        
        $current = $conn->query("SELECT logo_path FROM affiliates WHERE id = $id")->fetch_assoc();
        $logo_path = $current['logo_path'];
        
        if (!empty($_FILES['logo']['name'])) {
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
            logActivity($conn, 'edit', 'affiliates', $id, $name,
                'Affiliate record updated.');;
            } else {
                $error = 'Failed to update affiliate';
            }
            $stmt->close();
        }
    }
    
    // 3. Delete Affiliate
    elseif (isset($_POST['delete_affiliate'])) {
        $id = (int)$_POST['id'];

        // Archive before delete
        require_once 'includes/activity_helper.php';
        $fullRow = $conn->query("SELECT * FROM affiliates WHERE id = $id")->fetch_assoc();
        if ($fullRow) {
            archiveRecord($conn, 'affiliates', $id, $fullRow['name'], $fullRow);
            logActivity($conn, 'delete', 'affiliates', $id, $fullRow['name'], $fullRow['contact_email']);
        }
        
        $result = $conn->query("SELECT logo_path FROM affiliates WHERE id = $id");
        if ($affiliate = $result->fetch_assoc()) {
            if (!empty($affiliate['logo_path']) && file_exists($affiliate['logo_path'])) {
                deleteFile($affiliate['logo_path']);
            }
            
            if ($conn->query("DELETE FROM affiliates WHERE id = $id")) {
                $success = 'Affiliate deleted successfully!';
            } else {
                $error = 'Failed to delete affiliate';
            }
        }
    }
}

// Get affiliates — PAGINATED
$_per_page  = isset($_GET['per_page']) ? max(10, min(100, (int)$_GET['per_page'])) : 12;
$_cur_page  = isset($_GET['page'])     ? max(1, (int)$_GET['page']) : 1;
$_offset    = ($_cur_page - 1) * $_per_page;
$_total_aff = $conn->query("SELECT COUNT(*) as c FROM affiliates")->fetch_assoc()['c'];
if ($_cur_page > max(1, ceil($_total_aff / $_per_page))) { $_cur_page = max(1, ceil($_total_aff / $_per_page)); $_offset = ($_cur_page-1)*$_per_page; }
$affiliates = $conn->query("SELECT * FROM affiliates ORDER BY display_order ASC, name ASC LIMIT $_per_page OFFSET $_offset");

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
            <i class="fas fa-plus"></i> Add New Affiliate
        </button>
    </div>

    <div class="search-container">
        <input type="text" id="affiliateSearch" onkeyup="filterAffiliates()" placeholder=" Search affiliates by name, email, phone..." class="search-input">
    </div>
    
    <div class="affiliates-grid" id="affiliatesGrid">
        <?php while ($affiliate = $affiliates->fetch_assoc()): ?>
        <div class="affiliate-card">
            
            <span class="card-badge badge-<?php echo $affiliate['status']; ?>">
                <?php echo ucfirst($affiliate['status']); ?>
            </span>

            <div class="card-image-container">
                <?php if (!empty($affiliate['logo_path'])): ?>
                    <img src="<?php echo SITE_URL . '/' . $affiliate['logo_path']; ?>" alt="<?php echo htmlspecialchars($affiliate['name']); ?>">
                <?php else: ?>
                    <div class="no-logo"><span>No Logo</span></div>
                <?php endif; ?>
            </div>

            <div class="card-content">
                <div class="card-meta">Order: <?php echo $affiliate['display_order']; ?></div>
                <h3 class="card-title"><?php echo htmlspecialchars($affiliate['name']); ?></h3>
                
                <p class="card-description">
                    <?php 
                        $desc = htmlspecialchars($affiliate['description']);
                        echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc; 
                    ?>
                </p>

                <div class="card-contact">
                    <?php if ($affiliate['website_url']): ?>
                        <a href="<?php echo htmlspecialchars($affiliate['website_url']); ?>" target="_blank">🌐 Website</a>
                    <?php endif; ?>
                    <?php if ($affiliate['facebook_url']): ?>
                        <a href="<?php echo htmlspecialchars($affiliate['facebook_url']); ?>" target="_blank">📘 Facebook</a>
                    <?php endif; ?>
                </div>
                
                <div class="card-contact-details">
                    <?php if ($affiliate['contact_email']): ?>
                        <div>✉️ <?php echo htmlspecialchars($affiliate['contact_email']); ?></div>
                    <?php endif; ?>
                    <?php if ($affiliate['contact_phone']): ?>
                        <div>📞 <?php echo htmlspecialchars($affiliate['contact_phone']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-actions">
                <button class="btn btn-block btn-outline" onclick="editAffiliate(<?php echo htmlspecialchars(json_encode($affiliate)); ?>)">
                    Edit
                </button>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this affiliate?');">
                    <input type="hidden" name="id" value="<?php echo $affiliate['id']; ?>">
                    <button type="submit" name="delete_affiliate" class="btn btn-block btn-danger-outline">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <div id="noResults" style="display:none; text-align:center; padding: 2rem; color: #666;">
        <h3>No affiliates found matching your search.</h3>
    </div>
    <?php echo buildPaginationBar($_total_aff, $_per_page, $_cur_page, []); ?>
</div>

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
/* Search Bar */
.search-container {
    margin-bottom: 20px;
    position: relative;
}

.search-input {
    width: 100%;
    padding: 15px 20px;
    font-size: 16px;
    border: 1px solid #e1e4e8;
    border-radius: 8px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
}

.search-input:focus {
    background-color: #fff;
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

/* Grid Layout */
.affiliates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 1rem;
}

/* Card Styling */
.affiliate-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid #eee;
}

.affiliate-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Status Badge */
.card-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: bold;
    color: white;
    z-index: 2;
}
.badge-active { background-color: #28a745; }
.badge-inactive { background-color: #6c757d; }

/* Logo Area */
.card-image-container {
    height: 180px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.card-image-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.no-logo {
    color: #999;
    font-weight: bold;
    font-size: 1.2rem;
}

/* Card Body */
.card-content {
    padding: 1.25rem;
    flex-grow: 1; 
}

.card-meta {
    font-size: 0.75rem;
    color: #888;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-title {
    margin: 0 0 0.5rem 0;
    font-size: 1.25rem;
    color: #333;
}

.card-description {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 1rem;
    line-height: 1.4;
    min-height: 40px;
}

.card-contact, .card-contact-details {
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
}

.card-contact a {
    margin-right: 10px;
    color: #007bff;
    text-decoration: none;
}
.card-contact a:hover { text-decoration: underline; }

.card-contact-details div {
    margin-bottom: 3px;
    color: #555;
}

/* Card Actions */
.card-actions {
    padding: 1rem;
    background: #fcfcfc;
    border-top: 1px solid #eee;
    display: flex;
    gap: 0.5rem;
}

.btn-block {
    flex: 1;
    display: block;
    width: 100%;
    text-align: center;
}

.btn-danger-outline {
    background: transparent;
    border: 1px solid #dc3545;
    color: #dc3545;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-danger-outline:hover {
    background: #dc3545;
    color: white;
}

/* Modal Styles */
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
// Filter Search Function
function filterAffiliates() {
    const input = document.getElementById('affiliateSearch');
    const filter = input.value.toLowerCase();
    const grid = document.getElementById('affiliatesGrid');
    const cards = grid.getElementsByClassName('affiliate-card');
    let visibleCount = 0;

    for (let i = 0; i < cards.length; i++) {
        const card = cards[i];
        const textContent = card.textContent || card.innerText;
        
        if (textContent.toLowerCase().indexOf(filter) > -1) {
            card.style.display = "";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    }

    const noResultsMsg = document.getElementById('noResults');
    if (visibleCount === 0) {
        noResultsMsg.style.display = 'block';
    } else {
        noResultsMsg.style.display = 'none';
    }
}

// Edit Modal Population
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

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = "none";
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>