<?php
session_start();
require_once 'auth_check.php';
require_once '../config/database.php';

$errors = [];
$name = '';
$price = '';
$category = '';
$image_url_text = ''; // New variable for image URL input
$is_active = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $image_url_text = trim($_POST['image_url_text'] ?? ''); // Get value from new URL input
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Basic validation
    if (empty($name)) {
        $errors[] = "Nama menu wajib diisi.";
    }
    if (!is_numeric($price) || $price <= 0) {
        $errors[] = "Harga harus berupa angka positif.";
    }
    if (empty($category) || !in_array($category, ['coffee', 'tea', 'snack', 'other'])) {
        $errors[] = "Kategori tidak valid.";
    }

    $final_image_path = null;

    // Prioritize URL if provided
    if (!empty($image_url_text)) {
        if (filter_var($image_url_text, FILTER_VALIDATE_URL)) {
            $final_image_path = $image_url_text; // Use the provided URL directly
        } else {
            $errors[] = "Format URL gambar tidak valid.";
        }
    } else {
        // Fallback to file upload if no URL is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../public/uploads/menu_images/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
            }

            $file_tmp_name = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($file_extension, $allowed_extensions)) {
                $errors[] = "Jenis file gambar tidak diizinkan. Hanya JPG, JPEG, PNG, GIF, WEBP yang diizinkan.";
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB max
                $errors[] = "Ukuran file gambar terlalu besar (maks 5MB).";
            } else {
                // Generate unique file name
                $new_file_name = uniqid('menu_') . '.' . $file_extension;
                $upload_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp_name, $upload_path)) {
                    $final_image_path = $new_file_name; // Store just the file name
                } else {
                    $errors[] = "Gagal mengunggah gambar file.";
                }
            }
        }
    }

    // Insert into database if no errors
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO menu_items (name, price, category, image_url, is_active, created_at) VALUES (:name, :price, :category, :image_url, :is_active, NOW())");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':image_url', $final_image_path); // Use the final determined image path
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Item menu berhasil ditambahkan!";
                $_SESSION['message_type'] = "success";
                header("Location: dashboard.php?page=menu_items");
                exit();
            } else {
                $errors[] = "Gagal menambahkan item menu.";
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tambah Menu Baru</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <div class="content-wrapper" style="margin-left: 0 !important;">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Tambah Item Menu Baru</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php?page=menu_items">Manajemen Menu</a></li>
                            <li class="breadcrumb-item active">Tambah</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Form Tambah Item Menu</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h4 class="alert-heading"><i class="icon fas fa-ban"></i> Error!</h4>
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <form action="create_menu_item.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="name">Nama Menu</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="price">Harga</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($price); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="category">Kategori</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="coffee" <?= ($category == 'coffee') ? 'selected' : ''; ?>>Kopi</option>
                                    <option value="tea" <?= ($category == 'tea') ? 'selected' : ''; ?>>Teh</option>
                                    <option value="snack" <?= ($category == 'snack') ? 'selected' : ''; ?>>Snack</option>
                                    <option value="other" <?= ($category == 'other') ? 'selected' : ''; ?>>Lain-lain</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="image_url_text">URL Gambar Menu (Opsional)</label>
                                <input type="url" class="form-control" id="image_url_text" name="image_url_text" value="<?= htmlspecialchars($image_url_text); ?>" placeholder="http://example.com/image.jpg">
                                <small class="form-text text-muted">Jika diisi, ini akan diprioritaskan daripada unggahan file. Pastikan format URL benar.</small>
                            </div>
                            <div class="form-group">
                                <label>Atau Unggah Gambar File (Opsional)</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="image" name="image">
                                        <label class="custom-file-label" for="image">Pilih file</label>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Maks. 5MB, format: JPG, PNG, GIF, WEBP.</small>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?= $is_active ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Aktif (Tampilkan di website)</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan Item Menu</button>
                            <a href="dashboard.php?page=menu_items" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
$(document).ready(function () {
    // Update the custom file input label with the selected file name
    $('#image').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
});
</script>
</body>
</html>
