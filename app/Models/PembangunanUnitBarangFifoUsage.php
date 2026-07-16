<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembangunanUnitBarangFifoUsage extends Model
{
    protected $table = 'pembangunan_unit_barang_fifo_usage';

    protected $fillable = [
        'order_detail_id',
        'nota_barang_masuk_detail_id',
        'jumlah_base',
        'jumlah_return_base',
        'harga_satuan_snapshot',
        'harga_total_snapshot',
    ];

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnitBarangOrderDetail::class, 'order_detail_id');
    }

    public function notaDetail(): BelongsTo
    {
        return $this->belongsTo(NotaBarangMasukDetail::class, 'nota_barang_masuk_detail_id');
    }

    public function returnFifos(): HasMany
    {
        return $this->hasMany(PembangunanUnitBarangReturnFifo::class, 'fifo_usage_id');
    }
}
