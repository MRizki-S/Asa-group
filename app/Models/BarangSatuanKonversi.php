<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangSatuanKonversi extends Model
{
    protected $table = 'barang_satuan_konversi';

    protected $fillable = [
        'barang_id',
        'satuan_id',
        'konversi_ke_base',
        'is_default'
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }
}
