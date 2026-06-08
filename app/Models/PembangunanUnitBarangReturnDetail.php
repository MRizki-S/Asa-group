<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnitBarangReturnDetail extends Model
{
    protected $table = "pembangunan_unit_barang_return_detail";

    protected $fillable = [
        'return_id', 'order_detail_id', 'barang_id', 'jumlah_return', 'keterangan_return'
    ];

    public function pembangunanUnitBarangReturn(){
        return $this->belongsTo(PembangunanUnitBarangReturn::class, 'return_id');
    }

    public function pembangunanUnitBarangOrderDetail(){
        return $this->belongsTo(PembangunanUnitBarangOrderDetail::class, 'order_detail_id');
    }

    public function orderDetail(){
        return $this->belongsTo(PembangunanUnitBarangOrderDetail::class, 'order_detail_id');
    }

    public function barang(){
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
