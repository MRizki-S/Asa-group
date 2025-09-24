<?php
namespace App\Models;

use App\Models\TahapKualifikasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KualifikasiBlok extends Model
{
    protected $table    = 'kualifikasi_blok';
    protected $fillable = ['nama_kualifikasi_blok', 'slug'];

    protected static function booted()
    {
        static::creating(function ($kualifikasi) {
            $kualifikasi->slug = Str::slug($kualifikasi->nama_kualifikasi_blok) . '-' . Str::random(5);
        });

        static::updating(function ($kualifikasi) {
            if ($kualifikasi->isDirty('nama_kualifikasi_blok')) {
                $kualifikasi->slug = Str::slug($kualifikasi->nama_kualifikasi_blok) . '-' . Str::random(5);
            }
        });
    }

    public function tahapKualifikasi()
    {
        return $this->hasMany(TahapKualifikasi::class);
    }

    public function tahaps()
    {
        return $this->belongsToMany(
            Tahap::class,
            'tahap_kualifikasi',
            'kualifikasi_blok_id',
            'tahap_id'
        );
    }
}
