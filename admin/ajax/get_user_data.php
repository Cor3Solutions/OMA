<?php
require_once '../../config/database.php';
requireAdmin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

$user_id = (int)$_GET['id'];
$conn = getDbConnection();

// Get user data
$user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");

if ($user_query->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = $user_query->fetch_assoc();

// Get instructor data if role is instructor
$instructor_data = null;
if ($user['role'] === 'instructor') {
    $instructor_query = $conn->query("SELECT * FROM instructors WHERE user_id = $user_id");
    if ($instructor_query->num_rows > 0) {
        $instructor_data = $instructor_query->fetch_assoc();
    }
}

// Get member data if role is member
$member_data = null;
if ($user['role'] === 'member') {
    $member_query = $conn->query("SELECT * FROM khan_members WHERE user_id = $user_id");
    if ($member_query->num_rows > 0) {
        $member_data = $member_query->fetch_assoc();
    }
}

// Get instructors list for member dropdown
$instructors_list = [];
$instructors_query = $conn->query("SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name");
while ($instructor = $instructors_query->fetch_assoc()) {
    $instructors_list[] = $instructor;
}

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'role' => $user['role'],
        'status' => $user['status'],
        'instructor_data' => $instructor_data,
        'member_data' => $member_data,
        'instructors_list' => $instructors_list
    ]
]);