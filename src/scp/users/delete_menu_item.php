<?php
session_start();
require_once 'auth_check.php';
require_once '../config/database.php';

$item_id = $_GET['id'] ?? null;

if (!$item_id) {
    $_SESSION['message'] = "ID item menu tidak ditemukan.";
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php?page=menu_items");
    exit();
}

try {
    // First, get the image_url to delete the file
    $stmt_get_image = $conn->prepare("SELECT image_url FROM menu_items WHERE id = :id");
    $stmt_get_image->bindParam(':id', $item_id, PDO::PARAM_INT);
    $stmt_get_image->execute();
    $item = $stmt_get_image->fetch(PDO::FETCH_ASSOC);

    if ($item && $item['image_url']) {
        $image_path = '../public/uploads/menu_images/' . $item['image_url'];
        if (file_exists($image_path)) {
            unlink($image_path); // Delete the image file
        }
    }

    // Then, delete the record from the database
    $stmt_delete = $conn->prepare("DELETE FROM menu_items WHERE id = :id");
    $stmt_delete->bindParam(':id', $item_id, PDO::PARAM_INT);

    if ($stmt_delete->execute()) {
        $_SESSION['message'] = "Item menu berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus item menu.";
        $_SESSION['message_type'] = "danger";
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Error database saat menghapus item menu: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

header("Location: dashboard.php?page=menu_items");
exit();
?>
