<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\TeamMember; // <-- PASTIKAN INI ADA
use Illuminate\Http\Request;

class PublicMenuController extends Controller
{
    public function index()
    {
        // ... (mungkin ada kode lain untuk mengambil data menu)
        $categorizedMenu = MenuItem::where('is_active', true)->get()->groupBy('category');

        // INI BAGIAN PENTINGNYA:
        // 1. Mengambil data dari database
        $teamMembers = TeamMember::all(); 
        
        // 2. Mengirim data '$teamMembers' ke view 'index'
        return view('index', compact('categorizedMenu', 'teamMembers')); 
    }
}