<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Spatie\Activitylog\Facades\Activity;

class UserController extends Controller
{
    /**
     * Menampilkan halaman daftar semua user.
     */
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Menampilkan form untuk membuat user baru.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Menyimpan user baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // --- PERUBAHAN DI SINI ---
        activity()->useLog('info')->log('Created new user: ' . $user->username);

        return redirect()->route('admin.users.index')
                         ->with('success', 'User baru berhasil ditambahkan!');
    }

    /**
     * Menampilkan form untuk mengedit user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Memperbarui data user di database.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $updateData = $request->only('full_name', 'username', 'email');

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $updateData['profile_image'] = $request->file('profile_image')->store('profile_pictures', 'public');
        }

        $user->update($updateData);
        
        // --- PERUBAHAN DI SINI ---
        activity()->useLog('info')->log('Updated user: ' . $user->username);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Data user berhasil diperbarui!');
    }

    /**
     * Menghapus user dari database.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }
        
        $userName = $user->username;
        $user->delete();

        // --- PERUBAHAN DI SINI ---
        activity()->useLog('delete')->log('Deleted user: ' . $userName);

        return redirect()->route('admin.users.index')
                         ->with('success', 'User berhasil dihapus.');
    }
}
