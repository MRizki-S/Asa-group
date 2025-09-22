<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Perumahaan extends Model
{
    protected $table    = "perumahaan";
    protected $fillable = ['nama_perumahaan', 'slug', 'alamat'];

    // route model binding pakai slug
    public function getRouteKeyName()
    {
        return 'slug'; // agar {perumahaan} pakai slug, bukan id
    }

    // slug otomatis dari nama_perumahaan
    protected static function booted()
    {
        static::creating(function ($perumahaan) {
            $perumahaan->slug = Str::slug($perumahaan->nama_perumahaan);
        });
    }

    public function types()
    {
        return $this->hasMany(Type::class);
    }

    public function tahap()
    {
        return $this->hasMany(Tahap::class);
    }

    public function blok()
    {
        return $this->hasMany(Blok::class);
    }
}
