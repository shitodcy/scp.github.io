<?php
session_start();
require_once '../config/database.php';

// Jika sudah login, arahkan ke halaman dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../users/dashboard.php");
    exit();
}

$errors = [];
$username = "";
$email = "";
$fullname = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $fullname = trim($_POST['fullname']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($username)) {
        $errors[] = "Username wajib diisi.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username hanya boleh mengandung huruf, angka, dan underscore.";
    }
    
    if (empty($email)) {
        $errors[] = "Email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    } elseif (!str_ends_with(strtolower($email), '@students.amikom.ac.id')) {
        $errors[] = "Email harus menggunakan domain @students.amikom.ac.id";
    }
    
    if (empty($fullname)) {
        $errors[] = "Nama lengkap wajib diisi.";
    } elseif (strlen($fullname) < 2) {
        $errors[] = "Nama lengkap minimal 2 karakter.";
    }
    
    if (empty($password)) {
        $errors[] = "Password wajib diisi.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }
    
    if (empty($confirm_password)) {
        $errors[] = "Konfirmasi password wajib diisi.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Password dan konfirmasi password tidak sama.";
    }
    
    // Cek apakah username sudah ada
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username sudah digunakan. Silakan pilih username lain.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error database: " . $e->getMessage();
        }
    }
    
    // Cek apakah email sudah terdaftar
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email sudah terdaftar. Silakan gunakan email lain atau login jika Anda sudah memiliki akun.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error database: " . $e->getMessage();
        }
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, fullname, password, created_at) VALUES (:username, :email, :fullname, :password, NOW())");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':password', $hashed_password);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Registrasi berhasil! Silakan login dengan akun Anda.";
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Gagal mendaftar. Silakan coba lagi.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error database: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Kedai Kopi Kayu</title>
    <link rel="stylesheet" href="../public/css/register.css">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="overlay">
                <h1>Kedai Kopi Kayu</h1>
                <p>Bergabunglah dengan komunitas pecinta kopi kami</p>
            </div>
        </div>
        <div class="right-panel">
            <div class="register-box">
                <h2>Daftar Akun Baru</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="errors">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form action="register.php" method="POST">
                    <label for="fullname">Nama Lengkap</label>
                    <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($fullname); ?>" required>
                    
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($username); ?>" required>
                    <small>Username minimal 3 karakter, hanya huruf, angka, dan underscore</small>
                    
                    <label for="email">Email Mahasiswa AMIKOM</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email); ?>" placeholder="nama@students.amikom.ac.id" required>
                    <small>Hanya email dengan domain @students.amikom.ac.id yang diperbolehkan</small>
                    
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Password minimal 6 karakter</small>
                    
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    
                    <button type="submit">Daftar</button>
                </form>
                
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
            </div>
        </div>
    </div>
</body>
</html>