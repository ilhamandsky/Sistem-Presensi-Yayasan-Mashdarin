<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_titles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama jabatan
            $table->timestamps();   // Timestamps untuk created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_titles');
    }
};
