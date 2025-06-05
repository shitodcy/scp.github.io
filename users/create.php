<?php
require_once 'auth_check.php'; // Pastikan user sudah login
require_once '../config/database.php';

$errors = [];
$username = '';
$email = '';
$full_name = ''; // Initialize full_name variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? ''); // Get full_name from POST
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validasi sederhana
    if (empty($username)) {
        $errors[] = "Username wajib diisi.";
    }
    if (empty($email)) {
        $errors[] = "Email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    // Full name can be optional, so no 'empty' check here unless required
    // if (empty($full_name)) {
    //     $errors[] = "Nama Lengkap wajib diisi.";
    // }
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

    // Cek email sudah dipakai atau belum
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email sudah dipakai.";
        }
    }

    // Insert ke database jika tidak ada error
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        // Add 'full_name' to the INSERT statement
        $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, password, created_at) VALUES (:username, :email, :full_name, :password, NOW())");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':full_name', $full_name); // Bind full_name parameter
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

            <label>Email:<br>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </label><br><br>

            <label>Nama Lengkap:<br>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>">
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