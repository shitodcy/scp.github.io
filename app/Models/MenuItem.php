<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MenuItem extends Model
{
    use HasFactory;



    protected $table = 'menu_items';


    protected $fillable = [
        'name',
        'price',
        'category',
        'image_url',
        'is_active',
    ];


    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp ' . number_format($this->price, 0, ',', '.'),
        );
    }


       protected function optimizedImageUrl(): Attribute
    {

        if ($this->image_url) {

            return Attribute::make(
                get: fn () => $this->image_url
            );
        }


        return Attribute::make(
            get: fn () => asset('/images/default-menu-item.png')
        );
    }
}
