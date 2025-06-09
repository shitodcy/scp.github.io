<?php
// users/auth_check.php
if (session_status() == PHP_SESSION_NONE) { // Mulai session jika belum aktif
    session_start();
}

// Jika user belum login (tidak ada user_id di session), arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Anda harus login untuk mengakses halaman ini.";
    header("Location: ../auth/login.php"); // Sesuaikan path jika file ini dipindah
    exit();
}

// Opsional: Pemeriksaan role jika diperlukan
// function isAdmin() {
//     return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
// }

// function requireAdmin() {
//     if (!isAdmin()) {
//         $_SESSION['error_message'] = "Anda tidak memiliki hak akses admin.";
//         header("Location: index.php"); // Arahkan ke halaman user biasa atau dashboard
//         exit();
//     }
// }
?>
