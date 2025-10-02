<?php
namespace App\Models;

use App\Models\Blok;
use App\Models\Type;
use App\Models\User;
use App\Models\Tahap;
use Illuminate\Support\Str;
use App\Models\PpjbCaraBayar;
use App\Models\PpjbMutuBatch;
use App\Models\PpjbPembatalan;
use App\Models\PpjbPromoBatch;
use App\Models\PpjbKeterlambatan;
use Illuminate\Database\Eloquent\Model;

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

        static::updating(function ($perumahaan) {
            if ($perumahaan->isDirty('nama_perumahaan')) {
                $perumahaan->slug = Str::slug($perumahaan->nama_perumahaan);
            }
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

    // Relasi ke Users (karyawan)
    public function users()
    {
        return $this->hasMany(User::class, 'perumahaan_id');
    }

    // Relasi ke PPJB
    public function caraBayar()
    {
        return $this->hasMany(PpjbCaraBayar::class, 'perumahaan_id');
    }

    public function keterlambatan()
    {
        return $this->hasMany(PpjbKeterlambatan::class, 'perumahaan_id');
    }

    public function pembatalan()
    {
        return $this->hasMany(PpjbPembatalan::class, 'perumahaan_id');
    }

    public function promoBatch()
    {
        return $this->hasMany(PpjbPromoBatch::class, 'perumahaan_id');
    }

    public function mutuBatch()
    {
        return $this->hasMany(PpjbMutuBatch::class, 'perumahaan_id');
    }

}
