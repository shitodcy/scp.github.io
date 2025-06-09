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
$menu_items = []; // Variabel untuk menyimpan item menu
$category_filter = $_GET['category_filter'] ?? 'all'; // Filter kategori default: 'all'
$search_query = $_GET['search_query'] ?? ''; // New: Variable to capture search query

// Define the absolute path to the project root for secure directory access
// This assumes 'admin' is directly inside 'your_project_root'
$project_root = dirname(__DIR__); // Go up one level from 'admin' to 'your_project_root'

// Define the secure backup directory
$backup_dir = $project_root . '/backups/';


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
        $page_title = 'Monitoring Website';
        $breadcrumb_active = 'Monitoring';

        // Inisialisasi status database
        $db_status = 'Online'; // Default status
        $db_status_class = 'bg-success'; // Default warna box
        $db_status_icon = 'fas fa-database'; // Default icon

        // NEW: Inisialisasi jumlah user aktif
        $active_users_count = 0;

        try {
            // Coba lakukan kueri sederhana untuk memeriksa koneksi database
            // Misalnya, ambil waktu saat ini dari database
            $stmt_check_db = $conn->query("SELECT NOW()");
            $stmt_check_db->fetch(); // Coba ambil hasilnya
            // Jika berhasil sampai sini, database online
        } catch (PDOException $e) {
            // Jika ada exception, berarti koneksi database gagal atau database offline
            $db_status = 'Offline';
            $db_status_class = 'bg-danger'; // Ganti warna menjadi merah
            $db_status_icon = 'fas fa-exclamation-triangle'; // Ganti icon menjadi peringatan
            error_log("Database connection check failed: " . $e->getMessage()); // Log error untuk debugging
            // $page_error = "Koneksi database bermasalah: " . $e->getMessage(); // Opsional: Tampilkan error di bagian atas halaman
        }

        // Ambil waktu terakhir diperbarui dari database (atau gunakan waktu server jika DB offline)
        try {
            $stmt_time = $conn->query("SELECT NOW()");
            $last_update_db = $stmt_time->fetchColumn();
            $last_update = date('d M Y, H:i:s', strtotime($last_update_db));

            // NEW: Ambil jumlah user terdaftar (dianggap sebagai user aktif untuk saat ini)
            $stmt_active_users = $conn->query("SELECT COUNT(id) FROM users");
            $active_users_count = $stmt_active_users->fetchColumn();

        } catch (PDOException $e) {
            // Jika gagal ambil waktu atau hitung user dari DB, gunakan waktu PHP dan set count ke 0
            $last_update = date('d M Y, H:i:s') . ' (dari server PHP)';
            $active_users_count = 'N/A'; // Not available if DB is offline
        }
        break;

    case 'menu_items': // New case for menu management
        $page_title = 'Manajemen Menu';
        $breadcrumb_active = 'Menu';
        try {
            $sql_query = "SELECT id, name, price, category, image_url, is_active FROM menu_items";
            $conditions = [];
            $params = [];

            if ($category_filter !== 'all') {
                $conditions[] = "category = :category_filter";
                $params[':category_filter'] = $category_filter;
            }

            // New: Add search query condition
            if (!empty($search_query)) {
                $conditions[] = "(name LIKE :search_query_name OR category LIKE :search_query_category)";
                $params[':search_query_name'] = '%' . $search_query . '%';
                $params[':search_query_category'] = '%' . $search_query . '%';
            }

            if (!empty($conditions)) {
                $sql_query .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql_query .= " ORDER BY category, name ASC";

            $stmt = $conn->prepare($sql_query);
            $stmt->execute($params);
            $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $page_error = "Error mengambil data menu: " . $e->getMessage();
            $menu_items = [];
        }
        break;

    case 'backup_data': // Case for Backup Data MySQL
        $page_title = 'Backup Data MySQL';
        $breadcrumb_active = 'Backup Data';
        $backup_message = '';
        $backup_message_type = '';

        // Ensure the backup directory exists and is writable
        if (!is_dir($backup_dir)) {
            if (!mkdir($backup_dir, 0755, true)) { // Use 0755 for permissions
                $backup_message = 'Gagal membuat direktori backup: ' . htmlspecialchars($backup_dir) . '. Pastikan izin server sudah benar.';
                $backup_message_type = 'danger';
                error_log("Failed to create backup directory: " . $backup_dir);
            }
        } elseif (!is_writable($backup_dir)) {
            $backup_message = 'Direktori backup tidak dapat ditulis: ' . htmlspecialchars($backup_dir) . '. Pastikan izin server sudah benar.';
            $backup_message_type = 'danger';
            error_log("Backup directory is not writable: " . $backup_dir);
        }

        // Check if the backup action is triggered and directory is writable
        if (isset($_POST['action']) && $_POST['action'] == 'backup_database' && empty($backup_message)) {
            try {
                // Get database credentials from database.php constants
                $dbHost = DB_HOST;
                $dbName = DB_NAME;
                $dbUser = DB_USER;
                $dbPass = DB_PASS;

                $filename = $dbName . '_' . date('Y-m-d_H-i-s') . '.sql';
                $filepath = $backup_dir . $filename;

                // Determine the path to mysqldump
                // IMPORTANT: Adjust this path based on your server environment.
                // Linux/macOS: /usr/bin/mysqldump or /usr/local/bin/mysqldump
                // Windows (XAMPP/WAMP): C:\xampp\mysql\bin\mysqldump.exe
                $mysqldump_path = '/usr/bin/mysqldump'; // Default, CHANGE THIS!

                // Build the command
                // Add --single-transaction and --skip-lock-tables forInnoDB tables for less disruption
                $command = sprintf(
                    '%s -h%s -u%s -p%s %s --single-transaction --skip-lock-tables > %s 2>&1', // 2>&1 redirects stderr to stdout
                    escapeshellarg($mysqldump_path),
                    escapeshellarg($dbHost),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($dbName),
                    escapeshellarg($filepath)
                );

                $output = null;
                $return_var = null;
                exec($command, $output, $return_var);

                if ($return_var === 0) {
                    $backup_message = 'Backup database berhasil dibuat: <strong>' . htmlspecialchars($filename) . '</strong>';
                    $backup_message_type = 'success';
                } else {
                    $backup_message = 'Gagal membuat backup database. Pastikan `mysqldump` terinstal dan path-nya benar. Pesan error: ' . implode("\n", $output);
                    $backup_message_type = 'danger';
                    error_log("MySQL backup failed (command: $command, return: $return_var, output: " . implode("\n", $output));
                }
            } catch (Exception $e) {
                $backup_message = 'Terjadi kesalahan saat mencoba backup database: ' . $e->getMessage();
                $backup_message_type = 'danger';
                error_log("Exception during MySQL backup: " . $e->getMessage());
            }
        }
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
                <a href="?page=monitoring" class="nav-link">Kedai Kopi Kayu Dashboard</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="nav-link">Halo, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></span>
            </li>
        </ul>
    </nav>
    <aside class="main-sidebar sidebar-dark-white elevation-4">

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
                    <li class="nav-item <?php echo ($requested_page == 'users' || $requested_page == 'menu_items' || $requested_page == 'backup_data') ? 'menu-open' : ''; ?>">
                        <a href="#" class="nav-link <?php echo ($requested_page == 'users' || $requested_page == 'menu_items' || $requested_page == 'backup_data') ? 'active' : ''; ?>">
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
                            <li class="nav-item">
                                <a href="?page=menu_items" class="nav-link <?php echo ($requested_page == 'menu_items') ? 'active' : ''; ?>">
                                    <i class="fas fa-coffee nav-icon"></i>
                                    <p>Manajemen Menu</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="?page=backup_data" class="nav-link <?php echo ($requested_page == 'backup_data') ? 'active' : ''; ?>">
                                    <i class="fas fa-database nav-icon"></i>
                                    <p>Backup Data MySQL</p>
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
                            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                <?php if (count($users) > 0): ?>
                                    <table class="table table-striped table-valign-middle mb-0">
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
                                <?php if (!empty($page_error) && $requested_page == 'monitoring'): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?php echo htmlspecialchars($page_error); ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                <?php endif; ?>
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
                                        <div class="info-box <?php echo $db_status_class; ?>">
                                            <span class="info-box-icon"><i class="<?php echo $db_status_icon; ?>"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Status Database</span>
                                                <span class="info-box-number"><?php echo $db_status; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box bg-primary"> <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total User Terdaftar</span>
                                                <span class="info-box-number"><?php echo htmlspecialchars($active_users_count); ?></span>
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

                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Visits by Week of Year</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-responsive">
                                    <canvas id="visitsChart" style="height: 250px;"></canvas>
                                </div>
                        </div>
    </div>
                        <?php
                        break;

                    case 'menu_items':
                        // KONTEN MANAJEMEN MENU
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
                                <h3 class="card-title">Daftar Menu</h3>
                                <div class="card-tools">
                                    <a href="create_menu_item.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Tambah Menu Baru
                                    </a>
                                    <form action="dashboard.php" method="GET" class="input-group input-group-sm d-inline-flex ml-2" style="width: 250px;">
                                        <input type="hidden" name="page" value="menu_items"> <input type="text" name="search_query" class="form-control float-right" placeholder="Cari nama/kategori" value="<?= htmlspecialchars($search_query); ?>">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <select class="form-control float-right ml-2" id="categoryFilter" name="category_filter" onchange="this.form.submit();">
                                            <option value="all" <?= ($category_filter == 'all') ? 'selected' : ''; ?>>Semua Kategori</option>
                                            <option value="coffee" <?= ($category_filter == 'coffee') ? 'selected' : ''; ?>>Kopi</option>
                                            <option value="tea" <?= ($category_filter == 'tea') ? 'selected' : ''; ?>>Teh</option>
                                            <option value="snack" <?= ($category_filter == 'snack') ? 'selected' : ''; ?>>Snack</option>
                                            <option value="other" <?= ($category_filter == 'other') ? 'selected' : ''; ?>>Lain-lain</option>
                                        </select>
                                    </form>
                                    </div>
                            </div>
                            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                <?php if (count($menu_items) > 0): ?>
                                    <table class="table table-striped table-valign-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Gambar</th>
                                                <th>Nama</th>
                                                <th>Harga</th>
                                                <th>Kategori</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($menu_items as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['id']); ?></td>
                                                    <td>
                                                        <?php
                                                        $image_src = 'https://placehold.co/50x50/cccccc/ffffff?text=No+Img';
                                                        if (!empty($item['image_url'])) {
                                                            if (filter_var($item['image_url'], FILTER_VALIDATE_URL)) {
                                                                $image_src = htmlspecialchars($item['image_url']);
                                                            } else {
                                                                $image_src = '../public/uploads/menu_images/' . htmlspecialchars($item['image_url']);
                                                            }
                                                        }
                                                        ?>
                                                        <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                    <td>Rp<?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                                    <td><?php echo htmlspecialchars(ucfirst($item['category'])); ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $item['is_active'] ? 'success' : 'secondary'; ?>">
                                                            <?php echo $item['is_active'] ? 'Aktif' : 'Tidak Aktif'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="edit_menu_item.php?id=<?php echo $item['id']; ?>" class="btn btn-info btn-sm btn-action">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <a href="delete_menu_item.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm btn-action" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?');">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="p-3">
                                        <p>Belum ada item menu terdaftar untuk kategori ini atau tidak ada hasil untuk pencarian Anda.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                        break;

                    case 'backup_data':
                        // KONTEN BACKUP DATA MYSQL
                        ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Buat Backup Database MySQL</h3>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($backup_message)): ?>
                                    <div class="alert alert-<?php echo htmlspecialchars($backup_message_type); ?> alert-dismissible fade show" role="alert">
                                        <?php echo $backup_message; ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <p>Klik tombol di bawah untuk membuat file backup database MySQL Anda. File backup akan disimpan di direktori <code><?php echo htmlspecialchars($backup_dir); ?></code>.</p>
                                <form action="dashboard.php?page=backup_data" method="POST">
                                    <input type="hidden" name="action" value="backup_database">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-download"></i> Buat Backup Sekarang
                                    </button>
                                </form>

                                <h4 class="mt-4">Daftar File Backup</h4>
                                <?php
                                $backup_files = [];
                                if (is_dir($backup_dir)) {
                                    $scanned_backups = scandir($backup_dir);
                                    foreach ($scanned_backups as $file) {
                                        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                                            $file_path = $backup_dir . $file;
                                            $backup_files[] = [
                                                'name' => htmlspecialchars($file),
                                                'size' => round(filesize($file_path) / 1024, 2) . ' KB',
                                                'created_at' => date('d M Y, H:i:s', filemtime($file_path)),
                                                'download_url' => 'download_backup.php?file=' . urlencode($file) // Secure download via PHP script
                                            ];
                                        }
                                    }
                                    // Sort by creation date (newest first)
                                    usort($backup_files, function($a, $b) {
                                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                                    });
                                }
                                ?>

                                <?php if (!empty($backup_files)): ?>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-striped table-valign-middle mb-0 mt-3">
                                            <thead>
                                                <tr>
                                                    <th>Nama File</th>
                                                    <th>Ukuran</th>
                                                    <th>Tanggal Dibuat</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($backup_files as $backup_file): ?>
                                                    <tr>
                                                        <td><?php echo $backup_file['name']; ?></td>
                                                        <td><?php echo $backup_file['size']; ?></td>
                                                        <td><?php echo $backup_file['created_at']; ?></td>
                                                        <td>
                                                            <a href="<?php echo $backup_file['download_url']; ?>" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                            <a href="delete_backup.php?file=<?php echo urlencode($backup_file['name']); ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Apakah Anda yakin ingin menghapus file backup \'<?php echo htmlspecialchars($backup_file['name']); ?>\'? Tindakan ini tidak dapat dibatalkan.');">
                                                                <i class="fas fa-trash"></i> Hapus
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="p-3">
                                        <p>Tidak ada file backup ditemukan.</p>
                                    </div>
                                <?php endif; ?>
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