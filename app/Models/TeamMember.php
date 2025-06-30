<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // <-- 1. Import Trait
use Spatie\Activitylog\LogOptions;           // <-- 2. Import LogOptions

class TeamMember extends Model
{
    use HasFactory, LogsActivity; // <-- 3. Gunakan Trait di sini

    protected $fillable = [
        'name', 'student_id', 'job_title', 'image_url'
    ];

    // 4. Tambahkan method ini untuk mengkonfigurasi log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['name', 'student_id', 'job_title']) // Hanya catat perubahan pada kolom ini
        ->setDescriptionForEvent(fn(string $eventName) => "Anggota Tim '{$this->name}' telah di-{$eventName}")
        ->useLogName('Manajemen Tim');
    }
}