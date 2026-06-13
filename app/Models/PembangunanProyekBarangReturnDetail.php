<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekBarangReturnDetail extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_proyek_barang_return_details';
    protected $guarded = [];

    public function return()
    {
        return $this->belongsTo(PembangunanProyekBarangReturn::class, 'return_id');
    }

    public function orderDetail()
    {
        return $this->belongsTo(PembangunanProyekBarangOrderDetail::class, 'order_detail_id');
    }
}