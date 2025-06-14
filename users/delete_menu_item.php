<?php
session_start();
require_once 'auth_check.php';
require_once '../config/database.php';
require_once '../utils/logger.php'; // <<< ADD THIS LINE

$item_id = $_GET['id'] ?? null;

// Get the current logged-in user's username for logging context
$current_admin_username = $_SESSION['username'] ?? 'UNKNOWN_ADMIN';

if (!$item_id) {
    $_SESSION['message'] = "ID item menu tidak ditemukan.";
    $_SESSION['message_type'] = "danger";
    log_activity("Attempt to delete menu item with missing ID.", 'WARNING', $current_admin_username); // <<< ADD LOG
    header("Location: dashboard.php?page=menu_items");
    exit();
}

// Fetch item data (especially name and image_url) before deletion for logging and file removal
$item_name_to_delete = 'Unknown Item';
$image_url_to_delete = null;

try {
    $stmt_get_item = $conn->prepare("SELECT name, image_url FROM menu_items WHERE id = :id");
    $stmt_get_item->bindParam(':id', $item_id, PDO::PARAM_INT);
    $stmt_get_item->execute();
    $item_data_for_log = $stmt_get_item->fetch(PDO::FETCH_ASSOC);

    if ($item_data_for_log) {
        $item_name_to_delete = $item_data_for_log['name'];
        $image_url_to_delete = $item_data_for_log['image_url'];
    } else {
        $_SESSION['message'] = "Item menu tidak ditemukan atau sudah dihapus.";
        $_SESSION['message_type'] = "danger";
        log_activity("Attempt to delete non-existent menu item with ID: {$item_id}.", 'WARNING', $current_admin_username); // <<< ADD LOG
        header("Location: dashboard.php?page=menu_items");
        exit();
    }

    // Handle image file deletion if it's a local file
    if ($image_url_to_delete && !filter_var($image_url_to_delete, FILTER_VALIDATE_URL)) { // Check if it's a local file, not an external URL
        $upload_dir = '../public/uploads/menu_images/';
        $image_full_path = $upload_dir . $image_url_to_delete;
        if (file_exists($image_full_path)) {
            if (unlink($image_full_path)) {
                log_activity("Deleted local image '{$image_url_to_delete}' for menu item ID {$item_id} ('{$item_name_to_delete}') during deletion.", 'INFO', $current_admin_username); // <<< ADD LOG
            } else {
                log_activity("Failed to delete local image '{$image_url_to_delete}' for menu item ID {$item_id} ('{$item_name_to_delete}'). Permissions issue?", 'ERROR', $current_admin_username); // <<< ADD LOG
            }
        } else {
            log_activity("Image file '{$image_full_path}' not found for menu item ID {$item_id} ('{$item_name_to_delete}') during deletion.", 'WARNING', $current_admin_username); // <<< ADD LOG
        }
    }

    // Then, delete the record from the database
    $stmt_delete = $conn->prepare("DELETE FROM menu_items WHERE id = :id");
    $stmt_delete->bindParam(':id', $item_id, PDO::PARAM_INT);

    if ($stmt_delete->execute()) {
        $_SESSION['message'] = "Item menu berhasil dihapus.";
        $_SESSION['message_type'] = "success";
        log_activity("Successfully deleted menu item '{$item_name_to_delete}' (ID: {$item_id}).", 'INFO', $current_admin_username); // <<< ADD LOG
    } else {
        $_SESSION['message'] = "Gagal menghapus item menu.";
        $_SESSION['message_type'] = "danger";
        $error_info = $stmt_delete->errorInfo();
        log_activity("Failed to delete menu item '{$item_name_to_delete}' (ID: {$item_id}). Database error: " . ($error_info[2] ?? 'Unknown error'), 'ERROR', $current_admin_username); // <<< ADD LOG
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Error database saat menghapus item menu: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
    log_activity("PDOException deleting menu item '{$item_name_to_delete}' (ID: {$item_id}): " . $e->getMessage(), 'ERROR', $current_admin_username); // <<< ADD LOG
}

header("Location: dashboard.php?page=menu_items");
exit();
?>