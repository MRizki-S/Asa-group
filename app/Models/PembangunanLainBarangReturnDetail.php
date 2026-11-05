<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanLainBarangReturnDetail extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_lain_barang_return_details';
    protected $guarded = [];

    protected $casts = [
        'jumlah_input'          => 'float',
        'jumlah_base'           => 'float',
        'jumlah_layak_base'     => 'float',
        'jumlah_rusak_base'     => 'float',
        'harga_satuan_snapshot' => 'decimal:2',
        'harga_total_snapshot'  => 'decimal:2',
    ];

    public function return()
    {
        return $this->belongsTo(PembangunanLainBarangReturn::class, 'return_id');
    }

    public function pembangunanLainBarangReturn()
    {
        return $this->return();
    }

    public function orderDetail()
    {
        return $this->belongsTo(PembangunanLainBarangOrderDetail::class, 'order_detail_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuanModel()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function returnFifos()
    {
        return $this->hasMany(PembangunanLainBarangReturnFifo::class, 'return_detail_id');
    }
}

