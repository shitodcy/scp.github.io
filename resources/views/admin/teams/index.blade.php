@extends('layouts.admin')

@section('title', 'Manajemen Tim')

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Anggota Tim</h3>
        <div class="card-tools">
            <a href="{{ route('admin.teams.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Anggota
            </a>
        </div>
    </div>
    <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Jabatan</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teamMembers as $member)
                <tr>
    {{-- Kolom 1: Gambar (Dengan kode yang sudah diperbaiki) --}}
    <td>
        @if($member->image_url)
          <img src="{{ asset('storage/' . $member->image_url) }}" alt="{{ $member->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
        @else
          {{-- Tampilkan placeholder jika tidak ada gambar --}}
          <img src="https://placehold.co/50x50/6c757d/f8f9fa?text={{ substr($member->name, 0, 1) }}" alt="{{ $member->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
        @endif
    </td>

    {{-- Kolom 2, 3, 4 tetap sama --}}
    <td>{{ $member->name }}</td>
    <td>{{ $member->student_id }}</td>
    <td>{{ $member->job_title }}</td>

    {{-- Kolom 5: Aksi (Kode gambar sudah dihapus dari sini) --}}
    <td>
        <a href="{{ route('admin.teams.edit', $member) }}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
        <form action="{{ route('admin.teams.destroy', $member) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
        </form>
    </td>
</tr>
                @empty
                <tr><td colspan="5" class="text-center p-3">Belum ada anggota tim.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
