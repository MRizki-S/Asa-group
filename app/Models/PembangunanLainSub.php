<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PembangunanLainSub extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pembangunan_lain_sub';
    protected $guarded = [];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Induk Proyek Lain-lain
     */
    public function pembangunanLain()
    {
        return $this->belongsTo(PembangunanLain::class, 'pembangunan_lain_id');
    }

    public function proyek()
    {
        return $this->pembangunanLain();
    }

    /**
     * Order barang khusus sub-pekerjaan ini
     */
    public function orders()
    {
        return $this->hasMany(PembangunanLainBarangOrder::class, 'pembangunan_lain_sub_id');
    }

    /**
     * Akumulasi bahan terpakai sub-pekerjaan ini (untuk termin per-sub)
     */
    public function bahan()
    {
        return $this->hasMany(PembangunanLainBahan::class, 'pembangunan_lain_sub_id');
    }

    /**
     * Retur barang sub-pekerjaan ini
     */
    public function returns()
    {
        return $this->hasMany(PembangunanLainBarangReturn::class, 'pembangunan_lain_sub_id');
    }
}

