<?php

// Pastikan file ini ada dan berfungsi untuk memeriksa status login
require_once 'auth_check.php';
// Pastikan file ini ada dan berfungsi untuk koneksi database
require_once '../config/database.php';

// --- Logika Umum yang Dibutuhkan untuk Sidebar ---
$current_user_profile_image = ''; // Variabel untuk menyimpan gambar profil user yang sedang login
$current_user_full_name = $_SESSION['full_name'] ?? 'Pengguna'; // Default jika full_name tidak ada di session

$current_user_id = $_SESSION['user_id'] ?? null;

if ($current_user_id && isset($conn)) {
    try {
        $stmt_current_user = $conn->prepare("SELECT profile_image, full_name, username FROM users WHERE id = :id");
        $stmt_current_user->bindParam(':id', $current_user_id, PDO::PARAM_INT);
        $stmt_current_user->execute();
        $current_user_data = $stmt_current_user->fetch(PDO::FETCH_ASSOC);

        if ($current_user_data) {
            if ($current_user_data['profile_image']) {
                $current_user_profile_image = $current_user_data['profile_image'];
            }
            $current_user_full_name = htmlspecialchars($current_user_data['full_name'] ?? $current_user_data['username']);
        }
    } catch (PDOException $e) {
        error_log("Error fetching current user profile for sidebar: " . $e->getMessage());
    }
}
// --- END Logika Umum ---

// --- Logika Khusus untuk Halaman yang Ditampilkan ---
$requested_page = $_GET['page'] ?? 'users'; // Default ke halaman manajemen user

// Inisialisasi variabel untuk pesan dan error
$page_error = '';
$users = []; // Untuk halaman manajemen user
$monitoring_status = 'Data monitoring akan ditampilkan di sini.'; // Untuk halaman monitoring
$last_update = date('d M Y, H:i:s'); // Untuk halaman monitoring
$backup_message = ''; // Untuk halaman backup
$backup_type = 'info'; // Untuk halaman backup

// Logika kondisional untuk mengambil data sesuai halaman yang diminta
switch ($requested_page) {
    case 'users':
        try {
            $stmt = $conn->prepare("SELECT id, username, email, full_name, created_at, profile_image FROM users ORDER BY created_at DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $page_error = "Error mengambil data user: " . $e->getMessage();
            $users = [];
        }
        $page_title = 'Manajemen User';
        $breadcrumb_active = 'User';
        break;

    case 'monitoring':
        // Logika untuk halaman monitoring (sesuai monitoring.php)
        // Di sini Anda bisa menambahkan logika pengambilan data monitoring yang sesungguhnya
        $page_title = 'Monitoring Website';
        $breadcrumb_active = 'Monitoring';
        break;

    case 'backup':
        // Logika untuk halaman backup (sesuai backup_db.php)
        if (isset($_POST['backup_now'])) {
            // Ini akan membutuhkan kredensial DB yang harus didefinisikan atau diakses di sini
            $db_host = 'localhost'; // Ganti dengan host database Anda
            $db_name = 'nama_database_anda'; // Ganti dengan nama database Anda
            $db_user = 'user_database_anda'; // Ganti dengan user database Anda
            $db_pass = 'password_database_anda'; // Ganti dengan password database Anda

            $backup_dir = '../backups/';
            if (!is_dir($backup_dir)) {
                mkdir($backup_dir, 0777, true);
            }
            $filename = $db_name . '_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backup_dir . $filename;

            $command = sprintf(
                'mysqldump --opt -h%s -u%s -p%s %s > %s',
                escapeshellarg($db_host),
                escapeshellarg($db_user),
                escapeshellarg($db_pass),
                escapeshellarg($db_name),
                escapeshellarg($filepath)
            );

            try {
                if (function_exists('exec')) {
                    exec($command, $output, $return_var);
                    if ($return_var === 0) {
                        $backup_message = "Backup database berhasil dibuat: <a href='" . htmlspecialchars($filepath) . "' download>". htmlspecialchars($filename) . "</a>";
                        $backup_type = 'success';
                    } else {
                        $backup_message = "Gagal membuat backup database menggunakan mysqldump. Error: " . implode("<br>", $output);
                        $backup_type = 'danger';
                    }
                } else {
                    $backup_message = "Fungsi exec() PHP tidak diizinkan. Mohon hubungi administrator server atau gunakan metode backup manual.";
                    $backup_type = 'warning';
                }
            } catch (Exception $e) {
                $backup_message = "Terjadi kesalahan saat membuat backup: " . $e->getMessage();
                $backup_type = 'danger';
            }
        }
        $page_title = 'Backup Database';
        $breadcrumb_active = 'Backup Database';
        break;

    default:
        // Jika parameter page tidak valid, kembali ke default (users)
        header('Location: ?page=users');
        exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $page_title; ?> - Kedai Kopi Kayu</title>
    <link rel="icon" href="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598314/logokkk_rtchku.ico" type="image/x-icon">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../public/css/index.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="?page=monitoring" class="nav-link">Monitoring Website</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="nav-link">Halo, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></span>
            </li>
        </ul>
    </nav>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <?php
                    $sidebar_profile_image_src = 'https://placehold.co/160x160/cccccc/ffffff?text=User'; // Placeholder default
                    $upload_dir_public = '../public/uploads/profile_pictures/'; // Path relatif untuk public
                    if ($current_user_profile_image) {
                        $image_path_full = $upload_dir_public . htmlspecialchars($current_user_profile_image);
                        if (file_exists($image_path_full)) {
                            $sidebar_profile_image_src = $image_path_full;
                        }
                    }
                    ?>
                    <img src="<?php echo $sidebar_profile_image_src; ?>" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?php echo htmlspecialchars($current_user_full_name); ?>
                        <i class="fa fa-circle text-success text-xs ml-1"></i>
                    </a>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="?page=monitoring" class="nav-link <?php echo ($requested_page == 'monitoring') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>
                                Monitoring Website
                            </p>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($requested_page == 'users' || $requested_page == 'backup') ? 'menu-open' : ''; ?>">
                        <a href="#" class="nav-link <?php echo ($requested_page == 'users' || $requested_page == 'backup') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                                Manajemen
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="?page=users" class="nav-link <?php echo ($requested_page == 'users') ? 'active' : ''; ?>">
                                    <i class="far fa-user nav-icon"></i>
                                    <p>Manajemen User</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="../auth/logout.php" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>
                                Logout
                            </p>
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
                        <h1 class="m-0"><?php echo $page_title; ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Manajemen</a></li>
                            <li class="breadcrumb-item active"><?php echo $breadcrumb_active; ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                <?php
                // Tampilkan konten sesuai dengan halaman yang diminta
                switch ($requested_page) {
                    case 'users':
                        // KONTEN MANAJEMEN USER
                        ?>
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-<?php echo isset($_SESSION['message_type']) ? htmlspecialchars($_SESSION['message_type']) : 'success'; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($_SESSION['message']); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <?php
                                unset($_SESSION['message']);
                                unset($_SESSION['message_type']);
                            ?>
                        <?php endif; ?>

                        <?php if (!empty($page_error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($page_error); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Daftar Pengguna</h3>
                                <div class="card-tools">
                                    <a href="create.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Tambah User Baru
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <?php if (count($users) > 0): ?>
                                    <table class="table table-striped table-valign-middle">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Nama Lengkap</th>
                                                <th>Tanggal Daftar</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($user['created_at']))); ?></td>
                                                    <td>
                                                        <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm btn-action">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <?php if ($_SESSION['user_id'] != $user['id']): ?>
                                                            <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm btn-action" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                                                <i class="fas fa-trash"></i> Hapus
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="p-3">
                                        <p>Belum ada user terdaftar.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                        break;

                    case 'monitoring':
                        // KONTEN MONITORING WEBSITE
                        ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Status Umum Website</h3>
                            </div>
                            <div class="card-body">
                                <p><?php echo htmlspecialchars($monitoring_status); ?></p>
                                <p>Terakhir diperbarui: **<?php echo htmlspecialchars($last_update); ?>**</p>
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="info-box bg-info">
                                            <span class="info-box-icon"><i class="far fa-envelope"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Pengunjung Hari Ini</span>
                                                <span class="info-box-number">1,410</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box bg-success">
                                            <span class="info-box-icon"><i class="fas fa-database"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Status Database</span>
                                                <span class="info-box-number">Online</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box bg-warning">
                                            <span class="info-box-icon"><i class="fas fa-server"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Load Server</span>
                                                <span class="info-box-number">0.52 (Normal)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        break;
                       }
                     ?>
               </div>
          </div>
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
</body>
</html>