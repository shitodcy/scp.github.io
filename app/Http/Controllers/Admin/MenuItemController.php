<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Facades\Activity;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
    // ... (method index, create, store dari sebelumnya) ...
    public function index(Request $request)
    {
        $query = MenuItem::query();
        if ($request->filled('category_filter') && $request->category_filter !== 'all') {
            $query->where('category', $request->category_filter);
        }
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('category', 'like', "%{$search}%");
            });
        }
        $menuItems = $query->orderBy('category')->orderBy('name')->get();
        return view('admin.menu.index', compact('menuItems'));
    }

    public function create()
    {
        return view('admin.menu.create');
    }

    public function store(Request $request)
{
    // 1. Validasi semua input dari form
    $validatedData = $request->validate([
        'name'      => 'required|string|max:255',
        'price'     => 'required|numeric|min:0',
        'category'  => 'required|string|in:coffee,tea,snack,other',
        'image_url' => 'nullable|url', // URL gambar dari input teks
        'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // Maks 5MB untuk file upload
        'is_active' => 'nullable', // Checkbox
    ]);

    $imagePathOrUrl = null;

    // 2. Logika untuk menentukan sumber gambar (prioritaskan URL)
    if ($request->filled('image_url')) {
        $imagePathOrUrl = $request->input('image_url');
    } 
    elseif ($request->hasFile('image')) {
        $imagePathOrUrl = $request->file('image')->store('menu_images', 'public');
    }

    // 3. Logika untuk checkbox 'is_active'
    $isActive = $request->has('is_active') ? 1 : 0;

    // 4. Simpan data ke database menggunakan ::create
    MenuItem::create([
        'name'      => $validatedData['name'],
        'price'     => $validatedData['price'],
        'category'  => $validatedData['category'],
        'image_url' => $imagePathOrUrl,
        'is_active' => $isActive,
    ]);

    // 5. Redirect kembali ke halaman daftar menu dengan pesan sukses
    return redirect()->route('admin.menu.index')
                     ->with('success', 'Item menu baru berhasil ditambahkan.');
}


    /**
     * Menampilkan form untuk mengedit item menu.
     * Laravel akan otomatis mencari MenuItem berdasarkan ID di URL.
     */
    public function edit(MenuItem $menuItem)
{
    // Variabel yang dikirim ke view harus bernama 'menuItem'
    return view('admin.menu.edit', compact('menuItem'));
}

    /**
     * Memperbarui item menu yang ada di database.
     */
    public function update(Request $request, MenuItem $menuItem)
{
    // 1. Validasi
    $validatedData = $request->validate([
        'name'      => 'required|string|max:255',
        'price'     => 'required|numeric|min:0',
        'category'  => 'required|string|in:coffee,tea,snack,other',
        'image_url' => 'nullable|url',
        'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        'is_active' => 'nullable',
        'remove_image' => 'nullable', // Untuk checkbox hapus gambar
    ]);

    // Simpan path gambar lama
    $oldImagePath = $menuItem->image_url;
    $newImageSource = null;

    // 2. Logika untuk sumber gambar baru (Prioritaskan URL)
    if ($request->filled('image_url')) {
        $newImageSource = $request->input('image_url');
    } elseif ($request->hasFile('image')) {
        $newImageSource = $request->file('image')->store('menu_images', 'public');
    }

    // 3. Update data dasar
    $menuItem->name = $validatedData['name'];
    $menuItem->price = $validatedData['price'];
    $menuItem->category = $validatedData['category'];
    $menuItem->is_active = $request->has('is_active') ? 1 : 0;

    // 4. Logika untuk memperbarui atau menghapus gambar
    if ($newImageSource) {
        // Jika ada gambar baru, ganti path/url lama
        $menuItem->image_url = $newImageSource;
        // Hapus file gambar lama jika ada dan bukan URL eksternal
        if ($oldImagePath && !Str::startsWith($oldImagePath, 'http')) {
            Storage::disk('public')->delete($oldImagePath);
        }
    } elseif ($request->has('remove_image')) {
        // Jika user mencentang hapus gambar
        $menuItem->image_url = null;
        // Hapus file gambar lama jika ada dan bukan URL eksternal
        if ($oldImagePath && !Str::startsWith($oldImagePath, 'http')) {
            Storage::disk('public')->delete($oldImagePath);
        }
    }

    // 5. Simpan semua perubahan
    $menuItem->save();

    return redirect()->route('admin.menu.index')
                     ->with('success', 'Item menu berhasil diperbarui.');
}

    /**
     * Menghapus item menu dari database.
     */
    public function destroy(MenuItem $menuItem)
{
    // ... (logika menghapus gambar dari storage)
    if ($menuItem->image_url && !Str::startsWith($menuItem->image_url, 'http')) {
        Storage::disk('public')->delete($menuItem->image_url);
    }

    // Ganti ->delete() menjadi ->forceDelete()
    $menuItem->forceDelete();

    return redirect()->route('admin.menu.index')
                     ->with('success', 'Item menu berhasil dihapus secara permanen.');
}

}
