<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne; // Import HasOne
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasUlids; // Pastikan ini ada
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    // ... (fillable, hidden, appends, casts tetap sama) ...

    protected $fillable = [
        'name',
        'email',
        'password',
        'raw_password',
        'gender',
        'job_title_id',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'raw_password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'datetime:Y-m-d',
            'password' => 'hashed',
        ];
    }

    // ... (group attributes dan relasi lain tetap sama) ...
    public static $groups = ['user', 'admin'];

    final public function getIsUserAttribute(): bool
    {
        return $this->group === 'user';
    }

    final public function getIsAdminAttribute(): bool
    {
        return $this->group === 'admin' || $this->isSuperadmin;
    }

    final public function getIsNotAdminAttribute(): bool
    {
        return !$this->isAdmin;
    }

    public function education()
    {
        return $this->belongsTo(Education::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the barcode associated with the user.
     */
    public function barcode(): HasOne // Definisikan relasi
    {
        return $this->hasOne(Barcode::class);
    }
}
