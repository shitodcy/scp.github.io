@extends('layouts.admin')

@section('title', 'Log Aktivitas')

@section('content')

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
        <h3 class="card-title">Log Aktivitas Website</h3>
        <div class="card-tools">
            <form action="{{ route('admin.logs.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA log aktivitas? Tindakan ini tidak dapat dibatalkan.');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash-alt"></i> Hapus Semua Log
                </button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        {{-- PERUBAHAN DI SINI: Menambahkan style untuk max-height dan overflow --}}
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-striped table-valign-middle">
                <thead>
                    <tr>
                        <th style="width: 10%;">Level</th>
                        <th style="width: 25%;">Timestamp</th>
                        <th style="width: 15%;">User</th>
                        <th>Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>
                                {{-- LOGIKA BARU UNTUK MENAMPILKAN BADGE --}}
                                @php
                                    $logLevel = strtoupper($activity->log_name);
                                    $badgeClass = 'info'; // Default badge color
                                    if ($logLevel === 'WARNING') $badgeClass = 'warning';
                                    if ($logLevel === 'DELETE') $badgeClass = 'danger';
                                    if ($logLevel === 'CREATE') $badgeClass = 'success';
                                    if ($logLevel === 'UPDATE') $badgeClass = 'primary';
                                @endphp
                                <span class="badge badge-{{ $badgeClass }}">{{ $logLevel }}</span>
                            </td>
                            <td>{{ $activity->created_at->format('d M Y, H:i:s') }}</td>
                            <td>{{ $activity->causer ? $activity->causer->username : 'System' }}</td>
                            <td>{{ $activity->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center p-3">Tidak ada log aktivitas untuk ditampilkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer clearfix">
        {{-- Link Pagination --}}
        {{ $activities->links() }}
    </div>
</div>
@endsection
