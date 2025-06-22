<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TeamMember;
use Illuminate\Support\Facades\Storage;

class TeamMemberController extends Controller
{
    // Menampilkan daftar semua anggota tim
    public function index()
    {
        $teamMembers = TeamMember::latest()->get();
        return view('admin.teams.index', compact('teamMembers'));
    }

    // Menampilkan form untuk menambah anggota baru
    public function create()
    {
        return view('admin.teams.create');
    }

    // Menyimpan anggota baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|max:20',
            'job_title' => 'nullable|string|max:100',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // Validasi untuk upload file
        ]);

        $imagePath = $request->file('image')->store('team_images', 'public');

        TeamMember::create([
            'name' => $request->name,
            'student_id' => $request->student_id,
            'job_title' => $request->job_title,
            'image_url' => $imagePath, // Simpan path gambar
        ]);

        return redirect()->route('admin.teams.index')->with('success', 'Anggota tim berhasil ditambahkan.');
    }

    // Menampilkan form untuk mengedit anggota
    public function edit(TeamMember $team)
    {
        return view('admin.teams.edit', ['member' => $team]);
    }

    // Memperbarui data anggota di database
    public function update(Request $request, TeamMember $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|max:20',
            'job_title' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Gambar opsional saat update
        ]);

        $imagePath = $team->image_url; // Gunakan gambar lama sebagai default
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            Storage::disk('public')->delete($team->image_url);
            // Upload gambar baru
            $imagePath = $request->file('image')->store('team_images', 'public');
        }

        $team->update([
            'name' => $request->name,
            'student_id' => $request->student_id,
            'job_title' => $request->job_title,
            'image_url' => $imagePath,
        ]);

        return redirect()->route('admin.teams.index')->with('success', 'Data anggota tim berhasil diperbarui.');
    }

    // Menghapus anggota tim dari database
    public function destroy(TeamMember $team)
    {
        Storage::disk('public')->delete($team->image_url);
        $team->delete();
        return redirect()->route('admin.teams.index')->with('success', 'Anggota tim berhasil dihapus.');
    }
}

