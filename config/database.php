<?php
// config/database.php

$host = "localhost"; // atau alamat IP server database kamu
$db_name = "scp";
$username_db = "root"; // username database kamu
$password_db = ""; // password database kamu (kosongkan jika tidak ada)

try {
    $conn = new PDO("mysql:host={$host};dbname={$db_name}", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   // echo "Koneksi sukses!"; // Hapus atau beri komentar setelah tes
} catch(PDOException $exception) {
    echo "Koneksi error: " . $exception->getMessage();
}

// Konfigurasi PHPMailer (Ganti dengan kredensial email Anda)
define('MAIL_HOST', 'smtp.gmail.com'); // Contoh untuk Gmail SMTP
define('MAIL_USERNAME', 'aryaputrabahari@students.amikom.ac.id'); // Email pengirim
define('MAIL_PASSWORD', 'wallnutTh3s'); // Password aplikasi Gmail atau password email biasa
define('MAIL_PORT', 587); // Port SMTP (misal 587 untuk TLS, 465 untuk SSL)
define('MAIL_ENCRYPTION', 'tls'); // Enkripsi (tls atau ssl)
define('MAIL_FROM_NAME', 'Kedai Kopi Kayu'); // Nama pengirim
?>