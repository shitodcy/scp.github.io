<?php

session_start();


require_once __DIR__ . '/../utils/logger.php'; 

// Capture username before destroying the session
$username_logged_out = $_SESSION['username'] ?? 'UNKNOWN';


log_activity("User '{$username_logged_out}' logged out.", 'INFO', $username_logged_out);


$_SESSION = array();



if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}


if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 120, "/"); 
}


// Akhirnya, hancurkan session.
session_destroy();


header("Location: login.php");
exit();
?>