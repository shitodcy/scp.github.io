@extends('layouts.admin')

@section('title', 'Manajemen User')

@section('content')

{{-- 1. Menampilkan Pesan Sukses (jika ada) --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif


<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pengguna</h3>
        <div class="card-tools">
            {{-- 2. Tombol untuk mengarah ke halaman tambah user --}}
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah User Baru
            </a>
        </div>
    </div>
    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped table-valign-middle mb-0">
            <thead>
                <tr>
                    {{-- 3. Header Tabel yang Lebih Lengkap --}}
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    {{-- 4. Menampilkan Data User yang Lebih Lengkap --}}
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->full_name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                    <td>
                        {{-- 5. Kolom Aksi (untuk Edit & Hapus nanti) --}}
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-info btn-sm">
                           <i class="fas fa-edit"></i> Edit
                        </a>
<form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">
        <i class="fas fa-trash"></i> Hapus
    </button>
</form>
                    </td>
                </tr>
                @empty
                <tr>
                    {{-- Sesuaikan colspan dengan jumlah header tabel --}}
                    <td colspan="6" class="text-center p-3">Belum ada user terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection