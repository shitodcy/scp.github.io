@extends('layouts.admin')

@section('title', 'Edit Anggota Tim: ' . $team->name)

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Formulir Edit Anggota Tim</h3>
    </div>
    <div class="card-body">
        {{-- Arahkan form ke route update dengan method PUT --}}
        <form action="{{ route('admin.teams.update', $team) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT') {{-- Wajib untuk form edit --}}

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
                {{-- Isi value dengan data lama --}}
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $team->name) }}" required>
            </div>

            <div class="form-group">
                <label for="student_id">NIM</label>
                <input type="text" class="form-control" id="student_id" name="student_id" value="{{ old('student_id', $team->student_id) }}" required>
            </div>

            <div class="form-group">
                <label for="job_title">Jabatan</label>
                <input type="text" class="form-control" id="job_title" name="job_title" value="{{ old('job_title', $team->job_title) }}" required>
            </div>

            <div class="form-group">
                <label>Gambar Saat Ini</label>
                <div>
                    @if($team->image_url)
                        @if(Illuminate\Support\Str::startsWith($team->image_url, 'http'))
                            <img src="{{ $team->image_url }}" alt="Current Image" style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px;">
                        @else
                            <img src="{{ asset('storage/' . $team->image_url) }}" alt="Current Image" style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px;">
                        @endif
                    @else
                        <p>Tidak ada gambar.</p>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="image">Unggah Gambar Baru (Opsional)</label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg, image/jpg, .webp">
                        <label class="custom-file-label" for="image">Pilih file baru...</label>
                    </div>
                </div>
                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah gambar.</small>
            </div>

            <div class="form-group">
                <label for="image_url">Atau Ganti dengan URL Gambar Baru (Opsional)</label>
                <input type="url" class="form-control" id="image_url" name="image_url" value="{{ old('image_url') }}" placeholder="https://...">
                <small class="form-text text-muted">Jika diisi, ini akan lebih diprioritaskan.</small>
            </div>

            <div class="card-footer bg-transparent">
                <a href="{{ route('admin.teams.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                <button type="submit" class="btn btn-primary float-right"><i class="fas fa-save"></i> Perbarui Anggota</button>
            </div>
        </form>
    </div>
</div>
@endsection