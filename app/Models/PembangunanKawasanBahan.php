<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanBahan extends Model
{
    protected $table = 'pembangunan_kawasan_bahan';

    protected $fillable = [
        'pembangunan_kawasan_id',
        'barang_id',
        'nama_barang',
        'satuan',
        'jumlah_pakai',
        'harga_total_snapshot',
    ];

    protected $casts = [
        'jumlah_pakai' => 'float',
        'harga_total_snapshot' => 'decimal:2',
    ];

    public function pembangunanKawasan()
    {
        return $this->belongsTo(PembangunanKawasan::class, 'pembangunan_kawasan_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
