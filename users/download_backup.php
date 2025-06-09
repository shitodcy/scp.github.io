<?php
require_once 'auth_check.php';

$project_root = dirname(__DIR__); // Go up one level from 'admin' to 'your_project_root'
$backup_dir = $project_root . '/backups/';

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
        exit();
    } else {
        header('Location: dashboard.php?page=backup_data&backup_message=' . urlencode('File backup tidak ditemukan atau tidak valid.') . '&backup_message_type=danger');
        exit();
    }
} else {
    header('Location: dashboard.php?page=backup_data&backup_message=' . urlencode('Nama file backup tidak diberikan.') . '&backup_message_type=danger');
    exit();
}
?>