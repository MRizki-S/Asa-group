<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnitBahan extends Model
{
    protected $table = 'pembangunan_unit_bahan';

    protected $fillable = [
        'pembangunan_unit_id',
        'pembangunan_unit_qc_id',
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

    public function pembangunanUnit()
    {
        return $this->belongsTo(PembangunanUnit::class, 'pembangunan_unit_id');
    }

    public function pembangunanUnitQc()
    {
        return $this->belongsTo(PembangunanUnitQc::class, 'pembangunan_unit_qc_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
