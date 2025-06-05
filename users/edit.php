<?php
require_once 'auth_check.php'; // Pastikan user sudah login
require_once '../config/database.php';

$errors = [];
$user = null; // Initialize user data as null
$user_id = $_GET['id'] ?? null; // Get user ID from URL

// Redirect if no ID is provided or ID is invalid
if (!isset($user_id) || !is_numeric($user_id)) {
    $_SESSION['message'] = "ID user tidak valid.";
    $_SESSION['message_type'] = "errors";
    header("Location: index.php");
    exit;
}

// Fetch user data for pre-filling the form
try {
    $stmt = $conn->prepare("SELECT id, username, email, full_name FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['message'] = "User tidak ditemukan.";
        $_SESSION['message_type'] = "errors";
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    $errors[] = "Error mengambil data user: " . $e->getMessage();
}

// Handle form submission for updating user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Basic validation
    if (empty($username)) {
        $errors[] = "Username wajib diisi.";
    }
    if (empty($email)) {
        $errors[] = "Email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    // Check if username is already taken by another user (excluding current user)
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
        $stmt->execute([':username' => $username, ':id' => $user_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username sudah dipakai oleh user lain.";
        }
    }

    // Check if email is already taken by another user (excluding current user)
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND id != :id");
        $stmt->execute([':email' => $email, ':id' => $user_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email sudah dipakai oleh user lain.";
        }
    }

    // Password validation (only if provided)
    if (!empty($password)) {
        if ($password !== $password_confirm) {
            $errors[] = "Konfirmasi password tidak cocok.";
        }
    }

    // Update database if no errors
    if (empty($errors)) {
        $sql = "UPDATE users SET username = :username, email = :email, full_name = :full_name";
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':full_name' => $full_name,
            ':id' => $user_id
        ];

        // If password is provided, update it
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = :password";
            $params[':password'] = $password_hash;
        }

        $sql .= " WHERE id = :id";

        try {
            $stmt = $conn->prepare($sql);
            if ($stmt->execute($params)) {
                $_SESSION['message'] = "Data user berhasil diperbarui.";
                $_SESSION['message_type'] = "success";
                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Gagal memperbarui user.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error memperbarui data user: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit User</title>
    <link rel="stylesheet" href="../public/css/style.css" />
</head>
<body>
    <div class="container">
        <h2>Edit User</h2>
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

        <?php if ($user): ?>
            <form action="" method="POST">
                <label>Username:<br>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </label><br><br>

                <label>Email:<br>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </label><br><br>

                <label>Nama Lengkap:<br>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                </label><br><br>

                <p>Biarkan kosong jika tidak ingin mengubah password.</p>
                <label>Password Baru:<br>
                    <input type="password" name="password">
                </label><br><br>

                <label>Konfirmasi Password Baru:<br>
                    <input type="password" name="password_confirm">
                </label><br><br>

                <button type="submit" class="btn">Perbarui</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>