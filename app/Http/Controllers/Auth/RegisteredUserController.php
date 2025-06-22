<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

// Impor custom rules yang sudah kita buat
use App\Rules\IsAmikomEmail;
use App\Rules\IsApprovedEmail;
use Illuminate\Http\RedirectResponse;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    // app/Http/Controllers/Auth/RegisteredUserController.php

public function store(Request $request): RedirectResponse
{
    // Validasi diubah dari 'username' menjadi 'name'
    $request->validate([
        'full_name' => ['required', 'string', 'min:2', 'max:255'],
        'username' => ['required', 'string', 'min:3', 'max:255', 'regex:/^[a-zA-Z0-9_]+$/', 'unique:users,username'], // <-- DIUBAH DI SINI
        'email' => [
            'required', 
            'string', 
            'email', 
            'max:255', 
            'unique:users,email',
            new IsAmikomEmail,
            new IsApprovedEmail,
        ],
        'password' => ['required', 'confirmed', Rules\Password::min(6)],
    ]);

    // Membuat user dengan kolom 'name'
    $user = User::create([
        'full_name' => $request->full_name,
        'username' => $request->username, // <-- DIUBAH DI SINI
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    event(new Registered($user));

    Auth::login($user);

    return redirect()->route('dashboard');
}
}