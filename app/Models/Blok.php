<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blok extends Model
{
    protected $table    = 'blok';
    protected $fillable = ['perumahaan_id', 'tahap_id', 'nama_blok', 'slug'];

    protected static function booted()
    {
        static::creating(function ($blok) {
            $blok->slug = Str::slug($blok->nama_blok) . '-' . Str::random(5);
        });

        static::updating(function ($blok) {
            if ($blok->isDirty('nama_blok')) {
                $blok->slug = Str::slug($blok->nama_blok) . '-' . Str::random(5);
            }
        });
    }

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class);
    }

    public function tahap()
    {
        return $this->belongsTo(Tahap::class);
    }

    public function unit()
    {
        return $this->hasMany(Unit::class);
    }
}
