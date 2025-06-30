@extends('layouts.admin')

@section('title', 'Manajemen Menu')

@section('content')

{{-- Menampilkan pesan sukses setelah create/update/delete --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Daftar Item Menu</h3>
        <div class="card-tools">
            <a href="{{ route('admin.menu.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Menu Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        {{-- Form untuk Filter dan Pencarian --}}
        <form action="{{ route('admin.menu.index') }}" method="GET" class="form-inline mb-3">
            <div class="form-group mr-2">
                <input type="text" name="search_query" class="form-control" placeholder="Cari nama/kategori..." value="{{ request('search_query') }}">
            </div>
            <div class="form-group mr-2">
                <select class="form-control" name="category_filter">
                    <option value="all">Semua Kategori</option>
                    <option value="coffee" @selected(request('category_filter') == 'coffee')>Kopi</option>
                    <option value="tea" @selected(request('category_filter') == 'tea')>Teh</option>
                    <option value="snack" @selected(request('category_filter') == 'snack')>Snack</option>
                    <option value="other" @selected(request('category_filter') == 'other')>Lain-lain</option>
                </select>
            </div>
            <button type="submit" class="btn btn-default">
                <i class="fas fa-search"></i> Cari
            </button>
        </form>

        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-striped table-valign-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menuItems as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>
                                @php
                                    $imageUrl = $item->image_url;
                                    // Cek apakah ini URL eksternal atau file lokal
                                    if ($imageUrl) {
                                        $imageSrc = Str::startsWith($imageUrl, ['http://', 'https://']) ? $imageUrl : asset('storage/' . $imageUrl);
                                    } else {
                                        $imageSrc = 'https://placehold.co/50x50/cccccc/ffffff?text=No+Img';
                                    }
                                @endphp
                                <img src="{{ $imageSrc }}" alt="{{ $item->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            </td>
                            <td>{{ $item->name }}</td>
                            <td>Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($item->category) }}</td>
                            <td>
                                <span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.menu.edit', $item->id) }}" class="btn btn-info btn-sm">
                                  <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.menu.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center p-3">Tidak ada item menu yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
