<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovedEmail extends Model
{
    use HasFactory;

    protected $table = 'approved_emails'; // Nama tabel
    protected $fillable = ['email']; // Kolom yang bisa diisi massal
}

