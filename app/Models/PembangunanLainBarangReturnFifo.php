<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanLainBarangReturnFifo extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_lain_barang_return_fifo';
    protected $guarded = [];

    protected $casts = [
        'jumlah_base'           => 'float',
        'jumlah_return_base'    => 'float',
        'jumlah_layak_base'     => 'float',
        'jumlah_rusak_base'     => 'float',
        'harga_satuan_snapshot' => 'decimal:2',
        'harga_total_snapshot'  => 'decimal:2',
    ];

    public function returnDetail()
    {
        return $this->belongsTo(PembangunanLainBarangReturnDetail::class, 'return_detail_id');
    }

    public function fifoUsage()
    {
        return $this->belongsTo(PembangunanLainBarangFifoUsage::class, 'fifo_usage_id');
    }
}

