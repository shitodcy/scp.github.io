<?php
session_start();
require_once 'auth_check.php';
require_once '../config/database.php';
require_once '../utils/logger.php'; // <<< ADD THIS LINE

$errors = [];
$name = '';
$price = '';
$category = '';
$image_url_text = '';
$is_active = true;

// Get the current logged-in user's username for logging context
$current_admin_username = $_SESSION['username'] ?? 'UNKNOWN_ADMIN';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $image_url_text = trim($_POST['image_url_text'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Basic validation
    if (empty($name)) {
        $errors[] = "Nama menu wajib diisi.";
        log_activity("Failed attempt to create menu item: Name is empty.", 'WARNING', $current_admin_username); // <<< ADD LOG
    }
    if (!is_numeric($price) || $price <= 0) {
        $errors[] = "Harga harus berupa angka positif.";
        log_activity("Failed attempt to create menu item (name: {$name}): Invalid price '{$price}'.", 'WARNING', $current_admin_username); // <<< ADD LOG
    }
    if (empty($category) || !in_array($category, ['coffee', 'tea', 'snack', 'other'])) {
        $errors[] = "Kategori tidak valid.";
        log_activity("Failed attempt to create menu item (name: {$name}): Invalid category '{$category}'.", 'WARNING', $current_admin_username); // <<< ADD LOG
    }

    $final_image_path = null;

    // Prioritize URL if provided
    if (!empty($image_url_text)) {
        if (filter_var($image_url_text, FILTER_VALIDATE_URL)) {
            $final_image_path = $image_url_text; // Use the provided URL directly
            log_activity("Using external image URL for menu item '{$name}'.", 'INFO', $current_admin_username); // <<< ADD LOG
        } else {
            $errors[] = "Format URL gambar tidak valid.";
            log_activity("Failed to create menu item (name: {$name}): Invalid image URL format '{$image_url_text}'.", 'WARNING', $current_admin_username); // <<< ADD LOG
        }
    } else {
        // Fallback to file upload if no URL is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../public/uploads/menu_images/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) { // Create directory if it doesn't exist
                    $errors[] = "Gagal membuat direktori upload: {$upload_dir}. Periksa izin server.";
                    log_activity("Failed to create menu image upload directory: {$upload_dir}. Check permissions.", 'ERROR', 'SYSTEM'); // <<< ADD LOG
                }
            }

            $file_tmp_name = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($file_extension, $allowed_extensions)) {
                $errors[] = "Jenis file gambar tidak diizinkan. Hanya JPG, JPEG, PNG, GIF, WEBP yang diizinkan.";
                log_activity("Failed to upload image for menu item '{$name}': Invalid file extension '{$file_extension}'.", 'WARNING', $current_admin_username); // <<< ADD LOG
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB max
                $errors[] = "Ukuran file gambar terlalu besar (maks 5MB).";
                log_activity("Failed to upload image for menu item '{$name}': File size too large ({$_FILES['image']['size']} bytes).", 'WARNING', $current_admin_username); // <<< ADD LOG
            } else {
                // Generate unique file name
                $new_file_name = uniqid('menu_') . '.' . $file_extension;
                $upload_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp_name, $upload_path)) {
                    $final_image_path = $new_file_name; // Store just the file name
                    log_activity("Successfully uploaded image '{$new_file_name}' for menu item '{$name}'.", 'INFO', $current_admin_username); // <<< ADD LOG
                } else {
                    $errors[] = "Gagal mengunggah gambar file.";
                    log_activity("Failed to move uploaded image for menu item '{$name}'. PHP error: " . error_get_last()['message'] ?? 'Unknown', 'ERROR', $current_admin_username); // <<< ADD LOG
                }
            }
        } else if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors[] = "Terjadi kesalahan saat mengunggah file: " . $_FILES['image']['error'];
            log_activity("File upload error for menu item '{$name}': Code " . $_FILES['image']['error'], 'ERROR', $current_admin_username); // <<< ADD LOG for other upload errors
        }
    }

    // Insert into database if no errors
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO menu_items (name, price, category, image_url, is_active, created_at) VALUES (:name, :price, :category, :image_url, :is_active, NOW())");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':image_url', $final_image_path);
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Item menu berhasil ditambahkan!";
                $_SESSION['message_type'] = "success";
                log_activity("Successfully created new menu item: '{$name}' (Category: {$category}, Price: {$price}).", 'INFO', $current_admin_username); // <<< ADD LOG
                header("Location: dashboard.php?page=menu_items");
                exit();
            } else {
                $errors[] = "Gagal menambahkan item menu.";
                $error_info = $stmt->errorInfo();
                log_activity("Database error adding menu item '{$name}': " . ($error_info[2] ?? 'Unknown error'), 'ERROR', $current_admin_username); // <<< ADD LOG
            }
        } catch (PDOException $e) {
            $errors[] = "Error database: " . $e->getMessage();
            log_activity("PDOException adding menu item '{$name}': " . $e->getMessage(), 'ERROR', $current_admin_username); // <<< ADD LOG
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/create_menu_item.css">

</head>
<body>
    <div class="page-container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php?page=menu_items">Manajemen Menu</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Menu</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-utensils me-2"></i>Tambah Item Menu Baru</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Oops! Ada kesalahan:</h5>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="create_menu_item.php" method="POST" enctype="multipart/form-data">
                    <div class="form-section">
                        <h5 class="section-title"><i class="fas fa-info-circle"></i>Informasi Dasar</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama Menu</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name); ?>" placeholder="Contoh: Cappuccino" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Harga (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                    <input type="number" step="1000" class="form-control" id="price" name="price" value="<?= htmlspecialchars($price); ?>" placeholder="Contoh: 25000" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Kategori</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="coffee" <?= ($category == 'coffee') ? 'selected' : ''; ?>><i class="fas fa-coffee"></i> Kopi</option>
                                <option value="tea" <?= ($category == 'tea') ? 'selected' : ''; ?>><i class="fas fa-mug-hot"></i> Teh</option>
                                <option value="snack" <?= ($category == 'snack') ? 'selected' : ''; ?>><i class="fas fa-cookie"></i> Snack</option>
                                <option value="other" <?= ($category == 'other') ? 'selected' : ''; ?>><i class="fas fa-utensils"></i> Lain-lain</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5 class="section-title"><i class="fas fa-image"></i>Gambar Menu</h5>

                        <div class="image-preview mb-3" id="imagePreview">
                            <div class="image-preview-placeholder">
                                <i class="fas fa-image fa-3x mb-2"></i>
                                <p>Preview gambar akan ditampilkan di sini</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image_url_text" class="form-label">URL Gambar Menu (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-link"></i></span>
                                <input type="url" class="form-control" id="image_url_text" name="image_url_text" value="<?= htmlspecialchars($image_url_text); ?>" placeholder="https://example.com/image.jpg">
                            </div>
                            <div class="form-text">Jika diisi, ini akan diprioritaskan daripada unggahan file.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Atau Unggah Gambar File (Opsional)</label>
                            <div class="file-upload-wrapper">
                                <input type="file" class="file-upload-input" id="image" name="image" accept="image/*">
                                <div class="file-upload-text">
                                    <i class="fas fa-upload me-2"></i>
                                    <span id="file-upload-name">Pilih file gambar...</span>
                                </div>
                            </div>
                            <div class="form-text">Maks. 5MB, format: JPG, PNG, GIF, WEBP.</div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5 class="section-title"><i class="fas fa-cog"></i>Pengaturan Tambahan</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $is_active ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Aktif (Tampilkan di website)</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="dashboard.php?page=menu_items" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Item Menu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle file input change
        const fileInput = document.getElementById('image');
        const fileUploadName = document.getElementById('file-upload-name');
        const imagePreview = document.getElementById('imagePreview');
        const imageUrlInput = document.getElementById('image_url_text');

        // Function to update image preview
        function updatePreview(src) {
            // Clear previous preview
            imagePreview.innerHTML = '';

            // Create and add image element
            const img = document.createElement('img');
            img.src = src;
            img.alt = 'Preview';
            imagePreview.appendChild(img);
        }

        // Handle file selection
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                fileUploadName.textContent = file.name;

                const reader = new FileReader();
                reader.onload = function(e) {
                    updatePreview(e.target.result);
                };
                reader.readAsDataURL(file);
            } else {
                fileUploadName.textContent = 'Pilih file gambar...';
            }
        });

        // Handle URL input change
        imageUrlInput.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                updatePreview(this.value);
            } else {
                // If URL is cleared and there's a file selected, show file preview
                if (fileInput.files && fileInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        updatePreview(e.target.result);
                    };
                    reader.readAsDataURL(fileInput.files[0]);
                } else {
                    // If no URL and no file, show placeholder
                    imagePreview.innerHTML = `
                        <div class="image-preview-placeholder">
                            <i class="fas fa-image fa-3x mb-2"></i>
                            <p>Preview gambar akan ditampilkan di sini</p>
                        </div>
                    `;
                }
            }
        });

        // Add icons to category options
        const categorySelect = document.getElementById('category');
        const options = categorySelect.options;

        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            let icon = '';

            switch (option.value) {
                case 'coffee':
                    icon = 'coffee';
                    break;
                case 'tea':
                    icon = 'mug-hot';
                    break;
                case 'snack':
                    icon = 'cookie';
                    break;
                case 'other':
                    icon = 'utensils';
                    break;
            }

            if (icon) {
                option.innerHTML = `<i class="fas fa-${icon}"></i> ${option.text}`;
            }
        }
    });
    </script>
</body>
</html>