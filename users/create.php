<?php
// Pastikan sesi sudah dimulai
session_start();

// Ganti auth_check.php dengan logika pengecekan sesi Anda
// Contoh sederhana:
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

// Memanggil koneksi database
require_once '../config/database.php';

$errors = [];
$username = '';
$email = '';
$full_name = '';

// Proses formulir saat di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validasi Dasar
    if (empty($username)) {
        $errors[] = "Username wajib diisi.";
    }
    if (empty($email)) {
        $errors[] = "Email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    if (empty($password)) {
        $errors[] = "Password wajib diisi.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    // Cek duplikasi username
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username sudah digunakan.";
        }
    }

    // Cek duplikasi email
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email sudah digunakan.";
        }
    }

    // Masukkan user ke database jika tidak ada error
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare(
            "INSERT INTO users (username, email, full_name, password, created_at) 
             VALUES (:username, :email, :full_name, :password, NOW())"
        );
        
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':full_name' => $full_name,
            ':password' => $password_hash,
        ];

        if ($stmt->execute($params)) {
            $_SESSION['message'] = "User baru berhasil ditambahkan.";
            $_SESSION['message_type'] = "success";
            header("Location: dashboard.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tambah User Baru</title>
    
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
        rel="stylesheet" 
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
        crossorigin="anonymous"
    >
    
    <link 
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" 
        rel="stylesheet"
    >

    <style>
        /* === BAGIAN STYLING UTAMA === */

        /* Definisi variabel warna dan font dasar */
        :root {
            --brand-card-bg: #543a2d;
            --brand-input-bg: #4a3428;
            --brand-input-border: #6b5a53;
            --brand-text-primary: #f8f9fa;
            --brand-text-secondary: #adb5bd;
            --brand-accent: #e2b79b;
            --brand-button-bg: #8a6855;
            --brand-button-border: #8a6855;
            --brand-button-hover-bg: #a17a65;
            --brand-button-hover-border: #a17a65;
            --brand-focus-ring: rgba(226, 183, 155, 0.5);
            --bs-body-font-family: 'Inter', sans-serif;
        }

        body {
            margin: 0;
            background-color: #0c0c0c; /* Warna background gelap agar animasi terlihat */
            overflow: hidden; /* Mencegah scrollbar dari animasi */
        }

        /* === BAGIAN STYLING ANIMASI BACKGROUND (BARU) === */

        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
        }

        .blob1 {
            width: 450px;
            height: 450px;
            top: -150px;
            left: -200px;
            background: #8a6855; /* Warna dari tombol */
            animation: move1 25s infinite alternate;
        }

        .blob2 {
            width: 350px;
            height: 350px;
            bottom: -150px;
            right: -150px;
            background: #e2b79b; /* Warna dari link accent */
            animation: move2 20s infinite alternate;
        }
        
        .blob3 {
            width: 250px;
            height: 250px;
            top: 20%;
            right: 20%;
            background: #4a3428; /* Warna dari input */
            animation: move1 18s infinite alternate-reverse; /* Gerakan berbeda */
        }

        @keyframes move1 {
            from { transform: translate(0, 0) rotate(0deg) scale(1.2); }
            to { transform: translate(200px, 400px) rotate(180deg) scale(1); }
        }

        @keyframes move2 {
            from { transform: translate(0, 0) rotate(0deg) scale(1); }
            to { transform: translate(-400px, -200px) rotate(-180deg) scale(1.3); }
        }


        /* === BAGIAN STYLING FORM ANDA === */

        /* Wrapper untuk konten agar bisa ditaruh di atas background */
        .content-wrapper {
            position: relative;
            z-index: 1;
        }

        .card {
            background-color: var(--brand-card-bg);
            color: var(--brand-text-primary);
            border: none;
            max-width: 450px;
            border-radius: 20px;
        }

        .form-label {
            color: var(--brand-text-secondary);
        }

        .form-control {
            background-color: var(--brand-input-bg);
            border-color: var(--brand-input-border);
            color: var(--brand-text-primary);
            border-radius: 15px;
            width: 100%;
        }

        .form-control::placeholder {
            color: var(--brand-text-secondary);
        }

        .form-control:focus {
            background-color: var(--brand-input-bg);
            color: var(--brand-text-primary);
            border-color: var(--brand-accent);
            box-shadow: 0 0 0 0.25rem var(--brand-focus-ring);
        }

        .btn-primary {
            --bs-btn-bg: var(--brand-button-bg);
            --bs-btn-border-color: var(--brand-button-border);
            --bs-btn-hover-bg: var(--brand-button-hover-bg);
            --bs-btn-hover-border-color: var(--brand-button-hover-border);
            --bs-btn-active-bg: var(--brand-button-hover-bg);
            --bs-btn-active-border-color: var(--brand-button-hover-border);
            --bs-btn-focus-shadow-rgb: 226, 183, 155;
            font-weight: 600;
            border-radius: 15px;
        }

        .link-accent {
            color: var(--brand-accent);
            text-decoration: none;
            transition: color 0.2s;
        }

        .link-accent:hover {
            color: white;
        }
    </style>
</head>
<body>
    <div class="background-container">
        <div class="blob blob1"></div>
        <div class="blob blob2"></div>
        <div class="blob blob3"></div>
    </div>

    <div class="content-wrapper container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card p-4 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Tambah User Baru</h2>
                <p class="text-center mb-4">
                    <a href="dashboard.php" class="link-accent">&larr; Kembali ke daftar user</a>
                </p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Error!</h4>
                        <ul class="mb-0">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="tambah_user.php" method="POST" class="row g-3">
                    <div class="col-12">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>

                    <div class="col-12">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>

                    <div class="col-12">
                        <label for="full_name" class="form-label">Nama Lengkap (Optional):</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($full_name); ?>">
                    </div>

                    <div class="col-12">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label for="password_confirm" class="form-label">Konfirmasi Password:</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary w-100">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script 
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
        crossorigin="anonymous">
    </script>
</body>
</html>