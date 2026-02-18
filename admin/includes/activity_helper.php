<?php
/**
 * OMA Activity Log & Archive Helper
 * Include this file in any admin page that performs actions.
 * Requires: $conn (mysqli), $_SESSION['user_id'] + $_SESSION['user_name']
 */

/**
 * Log an admin action to activity_log table.
 */
function logActivity($conn, $action, $module, $record_id, $record_label, $details = '') {
    $admin_id   = $_SESSION['user_id']   ?? 0;
    $admin_name = $_SESSION['user_name'] ?? 'Unknown';
    $ip         = $_SERVER['REMOTE_ADDR'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO activity_log (admin_user_id, admin_name, action, module, record_id, record_label, details, ip_address)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssssss", $admin_id, $admin_name, $action, $module, $record_id, $record_label, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

/**
 * Archive a record (snapshot to JSON) before deleting it.
 * Returns the archive insert ID.
 */
function archiveRecord($conn, $module, $original_id, $record_label, $record_data_array) {
    $admin_id   = $_SESSION['user_id']   ?? 0;
    $admin_name = $_SESSION['user_name'] ?? 'Unknown';
    $json       = json_encode($record_data_array, JSON_UNESCAPED_UNICODE);

    $stmt = $conn->prepare("
        INSERT INTO archives (module, original_id, record_label, record_data, deleted_by_user_id, deleted_by_name)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sissis", $module, $original_id, $record_label, $json, $admin_id, $admin_name);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return $id;
}