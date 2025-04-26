<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // Nama shift (misalnya: pagi, sore)
            $table->time('start_time'); // Waktu mulai shift
            $table->time('end_time');   // Waktu selesai shift
            $table->timestamps();   // Timestamps untuk created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
