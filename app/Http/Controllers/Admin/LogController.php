<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity; // <-- Import model Activity

class LogController extends Controller
{
    /**
     * Menampilkan daftar log aktivitas dari database.
     */
    public function index()
    {
        // Ambil semua log, urutkan dari yang paling baru, dan buat pagination
        $activities = Activity::latest()->paginate(20); // 20 log per halaman

        return view('admin.logs.index', compact('activities'));
    }

    /**
     * Menghapus semua data log aktivitas dari database.
     */
    public function clear()
    {
        // Hapus semua data dari tabel activity_log
        Activity::truncate();

        // Catat aktivitas penghapusan itu sendiri
        activity()->log('All activity logs were cleared.');
        
        return redirect()->route('admin.logs.index')->with('success', 'Semua log aktivitas berhasil dihapus.');
    }
}