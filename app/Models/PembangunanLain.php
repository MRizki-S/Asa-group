<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PembangunanLain extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pembangunan_lain';
    protected $guarded = [];

    /**
     * Sub-pekerjaan di bawah proyek ini
     */
    public function subs()
    {
        return $this->hasMany(PembangunanLainSub::class, 'pembangunan_lain_id');
    }

    /**
     * Seluruh order barang untuk proyek ini
     */
    public function orders()
    {
        return $this->hasMany(PembangunanLainBarangOrder::class, 'pembangunan_lain_id');
    }

    /**
     * Akumulasi bahan terpakai untuk rekap & cetak termin
     */
    public function bahan()
    {
        return $this->hasMany(PembangunanLainBahan::class, 'pembangunan_lain_id');
    }

    /**
     * Seluruh retur barang untuk proyek ini
     */
    public function returns()
    {
        return $this->hasMany(PembangunanLainBarangReturn::class, 'pembangunan_lain_id');
    }
}

