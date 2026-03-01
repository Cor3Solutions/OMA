<?php
/**
 * activity_helper.php
 * ──────────────────────────────────────────────────────────────────
 * Centralized activity logging + archiving helpers.
 * Include this file once at the top of any admin page that performs
 * write operations.
 *
 * TABLE: activity_log
 *   id, admin_id, admin_name, action, module, record_id,
 *   record_label, details, ip_address, created_at
 *
 * TABLE: archives
 *   id, module, original_id, record_label, record_data (JSON),
 *   deleted_by_name, deleted_at, is_restored, restored_by_name, restored_at
 * ──────────────────────────────────────────────────────────────────
 */

if (!function_exists('logActivity')) {

    /**
     * logActivity — write a detailed entry to activity_log.
     *
     * @param mysqli  $conn
     * @param string  $action       create | edit | delete | restore | promote |
     *                              refresher | import | login | logout | view | archive
     * @param string  $module       khan_members | instructors | affiliates | users |
     *                              contact_messages | course_materials | event_gallery |
     *                              training_history | system
     * @param int     $record_id    Primary key of the affected record (0 if N/A)
     * @param string  $record_label Human-readable name / title of the record
     * @param string  $details      Free-text detail string — be as verbose as possible
     * @param array   $before       (optional) Snapshot of the row BEFORE the change
     * @param array   $after        (optional) Snapshot of the row AFTER the change
     */
    function logActivity(
        $conn,
        string $action,
        string $module,
        int    $record_id,
        string $record_label,
        string $details   = '',
        array  $before    = [],
        array  $after     = []
    ): void {

        // ── Resolve admin identity from session ──────────────────────
        $admin_id   = isset($_SESSION['user_id'])   ? (int)$_SESSION['user_id']        : 0;
        $admin_name = isset($_SESSION['user_name'])  ? $_SESSION['user_name']           : 'System';
        $admin_role = isset($_SESSION['user_role'])  ? ' [' . $_SESSION['user_role'] . ']' : '';

        // ── Build diff string if before/after provided ───────────────
        $diff_parts = [];
        if (!empty($before) && !empty($after)) {
            $skip = ['password', 'updated_at'];
            foreach ($after as $key => $newVal) {
                if (in_array($key, $skip, true)) continue;
                $oldVal = $after[$key] ?? null;          // default: same
                $oldVal = $before[$key] ?? null;
                if ($oldVal !== $newVal) {
                    $diff_parts[] = "$key: \"$oldVal\" → \"$newVal\"";
                }
            }
        }
        if (!empty($diff_parts)) {
            $details = rtrim($details, ' .') . '. Changes: ' . implode('; ', $diff_parts);
        }

        // ── Sanitise ─────────────────────────────────────────────────
        $details_esc      = $conn->real_escape_string(mb_substr($details, 0, 1000));
        $record_label_esc = $conn->real_escape_string(mb_substr($record_label, 0, 255));
        $admin_name_full  = $conn->real_escape_string($admin_name . $admin_role);
        $module_esc       = $conn->real_escape_string($module);
        $action_esc       = $conn->real_escape_string($action);
        $ip               = $_SERVER['REMOTE_ADDR'] ?? '';
        $ip_esc           = $conn->real_escape_string($ip);

        $conn->query("
            INSERT INTO activity_log
                (admin_name, action, module, record_id, record_label, details, ip_address, created_at)
            VALUES
                ('$admin_name_full', '$action_esc', '$module_esc',
                 $record_id, '$record_label_esc', '$details_esc', '$ip_esc', NOW())
        ");
    }
}


if (!function_exists('archiveRecord')) {

    /**
     * archiveRecord — soft-delete: save full row JSON before actual DELETE.
     *
     * @param mysqli $conn
     * @param string $module
     * @param int    $original_id   PK of the record being deleted
     * @param string $record_label  Human-readable identifier
     * @param array  $full_row      Entire DB row as associative array
     */
    function archiveRecord($conn, string $module, int $original_id, string $record_label, array $full_row): void
    {
        $admin_name  = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Unknown';
        $admin_role  = isset($_SESSION['user_role']) ? ' [' . $_SESSION['user_role'] . ']' : '';

        $module_esc  = $conn->real_escape_string($module);
        $label_esc   = $conn->real_escape_string(mb_substr($record_label, 0, 255));
        $data_json   = $conn->real_escape_string(json_encode($full_row, JSON_UNESCAPED_UNICODE));
        $deleted_by  = $conn->real_escape_string($admin_name . $admin_role);

        $conn->query("
            INSERT INTO archives
                (module, original_id, record_label, record_data, deleted_by_name, deleted_at, is_restored)
            VALUES
                ('$module_esc', $original_id, '$label_esc', '$data_json', '$deleted_by', NOW(), 0)
        ");
    }
}


if (!function_exists('buildDetailString')) {

    /**
     * buildDetailString — build a rich detail string from a row array.
     * Useful for create/edit logs where you want to capture key fields.
     *
     * @param array  $row        Associative array of field => value
     * @param array  $include    Fields to include (empty = all except $skip)
     * @param array  $skip       Fields to always exclude
     * @return string
     */
    function buildDetailString(array $row, array $include = [], array $skip = []): string
    {
        $default_skip = ['password', 'updated_at', 'created_at', 'photo_path',
                         'logo_path', 'file_path', 'thumbnail_path'];
        $skip = array_merge($default_skip, $skip);

        $parts = [];
        foreach ($row as $key => $val) {
            if (!empty($include) && !in_array($key, $include, true)) continue;
            if (in_array($key, $skip, true)) continue;
            if ($val === null || $val === '') continue;
            $label = ucwords(str_replace('_', ' ', $key));
            $parts[] = "$label: $val";
        }
        return implode(' | ', $parts);
    }
}