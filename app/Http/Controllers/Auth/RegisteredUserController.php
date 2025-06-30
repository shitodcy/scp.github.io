<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;


use App\Rules\IsAmikomEmail;
use App\Rules\IsApprovedEmail;
use Illuminate\Http\RedirectResponse;

class RegisteredUserController extends Controller
{

    public function create()
    {
        return view('auth.register');
    }



public function store(Request $request): RedirectResponse
{

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


    $user = User::create([
        'full_name' => $request->full_name,
        'username' => $request->username, 
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    event(new Registered($user));

    Auth::login($user);

    return redirect()->route('admin.dashboard');
}
}
