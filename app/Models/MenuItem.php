<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MenuItem extends Model
{
    use HasFactory;

    
    /**
     * The table associated with the model.
     * Laravel is smart, but it's good practice to be explicit.
     * @var string
     */
    protected $table = 'menu_items';

    /**
     * The attributes that are mass assignable.
     * We've added 'is_active'.
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'price',
        'category',
        'image_url',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     * This is the key part for your schema.
     *
     * @var array<string, string>
     */
    // protected $casts = [
    //     'is_active' => 'boolean', // Treats tinyint(1) as true/false
    //     'category' => MenuCategory::class, // Casts the string 'coffee' to the MenuCategory::Coffee object
    // ];

    /**
     * Get the item's formatted price for the view.
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp ' . number_format($this->price, 0, ',', '.'),
        );
    }

    /**
     * Get the item's image URL for the view.
     */
       protected function optimizedImageUrl(): Attribute
    {
        // Check if the image_url from the database is not empty.
        if ($this->image_url) {
            // If it's a Cloudinary link, just return that link directly.
            // We do NOT use Storage::url() or asset() on it.
            return Attribute::make(
                get: fn () => $this->image_url
            );
        }

        // If the database has no URL, then use the local default image.
        // For this, we DO use asset().
        return Attribute::make(
            get: fn () => asset('/images/default-menu-item.png')
        );
    }
}