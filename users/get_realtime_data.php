<?php
// get_realtime_data.php

// Pastikan file ini ada dan berfungsi untuk koneksi database
require_once '../config/database.php';

header('Content-Type: application/json');

// Inisialisasi data yang akan dikembalikan
$realtime_data = [
    'pengunjung_hari_ini' => 0,
    'daily_visits_labels' => [],
    'daily_visits_data' => [],
    'last_update' => date('d M Y, H:i:s'),
    'db_status' => 'Offline',
    'db_status_class' => 'bg-danger',
    'db_status_icon' => 'fas fa-exclamation-triangle',
    'active_users_count' => 'N/A'
];

try {
    // --- Data Pengunjung Hari Ini (Contoh Realtime) ---
    // ASUMSI: Anda memiliki tabel atau mekanisme untuk mencatat pengunjung harian.
    // Ini adalah contoh dummy. GANTI dengan LOGIKA NYATA Anda.
    // Misalnya, Anda bisa punya tabel `daily_visits` dengan `date` dan `count`.
    // Atau Anda bisa menggunakan Redis/file counter yang diperbarui setiap kali ada visitor.

    // Untuk demo, kita akan membuat angka acak yang sedikit berbeda setiap kali dipanggil
    // Ini BUKAN solusi realtime sungguhan tanpa backend pelacakan.
    $current_day_visits = 1410 + rand(-50, 50); // Angka asli + sedikit variasi
    if ($current_day_visits < 0) $current_day_visits = 0; // Pastikan tidak negatif

    $realtime_data['pengunjung_hari_ini'] = number_format($current_day_visits, 0, ',', '.'); // Format angka

    // --- Data Tren Pengunjung Harian (Contoh) ---
    // Data ini juga harus diambil dari database Anda
    $daily_visits_labels = [];
    $daily_visits_data = [];

    // Mengambil data untuk 7 hari terakhir
    for ($i = 6; $i >= 0; $i--) {
        $date_str = date('Y-m-d', strtotime("-$i days"));
        $day_label = date('D', strtotime("-$i days")); // Contoh: Mon, Tue, Wed

        // GANTI INI dengan kueri database Anda yang sebenarnya
        // Contoh: SELECT visit_count FROM daily_stats WHERE visit_date = '$date_str'
        $dummy_visit_count = rand(800, 1500);
        if ($i == 0) { // Untuk hari ini, gunakan angka realtime yang sudah digenerate
            $dummy_visit_count = $current_day_visits;
            $day_label = 'Hari Ini'; // Ubah label hari ini
        }

        $daily_visits_labels[] = $day_label;
        $daily_visits_data[] = $dummy_visit_count;
    }

    $realtime_data['daily_visits_labels'] = $daily_visits_labels;
    $realtime_data['daily_visits_data'] = $daily_visits_data;

    // --- Status Database (diambil dari logika dashboard.php) ---
    $stmt_check_db = $conn->query("SELECT NOW()");
    $stmt_check_db->fetch();
    $realtime_data['db_status'] = 'Online';
    $realtime_data['db_status_class'] = 'bg-success';
    $realtime_data['db_status_icon'] = 'fas fa-database';

    // Ambil waktu terakhir diperbarui dari database
    $stmt_time = $conn->query("SELECT NOW()");
    $last_update_db = $stmt_time->fetchColumn();
    $realtime_data['last_update'] = date('d M Y, H:i:s', strtotime($last_update_db));

    // Ambil jumlah user terdaftar
    $stmt_active_users = $conn->query("SELECT COUNT(id) FROM users");
    $realtime_data['active_users_count'] = $stmt_active_users->fetchColumn();

} catch (PDOException $e) {
    // Jika ada masalah koneksi database, tetap berikan status offline
    error_log("Error in get_realtime_data.php: " . $e->getMessage());
    // realtim_data sudah diset default ke offline di awal
}

echo json_encode($realtime_data);