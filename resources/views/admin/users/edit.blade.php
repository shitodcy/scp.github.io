@extends('layouts.admin')

@section('title', 'Edit User: ' . $user->username)

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h5 class="m-0">Form Edit User</h5>
    </div>
    <div class="card-body">
        <p>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke daftar user</a>
        </p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form action menunjuk ke rute update, dengan method PUT --}}
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT') {{-- Method spoofing untuk request UPDATE --}}

            <div class="text-center form-group">
                <label>Gambar Profil Saat Ini:</label><br>
                <img src="{{ $user->profile_image ? asset('storage/' . $user->profile_image) : 'https://placehold.co/150x150/cccccc/ffffff?text=No+Image' }}" alt="Gambar Profil" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
            </div>

            <div class="form-group">
                <label for="profile_image">Unggah Gambar Profil Baru (Opsional):</label>
                <input type="file" class="form-control-file" id="profile_image" name="profile_image">
                <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah gambar.</small>
            </div>

            <div class="form-group">
                <label for="full_name">Nama Lengkap:</label>
                {{-- Menggunakan old() dengan nilai default dari data user --}}
                <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" required>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" value="{{ old('username', $user->username) }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>

            <hr>
            <p class="text-muted">Biarkan kolom password kosong jika tidak ingin mengubahnya.</p>
            
            <div class="form-group">
                <label for="password">Password Baru:</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password Baru:</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
            </div>

            <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save"></i> Perbarui Data</button>
        </form>
    </div>
</div>
@endsection