<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanBarangReturnDetail extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_kawasan_barang_return_details';
    protected $guarded = [];

    public function return()
    {
        return $this->belongsTo(PembangunanKawasanBarangReturn::class, 'return_id');
    }

    public function orderDetail()
    {
        return $this->belongsTo(PembangunanKawasanBarangOrderDetail::class, 'order_detail_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuanModel()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }
}