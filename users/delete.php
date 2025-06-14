<?php
require_once 'auth_check.php'; // Pastikan user sudah login
require_once '../config/database.php';
require_once '../utils/logger.php'; // <<< ADD THIS LINE

// Get the current logged-in user's username for logging context
$current_admin_username = $_SESSION['username'] ?? 'UNKNOWN_ADMIN';

// Cek apakah ada parameter id dari URL
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "ID user tidak ditemukan.";
    $_SESSION['message_type'] = "error";
    log_activity("Attempt to delete user with no ID provided.", 'WARNING', $current_admin_username); // <<< ADD LOG
    header("Location: dashboard.php");
    exit;
}

$userIdToDelete = (int) $_GET['id'];

// Jangan izinkan user menghapus diri sendiri
if ($userIdToDelete === (int)$_SESSION['user_id']) {
    $_SESSION['message'] = "Anda tidak bisa menghapus user sendiri.";
    $_SESSION['message_type'] = "error";
    log_activity("User '{$current_admin_username}' attempted to delete their own account (ID: {$userIdToDelete}).", 'WARNING', $current_admin_username); // <<< ADD LOG
    header("Location: dashboard.php");
    exit;
}

// Fetch username of the user being deleted for logging purposes
$deleted_username = 'UNKNOWN';
try {
    $stmt_fetch_username = $conn->prepare("SELECT username, profile_image FROM users WHERE id = :id");
    $stmt_fetch_username->bindParam(':id', $userIdToDelete, PDO::PARAM_INT);
    $stmt_fetch_username->execute();
    $user_data_to_delete = $stmt_fetch_username->fetch(PDO::FETCH_ASSOC);
    if ($user_data_to_delete) {
        $deleted_username = $user_data_to_delete['username'];
        // Also, attempt to delete the profile image from the server
        $profile_image_filename = $user_data_to_delete['profile_image'];
        $upload_dir = '../public/uploads/profile_pictures/';
        if ($profile_image_filename && file_exists($upload_dir . $profile_image_filename)) {
            unlink($upload_dir . $profile_image_filename);
            log_activity("Deleted profile image '{$profile_image_filename}' for user '{$deleted_username}' (ID: {$userIdToDelete}) during deletion.", 'INFO', $current_admin_username); // <<< ADD LOG
        }
    } else {
        log_activity("Attempt to delete non-existent user with ID: {$userIdToDelete}.", 'WARNING', $current_admin_username); // <<< ADD LOG
    }
} catch (PDOException $e) {
    error_log("Error fetching user data before deletion: " . $e->getMessage());
    log_activity("Database error fetching user data before deletion for ID '{$userIdToDelete}': " . $e->getMessage(), 'ERROR', $current_admin_username); // <<< ADD LOG
}


try {
    // Persiapkan query hapus user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userIdToDelete, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = "User berhasil dihapus.";
        $_SESSION['message_type'] = "success";
        log_activity("User '{$deleted_username}' (ID: {$userIdToDelete}) successfully deleted.", 'INFO', $current_admin_username); // <<< ADD LOG
    } else {
        $_SESSION['message'] = "User tidak ditemukan atau sudah dihapus.";
        $_SESSION['message_type'] = "error";
        log_activity("Failed to delete user ID '{$userIdToDelete}': User not found or already deleted.", 'WARNING', $current_admin_username); // <<< ADD LOG
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Terjadi kesalahan: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    log_activity("Database error deleting user ID '{$userIdToDelete}': " . $e->getMessage(), 'ERROR', $current_admin_username); // <<< ADD LOG
}

// Redirect kembali ke halaman manajemen user
header("Location: dashboard.php?page=users"); // Redirect to dashboard, specifically the users page
exit;
?>