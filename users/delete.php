<?php
require_once 'auth_check.php'; // Pastikan user sudah login
require_once '../config/database.php';

// Cek apakah ada parameter id dari URL
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "ID user tidak ditemukan.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit;
}

$userIdToDelete = (int) $_GET['id'];

// Jangan izinkan user menghapus diri sendiri
if ($userIdToDelete === (int)$_SESSION['user_id']) {
    $_SESSION['message'] = "Anda tidak bisa menghapus user sendiri.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit;
}

try {
    // Persiapkan query hapus user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userIdToDelete, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = "User berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "User tidak ditemukan atau sudah dihapus.";
        $_SESSION['message_type'] = "error";
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Terjadi kesalahan: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

// Redirect kembali ke halaman manajemen user
header("Location: index.php");
exit;
