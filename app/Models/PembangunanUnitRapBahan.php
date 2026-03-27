<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnitRapBahan extends Model
{
    protected $table = 'pembangunan_unit_rap_bahan';
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
}
