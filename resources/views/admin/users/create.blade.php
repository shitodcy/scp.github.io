<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User Baru - Kedai Kopi Kayu</title>
    {{-- Memuat Tailwind CSS untuk styling --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Memuat font Inter dari Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .form-input {
            background-color: #44475a;
            border-color: #6272a4;
            color: #f8f8f2;
            transition: all 0.2s ease-in-out;
        }
        .form-input::placeholder {
            color: #6272a4;
        }
        .form-input:focus {
            border-color: #bd93f9;
            outline: none;
            box-shadow: 0 0 0 2px rgba(189, 147, 249, 0.5);
        }
    </style>
</head>
<body class="bg-[#282a36] flex items-center justify-center min-h-screen p-4">

    <div class="bg-[#2e303c] p-8 rounded-xl shadow-2xl w-full max-w-md border border-[#44475a]">
        <h2 class="text-2xl font-bold text-[#f8f8f2] mb-2 text-center">Tambah User Baru</h2>
        <p class="text-center mb-6">
            <a href="{{ route('admin.users.index') }}" class="text-sm text-[#bd93f9] hover:text-[#ff79c6] transition-colors duration-200">
                &larr; Kembali ke daftar user
            </a>
        </p>

        {{-- Menampilkan error validasi jika ada --}}
        @if ($errors->any())
            <div class="bg-red-500/20 border border-red-500/30 text-red-300 px-4 py-3 rounded-md relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf {{-- Token Keamanan Laravel --}}

            <div>
                <label for="full_name" class="block text-sm font-medium mb-1 text-[#f8f8f2]">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="Masukkan nama lengkap" required
                       class="form-input mt-1 block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm">
            </div>

            <div>
                <label for="username" class="block text-sm font-medium mb-1 text-[#f8f8f2]">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required
                       class="form-input mt-1 block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium mb-1 text-[#f8f8f2]">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan alamat email" required
                       class="form-input mt-1 block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium mb-1 text-[#f8f8f2]">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required
                       class="form-input mt-1 block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium mb-1 text-[#f8f8f2]">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Konfirmasi password Anda" required
                       class="form-input mt-1 block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm">
            </div>

            <button type="submit"
                    class="w-full bg-[#bd93f9] text-[#282a36] font-semibold py-2 px-4 rounded-md hover:bg-[#ff79c6] focus:outline-none focus:ring-2 focus:ring-[#bd93f9] focus:ring-opacity-50 transition-colors duration-200 mt-6">
                Simpan
            </button>
        </form>
    </div>

</body>
</html>
