<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanProyekBahan extends Model
{
    protected $table = 'pembangunan_proyek_bahan';

    protected $fillable = [
        'pembangunan_proyek_id',
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

    public function pembangunanProyek()
    {
        return $this->belongsTo(PembangunanProyek::class, 'pembangunan_proyek_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
