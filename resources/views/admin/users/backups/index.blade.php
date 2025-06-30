@extends('layouts.admin')

@section('title', 'Backup Data MySQL')

@section('content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header"><h3 class="card-title">Buat Backup Database</h3></div>
    <div class="card-body">
        <p>Klik tombol di bawah untuk membuat file backup database. File akan disimpan di <code>storage/app/backups/</code>.</p>
        <form action="{{ route('admin.backups.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Buat Backup Sekarang</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar File Backup</h3></div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr><th>Nama File</th><th>Ukuran</th><th>Tanggal Dibuat</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($backupFiles as $file)
                <tr>
                    <td>{{ $file['name'] }}</td>
                    <td>{{ $file['size'] }}</td>
                    <td>{{ $file['created_at'] }}</td>
                    <td>
                        <a href="{{ route('admin.backups.download', ['filename' => $file['name']]) }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i> Download</a>
                        <form action="{{ route('admin.backups.destroy', ['filename' => $file['name']]) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus file ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center p-3">Tidak ada file backup.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection