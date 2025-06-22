<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Kedai Kopi Kayu</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  {{-- Use Laravel's asset() helper for CSS files in the public directory --}}
  <link rel="stylesheet" href="{{ asset('/css/login.css') }}">
</head>
<body>

  <div class="split-login-container">
    <div class="left-panel-abstract">
      <div class="abstract-content">
        <div class="abstract-main-text">
        </div>
        <p class="left-panel-footer-text">if you can do it yourself why not @scp9242</p>
      </div>
    </div>

    <div class="right-panel-white-form">
      <div class="form-wrapper">
        <div class="form-header">
          <h2>Welcome Back</h2>
          <p class="subtitle">Enter your username and password to access your account!</p>
        </div>

        {{-- This is how you display validation errors in Laravel --}}
        @if ($errors->any())
          <div class="message error">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- Use named routes for form actions. It's more reliable than hard-coded URLs --}}
        <form action="{{ route('login') }}" method="POST">
          {{-- Add @csrf token for security. Laravel requires this on all POST forms. --}}
          @csrf

          <div class="form-group">
            <label for="username">Username</label>
            {{-- Use old('username') to repopulate the field on validation error --}}
            <input type="text" id="username" name="username" value="{{ old('username') }}" required
                   placeholder="Enter your username">
          </div>

          <div class="form-group password-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required
                   placeholder="Enter your password">
          </div>

          <div class="form-options">
            <div class="remember-me">
              <input type="checkbox" id="remember_me" name="remember_me">
              <label for="remember_me">Remember me</label>
            </div>
           <a href="{{ route('password.request') }}" class="forgot-password">Forgot Password</a>
          </div>

          <button type="submit" class="btn-sign-in">Sign In</button>
        </form>

        <p class="no-account-link">
          Don't have an account? <a href="{{ route('register') }}" aria-label="Halaman Login Admin">Sign Up</a>
        </p>
      </div>
    </div>
  </div>

</body>
</html>