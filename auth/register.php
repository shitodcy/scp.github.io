<?php
session_start();
require_once '../config/database.php'; // Hubungkan ke database

$errors = [];
$username = ""; // Inisialisasi variabel

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // --- Validasi Input Sederhana ---
    if (empty($username)) { $errors[] = "Username wajib diisi."; }
    if (strlen($username) < 4) { $errors[] = "Username minimal 4 karakter."; }
    if (empty($password)) { $errors[] = "Password wajib diisi."; }
    if (strlen($password) < 6) { $errors[] = "Password minimal 6 karakter."; }
    if ($password !== $konfirmasi_password) { $errors[] = "Konfirmasi password tidak cocok."; }

    // --- Cek apakah username sudah ada ---
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username sudah terdaftar. Silakan gunakan yang lain.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error saat memeriksa data: " . $e->getMessage();
        }
    }

    // --- Jika tidak ada error, simpan ke database ---
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Registrasi berhasil! Silakan login.";
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Registrasi gagal. Silakan coba lagi.";
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
    <title>Registrasi User</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Registrasi User Baru</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="konfirmasi_password">Konfirmasi Password:</label>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password" required>
            </div>
            <div>
                <button type="submit">Daftar</button>
            </div>
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </form>
    </div>
</body>
</html>
