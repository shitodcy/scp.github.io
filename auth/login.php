<?php
session_start();
require_once '../config/database.php';

// --- REMEMBER ME: Check for remember me cookie before redirecting ---
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    list($user_id, $token) = explode(':', $_COOKIE['remember_me']);

    try {
        $stmt = $conn->prepare("SELECT id, username, full_name, profile_image, remember_token FROM users WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && hash_equals($user['remember_token'], $token)) {
            // Valid remember me token, log the user in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['profile_image'] = $user['profile_image'];

            // Optionally, refresh the remember me cookie with a new token for security
            $new_token = bin2hex(random_bytes(32)); // Generate a new token
            $hashed_new_token = password_hash($new_token, PASSWORD_DEFAULT); // Hash if you prefer to hash tokens in DB
            $stmt_update = $conn->prepare("UPDATE users SET remember_token = :new_token WHERE id = :id");
            $stmt_update->bindParam(':new_token', $new_token); // Store unhashed in cookie, hashed in DB
            $stmt_update->bindParam(':id', $user['id']);
            $stmt_update->execute();
            setcookie('remember_me', $user['id'] . ':' . $new_token, time() + (86400 * 30), "/"); // 30 days

            header("Location: ../users/dashboard.php");
            exit();
        } else {
            // Invalid or tampered remember me cookie, clear it
            setcookie('remember_me', '', time() - 3600, "/");
        }
    } catch (PDOException $e) {
        // Log the error but don't show it to the user for security
        error_log("Remember Me Error: " . $e->getMessage());
    }
}

// Redirect jika sudah login (after remember me check)
if (isset($_SESSION['user_id'])) {
    header("Location: ../users/dashboard.php");
    exit();
}

$errors = [];
$username = ""; // Changed from email to username based on your form

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']); // --- REMEMBER ME: Get checkbox state ---

    // Input validation
    if (empty($username)) { $errors[] = "Username wajib diisi."; }
    if (empty($password)) { $errors[] = "Password wajib diisi."; }

    // Attempt login if no validation errors
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT id, username, password, full_name, profile_image FROM users WHERE username= :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['profile_image'] = $user['profile_image'];

                    // --- REMEMBER ME: Set remember me cookie if checked ---
                    if ($remember_me) {
                        // Generate a unique token for remember me
                        $token = bin2hex(random_bytes(32));
                        // Update user's remember_token in the database
                        $stmt_update = $conn->prepare("UPDATE users SET remember_token = :token WHERE id = :id");
                        $stmt_update->bindParam(':token', $token);
                        $stmt_update->bindParam(':id', $user['id']);
                        $stmt_update->execute();
                        // Set the cookie with user ID and token, valid for 30 days
                        setcookie('remember_me', $user['id'] . ':' . $token, time() + (86400 * 30), "/"); // 86400 = 1 day
                    }

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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../public/css/login.css">
</head>
<body>

  <div class="split-login-container">
    <div class="left-panel-abstract">
      <div class="abstract-content">
        <div class="abstract-main-text">
          </div>
        
        <p class="left-panel-footer-text">if you can do it yourself why not @scp9242</p>
      </div>
    </div>

    <div class="right-panel-white-form">
      <div class="form-wrapper">
        <div class="form-header">
          <h2>Welcome Back</h2>
          <p class="subtitle">Enter your username and password to access your account!</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="message error">
            <ul>
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
          <div class="message success">
            <p><?= htmlspecialchars($success_message); ?></p>
          </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username); ?>" required
                   placeholder="Enter your username">
          </div>

          <div class="form-group password-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required
                   placeholder="Enter your password">
          </div>

          <div class="form-options">
            <div class="remember-me">
              <input type="checkbox" id="remember_me" name="remember_me">
              <label for="remember_me">Remember me</label>
            </div>
           <a href="/auth/forgot_password.php" class="forgot-password">Forgot Password</a>
          </div>

          <button type="submit" class="btn-sign-in">Sign In</button>
        </form>

        <p class="no-account-link">
          Don't have an account? <a href="/auth/register.php">Sign Up</a>
        </p>
      </div>
    </div>
  </div>

</body>
</html>