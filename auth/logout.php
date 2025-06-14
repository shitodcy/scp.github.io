<?php
// auth/logout.php
session_start();

// Include the logging utility
require_once __DIR__ . '/../utils/logger.php'; // Adjust path if necessary

// Capture username before destroying the session
$username_logged_out = $_SESSION['username'] ?? 'UNKNOWN';

// Log the logout activity BEFORE destroying the session
log_activity("User '{$username_logged_out}' logged out.", 'INFO', $username_logged_out);

// Hapus semua variabel session
$_SESSION = array();

// Jika ingin menghancurkan session, juga hapus cookie session.
// Catatan: Ini akan menghancurkan session, dan bukan hanya data session!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// --- Tambahkan ini untuk menghapus cookie 'remember_me' ---
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 120, "/"); // Set cookie to expire in the past
}
// --- Akhir penambahan ---

// Akhirnya, hancurkan session.
session_destroy();

// Arahkan ke halaman login atau halaman utama
header("Location: login.php");
exit();
?>