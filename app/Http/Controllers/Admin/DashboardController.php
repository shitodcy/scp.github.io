<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- Jangan lupa import DB

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman utama dashboard (monitoring).
     */
    public function index()
    {
        // Mengambil logika dari file dashboard.php lama Anda
        $userCount = User::count();
        $dbStatus = 'Online';
        $dbStatusClass = 'bg-success';

        try {
            // Cara Laravel untuk memeriksa koneksi database
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'Offline';
            $dbStatusClass = 'bg-danger';
        }

        // Mengirim data ke view
        return view('layouts.dashboard', [
        'userCount' => $userCount,
        'dbStatus' => $dbStatus,
        'dbStatusClass' => $dbStatus === 'Online' ? 'bg-success' : 'bg-danger',
    ]);
    }
}