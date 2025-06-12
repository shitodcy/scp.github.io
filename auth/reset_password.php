<?php
session_start();
require_once '../config/database.php';

$message = '';
$message_type = ''; // 'success' or 'error'
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

// Check if token and email are provided in URL
if (empty($token) || empty($email)) {
    $message = "Link reset password tidak valid atau sudah kedaluwarsa.";
    $message_type = 'error';
} else {
    try {
        // Find user by token and email, and check token expiry
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND reset_token = :token AND reset_token_expiry > NOW() LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $message = "Link reset password tidak valid atau sudah kedaluwarsa.";
            $message_type = 'error';
        }
    } catch (PDOException $e) {
        $message = "Error database: " . $e->getMessage();
        $message_type = 'error';
        error_log("Reset Password Token Check DB Error: " . $e->getMessage());
    }
}

// Handle password reset submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_POST['token']; // Get token from hidden field
    $email = $_POST['email']; // Get email from hidden field

    // Re-validate token and email
    if (empty($token) || empty($email)) {
        $message = "Permintaan reset password tidak valid.";
        $message_type = 'error';
    } else {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND reset_token = :token AND reset_token_expiry > NOW() LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $message = "Link reset password tidak valid atau sudah kedaluwarsa.";
                $message_type = 'error';
            } else {
                // Validate new password
                if (empty($new_password) || empty($confirm_password)) {
                    $message = "Password baru dan konfirmasi password wajib diisi.";
                    $message_type = 'error';
                } elseif ($new_password !== $confirm_password) {
                    $message = "Konfirmasi password tidak cocok.";
                    $message_type = 'error';
                } elseif (strlen($new_password) < 6) { // Example: minimum 6 characters
                    $message = "Password baru minimal 6 karakter.";
                    $message_type = 'error';
                } else {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update password and clear reset token
                    $stmt_update = $conn->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id");
                    $stmt_update->bindParam(':password', $hashed_password);
                    $stmt_update->bindParam(':id', $user['id']);
                    $stmt_update->execute();

                    $message = "Password Anda berhasil direset! Silakan login dengan password baru Anda.";
                    $message_type = 'success';
                    // Redirect to login page after successful reset
                    header("Refresh: 3; url=/auth/login.php"); // Redirect after 3 seconds
                    exit();
                }
            }
        } catch (PDOException $e) {
            $message = "Error database: " . $e->getMessage();
            $message_type = 'error';
            error_log("Reset Password DB Error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - Kedai Kopi Kayu</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../public/css/login.css"> </head>
<body>

  <div class="split-login-container">
    <div class="left-panel-abstract">
      <div class="abstract-content">
        <div class="abstract-main-text"></div>
        <p class="left-panel-footer-text">if you can do it yourself why not @scp9242</p>
      </div>
    </div>

    <div class="right-panel-white-form">
      <div class="form-wrapper">
        <div class="form-header">
          <h2>Atur Ulang Password Anda</h2>
          <p class="subtitle">Masukkan password baru Anda.</p>
        </div>

        <?php if (!empty($message)): ?>
          <div class="message <?= $message_type; ?>">
            <p><?= htmlspecialchars($message); ?></p>
          </div>
        <?php endif; ?>

        <?php if ($message_type !== 'error' || (isset($user) && $user)): ?>
        <form action="reset_password.php" method="POST">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
          <input type="hidden" name="email" value="<?= htmlspecialchars($email); ?>">

          <div class="form-group password-group">
            <label for="new_password">Password Baru</label>
            <input type="password" id="new_password" name="new_password" required
                   placeholder="Enter your new password">
          </div>

          <div class="form-group password-group">
            <label for="confirm_password">Konfirmasi Password Baru</label>
            <input type="password" id="confirm_password" name="confirm_password" required
                   placeholder="Confirm your new password">
          </div>

          <button type="submit" class="btn-sign-in">Reset Password</button>
        </form>
        <?php endif; ?>

        <p class="no-account-link">
          Kembali ke <a href="/auth/login.php">Login</a>
        </p>
      </div>
    </div>
  </div>

</body>
</html>