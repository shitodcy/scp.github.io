<?php
require_once 'auth_check.php'; // Pastikan user sudah login
require_once '../config/database.php';

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validasi sederhana
    if (empty($username)) {
        $errors[] = "Username wajib diisi.";
    }
    if (empty($password)) {
        $errors[] = "Password wajib diisi.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    // Cek username sudah dipakai atau belum
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username sudah dipakai.";
        }
    }

    // Insert ke database jika tidak ada error
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, created_at) VALUES (:username, :password, NOW())");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password_hash);

        if ($stmt->execute()) {
            $_SESSION['message'] = "User baru berhasil ditambahkan.";
            $_SESSION['message_type'] = "success";
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Gagal menambahkan user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tambah User Baru</title>
    <link rel="stylesheet" href="../public/css/style.css" />
</head>
<body>
    <div class="container">
        <h2>Tambah User Baru</h2>
        <p><a href="index.php">← Kembali ke daftar user</a></p>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <label>Username:<br>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </label><br><br>

            <label>Password:<br>
                <input type="password" name="password" required>
            </label><br><br>

            <label>Konfirmasi Password:<br>
                <input type="password" name="password_confirm" required>
            </label><br><br>

            <button type="submit" class="btn">Simpan</button>
        </form>
    </div>
</body>
</html>
