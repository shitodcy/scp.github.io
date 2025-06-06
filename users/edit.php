<?php
require_once 'auth_check.php'; // Pastikan user sudah login
require_once '../config/database.php';

$errors = [];
$user = null; // Initialize user data as null
$user_id = $_GET['id'] ?? null; // Get user ID from URL

// --- Variabel untuk sidebar (mengambil data user yang sedang login) ---
$current_user_profile_image = ''; 
$current_user_full_name = $_SESSION['full_name'] ?? 'Pengguna'; // Default jika full_name tidak ada di session

// Ambil gambar profil dan nama lengkap untuk user yang sedang login dari database
$logged_in_user_id = $_SESSION['user_id'] ?? null;
if ($logged_in_user_id) {
    try {
        $stmt_logged_in_user = $conn->prepare("SELECT profile_image, full_name FROM users WHERE id = :id");
        $stmt_logged_in_user->bindParam(':id', $logged_in_user_id, PDO::PARAM_INT);
        $stmt_logged_in_user->execute();
        $logged_in_user_data = $stmt_logged_in_user->fetch(PDO::FETCH_ASSOC);
        if ($logged_in_user_data) {
            if ($logged_in_user_data['profile_image']) {
                $current_user_profile_image = $logged_in_user_data['profile_image'];
            }
            if ($logged_in_user_data['full_name']) {
                $current_user_full_name = $logged_in_user_data['full_name'];
            }
        }
    } catch (PDOException $e) {
        error_log("Error fetching current user data for sidebar: " . $e->getMessage());
    }
}
// --- Akhir bagian sidebar ---


// Redirect if no ID is provided or ID is invalid
if (!isset($user_id) || !is_numeric($user_id)) {
    $_SESSION['message'] = "ID user tidak valid.";
    $_SESSION['message_type'] = "danger"; // Menggunakan 'danger' untuk pesan error
    header("Location: index.php");
    exit;
}

// Path untuk menyimpan gambar profil (PASTIKAN FOLDER INI ADA DAN BISA DITULIS OLEH WEB SERVER)
$upload_dir = '../public/uploads/profile_pictures/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Buat direktori jika belum ada
}

