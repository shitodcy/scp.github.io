<?php
session_start();
require_once 'auth_check.php';
require_once '../config/database.php';

$errors = [];
$item_id = $_GET['id'] ?? null;
$item_data = null;

if (!$item_id) {
    $_SESSION['message'] = "ID item menu tidak ditemukan.";
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php?page=menu_items");
    exit();
}

// Fetch existing item data
try {
    $stmt = $conn->prepare("SELECT id, name, price, category, image_url, is_active FROM menu_items WHERE id = :id");
    $stmt->bindParam(':id', $item_id, PDO::PARAM_INT);
    $stmt->execute();
    $item_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item_data) {
        $_SESSION['message'] = "Item menu tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php?page=menu_items");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Error database saat mengambil data item menu: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php?page=menu_items");
    exit();
}

// If form is submitted for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $new_image_url_text = trim($_POST['new_image_url_text'] ?? ''); // New URL input
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $remove_existing_image = isset($_POST['remove_image']) ? true : false; // Checkbox for removing existing image

    $final_image_path = $item_data['image_url']; // Start with current image path

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

    // Logic for image handling: Prioritize new URL, then file upload, then existing, then remove
    if (!empty($new_image_url_text)) {
        if (filter_var($new_image_url_text, FILTER_VALIDATE_URL)) {
            // New URL provided, use it and delete old file if it was a file upload
            if ($final_image_path && !filter_var($final_image_path, FILTER_VALIDATE_URL)) { // Check if it was a local file
                $upload_dir = '../public/uploads/menu_images/';
                if (file_exists($upload_dir . $final_image_path)) {
                    unlink($upload_dir . $final_image_path);
                }
            }
            $final_image_path = $new_image_url_text;
        } else {
            $errors[] = "Format URL gambar baru tidak valid.";
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // New file uploaded, use it and delete old image (URL or file)
        $upload_dir = '../public/uploads/menu_images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Delete old image if it was a local file
        if ($final_image_path && !filter_var($final_image_path, FILTER_VALIDATE_URL)) {
            if (file_exists($upload_dir . $final_image_path)) {
                unlink($upload_dir . $final_image_path);
            }
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
            $new_file_name = uniqid('menu_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_name, $upload_path)) {
                $final_image_path = $new_file_name;
            } else {
                $errors[] = "Gagal mengunggah gambar file baru.";
            }
        }
    } elseif ($remove_existing_image) {
        // Remove existing image, delete local file if it was one
        if ($final_image_path && !filter_var($final_image_path, FILTER_VALIDATE_URL)) {
            $upload_dir = '../public/uploads/menu_images/';
            if (file_exists($upload_dir . $final_image_path)) {
                unlink($upload_dir . $final_image_path);
            }
        }
        $final_image_path = null; // Set image_url to null in DB
    }
    // If no new URL, no new file, and not removing, $final_image_path remains the original $item_data['image_url']

    // Update database if no errors
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE menu_items SET name = :name, price = :price, category = :category, image_url = :image_url, is_active = :is_active, updated_at = NOW() WHERE id = :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':image_url', $final_image_path); // Use the final determined image path
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $item_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Item menu berhasil diperbarui!";
                $_SESSION['message_type'] = "success";
                header("Location: dashboard.php?page=menu_items");
                exit();
            } else {
                $errors[] = "Gagal memperbarui item menu.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error database: " . $e->getMessage();
        }
    }
    // Re-fetch data to reflect changes if there were errors but no redirect
    if (!empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT id, name, price, category, image_url, is_active FROM menu_items WHERE id = :id");
            $stmt->bindParam(':id', $item_id, PDO::PARAM_INT);
            $stmt->execute();
            $item_data = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error re-fetching menu data after failed update: " . $e->getMessage());
        }
    }

}
// Prepare form fields with current data
$name_val = htmlspecialchars($item_data['name'] ?? '');
$price_val = htmlspecialchars($item_data['price'] ?? '');
$category_val = htmlspecialchars($item_data['category'] ?? '');
$image_url_val = htmlspecialchars($item_data['image_url'] ?? ''); // This will be either a local filename or an external URL
$is_active_val = $item_data['is_active'] ?? false;

// Determine if the current image is a URL or a local file
$is_current_image_url = filter_var($image_url_val, FILTER_VALIDATE_URL);
$current_image_display_path = '';
if ($is_current_image_url) {
    $current_image_display_path = $image_url_val;
} elseif (!empty($image_url_val)) {
    $current_image_display_path = '../public/uploads/menu_images/' . $image_url_val;
} else {
    $current_image_display_path = 'https://placehold.co/200x150/cccccc/ffffff?text=No+Img';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Menu</title>
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
                        <h1 class="m-0">Edit Item Menu</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php?page=menu_items">Manajemen Menu</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Form Edit Item Menu</h3>
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

                        <form action="edit_menu_item.php?id=<?= htmlspecialchars($item_id); ?>" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="name">Nama Menu</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= $name_val; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="price">Harga</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= $price_val; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="category">Kategori</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="coffee" <?= ($category_val == 'coffee') ? 'selected' : ''; ?>>Kopi</option>
                                    <option value="tea" <?= ($category_val == 'tea') ? 'selected' : ''; ?>>Teh</option>
                                    <option value="snack" <?= ($category_val == 'snack') ? 'selected' : ''; ?>>Snack</option>
                                    <option value="other" <?= ($category_val == 'other') ? 'selected' : ''; ?>>Lain-lain</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Gambar Menu Saat Ini</label>
                                <?php if (!empty($image_url_val)): ?>
                                    <div class="mb-2">
                                        <img src="<?= $current_image_display_path; ?>" alt="Current Menu Image" style="max-width: 200px; height: auto; border-radius: 5px;">
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="remove_image" name="remove_image" value="1">
                                        <label class="form-check-label" for="remove_image">Hapus Gambar Saat Ini</label>
                                    </div>
                                <?php else: ?>
                                    <p>Tidak ada gambar saat ini.</p>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="new_image_url_text" class="mt-2">URL Gambar Baru (Opsional)</label>
                                <input type="url" class="form-control" id="new_image_url_text" name="new_image_url_text" value="" placeholder="http://example.com/new_image.jpg">
                                <small class="form-text text-muted">Jika diisi, ini akan diprioritaskan daripada unggahan file. Pastikan format URL benar.</small>
                            </div>
                            <div class="form-group">
                                <label for="image">Atau Unggah Gambar File Baru (Opsional)</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="image" name="image">
                                        <label class="custom-file-label" for="image">Pilih file</label>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Maks. 5MB, format: JPG, PNG, GIF, WEBP. Ini akan menggantikan gambar saat ini.</small>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?= $is_active_val ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Aktif (Tampilkan di website)</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Perbarui Item Menu</button>
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
