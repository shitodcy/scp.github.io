<?php
// config/database.php

$host = "localhost"; // atau alamat IP server database kamu
$db_name = "scp";
$username_db = "root"; // username database kamu
$password_db = "newpassword"; // password database kamu (kosongkan jika tidak ada)

try {
    $conn = new PDO("mysql:host={$host};dbname={$db_name}", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   // echo "Koneksi sukses!"; // Hapus atau beri komentar setelah tes
} catch(PDOException $exception) {
    echo "Koneksi error: " . $exception->getMessage();
}
?>