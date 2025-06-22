@extends('layouts.admin')

@section('title', 'Tambah Anggota Tim')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Formulir Tambah Anggota Tim Baru</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.teams.store') }}" method="POST" enctype="multipart/form-data">
            @csrf {{-- Wajib untuk keamanan --}}

            {{-- Menampilkan error validasi jika ada --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label for="student_id">NIM</label>
                <input type="text" class="form-control" id="student_id" name="student_id" value="{{ old('student_id') }}" required>
            </div>

            <div class="form-group">
                <label for="job_title">Jabatan</label>
                <input type="text" class="form-control" id="job_title" name="job_title" value="{{ old('job_title') }}" required>
            </div>

            <div class="form-group">
    <label for="image">Unggah Gambar (Opsional)</label>
    <div class="input-group">
        <div class="custom-file">
            <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg, image/jpg, .webp">
            <label class="custom-file-label" for="image">Pilih file...</label>
        </div>
    </div>
    <small class="form-text text-muted">Gunakan ini jika Anda ingin mengunggah file dari komputer.</small>
</div>

<div class="form-group">
    <label for="image_url">Atau Masukkan URL Gambar (Opsional)</label>
    <input type="url" class="form-control" id="image_url" name="image_url" value="{{ old('image_url') }}" placeholder="https://...">
    <small class="form-text text-muted">Jika diisi, ini akan lebih diprioritaskan daripada unggahan file.</small>
</div>

            <div class="card-footer bg-transparent">
                <a href="{{ route('admin.teams.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary float-right">
                    <i class="fas fa-save"></i> Simpan Anggota
                </button>
            </div>
        </form>
    </div>
</div>
@endsection