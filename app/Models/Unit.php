<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\TahapKualifikasi;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = "unit";
    protected $fillable = [
        'perumahaan_id',
        'tahap_id',
        'blok_id',
        'type_id',
        'nama_unit',
        'slug',
        'kualifikasi_dasar',
        'luas_kelebihan',
        'nominal_kelebihan',
        'tahap_kualifikasi_id',
        'status_unit',
        'harga_final',
        'harga_jual',
    ];

    protected static function booted()
    {
        static::creating(function ($unit) {
            $unit->slug = Str::slug($unit->nama_unit) . '-' . Str::random(5);
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

    public function blok()
    {
        return $this->belongsTo(Blok::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function tahapKualifikasi()
    {
        return $this->belongsTo(TahapKualifikasi::class);
    }

    public function customerBooking()
    {
        return $this->hasOne(CustomerBooking::class);
    }
}
