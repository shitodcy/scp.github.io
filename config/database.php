<?php
// config/database.php

define('DB_HOST', 'mysql-server'); // atau alamat IP server database kamu
define('DB_NAME', 'scp');
define('DB_USER', 'root'); // username database kamu
define('DB_PASS', 'newpassword'); // password database kamu (kosongkan jika tidak ada)

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $exception) {
    echo "Koneksi error: " . $exception->getMessage();
    error_log("Database Connection Error: " . $exception->getMessage());
    die("Sistem sedang mengalami masalah koneksi database. Mohon coba lagi nanti.");
}
?>
