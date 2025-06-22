<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function store(Request $request)
    {
        // 1. Validate the input
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // 2. Handle the "Remember Me" checkbox
        $remember = $request->boolean('remember_me');

        // 3. Attempt to authenticate the user
        // Laravel handles password_verify, session creation, and remember_token logic here
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Redirect to the page they intended to visit, or the dashboard
            return redirect()->intended('dashboard');
        }

        // 4. If authentication fails, redirect back with an error
        // The 'username' error key is used to display the error under the username field if desired
        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Destroy an authenticated session (logout).
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}