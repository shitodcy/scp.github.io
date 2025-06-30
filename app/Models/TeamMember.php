<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; 
use Spatie\Activitylog\LogOptions;

class TeamMember extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name', 'student_id', 'job_title', 'image_url'
    ];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['name', 'student_id', 'job_title'])
        ->setDescriptionForEvent(fn(string $eventName) => "Anggota Tim '{$this->name}' telah di-{$eventName}")
        ->useLogName('Manajemen Tim');
    }
}
