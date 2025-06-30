<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('student_id');
            $table->string('job_title');
            $table->string('image_url');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
