<?php

namespace App\Models;

use App\Models\MasterBarang;
use Illuminate\Database\Eloquent\Model;

class StockGudangHub extends Model
{
    protected $table = 'stock_gudang_hub';

    protected $fillable = [
        'barang_id',
        'jumlah_stock',
        'minimal_stock',
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}

