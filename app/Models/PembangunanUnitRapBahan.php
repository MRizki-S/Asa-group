<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnitRapBahan extends Model
{
    protected $table = 'pembangunan_unit_rap_bahan';

    protected $appends = ['faktor_konversi', 'is_stock'];

    protected $fillable = [
        'pembangunan_unit_id',
        'pembangunan_unit_qc_id',
        'master_rap_bahan_id',
        'barang_id',
        'nama_barang',
        'satuan_id',
        'satuan',
        'jumlah_standar'
    ];


    public function pembangunanUnit()
    {
        return $this->belongsTo(PembangunanUnit::class, 'pembangunan_unit_id');
    }

    public function pembangunanUnitQc()
    {
        return $this->belongsTo(PembangunanUnitQc::class, 'pembangunan_unit_qc_id');
    }

    public function masterRapBahan()
    {
        return $this->belongsTo(MasterRapBahan::class, 'master_rap_bahan_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function getIsStockAttribute()
    {
        return $this->barang ? (bool) $this->barang->is_stock : false;
    }

    public function getFaktorKonversiAttribute()
    {
        $konversi = \App\Models\BarangSatuanKonversi::where('barang_id', $this->barang_id)
            ->where('satuan_id', $this->satuan_id)
            ->first();

        return $konversi ? (float) $konversi->konversi_ke_base : 1.0;
    }
}
