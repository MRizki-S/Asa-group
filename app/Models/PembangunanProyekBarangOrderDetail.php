<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekBarangOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_proyek_barang_order_detail';
    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(PembangunanProyekBarangOrder::class, 'order_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuanModel()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function returnDetail()
    {
        return $this->hasOne(PembangunanProyekBarangReturnDetail::class, 'order_detail_id');
    }
}