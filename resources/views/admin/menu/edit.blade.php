@extends('layouts.admin')

@section('title', 'Edit Menu: ' . $menuItem->name)

@push('styles')
    {{-- Kita gunakan CSS yang sama dengan halaman create --}}
    <link rel="stylesheet" href="{{ asset('css/create_menu_item.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Item Menu
                    </h3>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Oops! Ada kesalahan:</h5>
                            <ul class="mb-0 pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Form action ke route update, dengan method PUT --}}
                    <form action="{{ route('admin.menu.update', $menuItem) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') {{-- Wajib untuk form edit --}}
                        
                        <div class="row">
                            {{-- Kolom Kiri: Form Input --}}
                            <div class="col-md-8">
                                <div class="form-section">
                                    <h4 class="section-title"><i class="fas fa-info-circle mr-2"></i>Informasi Dasar</h4>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="name">Nama Menu</label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $menuItem->name) }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="price">Harga (Rp)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                                <input type="number" step="500" class="form-control" id="price" name="price" value="{{ old('price', $menuItem->price) }}" required>
                                                <div class="input-group-append"><span class="input-group-text">.00</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="category">Kategori</label>
                                        <select class="form-control custom-select" id="category" name="category" required>
                                            <option value="coffee" @selected(old('category', $menuItem->category) == 'coffee')>Kopi</option>
                                            <option value="tea" @selected(old('category', $menuItem->category) == 'tea')>Teh</option>
                                            <option value="snack" @selected(old('category', $menuItem->category) == 'snack')>Snack</option>
                                            <option value="other" @selected(old('category', $menuItem->category) == 'other')>Lain-lain</option>
                                        </select>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $menuItem->is_active))>
                                        <label class="custom-control-label" for="is_active">Aktif (Tampilkan di website)</label>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h4 class="section-title"><i class="fas fa-image mr-2"></i>Ganti Gambar</h4>
                                    <hr>
                                    <div class="form-group">
                                        <label for="image_url">URL Gambar Baru (Opsional)</label>
                                        <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://... atau kosongkan">
                                    </div>
                                    <div class="form-group">
                                        <label for="image">Atau Unggah Gambar Baru (Opsional)</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="image" name="image">
                                            <label class="custom-file-label" for="image">Pilih file baru...</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Gambar Saat Ini --}}
                            <div class="col-md-4">
                                <div class="form-section text-center">
                                    <h4 class="section-title justify-content-center"><i class="fas fa-camera mr-2"></i>Gambar Saat Ini</h4>
                                    <hr>
                                    @php
                                        $imageSrc = 'https://placehold.co/200x200/343a40/6c757d?text=No+Image';
                                        if ($menuItem->image_url) {
                                            $imageSrc = Str::startsWith($menuItem->image_url, 'http') ? $menuItem->image_url : asset('storage/' . $menuItem->image_url);
                                        }
                                    @endphp
                                    <img src="{{ $imageSrc }}" alt="Current Image" class="img-fluid rounded mb-2" style="max-height: 200px; object-fit: cover;">
                                    
                                    @if($menuItem->image_url)
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input class="custom-control-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                        <label for="remove_image" class="custom-control-label">Hapus Gambar Saat Ini</label>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent">
                            <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-2"></i>Batal</a>
                            <button type="submit" class="btn btn-primary float-right"><i class="fas fa-save mr-2"></i>Perbarui Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
<script>
$(document).ready(function () {
    bsCustomFileInput.init();
});
</script>
@endpush