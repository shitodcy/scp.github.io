<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * Menampilkan daftar semua anggota tim.
     */
    public function index()
    {
        $teamMembers = TeamMember::orderBy('id')->get();
        return view('admin.teams.index', compact('teamMembers'));
    }

    /**
     * Menampilkan form untuk menambah anggota baru.
     */
    public function create()
    {
        return view('admin.teams.create');
    }

    /**
     * Menyimpan anggota baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|max:20|unique:team_members,student_id',
            'job_title' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url' => 'nullable|url',
        ]);

        $imagePathOrUrl = null;

        if ($request->filled('image_url')) {
            $imagePathOrUrl = $request->input('image_url');
        } elseif ($request->hasFile('image')) {
            $imagePathOrUrl = $request->file('image')->store('team_images', 'public');
        }

        // Simpan ke variabel agar bisa diambil namanya untuk log
        $newTeamMember = TeamMember::create([
            'name' => $validatedData['name'],
            'student_id' => $validatedData['student_id'],
            'job_title' => $validatedData['job_title'],
            'image_url' => $imagePathOrUrl,
        ]);

        // Log aktivitas
        Log::create([
            'user_id'     => Auth::id(),
            'action'      => 'TAMBAH_TIM',
            'description' => 'Pengguna ' . Auth::user()->username . ' menambahkan anggota tim baru: ' . $newTeamMember->name
        ]);

        return redirect()->route('admin.teams.index')
                         ->with('success', 'Anggota tim baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit anggota.
     * (INI METHOD YANG HILANG)
     */
    public function edit(TeamMember $team)
    {
        // Method ini hanya bertugas mengirim data member yang akan diedit ke view
        return view('admin.teams.edit', compact('team'));
    }


    /**
     * Memperbarui data anggota di database.
     */
    public function update(Request $request, TeamMember $team)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|max:20|unique:team_members,student_id,' . $team->id,
            'job_title' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url' => 'nullable|url',
        ]);

        // Perbaikan: Kita gunakan $updateData agar tidak error saat gambar tidak diupdate
        $updateData = [
            'name' => $validatedData['name'],
            'student_id' => $validatedData['student_id'],
            'job_title' => $validatedData['job_title'],
        ];

        $oldImagePath = $team->image_url;
        $newImageSource = null;

        if ($request->filled('image_url')) {
            $newImageSource = $request->input('image_url');
        } elseif ($request->hasFile('image')) {
            $newImageSource = $request->file('image')->store('team_images', 'public');
        }

        if ($newImageSource) {
            $updateData['image_url'] = $newImageSource;
            if ($oldImagePath && !Str::startsWith($oldImagePath, 'http')) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }
        
        $team->update($updateData);
        
        // Perbaikan: Aksi log diubah menjadi UPDATE_TIM
        Log::create([
            'user_id'     => Auth::id(),
            'action'      => 'UPDATE_TIM',
            'description' => 'Pengguna ' . Auth::user()->username . ' memperbarui anggota tim: ' . $team->name
        ]);

        return redirect()->route('admin.teams.index')
                         ->with('success', 'Data anggota tim berhasil diperbarui.');
    }

    /**
     * Menghapus anggota dari database.
     */
    public function destroy(TeamMember $team)
    {
        $teamName = $team->name;
        $username = Auth::user()->username; // Gunakan username agar konsisten

        if ($team->image_url && !Str::startsWith($team->image_url, 'http')) {
            Storage::disk('public')->delete($team->image_url);
        }

        $team->delete();

        Log::create([
           'user_id'     => Auth::id(),
           'action'      => 'HAPUS_TIM',
           'description' => "Pengguna {$username} menghapus anggota tim: {$teamName}"
        ]);
    
        return redirect()->route('admin.teams.index')
                         ->with('success', 'Anggota tim berhasil dihapus.');
    }
}