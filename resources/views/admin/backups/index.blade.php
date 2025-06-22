@extends('layouts.admin')

@section('title', 'Backup Data MySQL')

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">Buat Backup Database MySQL</h3>
    </div>
    <div class="card-body">
        <p>Klik tombol di bawah untuk membuat file backup database MySQL Anda. File backup akan disimpan di server dan dapat diunduh dari daftar di bawah.</p>
        <form action="{{ route('admin.backups.create') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="fas fa-play-circle mr-2"></i>Buat Backup Sekarang
            </button>
        </form>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Daftar File Backup</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nama File</th>
                    <th>Ukuran</th>
                    <th>Tanggal Dibuat</th>
                    <th style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($backupFiles as $file)
                <tr>
                    <td>{{ $file->getFilename() }}</td>
                    <td>{{ number_format($file->getSize() / 1024, 2) }} KB</td>
                    <td>{{ \Carbon\Carbon::createFromTimestamp($file->getMTime())->format('d M Y, H:i:s') }}</td>
                    <td>
                        <a href="{{ route('admin.backups.download', $file->getFilename()) }}" class="btn btn-primary btn-sm" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        <form action="{{ route('admin.backups.destroy', $file->getFilename()) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file backup ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center p-4">Tidak ada file backup ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection