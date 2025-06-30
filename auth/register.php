<?php
session_start();
require_once '../config/database.php'; 


if (isset($_SESSION['user_id'])) {
    header("Location: ../users/dashboard.php");
    exit();
}

$errors = [];
$full_name = '';
$username = '';
$email = '';
$password = '';
$confirm_password = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING));
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password']; 
    $confirm_password = $_POST['confirm_password'];


    if (empty($full_name)) {
        $errors[] = "Nama lengkap wajib diisi.";
    } elseif (strlen($full_name) < 2) {
        $errors[] = "Nama lengkap minimal 2 karakter.";
    }

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


    if (empty($errors)) {
        try {
            $stmt_approved = $conn->prepare("SELECT id FROM approved_emails WHERE email = :email LIMIT 1");
            $stmt_approved->bindParam(':email', $email);
            $stmt_approved->execute();

            if ($stmt_approved->rowCount() === 0) {
                $errors[] = "Email Anda tidak terdaftar dalam daftar email yang disetujui. Silakan hubungi administrator.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error database saat memeriksa email yang disetujui: " . $e->getMessage();
        }
    }


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


    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);


            $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, password, created_at) VALUES (:username, :email, :full_name, :password, NOW())");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':full_name', $full_name);
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
    <title>Register - AMIKOM</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/register.css">
    </head>
<body>
    <div class="background-image"></div> <div class="container">
        <div class="header">
            <h1>CREATE YOUR ACCOUNT</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post" class="register-form">
            <div class="form-group">
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    value="<?php echo htmlspecialchars($full_name); ?>"
                    placeholder="Full name"
                    required
                >
            </div>

            <div class="form-group">
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlspecialchars($username); ?>"
                    placeholder="Username"
                    required
                >
                <small class="input-hint">Username minimal 3 karakter, hanya huruf, angka, dan underscore</small>
            </div>

            <div class="form-group">
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    placeholder="Email"
                    required
                >
                <small class="input-hint">Hanya email dengan domain @students.amikom.ac.id yang diperbolehkan</small>
            </div>

            <div class="form-group">
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Password"
                    required
                >
                <small class="input-hint">Password minimal 6 karakter</small>
            </div>

            <div class="form-group">
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm Password"
                    required
                >
            </div>

            <p class="login-prompt">Already a member? <a href="login.php">Log in</a></p>
            <button type="submit" class="btn-signup">
                <i class="fas fa-user-plus"></i> Sign up
            </button>
        </form>
    </div>
</body>
</html>