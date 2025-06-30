<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Untuk logging Laravel

class AuthController extends Controller
{
    /**
     * Menampilkan form login.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm()
    {
        // --- REMEMBER ME (Laravel handles this implicitly via Auth::check()) ---
        // Jika user sudah login (baik langsung atau via remember me cookie), redirect.
        if (Auth::check()) {
            // Log aktivitas ini
            Log::info("User '" . (Auth::user()->username ?? 'UNKNOWN') . "' attempted to access login form while already logged in.", ['user_id' => Auth::id()]);
            return redirect()->route('dashboard'); // Ganti 'dashboard' dengan nama route dashboard Anda
        }

        return view('auth.login'); // Mengarah ke resources/views/auth/login.blade.php
    }

    /**
     * Menangani proses login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Input Validation (menggantikan if (empty(...)) manual Anda)
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $credentials = $request->only('username', 'password');
        $remember = $request->boolean('remember_me'); // Mengambil nilai boolean dari checkbox

        // Attempt autentikasi menggunakan Auth facade Laravel
        // Auth::attempt akan otomatis memeriksa password_verify dan remember_me
        if (Auth::attempt($credentials, $remember)) {
            // Regenerate session ID untuk mencegah session fixation attacks
            $request->session()->regenerate();

            // Ambil data user yang baru login
            $user = Auth::user();

            // Logging sukses login
            Log::info("User '{$user->username}' logged in successfully.", ['user_id' => $user->id, 'ip_address' => $request->ip()]);

            // Redirect ke halaman yang dimaksud atau dashboard
            return redirect()->intended(route('dashboard')); // Ganti 'dashboard' dengan nama route dashboard Anda
        }

        // Jika autentikasi gagal
        Log::warning("Failed login attempt for username '{$request->username}' (incorrect credentials).", ['ip_address' => $request->ip()]);

        // Redirect kembali dengan error dan input lama
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput($request->only('username', 'remember_me'));
    }

    /**
     * Menangani proses logout.
     * Anda mungkin ingin menambahkan rute logout ini juga.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $username = Auth::user() ? Auth::user()->username : 'UNKNOWN';

        Auth::logout(); // Logout user saat ini

        $request->session()->invalidate(); // Invalidasi session saat ini
        $request->session()->regenerateToken(); // Regenerasi CSRF token

        Log::info("User '{$username}' logged out.", ['user_id' => null, 'ip_address' => $request->ip()]);

        return redirect('/'); // Redirect ke halaman utama atau halaman login
    }
}