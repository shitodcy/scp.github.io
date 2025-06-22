@extends('layouts.admin')

@section('title', 'Log Aktivitas')

@section('content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Log Aktivitas Website</h3>
        <div class="card-tools">
            <form action="{{ route('admin.logs.clear') }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus SEMUA log?');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Hapus Semua Log</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
        <table class="table table-sm table-striped">
            <tbody>
                @forelse($logs as $log)
                    <tr><td><code>{{ $log }}</code></td></tr>
                @empty
                    <tr><td class="p-3">Tidak ada log untuk ditampilkan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection