<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembangunanUnitBarangReturnFifo extends Model
{
    protected $table = 'pembangunan_unit_barang_return_fifo';

    protected $fillable = [
        'return_detail_id',
        'fifo_usage_id',
        'jumlah_base',
        'jumlah_return_base',
        'jumlah_layak_base',
        'jumlah_rusak_base',
        'harga_satuan_snapshot',
        'harga_total_snapshot',
    ];

    public function returnDetail(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnitBarangReturnDetail::class, 'return_detail_id');
    }

    public function fifoUsage(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnitBarangFifoUsage::class, 'fifo_usage_id');
    }
}
