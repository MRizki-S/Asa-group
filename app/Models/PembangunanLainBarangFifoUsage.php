<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanLainBarangFifoUsage extends Model
{
    protected $table = 'pembangunan_lain_barang_fifo_usage';
    protected $guarded = [];

    protected $casts = [
        'jumlah_base'           => 'float',
        'jumlah_return_base'    => 'float',
        'harga_satuan_snapshot' => 'decimal:2',
        'harga_total_snapshot'  => 'decimal:2',
    ];

    public function orderDetail()
    {
        return $this->belongsTo(PembangunanLainBarangOrderDetail::class, 'order_detail_id');
    }

    public function notaDetail()
    {
        return $this->belongsTo(NotaBarangMasukDetail::class, 'nota_barang_masuk_detail_id');
    }
}

