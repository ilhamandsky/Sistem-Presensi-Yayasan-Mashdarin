<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('barcodes', function (Blueprint $table) {
            // Hapus kolom user_id jika ada dari percobaan sebelumnya (opsional, migrate:fresh sudah menghapusnya)
            // if (Schema::hasColumn('barcodes', 'user_id')) {
            //     $table->dropConstrainedForeignId('user_id');
            // }

            // Tambahkan kolom user_id menggunakan foreignUlid
             $table->foreignUlid('user_id') // <-- GANTI DENGAN INI
                   ->nullable() // Atau hapus nullable() jika setiap barcode WAJIB punya user
                   ->after('name') // Posisi kolom (opsional)
                   ->constrained('users') // Menghubungkan ke tabel 'users' kolom 'id' (otomatis tipe ULID)
                   ->onDelete('cascade'); // Aksi saat user dihapus

            // Pastikan user_id unik jika satu user hanya boleh punya satu barcode
             $table->unique('user_id');

            // Ubah kolom yang tidak relevan menjadi nullable
            $table->double('latitude')->nullable()->change();
            $table->double('longitude')->nullable()->change();
            $table->integer('radius')->nullable()->change();

            // Indeks untuk performa query (foreignUlid + constrained biasanya sudah otomatis membuat index)
            // $table->index('user_id'); // Bisa dihapus jika constrained sudah membuatnya
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barcodes', function (Blueprint $table) {
            // Urutan drop: unique constraint, foreign key, baru kolom
            $table->dropUnique(['user_id']);
            $table->dropForeign(['user_id']); // Drop foreign key constraint
            $table->dropColumn('user_id'); // Drop kolom

            // Kembalikan kolom ke state semula (jika diperlukan, sesuaikan tipenya)
            // $table->double('latitude')->nullable(false)->change();
            // $table->double('longitude')->nullable(false)->change();
            // $table->integer('radius')->nullable(false)->change();
        });
    }
};
