<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Models\TeamMember;

class PageController extends Controller
{
    /**
     * Menampilkan halaman utama dengan data menu dan tim.
     */
    public function index()
    {
        // 1. Ambil data menu yang aktif
        $activeMenuItems = MenuItem::where('is_active', true)->get();
        $categorizedMenu = $activeMenuItems->groupBy('category');

        // 2. Ambil data anggota tim
        $teamMembers = TeamMember::all();

        // 3. Kirim semua data ke view 'index'
        return view('index', [
            'categorizedMenu' => $categorizedMenu,
            'teamMembers'     => $teamMembers,
        ]);
    }
}