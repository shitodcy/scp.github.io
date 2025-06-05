<?php
require_once 'auth_check.php'; // Wajib login
require_once '../config/database.php';

// Ambil data user: id, username, email, full_name, dan created_at
try {
    $stmt = $conn->prepare("SELECT id, username, email, full_name, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $page_error = "Error mengambil data user: " . $e->getMessage();
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manajemen User</title>
    <link rel="stylesheet" href="../public/css/style.css" />
</head>
<body>
    <div class="container">
        <div class="header-nav">
            <h2>Manajemen User</h2>
            <div>
                <span>Halo, <?php echo htmlspecialchars($_SESSION['username']); ?> | </span>
                <a href="/index.html">Halaman Utama</a> |
                <a href="../auth/logout.php">Logout</a>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success'; ?>">
                <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
            </div>
            <?php
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <?php if (isset($page_error)): ?>
            <div class="errors"><p><?php echo htmlspecialchars($page_error); ?></p></div>
        <?php endif; ?>

        <p><a href="create.php" class="btn">Tambah User Baru</a></p>

        <?php if (count($users) > 0): ?>
            <table>
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
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn-edit">Edit</a>
                                <?php if ($_SESSION['user_id'] != $user['id']): ?>
                                    <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?');">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Belum ada user terdaftar.</p>
        <?php endif; ?>
    </div>
</body>
</html>