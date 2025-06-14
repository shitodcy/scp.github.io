<?php
// Ensure user is logged in (auth_check.php)
require_once 'auth_check.php';
// Database configuration (../config/database.php)
require_once '../config/database.php';
// Include the logging utility
require_once '../utils/logger.php'; // Make sure this path is correct

$errors = [];
$username = '';
$email = '';
$full_name = ''; // Initialize full_name variable for form pre-filling

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Get the current logged-in user's username for logging context
    $current_admin_username = $_SESSION['username'] ?? 'UNKNOWN_ADMIN';

    // Basic validation
    if (empty($username)) {
        $errors[] = "Username is required.";
        log_activity("Failed attempt to create user: Username is empty.", 'WARNING', $current_admin_username);
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
        log_activity("Failed attempt to create user (username: {$username}): Email is empty.", 'WARNING', $current_admin_username);
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
        log_activity("Failed attempt to create user (username: {$username}): Invalid email format.", 'WARNING', $current_admin_username);
    }
    // full_name is optional, so no empty check is needed here
    if (empty($password)) {
        $errors[] = "Password is required.";
        log_activity("Failed attempt to create user (username: {$username}): Password is empty.", 'WARNING', $current_admin_username);
    }
    if ($password !== $password_confirm) {
        $errors[] = "Password confirmation does not match.";
        log_activity("Failed attempt to create user (username: {$username}): Password confirmation mismatch.", 'WARNING', $current_admin_username);
    }

    // Check if username already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username already taken.";
            log_activity("Failed attempt to create user: Username '{$username}' already exists.", 'WARNING', $current_admin_username);
        }
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email already taken.";
            log_activity("Failed attempt to create user (username: {$username}): Email '{$email}' already exists.", 'WARNING', $current_admin_username);
        }
    }

    // Insert user into database if no errors
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        // Prepare and execute the insert statement, including full_name
        $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, password, created_at) VALUES (:username, :email, :full_name, :password, NOW())");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':password', $password_hash);

        if ($stmt->execute()) {
            $_SESSION['message'] = "New user successfully added.";
            $_SESSION['message_type'] = "success";
            log_activity("Successfully created new user: '{$username}'.", 'INFO', $current_admin_username);
            header("Location: dashboard.php?page=users"); // Redirect to dashboard, specifically the users page
            exit;
        } else {
            $errors[] = "Failed to add user.";
            log_activity("Database error while creating user '{$username}': " . implode(", ", $stmt->errorInfo()), 'ERROR', $current_admin_username);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tambah User Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif; /* Apply Inter font */
            background-color: #282a36; /* Dracula background */
            color: #f8f8f2; /* Dracula foreground text */
        }
        /* Custom styling for inputs to match Dracula theme */
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            background-color: #44475a; /* Current Line background */
            border-color: #6272a4; /* Comment color for border */
            color: #f8f8f2; /* Foreground text */
        }
        input[type="text"]::placeholder,
        input[type="email"]::placeholder,
        input[type="password"]::placeholder {
            color: #6272a4; /* Comment color for placeholder */
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #bd93f9; /* Purple accent on focus */
            outline: none;
            box-shadow: 0 0 0 2px rgba(189, 147, 249, 0.5); /* Purple shadow on focus */
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-[#2e303c] p-8 rounded-lg shadow-md w-full max-w-md border border-[#44475a]">
        <h2 class="text-2xl font-semibold text-[#f8f8f2] mb-6 text-center">Tambah User Baru</h2>
        <p class="text-center mb-6">
            <a href="dashboard.php?page=users" class="text-[#bd93f9] hover:text-[#ff79c6] transition-colors duration-200">
                &larr; Kembali ke daftar user
            </a>
        </p>

        <?php if (!empty($errors)): ?>
            <div class="bg-[#ff5555] border border-[#ff5555] text-white px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-[#f8f8f2] text-sm font-medium mb-1">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required
                       class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm">
            </div>

            <div>
                <label for="email" class="block text-[#f8f8f2] text-sm font-medium mb-1">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required
                       class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm">
            </div>

            <div>
                <label for="full_name" class="block text-[#f8f8f2] text-sm font-medium mb-1">Nama Lengkap (Optional):</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>"
                       class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm">
            </div>

            <div>
                <label for="password" class="block text-[#f8f8f2] text-sm font-medium mb-1">Password:</label>
                <input type="password" id="password" name="password" required
                       class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm">
            </div>

            <div>
                <label for="password_confirm" class="block text-[#f8f8f2] text-sm font-medium mb-1">Konfirmasi Password:</label>
                <input type="password" id="password_confirm" name="password_confirm" required
                       class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm">
            </div>

            <button type="submit"
                    class="w-full bg-[#bd93f9] text-[#282a36] py-2 px-4 rounded-md hover:bg-[#ff79c6] focus:outline-none focus:ring-2 focus:ring-[#bd93f9] focus:ring-opacity-50 transition-colors duration-200">
                Simpan
            </button>
        </form>
    </div>
</body>
</html>