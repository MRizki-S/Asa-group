<?php
namespace App\Models;

use App\Models\PemesananUnit;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str; // trait dari Spatie
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Kolom yang bisa diisi mass assignment
     */
    protected $fillable = [
        'nama_lengkap',
        'username',
        'no_hp',
        'password',
        'slug',
        'type',
        'perumahaan_id',
        'is_global',
        'tanggal_expired',
    ];

    /**
     * Kolom yang disembunyikan saat serialisasi
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Kolom yang di-cast otomatis
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * Event boot: auto-generate slug saat create user
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->slug)) {
                $user->slug = Str::slug($user->username . '-' . Str::random(6));
            }
        });

        static::updating(function ($user) {
            if ($user->isDirty('username')) {
                $user->slug = Str::slug($user->username . '-' . Str::random(6));
            }
        });
    }

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class, 'perumahaan_id');
    }

    public function hasGlobalAccess()
    {
        return $this->is_global === 1;
    }

    // relasi ke cutomer_booking
    public function booking()
    {
        return $this->hasOne(CustomerBooking::class, 'user_id');
    }

    public function pemesananSebagaiCustomer()
    {
        return $this->hasOne(PemesananUnit::class, 'customer_id');
    }

    public function pemesananSebagaiSales()
    {
        return $this->hasMany(PemesananUnit::class, 'sales_id');
    }

}
