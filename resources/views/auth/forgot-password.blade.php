<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Kedai Kopi Kayu</title>
    <link rel="icon" href="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598314/logokkk_rtchku.ico" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/forgot_password.css') }}">
</head>
<body class="auth-page"> {{-- Tambahkan class auth-page --}}
    <div class="auth-container">
        <div class="auth-image-column">
            <img src="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598325/fotolokasi.depan_av71fx.webp" alt="Kedai Kopi Kayu">
            <div class="overlay">
                <p>If you can do it yourself why not @scp9242</p>
            </div>
        </div>

        <div class="auth-form-column">
            <h2>Lupa Password Anda?</h2>
            <p class="description">
                Masukkan alamat email Anda yang terdaftar, kami akan mengirimkan link untuk mereset password Anda.
            </p>

            @if (session('status'))
                <div class="session-status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your registered email address">

                    @error('email')
                        <span style="color: #dc3545; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-submit">
                    Kirim Link Reset
                </button>
            </form>

            <div class="auth-footer">
                Ingat password Anda? <a href="{{ route('login') }}">Login</a>
            </div>
        </div>
    </div>
</body>
</html>