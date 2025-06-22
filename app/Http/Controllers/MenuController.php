<?php

// app/Http/Controllers/Admin/MenuController.php

use App\Models\MenuItem; // Pastikan model di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Penting untuk mengelola file
use Illuminate\Support\Str;

// ... (method lainnya)

class MenuController
{
    public function update(Request $request, MenuItem $menuItem)
    {
        // Validasi input dari form
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|in:coffee,tea,snack,other',
            'image_url_text' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // Maks 5MB
            'is_active' => 'nullable',
            'remove_image' => 'nullable',
        ]);

        // Mengisi data model dengan data yang divalidasi, kecuali gambar
        $menuItem->fill([
            'name' => $validatedData['name'],
            'price' => $validatedData['price'],
            'category' => $validatedData['category'],
            'is_active' => $request->has('is_active') ? 1 : 0, // Cek checkbox
        ]);

        $currentImage = $menuItem->image_url;

        // Logika untuk menghapus gambar lama jika dicentang 'Hapus Gambar'
        if ($request->has('remove_image') && $currentImage) {
            // Hapus file dari storage jika bukan URL eksternal
            if (!Str::startsWith($currentImage, 'http')) {
                Storage::disk('public')->delete($currentImage);
            }
            $menuItem->image_url = null; // Kosongkan path di database
        }

        // Prioritaskan URL Gambar jika diisi
        if ($request->filled('image_url_text')) {
            // Jika ada gambar lama (bukan URL) yang tersimpan, hapus
            if ($currentImage && !Str::startsWith($currentImage, 'http')) {
                Storage::disk('public')->delete($currentImage);
            }
            $menuItem->image_url = $validatedData['image_url_text'];
        
        // Jika ada file gambar baru yang diunggah
        } elseif ($request->hasFile('image')) {
            // Jika ada gambar lama (bukan URL) yang tersimpan, hapus
            if ($currentImage && !Str::startsWith($currentImage, 'http')) {
                Storage::disk('public')->delete($currentImage);
            }
            // Simpan gambar baru dan dapatkan path-nya
            $path = $request->file('image')->store('menu_images', 'public');
            $menuItem->image_url = $path;
        }

        // Simpan semua perubahan ke database
        $menuItem->save();

        return redirect()->route('admin.menu.index')
                        ->with('success', 'Item menu berhasil diperbarui.');
    }
}


