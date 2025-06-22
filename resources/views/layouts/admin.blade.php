<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Kedai Kopi Kayu')</title>
    <link rel="icon" href="https://res.cloudinary.com/dbdmqec1q/image/upload/v1748598314/logokkk_rtchku.ico" type="image/x-icon">

    {{-- Aset CSS --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="{{ asset('css/dracula_theme.css') }}">
</head>
<body class="hold-transition sidebar-mini dark-mode">
<div class="wrapper">

    {{-- Navbar --}}
    <nav class="main-header navbar navbar-expand navbar-dark navbar-gray-dark">
        @auth
            {{-- Tampilkan tombol sidebar toggle HANYA jika user sudah login --}}
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="?page=monitoring" class="nav-link">Kedai Kopi Kayu Dashboard</a>
            </li>
            </ul>
        @endauth

        <ul class="navbar-nav ml-auto">
            @auth
                {{-- Tampilkan ini jika user sudah login --}}
                <li class="nav-item">
                    <span class="nav-link">Halo, <b>{{ auth()->user()->username }}</b></span>
                </li>
            @else
                {{-- Tampilkan ini jika user adalah tamu --}}
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('register') }}" class="nav-link">Register</a>
                </li>
            @endauth
        </ul>
    </nav>

    {{-- Sidebar --}}
    {{-- Seluruh sidebar HANYA ditampilkan jika user sudah login --}}
    @auth
    <aside class="main-sidebar sidebar-dark-purple elevation-4">
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ auth()->user()->profile_image ? asset('storage/' . auth()->user()->profile_image) : 'https://placehold.co/160x160/cccccc/ffffff?text=User' }}" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ auth()->user()->full_name }}
                        <i class="fa fa-circle text-success text-xs ml-1"></i>
                    </a>
                </div>
            </div>

            {{-- Menu Navigasi Sidebar --}}
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-line"></i><p>Monitoring</p>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('admin.users.*', 'admin.menu.*', 'admin.backups.*', 'admin.logs.*', 'admin.teams.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ Route::is('admin.users.*', 'admin.menu.*', 'admin.backups.*', 'admin.logs.*', 'admin.teams.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cogs"></i><p>Manajemen<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                           {{-- ... (semua item submenu Anda di sini) ... --}}
                           <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link {{ Route::is('admin.users.*') ? 'active' : '' }}"><i class="far fa-user nav-icon"></i><p>Manajemen User</p></a></li>
                           <li class="nav-item"><a href="{{ route('admin.menu.index') }}" class="nav-link {{ Route::is('admin.menu.*') ? 'active' : '' }}"><i class="fas fa-coffee nav-icon"></i><p>Manajemen Menu</p></a></li>
                           <li class="nav-item"><a href="{{ route('admin.backups.index') }}" class="nav-link {{ Route::is('admin.backups.*') ? 'active' : '' }}"><i class="fas fa-database nav-icon"></i><p>Backup Data</p></a></li>
                           <li class="nav-item"><a href="{{ route('admin.logs.index') }}" class="nav-link {{ Route::is('admin.logs.*') ? 'active' : '' }}"><i class="fas fa-history nav-icon"></i><p>Log Aktivitas</p></a></li>
                           <li class="nav-item"><a href="{{ route('admin.teams.index') }}" class="nav-link {{ Route::is('admin.teams.*') ? 'active' : '' }}"><i class="fas fa-users nav-icon"></i><p>Manajemen Tim</p></a></li>
                        </ul>
                    </li>
                    {{-- Tombol Logout --}}
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}" class="nav-link" onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p>
                            </a>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
    @endauth

    {{-- Wrapper Konten Utama --}}
    {{-- Tambahkan style untuk menghapus margin kiri jika user adalah tamu (karena tidak ada sidebar) --}}
    <div class="content-wrapper" @guest style="margin-left: 0 !important;" @endguest>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('title')</h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    <footer class="main-footer" @guest style="margin-left: 0 !important;" @endguest>
        <strong>©SCP9242. All rights reserved.</strong>
    </footer>
</div>

{{-- Aset JavaScript --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>