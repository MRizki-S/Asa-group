<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanBarangOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_kawasan_barang_order_detail';
    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(PembangunanKawasanBarangOrder::class, 'order_id');
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
        return $this->hasOne(PembangunanKawasanBarangReturnDetail::class, 'order_detail_id');
    }
}