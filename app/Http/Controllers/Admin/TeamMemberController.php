<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TeamMember;
use Illuminate\Support\Facades\Storage;

class TeamMemberController extends Controller
{

    public function index()
    {
        $teamMembers = TeamMember::latest()->get();
        return view('admin.teams.index', compact('teamMembers'));
    }


    public function create()
    {
        return view('admin.teams.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|max:20',
            'job_title' => 'nullable|string|max:100',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePath = $request->file('image')->store('team_images', 'public');

        TeamMember::create([
            'name' => $request->name,
            'student_id' => $request->student_id,
            'job_title' => $request->job_title,
            'image_url' => $imagePath,
        ]);

        return redirect()->route('admin.teams.index')->with('success', 'Anggota tim berhasil ditambahkan.');
    }


    public function edit(TeamMember $team)
    {
        return view('admin.teams.edit', ['member' => $team]);
    }


    public function update(Request $request, TeamMember $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|max:20',
            'job_title' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePath = $team->image_url;
        if ($request->hasFile('image')) {

            Storage::disk('public')->delete($team->image_url);

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


    public function destroy(TeamMember $team)
    {
        Storage::disk('public')->delete($team->image_url);
        $team->delete();
        return redirect()->route('admin.teams.index')->with('success', 'Anggota tim berhasil dihapus.');
    }
}

