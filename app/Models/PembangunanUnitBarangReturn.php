<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnitBarangReturn extends Model
{
    protected $table = "pembangunan_unit_barang_return";

    protected $fillable = [
        'order_id', 'status', 'diajukan_oleh', 'tanggal_diajukan', 'direspon_oleh', 'tanggal_direspon', 'alasan_ditolak'
    ];

    public function pembangunanUnitBarangOrder(){
        return $this->belongsTo(PembangunanUnitBarangOrder::class, 'order_id');
    }

    public function details(){
        return $this->hasMany(PembangunanUnitBarangReturnDetail::class, 'return_id');
    }

    public function diajukanOleh(){
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function diresponOleh(){
        return $this->belongsTo(User::class, 'direspon_oleh');
    }
}