// Fetch user data for pre-filling the form
try {
    // Tambahkan kolom 'profile_image' ke query SELECT
    $stmt = $conn->prepare("SELECT id, username, email, full_name, profile_image FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['message'] = "User tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
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

    // Handle profile image upload
    $profile_image_filename = $user['profile_image']; // Default to existing image

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['profile_image']['tmp_name'];
        $file_name = $_FILES['profile_image']['name'];
        $file_size = $_FILES['profile_image']['size'];
        $file_type = $_FILES['profile_image']['type'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 5 * 1024 * 1024; // 5 MB

        if (!in_array($file_ext, $allowed_extensions)) {
            $errors[] = "Ekstensi file tidak diizinkan. Hanya JPG, JPEG, PNG, dan GIF.";
        }
        if ($file_size > $max_file_size) {
            $errors[] = "Ukuran file terlalu besar. Maksimal 5 MB.";
        }
        
        if (empty($errors)) {
            // Hapus gambar lama jika ada dan berbeda dengan yang baru diunggah
            if ($profile_image_filename && file_exists($upload_dir . $profile_image_filename) && $profile_image_filename !== $file_name) {
                unlink($upload_dir . $profile_image_filename);
            }

            $new_file_name = uniqid('profile_') . '.' . $file_ext; // Nama file unik
            $destination_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_name, $destination_path)) {
                $profile_image_filename = $new_file_name; // Update dengan nama file baru
            } else {
                $errors[] = "Gagal mengunggah gambar profil.";
            }
        }
    } elseif (isset($_POST['remove_profile_image']) && $_POST['remove_profile_image'] === 'true') {
        // Hapus gambar jika checkbox "Hapus Gambar Profil" dicentang
        if ($user['profile_image'] && file_exists($upload_dir . $user['profile_image'])) {
            unlink($upload_dir . $user['profile_image']);
        }
        $profile_image_filename = null; // Setel nama file menjadi null di database
    }


    // Update database if no errors
    if (empty($errors)) {
        $sql = "UPDATE users SET username = :username, email = :email, full_name = :full_name, profile_image = :profile_image";
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':full_name' => $full_name,
            ':profile_image' => $profile_image_filename, // Tambahkan ini
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
                // Perbarui data user di variabel $user setelah sukses update
                $user['username'] = $username;
                $user['email'] = $email;
                $user['full_name'] = $full_name;
                $user['profile_image'] = $profile_image_filename;

                // Redirect ke halaman index.php setelah update sukses
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
    <title>Edit User - Kedai Kopi Kayu</title>
    <link rel="icon" href="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598314/logokkk_rtchku.ico" type="image/x-icon">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../public/css/edit.css"> 
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="/index.html" class="nav-link">Halaman Utama</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="nav-link">Halo, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></span>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../auth/logout.php" role="button">
                    Logout
                </a>
            </li>
        </ul>
    </nav>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <?php 
                    // Path relatif untuk public folder dari lokasi file PHP ini
                    $upload_dir_public_for_sidebar = '../public/uploads/profile_pictures/'; 
                    $sidebar_profile_image_src = 'https://placehold.co/160x160/cccccc/ffffff?text=User'; // Placeholder default
                    
                    if ($current_user_profile_image) {
                        $image_path_full = $upload_dir_public_for_sidebar . htmlspecialchars($current_user_profile_image);
                        // Periksa apakah file gambar benar-benar ada di server
                        if (file_exists($image_path_full)) {
                            $sidebar_profile_image_src = $image_path_full;
                        }
                    }
                    ?>
                    <img src="<?php echo $sidebar_profile_image_src; ?>" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?php echo htmlspecialchars($current_user_full_name); ?></a>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="/index.html" class="nav-link">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Halaman Utama</p>
                        </a>
                    </li>
                    <li class="nav-item menu-open">
                        <a href="#" class="nav-link active">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                                Manajemen
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="index.php" class="nav-link active">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Manajemen User</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="content_management.php" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Manajemen Konten</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="../auth/logout.php" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                </ul>
            </nav>
            </div>
        </aside>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Edit User</h1>
                    </div><div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Manajemen</a></li>
                            <li class="breadcrumb-item"><a href="index.php">User</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div></div></div></div>
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h5 class="m-0">Form Edit User</h5>
                            </div>
                            <div class="card-body">
                                <p><a href="index.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke daftar user</a></p>

                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                                        <ul>
                                            <?php foreach ($errors as $err): ?>
                                                <li><?php echo htmlspecialchars($err); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <?php if ($user): ?>
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <div class="form-group text-center">
                                            <label>Gambar Profil Saat Ini:</label><br>
                                            <?php
                                            $profile_image_url_form = '../public/uploads/profile_pictures/' . htmlspecialchars($user['profile_image'] ?? 'default.png');
                                            // Fallback ke placeholder jika gambar tidak ada atau default.png
                                            if (!($user['profile_image'] && file_exists('../public/uploads/profile_pictures/' . $user['profile_image']))) {
                                                $profile_image_url_form = 'https://placehold.co/150x150/cccccc/ffffff?text=No+Image';
                                            }
                                            ?>
                                            <img src="<?php echo $profile_image_url_form; ?>" alt="Gambar Profil" class="profile-image-preview">
                                            
                                            <?php if ($user['profile_image']): ?>
                                                <div class="form-check text-center mb-3">
                                                    <input class="form-check-input" type="checkbox" name="remove_profile_image" value="true" id="removeProfileImage">
                                                    <label class="form-check-label" for="removeProfileImage">
                                                        Hapus Gambar Profil
                                                    </label>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="form-group">
                                            <label for="profile_image">Unggah Gambar Profil Baru:</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="profile_image" name="profile_image" accept="image/*">
                                                    <label class="custom-file-label" for="profile_image">Pilih file</label>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">Maks. 5MB, format JPG, JPEG, PNG, GIF.</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="username">Username:</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="email">Email:</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="full_name">Nama Lengkap:</label>
                                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                                        </div>

                                        <hr>
                                        <p class="text-muted">Biarkan kolom password kosong jika tidak ingin mengubah password.</p>
                                        <div class="form-group">
                                            <label for="password">Password Baru:</label>
                                            <input type="password" class="form-control" id="password" name="password">
                                        </div>

                                        <div class="form-group">
                                            <label for="password_confirm">Konfirmasi Password Baru:</label>
                                            <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                                        </div>

                                        <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save"></i> Perbarui</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div></div>
        </div>
    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">
            Version 1.0
        </div>
        <strong>©SCP9242. All rights reserved.</strong>
    </footer>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    $(document).ready(function () {
        $('#profile_image').on('change', function () {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
    });
</script>
</body>
</html>
