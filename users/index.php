<?php

// Pastikan file ini ada dan berfungsi untuk memeriksa status login

require_once 'auth_check.php';

// Pastikan file ini ada dan berfungsi untuk koneksi database

require_once '../config/database.php';


// Inisialisasi variabel untuk pesan dan error

$page_error = '';

$users = [];

$current_user_profile_image = ''; // Variabel untuk menyimpan gambar profil user yang sedang login


// Ambil data user: id, username, email, full_name, dan created_at

try {

// Mempersiapkan query untuk mengambil data user

// Menggunakan ORDER BY created_at DESC untuk mengurutkan dari yang terbaru

// Tambahkan kolom 'profile_image' ke query SELECT

$stmt = $conn->prepare("SELECT id, username, email, full_name, created_at, profile_image FROM users ORDER BY created_at DESC");

$stmt->execute(); // Menjalankan query

$users = $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengambil semua hasil sebagai array asosiatif


// Ambil gambar profil dan nama lengkap untuk user yang sedang login

$current_user_id = $_SESSION['user_id'] ?? null;

$current_user_full_name = $_SESSION['full_name'] ?? 'Pengguna'; // Default jika full_name tidak ada di session


if ($current_user_id) {

$stmt_current_user = $conn->prepare("SELECT profile_image, full_name FROM users WHERE id = :id");

$stmt_current_user->bindParam(':id', $current_user_id, PDO::PARAM_INT);

$stmt_current_user->execute();

$current_user_data = $stmt_current_user->fetch(PDO::FETCH_ASSOC);

if ($current_user_data) {

if ($current_user_data['profile_image']) {

$current_user_profile_image = $current_user_data['profile_image'];

}

// Update full_name dari database jika ada

if ($current_user_data['full_name']) {

$current_user_full_name = $current_user_data['full_name'];

}

}

}


} catch (PDOException $e) {

// Menangkap dan menampilkan error jika terjadi masalah pada database

$page_error = "Error mengambil data user: " . $e->getMessage();

$users = []; // Pastikan array user kosong jika ada error

}

?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8" />

<meta name="viewport" content="width=device-width, initial-scale=1" />

<title>Manajemen User - Kedai Kopi Kayu</title>

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

<a href="/index.html" class="nav-link">Halaman Utama</a>

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

<a href="#" class="d-block"><?php echo htmlspecialchars($current_user_full_name); ?></a>

</div>

</div>


<nav class="mt-2">

<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

<li class="nav-item">

<a href="/index.html" class="nav-link">

<i class="nav-icon fas fa-home"></i>

<p>

Halaman Utama

</p>

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

<h1 class="m-0">Manajemen User</h1>

</div><div class="col-sm-6">

<ol class="breadcrumb float-sm-right">

<li class="breadcrumb-item"><a href="#">Manajemen</a></li>

<li class="breadcrumb-item active">User</li>

</ol>

</div></div></div></div>

<div class="content">

<div class="container-fluid">

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