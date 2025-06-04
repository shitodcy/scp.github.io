<?php
// auth/logout.php
session_start();

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

// Akhirnya, hancurkan session.
session_destroy();

// Arahkan ke halaman login atau halaman utama
header("Location: login.php");
exit();
?>
