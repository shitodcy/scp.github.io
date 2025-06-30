<?php
session_start();
require_once '../config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $message = "Alamat email wajib diisi.";
        $message_type = 'error';
    } else {
        try {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id, username, full_name, email FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Generate a unique token
                $token = bin2hex(random_bytes(32));
                // Set expiry time (e.g., 1 hour from now)
                $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Store token and expiry in database
                $stmt_update = $conn->prepare("UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE id = :id");
                $stmt_update->bindParam(':token', $token);
                $stmt_update->bindParam(':expiry', $expiry_time);
                $stmt_update->bindParam(':id', $user['id']);
                $stmt_update->execute();

                // Create reset link
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/auth/reset_password.php?token=" . $token . "&email=" . urlencode($user['email']); // Gunakan alamat domain Anda

                // Send email
                $mail = new PHPMailer(true);
                try {
                    //Server settings
                    $mail->isSMTP();                                            // Send using SMTP
                    $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
                    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                    $mail->Username   = 'your_email@example.com';               // SMTP username (e.g., your_gmail@gmail.com)
                    $mail->Password   = 'your_email_password';                  // SMTP password (e.g., App password for Gmail)
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable implicit TLS encryption
                    $mail->Port       = 465;                                    // TCP port to connect to; use 587 if you set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                    //Recipients
                    $mail->setFrom('no-reply@yourdomain.com', 'Kedai Kopi Kayu'); // Ganti dengan email pengirim Anda
                    $mail->addAddress($user['email'], $user['full_name']);     // Add a recipient

                    //Content
                    $mail->isHTML(true);                                        // Set email format to HTML
                    $mail->Subject = 'Permintaan Reset Password Anda';
                    $mail->Body    = "Halo " . htmlspecialchars($user['full_name']) . ",<br><br>"
                                   . "Kami menerima permintaan reset password untuk akun Anda. Silakan klik link di bawah ini untuk mengatur ulang password Anda:<br>"
                                   . "<a href='" . htmlspecialchars($reset_link) . "'>" . htmlspecialchars($reset_link) . "</a><br><br>"
                                   . "Link ini akan kedaluwarsa dalam 1 jam. Jika Anda tidak meminta reset password, abaikan email ini.<br><br>"
                                   . "Terima kasih,<br>"
                                   . "Tim Kedai Kopi Kayu";
                    $mail->AltBody = "Halo " . $user['full_name'] . ",\n\n"
                                   . "Kami menerima permintaan reset password untuk akun Anda. Silakan kunjungi link di bawah ini untuk mengatur ulang password Anda:\n"
                                   . $reset_link . "\n\n"
                                   . "Link ini akan kedaluwarsa dalam 1 jam. Jika Anda tidak meminta reset password, abaikan email ini.\n\n"
                                   . "Terima kasih,\n"
                                   . "Tim Kedai Kopi Kayu";

                    $mail->send();
                    $message = "Link reset password telah dikirimkan ke email Anda. Silakan cek kotak masuk Anda.";
                    $message_type = 'success';
                } catch (Exception $e) {
                    $message = "Gagal mengirim email. Mailer Error: {$mail->ErrorInfo}";
                    $message_type = 'error';
                    error_log("Mailer Error: " . $e->getMessage());
                }
            } else {
                $message = "Alamat email tidak ditemukan.";
                $message_type = 'error';
            }
        } catch (PDOException $e) {
            $message = "Error database: " . $e->getMessage();
            $message_type = 'error';
            error_log("Forgot Password DB Error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password - Kedai Kopi Kayu</title>
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
          <h2>Lupa Password Anda?</h2>
          <p class="subtitle">Masukkan alamat email Anda yang terdaftar, kami akan mengirimkan link untuk mereset password Anda.</p>
        </div>

        <?php if (!empty($message)): ?>
          <div class="message <?= $message_type; ?>">
            <p><?= htmlspecialchars($message); ?></p>
          </div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST">
          <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" id="email" name="email" required
                   placeholder="Enter your registered email address">
          </div>

          <button type="submit" class="btn-sign-in">Kirim Link Reset</button>
        </form>

        <p class="no-account-link">
          Ingat password Anda ? <a href="/auth/login.php">Login</a>
        </p>
      </div>
    </div>
  </div>

</body>
</html>