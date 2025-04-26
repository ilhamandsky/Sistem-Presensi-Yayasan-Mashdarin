<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();  // id untuk attendance
            $table->char('user_id', 26);  // Sesuaikan dengan ULID (char(26) untuk ULID)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');  // Foreign key untuk user_id
            $table->foreignId('barcode_id')->nullable()->constrained()->nullOnDelete();  // Foreign key untuk barcode_id
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();  // Foreign key untuk shift_id
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status')->nullable();
            $table->text('note')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
