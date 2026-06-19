<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProduksiBarangRakitanFifo extends Model
{
    protected $table = 'produksi_barang_rakitan_fifo';

    protected $fillable = [
        'produksi_barang_rakitan_detail_id',
        'nota_barang_masuk_detail_id',
        'qty_base_diambil',
        'harga_satuan_base',
        'harga_total',
    ];

    protected $casts = [
        'qty_base_diambil' => 'decimal:3',
        'harga_satuan_base' => 'decimal:2',
        'harga_total' => 'decimal:2',
    ];

    public function produksiDetail(): BelongsTo
    {
        return $this->belongsTo(ProduksiBarangRakitanDetail::class, 'produksi_barang_rakitan_detail_id');
    }

    public function notaDetail(): BelongsTo
    {
        return $this->belongsTo(NotaBarangMasukDetail::class, 'nota_barang_masuk_detail_id');
    }
}
