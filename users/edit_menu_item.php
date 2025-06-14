<?php
session_start(); // Tetap butuh session untuk pesan $_SESSION dan admin_username untuk logging
// require_once 'auth_check.php'; // Hapus ini karena halaman ini standalone
require_once '../config/database.php';
require_once '../utils/logger.php'; // Pastikan ini sudah ada

$errors = [];
$item_id = $_GET['id'] ?? null;
$item_data = null;

// Dapatkan username admin yang sedang login untuk konteks logging
// Kita akan mengasumsikan ada sesi 'username' yang aktif
// Jika halaman ini benar-benar standalone dan tidak memerlukan login,
// Anda mungkin ingin mengubah ini menjadi 'SYSTEM' atau 'ANONYMOUS'
$current_admin_username = $_SESSION['username'] ?? 'GUEST_OR_SYSTEM'; // Default jika tidak ada user login

// VERIFIKASI ID ITEM MENU (tetap penting meskipun standalone)
if (!$item_id || !is_numeric($item_id)) { // Tambahkan is_numeric untuk validasi ID
    $_SESSION['message'] = "ID item menu tidak ditemukan atau tidak valid.";
    $_SESSION['message_type'] = "danger";
    log_activity("Attempt to edit menu item with missing or invalid ID: '{$item_id}'.", 'WARNING', $current_admin_username);
    header("Location: dashboard.php?page=menu_items"); // Redirect kembali ke dashboard jika ID tidak valid
    exit();
}

// Path untuk menyimpan gambar menu (pastikan folder ada dan bisa ditulis)
$upload_dir = '../public/uploads/menu_images/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) { // Buat direktori jika belum ada
        $errors[] = "Gagal membuat direktori upload: {$upload_dir}. Periksa izin server.";
        log_activity("Failed to create menu image upload directory: {$upload_dir}. Check permissions.", 'ERROR', 'SYSTEM');
    }
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
        log_activity("Attempt to edit non-existent menu item with ID: {$item_id}.", 'WARNING', $current_admin_username);
        header("Location: dashboard.php?page=menu_items"); // Redirect kembali ke dashboard jika item tidak ditemukan
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Error database saat mengambil data item menu: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
    log_activity("Database error fetching menu item data for ID {$item_id}: " . $e->getMessage(), 'ERROR', $current_admin_username);
    header("Location: dashboard.php?page=menu_items"); // Redirect kembali ke dashboard jika error database
    exit();
}

