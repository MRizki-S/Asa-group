<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanLainBahan extends Model
{
    protected $table = 'pembangunan_lain_bahan';
    protected $guarded = [];

    protected $casts = [
        'jumlah_pakai'         => 'float',
        'harga_total_snapshot' => 'decimal:2',
    ];

    public function pembangunanLain()
    {
        return $this->belongsTo(PembangunanLain::class, 'pembangunan_lain_id');
    }

    public function proyek()
    {
        return $this->pembangunanLain();
    }

    public function sub()
    {
        return $this->belongsTo(PembangunanLainSub::class, 'pembangunan_lain_sub_id');
    }

    public function pembangunanLainSub()
    {
        return $this->sub();
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}

