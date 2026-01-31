<?php
require_once '../../config/database.php'; // Adjust path to your config file
requireAdmin(); // Ensure only admins access this

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

$id = (int)$_GET['id'];
$conn = getDbConnection();

try {
    // 1. Get Basic User Data
    $stmt = $conn->prepare("SELECT id, name, email, phone, role, status, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        throw new Exception('User not found');
    }

    // 2. Get Role Specific Data
    $user['instructor_data'] = null;
    $user['member_data'] = null;

    if ($user['role'] === 'instructor') {
        $stmt = $conn->prepare("SELECT * FROM instructors WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $user['instructor_data'] = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } elseif ($user['role'] === 'member') {
        $stmt = $conn->prepare("SELECT * FROM khan_members WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $user['member_data'] = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    // 3. Get List of Instructors (for the dropdown in Member edit)
    $instructors = [];
    $result = $conn->query("SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name");
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }
    
    // 4. Return combined data
    echo json_encode([
        'success' => true, 
        'user' => $user,
        'instructors_list' => $instructors
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>