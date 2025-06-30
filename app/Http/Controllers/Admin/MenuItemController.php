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

    $validatedData = $request->validate([
        'name'      => 'required|string|max:255',
        'price'     => 'required|numeric|min:0',
        'category'  => 'required|string|in:coffee,tea,snack,other',
        'image_url' => 'nullable|url',
        'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        'is_active' => 'nullable',
    ]);

    $imagePathOrUrl = null;


    if ($request->filled('image_url')) {
        $imagePathOrUrl = $request->input('image_url');
    }
    elseif ($request->hasFile('image')) {
        $imagePathOrUrl = $request->file('image')->store('menu_images', 'public');
    }


    $isActive = $request->has('is_active') ? 1 : 0;


    MenuItem::create([
        'name'      => $validatedData['name'],
        'price'     => $validatedData['price'],
        'category'  => $validatedData['category'],
        'image_url' => $imagePathOrUrl,
        'is_active' => $isActive,
    ]);


    return redirect()->route('admin.menu.index')
                     ->with('success', 'Item menu baru berhasil ditambahkan.');
}



    public function edit(MenuItem $menuItem)
{

    return view('admin.menu.edit', compact('menuItem'));
}


    public function update(Request $request, MenuItem $menuItem)
{

    $validatedData = $request->validate([
        'name'      => 'required|string|max:255',
        'price'     => 'required|numeric|min:0',
        'category'  => 'required|string|in:coffee,tea,snack,other',
        'image_url' => 'nullable|url',
        'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        'is_active' => 'nullable',
        'remove_image' => 'nullable',
    ]);


    $oldImagePath = $menuItem->image_url;
    $newImageSource = null;


    if ($request->filled('image_url')) {
        $newImageSource = $request->input('image_url');
    } elseif ($request->hasFile('image')) {
        $newImageSource = $request->file('image')->store('menu_images', 'public');
    }


    $menuItem->name = $validatedData['name'];
    $menuItem->price = $validatedData['price'];
    $menuItem->category = $validatedData['category'];
    $menuItem->is_active = $request->has('is_active') ? 1 : 0;


    if ($newImageSource) {

        $menuItem->image_url = $newImageSource;

        if ($oldImagePath && !Str::startsWith($oldImagePath, 'http')) {
            Storage::disk('public')->delete($oldImagePath);
        }
    } elseif ($request->has('remove_image')) {

        $menuItem->image_url = null;

        if ($oldImagePath && !Str::startsWith($oldImagePath, 'http')) {
            Storage::disk('public')->delete($oldImagePath);
        }
    }


    $menuItem->save();

    return redirect()->route('admin.menu.index')
                     ->with('success', 'Item menu berhasil diperbarui.');
}


    public function destroy(MenuItem $menuItem)
{

    if ($menuItem->image_url && !Str::startsWith($menuItem->image_url, 'http')) {
        Storage::disk('public')->delete($menuItem->image_url);
    }


    $menuItem->forceDelete();

    return redirect()->route('admin.menu.index')
                     ->with('success', 'Item menu berhasil dihapus secara permanen.');
}

}
