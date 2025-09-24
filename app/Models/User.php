<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str; // trait dari Spatie
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Kolom yang bisa diisi mass assignment
     */
    protected $fillable = [
        'username',
        'no_hp',
        'password',
        'slug',
        'type',
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

}
