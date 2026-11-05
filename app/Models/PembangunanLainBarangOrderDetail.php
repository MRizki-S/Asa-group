<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanLainBarangOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_lain_barang_order_detail';
    protected $guarded = [];

    protected $casts = [
        'jumlah_input'          => 'float',
        'jumlah_base'           => 'float',
        'konfirmasi'            => 'boolean',
        'harga_satuan_snapshot' => 'decimal:2',
        'harga_total_snapshot'  => 'decimal:2',
        'jumlah_return'         => 'float',
        'jumlah_return_base'    => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(PembangunanLainBarangOrder::class, 'order_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuanModel()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function fifoUsages()
    {
        return $this->hasMany(PembangunanLainBarangFifoUsage::class, 'order_detail_id');
    }

    public function returnDetail()
    {
        return $this->hasOne(PembangunanLainBarangReturnDetail::class, 'order_detail_id');
    }
}

