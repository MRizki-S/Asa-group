<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahapKualifikasi extends Model
{
    protected $table = "tahap_kualifikasi";
    protected $fillable = ['tahap_id', 'kualifikasi_blok_id', 'nominal_tambahan'];

    public function tahap()
    {
        return $this->belongsTo(Tahap::class);
    }

    public function kualifikasiBlok()
    {
        return $this->belongsTo(KualifikasiBlok::class);
    }

    public function unit()
    {
        return $this->hasMany(Unit::class);
    }
}
