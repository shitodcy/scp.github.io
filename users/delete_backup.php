<?php
require_once 'auth_check.php';
require_once '../config/database.php'; // Required for database constants if any
require_once '../utils/logger.php'; // <<< ADD THIS LINE

$project_root = dirname(__DIR__); // Go up one level from 'admin' to 'your_project_root'
$backup_dir = $project_root . '/backups/';

$message = '';
$message_type = '';

// Get the current logged-in user's username for logging context
$current_admin_username = $_SESSION['username'] ?? 'UNKNOWN_ADMIN';


if (isset($_GET['file'])) {
    $filename = basename($_GET['file']); // Sanitize filename: remove path components
    $filepath = $backup_dir . $filename;

    // Validate and Check File Existence
    if (file_exists($filepath) && pathinfo($filename, PATHINFO_EXTENSION) === 'sql') {
        if (unlink($filepath)) {
            $message = 'File backup <strong>' . htmlspecialchars($filename) . '</strong> berhasil dihapus.';
            $message_type = 'success';
            // error_log("Backup file deleted: " . $filepath . " by user " . ($_SESSION['username'] ?? 'unknown')); // Replaced by log_activity
            log_activity("Successfully deleted backup file: '{$filename}'.", 'INFO', $current_admin_username); // <<< ADD LOG
        } else {
            $message = 'Gagal menghapus file backup <strong>' . htmlspecialchars($filename) . '</strong>. Periksa izin server.';
            $message_type = 'danger';
            // error_log("Failed to delete backup file: " . $filepath . " by user " . ($_SESSION['username'] ?? 'unknown')); // Replaced by log_activity
            log_activity("Failed to delete backup file '{$filename}': Permissions issue or unknown error.", 'ERROR', $current_admin_username); // <<< ADD LOG
        }
    } else {
        $message = 'File backup tidak ditemukan atau tidak valid.';
        $message_type = 'danger';
        // error_log("Attempted to delete non-existent or invalid backup file: " . $filepath . " by user " . ($_SESSION['username'] ?? 'unknown')); // Replaced by log_activity
        log_activity("Attempted to delete non-existent or invalid backup file: '{$filename}'.", 'WARNING', $current_admin_username); // <<< ADD LOG
    }
} else {
    $message = 'Nama file backup tidak diberikan.';
    $message_type = 'warning';
    log_activity("Attempted to delete backup file without specifying filename.", 'WARNING', $current_admin_username); // <<< ADD LOG
}

$_SESSION['message'] = $message;
$_SESSION['message_type'] = $message_type;

header('Location: dashboard.php?page=backup_data');
exit();
?>