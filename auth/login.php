<?php
session_start();
require_once '../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../users/dashboard.php");
    exit();
}

$errors = [];
$username = "";

// Display success message from registration if present
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Input validation
    if (empty($username)) { $errors[] = "Username wajib diisi."; }
    if (empty($password)) { $errors[] = "Password wajib diisi."; }

    // Attempt login if no validation errors
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header("Location: ../users/dashboard.php");
                    exit();
                } else {
                    $errors[] = "Username atau password salah.";
                }
            } else {
                $errors[] = "Username atau password salah.";
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
  <title>Login - Kedai Kopi Kayu</title>
  <link rel="stylesheet" href="../public/css/login.css">
</head>
<body>
  <div class="container">
    <div class="left-panel">
      <div class="overlay">
        <h1>Kedai Kopi Kayu</h1>
        <p>Hangatnya kopi, hangatnya kebersamaan.</p>
      </div>
    </div>
    <div class="right-panel">
      <div class="login-box">
        <h2>Login ke Akunmu</h2>

        <?php if (isset($success_message)): ?>
          <div class="success">
            <p><?= htmlspecialchars($success_message); ?></p>
          </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
          <div class="errors">
            <?php foreach ($errors as $error): ?>
              <p><?= htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" value="<?= htmlspecialchars($username); ?>" required>

          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>

          <button type="submit">Masuk</button>
        </form>

        <p>Belum punya akun? <a href="/auth/register.php">Daftar di sini</a></p>
      </div>
    </div>
  </div>
</body>
</html>