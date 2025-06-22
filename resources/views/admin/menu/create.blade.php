@extends('layouts.admin')

@section('title', 'Tambah Menu Baru')

{{-- Menambahkan link ke CSS kustom kita di bagian head --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/create_menu_item.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Tambah Item Menu Baru
                    </h3>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle mr-2"></i>Oops! Ada kesalahan:</h5>
                            <ul class="mb-0 pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- SEKSI INFORMASI DASAR --}}
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-info-circle mr-2"></i>Informasi Dasar</h4>
                            <hr>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nama Menu</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Kopi Susu Gula Aren" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Harga (Rp)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" step="500" class="form-control" id="price" name="price" value="{{ old('price') }}" placeholder="15000" required>
                                        <span class="input-group-text">.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Kategori</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="" disabled selected>Pilih Kategori</option>
                                    <option value="coffee" @selected(old('category') == 'coffee')>Kopi</option>
                                    <option value="tea" @selected(old('category') == 'tea')>Teh</option>
                                    <option value="snack" @selected(old('category') == 'snack')>Snack</option>
                                    <option value="other" @selected(old('category') == 'other')>Lain-lain</option>
                                </select>
                            </div>
                        </div>

                        {{-- SEKSI GAMBAR MENU --}}
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-image mr-2"></i>Gambar Menu</h4>
                            <hr>
                            <div class="image-preview-container mb-3">
                                <i class="fas fa-camera placeholder-icon"></i>
                                <p class="placeholder-text">Preview gambar akan ditampilkan di sini</p>
                                <img id="imagePreview" src="#" alt="Image Preview"/>
                            </div>
                            <div class="mb-3">
                                <label for="image_url" class="form-label">URL Gambar Menu (Opsional)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                                    <input type="url" class="form-control" id="image_url" name="image_url" value="{{ old('image_url') }}" placeholder="https://...">
                                </div>
                                <small class="form-text text-muted">Jika diisi, ini akan diprioritaskan daripada unggahan file.</small>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Atau Unggah Gambar File (Opsional)</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            </div>
                        </div>

                        {{-- SEKSI PENGATURAN TAMBAHAN --}}
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-cog mr-2"></i>Pengaturan Tambahan</h4>
                            <hr>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Aktif (Tampilkan di website)</label>
                            </div>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="mt-4">
                            <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Simpan Item Menu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('image');
    const imageUrlInput = document.getElementById('image_url');
    const imagePreview = document.getElementById('imagePreview');
    const placeholderIcon = document.querySelector('.placeholder-icon');
    const placeholderText = document.querySelector('.placeholder-text');

    function updatePreview(src) {
        if (src) {
            imagePreview.src = src;
            imagePreview.style.display = 'block';
            placeholderIcon.style.display = 'none';
            placeholderText.style.display = 'none';
        } else {
            imagePreview.src = '#';
            imagePreview.style.display = 'none';
            placeholderIcon.style.display = 'block';
            placeholderText.style.display = 'block';
        }
    }

    // Event listener untuk input URL
    imageUrlInput.addEventListener('input', function () {
        updatePreview(this.value);
    });

    // Event listener untuk input File
    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                // Hapus isi input URL jika user memilih file, agar tidak konflik
                imageUrlInput.value = '';
                updatePreview(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush