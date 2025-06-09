<?php
require_once 'auth_check.php';

$project_root = dirname(__DIR__); // Go up one level from 'admin' to 'your_project_root'
$backup_dir = $project_root . '/backups/';

$message = '';
$message_type = '';

if (isset($_GET['file'])) {
    $filename = basename($_GET['file']); // Sanitize filename: remove path components
    $filepath = $backup_dir . $filename;

    // 3. Validate and Check File Existence
    if (file_exists($filepath) && pathinfo($filename, PATHINFO_EXTENSION) === 'sql') {
        if (unlink($filepath)) {
            $message = 'File backup <strong>' . htmlspecialchars($filename) . '</strong> berhasil dihapus.';
            $message_type = 'success';
            error_log("Backup file deleted: " . $filepath . " by user " . ($_SESSION['username'] ?? 'unknown'));
        } else {
            $message = 'Gagal menghapus file backup <strong>' . htmlspecialchars($filename) . '</strong>. Periksa izin server.';
            $message_type = 'danger';
            error_log("Failed to delete backup file: " . $filepath . " by user " . ($_SESSION['username'] ?? 'unknown'));
        }
    } else {
        $message = 'File backup tidak ditemukan atau tidak valid.';
        $message_type = 'danger';
        error_log("Attempted to delete non-existent or invalid backup file: " . $filepath . " by user " . ($_SESSION['username'] ?? 'unknown'));
    }
} else {
    $message = 'Nama file backup tidak diberikan.';
    $message_type = 'warning';
}

$_SESSION['message'] = $message;
$_SESSION['message_type'] = $message_type;

header('Location: dashboard.php?page=backup_data');
exit();
?>