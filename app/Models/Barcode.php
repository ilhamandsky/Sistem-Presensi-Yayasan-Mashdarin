<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo

class Barcode extends Model
{
    use HasFactory;
    use HasTimestamps;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // Bisa diisi nama user otomatis
        'value', // Akan diisi user->id
        'user_id', // Tambahkan user_id
        // 'latitude', // Hapus jika tidak dipakai lagi
        // 'longitude', // Hapus jika tidak dipakai lagi
        // 'radius', // Hapus jika tidak dipakai lagi
    ];

    /**
     * Get the user that owns the barcode.
     */
    public function user(): BelongsTo // Definisikan relasi
    {
        return $this->belongsTo(User::class);
    }

    /* Hapus jika tidak dipakai lagi
    function getLatLngAttribute(): array|null
    {
        if (is_null($this->latitude) || is_null($this->longitude)) {
            return null;
        }
        return  [
            'lat' => $this->latitude,
            'lng' => $this->longitude
        ];
    }
    */
}
