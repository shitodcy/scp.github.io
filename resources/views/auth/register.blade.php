<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <div class="background-image"></div>
    <div class="container">
        <div class="header">
            <h1>CREATE YOUR ACCOUNT</h1>
        </div>

        {{-- Cara Laravel menampilkan semua error validasi --}}
        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('register') }}" method="post" class="register-form">
            @csrf {{-- Token keamanan wajib ada di semua form Laravel --}}

            <div class="form-group">
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    value="{{ old('full_name') }}" {{-- Menampilkan kembali input lama jika ada error --}}
                    placeholder="Full name"
                    required
                >
            </div>

            <div class="form-group">
                <input
                 type="text"
                 id="username"
                 name="username" {{-- Pastikan ini "name", bukan "username" --}}
                 value="{{ old('username') }}"
                 placeholder="Username"
                 required
                 >
                <small class="input-hint">Username minimal 3 karakter, hanya huruf, angka, dan underscore</small>
            </div>

            <div class="form-group">
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Email"
                    required
                >
                <small class="input-hint">Hanya email dengan domain @students.amikom.ac.id yang diperbolehkan</small>
            </div>

            <div class="form-group">
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Password"
                    required
                >
                <small class="input-hint">Password minimal 6 karakter</small>
            </div>

            <div class="form-group">
                {{-- Laravel menangani konfirmasi password melalui rule 'confirmed' --}}
                <input
                    type="password"
                    id="password_confirmation" {{-- Nama harus 'password_confirmation' --}}
                    name="password_confirmation"
                    placeholder="Confirm Password"
                    required
                >
            </div>

            <p class="login-prompt">Already a member? <a href="{{ route('login') }}">Log in</a></p>
            <button type="submit" class="btn-signup">
                <i class="fas fa-user-plus"></i> Sign up
            </button>
        </form>
    </div>
</body>
</html>