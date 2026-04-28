<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferStockDetail extends Model
{
    protected $table = 'transfer_stock_detail';

    protected $fillable = [
        'transfer_id',
        'barang_id',
        'qty',
        'satuan_id',
        'qty_base',
        'nama_barang_snapshot'
    ];

    public function transfer()
    {
        return $this->belongsTo(TransferStock::class, 'transfer_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }
}
