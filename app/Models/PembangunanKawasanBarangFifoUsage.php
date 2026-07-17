<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanBarangFifoUsage extends Model
{
    protected $table = 'pembangunan_kawasan_barang_fifo_usage';

    protected $fillable = [
        'order_detail_id',
        'nota_barang_masuk_detail_id',
        'jumlah_base',
        'jumlah_return_base',
        'harga_satuan_snapshot',
        'harga_total_snapshot',
    ];

    protected $casts = [
        'jumlah_base' => 'float',
        'jumlah_return_base' => 'float',
        'harga_satuan_snapshot' => 'decimal:2',
        'harga_total_snapshot' => 'decimal:2',
    ];

    public function orderDetail()
    {
        return $this->belongsTo(PembangunanKawasanBarangOrderDetail::class, 'order_detail_id');
    }

    public function notaDetail()
    {
        return $this->belongsTo(NotaBarangMasukDetail::class, 'nota_barang_masuk_detail_id');
    }
}
