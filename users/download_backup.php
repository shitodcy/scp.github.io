<?php
require_once 'auth_check.php';
require_once '../utils/logger.php'; // <<< ADD THIS LINE

$project_root = dirname(__DIR__); // Go up one level from 'admin' to 'your_project_root'
$backup_dir = $project_root . '/backups/';

// Get the current logged-in user's username for logging context
$current_admin_username = $_SESSION['username'] ?? 'UNKNOWN_ADMIN';


if (isset($_GET['file'])) {
    $filename = basename($_GET['file']); // Sanitize filename to prevent directory traversal
    $filepath = $backup_dir . $filename;

    if (file_exists($filepath) && pathinfo($filename, PATHINFO_EXTENSION) === 'sql') {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));

        ob_clean();
        flush();
        readfile($filepath);
        log_activity("Successfully downloaded backup file: '{$filename}'.", 'INFO', $current_admin_username); // <<< ADD LOG
        exit();
    } else {
        $message = 'File backup tidak ditemukan atau tidak valid.';
        $message_type = 'danger';
        log_activity("Failed to download backup file '{$filename}': File not found or invalid type.", 'WARNING', $current_admin_username); // <<< ADD LOG
        header('Location: dashboard.php?page=backup_data&backup_message=' . urlencode($message) . '&backup_message_type=' . urlencode($message_type));
        exit();
    }
} else {
    $message = 'Nama file backup tidak diberikan.';
    $message_type = 'danger';
    log_activity("Attempted to download backup file without specifying filename.", 'WARNING', $current_admin_username); // <<< ADD LOG
    header('Location: dashboard.php?page=backup_data&backup_message=' . urlencode($message) . '&backup_message_type=' . urlencode($message_type));
    exit();
}
?>