// If form is submitted for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_item_data = $item_data; // Store original data for comparison in log

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
        log_activity("Failed to update menu item ID {$item_id} ('{$old_item_data['name']}'): Name is empty.", 'WARNING', $current_admin_username);
    }
    if (!is_numeric($price) || $price <= 0) {
        $errors[] = "Harga harus berupa angka positif.";
        log_activity("Failed to update menu item ID {$item_id} ('{$old_item_data['name']}'): Invalid price '{$price}'.", 'WARNING', $current_admin_username);
    }
    if (empty($category) || !in_array($category, ['coffee', 'tea', 'snack', 'other'])) {
        $errors[] = "Kategori tidak valid.";
        log_activity("Failed to update menu item ID {$item_id} ('{$old_item_data['name']}'): Invalid category '{$category}'.", 'WARNING', $current_admin_username);
    }

    // Logic for image handling: Prioritize new URL, then file upload, then existing, then remove
    if (!empty($new_image_url_text)) {
        if (filter_var($new_image_url_text, FILTER_VALIDATE_URL)) {
            // New URL provided, use it and delete old file if it was a file upload
            if ($final_image_path && !filter_var($final_image_path, FILTER_VALIDATE_URL)) { // Check if it was a local file
                if (file_exists($upload_dir . $final_image_path)) {
                    unlink($upload_dir . $final_image_path);
                    log_activity("Deleted old local image '{$final_image_path}' for menu item ID {$item_id} due to new URL.", 'INFO', $current_admin_username);
                }
            }
            $final_image_path = $new_image_url_text;
            log_activity("Menu item ID {$item_id} ('{$old_item_data['name']}'): Image updated with new URL.", 'INFO', $current_admin_username);
        } else {
            $errors[] = "Format URL gambar baru tidak valid.";
            log_activity("Failed to update menu item ID {$item_id} ('{$old_item_data['name']}'): Invalid new image URL format '{$new_image_url_text}'.", 'WARNING', $current_admin_username);
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // New file uploaded, use it and delete old image (URL or file)
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                 $errors[] = "Gagal membuat direktori upload: {$upload_dir}. Periksa izin server.";
                 log_activity("Failed to create menu image upload directory: {$upload_dir}. Check permissions.", 'ERROR', 'SYSTEM');
            }
        }

        // Delete old image if it was a local file
        if ($final_image_path && !filter_var($final_image_path, FILTER_VALIDATE_URL)) {
            if (file_exists($upload_dir . $final_image_path)) {
                unlink($upload_dir . $final_image_path);
                log_activity("Deleted old local image '{$final_image_path}' for menu item ID {$item_id} due to new file upload.", 'INFO', $current_admin_username);
            }
        }

        $file_tmp_name = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($file_ext, $allowed_extensions)) {
            $errors[] = "Jenis file gambar tidak diizinkan. Hanya JPG, JPEG, PNG, GIF, WEBP yang diizinkan.";
            log_activity("Failed to upload new image for menu item ID {$item_id} ('{$old_item_data['name']}'): Invalid file extension '{$file_extension}'.", 'WARNING', $current_admin_username);
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB max
            $errors[] = "Ukuran file gambar terlalu besar (maks 5MB).";
            log_activity("Failed to upload new image for menu item ID {$item_id} ('{$old_item_data['name']}'): File size too large ({$_FILES['image']['size']} bytes).", 'WARNING', $current_admin_username);
        } else {
            $new_file_name = uniqid('menu_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_name, $upload_path)) {
                $final_image_path = $new_file_name;
                log_activity("Uploaded new local image '{$new_file_name}' for menu item ID {$item_id} ('{$old_item_data['name']}').", 'INFO', $current_admin_username);
            } else {
                $errors[] = "Gagal mengunggah gambar file baru.";
                log_activity("Failed to move uploaded new image for menu item ID {$item_id} ('{$old_item_data['name']}'). PHP error: " . error_get_last()['message'] ?? 'Unknown', 'ERROR', $current_admin_username);
            }
        }
    } elseif ($remove_existing_image) {
        // Remove existing image, delete local file if it was one
        if ($final_image_path && !filter_var($final_image_path, FILTER_VALIDATE_URL)) {
            if (file_exists($upload_dir . $final_image_path)) {
                unlink($upload_dir . $final_image_path);
                log_activity("Removed existing local image '{$final_image_path}' for menu item ID {$item_id} ('{$old_item_data['name']}').", 'INFO', $current_admin_username);
            }
        }
        $final_image_path = null; // Set image_url to null in DB
        log_activity("Menu item ID {$item_id} ('{$old_item_data['name']}'): Existing image explicitly removed.", 'INFO', $current_admin_username);
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

            $log_message = "Updated menu item ID {$item_id} ('{$old_item_data['name']}'). Changes: ";
            $changes = [];

            // Track changes for logging
            if ($old_item_data['name'] !== $name) $changes[] = "Name from '{$old_item_data['name']}' to '{$name}'";
            if ($old_item_data['price'] !== $price) $changes[] = "Price from '{$old_item_data['price']}' to '{$price}'";
            if ($old_item_data['category'] !== $category) $changes[] = "Category from '{$old_item_data['category']}' to '{$category}'";
            if ($old_item_data['image_url'] !== $final_image_path) $changes[] = "Image URL updated";
            if ($old_item_data['is_active'] !== $is_active) $changes[] = "Active status from '" . ($old_item_data['is_active'] ? 'Active' : 'Inactive') . "' to '" . ($is_active ? 'Active' : 'Inactive') . "'";

            if ($stmt->execute()) {
                $_SESSION['message'] = "Item menu berhasil diperbarui!";
                $_SESSION['message_type'] = "success";

                if (empty($changes)) {
                    $log_message .= "No significant data changes.";
                } else {
                    $log_message .= implode("; ", $changes) . ".";
                }
                log_activity($log_message, 'INFO', $current_admin_username);

                header("Location: dashboard.php?page=menu_items"); // Redirect ke dashboard setelah update
                exit();
            } else {
                $errors[] = "Gagal memperbarui item menu.";
                $error_info = $stmt->errorInfo();
                log_activity("Database error updating menu item ID {$item_id} ('{$old_item_data['name']}'): " . ($error_info[2] ?? 'Unknown error'), 'ERROR', $current_admin_username);
            }
        } catch (PDOException $e) {
            $errors[] = "Error database: " . $e->getMessage();
            log_activity("PDOException updating menu item ID {$item_id} ('{$old_item_data['name']}'): " . $e->getMessage(), 'ERROR', $current_admin_username);
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
            log_activity("Database error re-fetching menu data after failed update for ID {$item_id}: " . $e->getMessage(), 'ERROR', 'SYSTEM');
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=fallback">
    <link rel="stylesheet" href="../public/css/edit_menu_item.css">

</head>
<body class="dark-mode"> <div class="page-container-standalone"> <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php?page=menu_items"><i class="fas fa-arrow-left"></i> Kembali ke Manajemen Menu</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Item</li>
            </ol>
        </nav>
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
                                <img src="<?= $current_image_display_path; ?>" alt="Current Menu Image" class="menu-image-preview">
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
                            <div class="d-flex justify-content-between mt-4">
                                <a href="dashboard.php?page=menu_items" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Perbarui Item Menu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
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




