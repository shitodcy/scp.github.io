@csrf
<div class="form-group">
    <label for="name">Nama Lengkap</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $member->name ?? '') }}" required>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="form-group">
    <label for="student_id">NIM</label>
    <input type="text" class="form-control @error('student_id') is-invalid @enderror" id="student_id" name="student_id" value="{{ old('student_id', $member->student_id ?? '') }}" required>
    @error('student_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="form-group">
    <label for="job_title">Jabatan</label>
    <input type="text" class="form-control @error('job_title') is-invalid @enderror" id="job_title" name="job_title" value="{{ old('job_title', $member->job_title ?? '') }}" required>
    @error('job_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="form-group">
    <label for="image_url">URL Gambar</label>
    <input type="url" class="form-control @error('image_url') is-invalid @enderror" id="image_url" name="image_url" value="{{ old('image_url', $member->image_url ?? '') }}" required>
    <small class="form-text text-muted">Gunakan URL dari Cloudinary atau layanan sejenis.</small>
    @error('image_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<button type="submit" class="btn btn-primary">{{ $buttonText ?? 'Simpan' }}</button>
