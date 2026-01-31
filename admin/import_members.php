<?php
$page_title = "Import Historical Data";
require_once '../config/database.php';
requireAdmin();

$conn = getDbConnection();
$message = '';
$messageType = '';

// Handle CSV Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        
        if ($handle !== FALSE) {
            $successCount = 0;
            $errorCount = 0;
            $row = 0;
            
            // Get Instructor Map (Name -> ID) to auto-assign instructors if found in CSV
            $instructors = [];
            $instResult = $conn->query("SELECT id, name FROM instructors");
            while($inst = $instResult->fetch_assoc()) {
                $instructors[strtolower($inst['name'])] = $inst['id'];
            }

            // Begin Transaction
            $conn->begin_transaction();

            try {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;
                    // Skip header row if it exists (checks if first cell is "Full Name")
                    if ($row === 1 && (strtolower($data[0]) === 'full name' || strtolower($data[0]) === 'name')) {
                        continue;
                    }

                    // Expected CSV Structure:
                    // 0: Full Name (Required)
                    // 1: Email (Optional - will generate dummy if empty)
                    // 2: Phone (Optional)
                    // 3: Khan Level (Required - integer)
                    // 4: Khan Color (Optional)
                    // 5: Date Promoted (YYYY-MM-DD)
                    // 6: Instructor Name (Optional - matches exact name in system)
                    // 7: Location (Optional)

                    $fullName = isset($data[0]) ? sanitize(trim($data[0])) : '';
                    if (empty($fullName)) continue; // Skip empty rows

                    $email = isset($data[1]) ? sanitize(trim($data[1])) : '';
                    // Generate placeholder email if missing: name_timestamp@archive.local
                    if (empty($email)) {
                        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $fullName)));
                        $email = $slug . '_' . time() . '@archive.local';
                    }

                    $phone = isset($data[2]) ? sanitize(trim($data[2])) : '';
                    $khanLevel = isset($data[3]) ? (int)$data[3] : 1;
                    $khanColor = isset($data[4]) ? sanitize(trim($data[4])) : '';
                    
                    // Handle Date
                    $datePromoted = isset($data[5]) ? trim($data[5]) : '';
                    if (empty($datePromoted) || $datePromoted == '0000-00-00') {
                        $datePromoted = date('Y-m-d'); // Default to today if missing
                    }
                    // Basic date validation could go here

                    // Match Instructor
                    $instructorName = isset($data[6]) ? strtolower(trim($data[6])) : '';
                    $instructorId = isset($instructors[$instructorName]) ? $instructors[$instructorName] : null;

                    $location = isset($data[7]) ? sanitize(trim($data[7])) : '';
                    
                    // Status defaults to 'graduated' for historical imports, or 'active'
                    $status = 'graduated'; 

                    // Insert into khan_members (user_id is NULL)
                    $stmt = $conn->prepare("INSERT INTO khan_members (user_id, full_name, email, phone, current_khan_level, khan_color, date_joined, date_promoted, instructor_id, training_location, status, notes) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Imported from History')");
                    
                    // Use date_promoted as date_joined for historical data
                    $stmt->bind_param("sssissssss", $fullName, $email, $phone, $khanLevel, $khanColor, $datePromoted, $datePromoted, $instructorId, $location, $status);

                    if ($stmt->execute()) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }

                $conn->commit();
                $message = "Import Successful! Imported: $successCount, Errors/Skipped: $errorCount";
                $messageType = "success";

            } catch (Exception $e) {
                $conn->rollback();
                $message = "Error during import: " . $e->getMessage();
                $messageType = "error";
            }

            fclose($handle);
        }
    } else {
        $message = "Please upload a valid CSV file.";
        $messageType = "error";
    }
}

include 'includes/admin_header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="admin-section">
    <div class="section-header">
        <h2><i class="fas fa-file-import"></i> Import Historical Data</h2>
        <a href="khan_members.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Members</a>
    </div>

    <div class="info-box" style="background: #e3f2fd; border-left: 4px solid #1976d2; padding: 1rem; margin-bottom: 1.5rem;">
        <strong><i class="fas fa-info-circle"></i> How to use:</strong>
        <ol style="margin-top: 0.5rem; margin-left: 1.5rem;">
            <li>Prepare your Excel file and <strong>Save As CSV (Comma delimited)</strong>.</li>
            <li>Ensure the columns follow the order below exactly.</li>
            <li><strong>Note:</strong> These members will NOT have login accounts created. They will appear in the "Members" list, where you can link them to user accounts later if needed.</li>
        </ol>
    </div>

    <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Upload CSV File</h3>
            <form method="POST" enctype="multipart/form-data" style="margin-top: 1rem;">
                <div class="form-group">
                    <label class="form-label">Select CSV File</label>
                    <input type="file" name="csv_file" class="form-input" accept=".csv" required>
                </div>
                <div class="action-buttons">
                    <button type="submit" name="import_csv" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i> Upload and Import
                    </button>
                </div>
            </form>
        </div>

        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border: 1px solid #ddd;">
            <h3>Required CSV Column Order</h3>
            <p style="color: #666; margin-bottom: 1rem;">Do not change the order of columns.</p>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;"><strong>Col 1:</strong> Full Name <span style="color: red;">*</span></li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;"><strong>Col 2:</strong> Email (Optional)</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;"><strong>Col 3:</strong> Phone</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;"><strong>Col 4:</strong> Khan Level (Number 1-16) <span style="color: red;">*</span></li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;"><strong>Col 5:</strong> Khan Color (e.g., "White")</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;"><strong>Col 6:</strong> Date Promoted (YYYY-MM-DD)</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;"><strong>Col 7:</strong> Instructor Name (Must match system spelling)</li>
                <li style="padding: 0.5rem 0;"><strong>Col 8:</strong> Location</li>
            </ul>
            <div style="margin-top: 1rem;">
                <a href="#" onclick="downloadTemplate()" style="color: #1976d2; font-weight: bold; text-decoration: underline;">
                    <i class="fas fa-download"></i> Download Sample CSV
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function downloadTemplate() {
    const csvContent = "data:text/csv;charset=utf-8," 
        + "Full Name,Email,Phone,Khan Level,Khan Color,Date Promoted,Instructor Name,Location\n"
        + "John Doe,john@example.com,09123456789,1,White,2023-01-01,Ajarn Mike,Quezon City\n"
        + "Jane Smith,,09987654321,5,Green/White,2024-05-15,,Manila";
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "khan_import_template.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php include 'includes/admin_footer.php'; ?>