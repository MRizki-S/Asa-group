<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangRusakFifo extends Model
{
    protected $table = 'barang_rusak_fifo';

    protected $fillable = [
        'barang_rusak_id',
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

    public function barangRusak(): BelongsTo
    {
        return $this->belongsTo(BarangRusak::class, 'barang_rusak_id');
    }

    public function notaDetail(): BelongsTo
    {
        return $this->belongsTo(NotaBarangMasukDetail::class, 'nota_barang_masuk_detail_id');
    }
}
