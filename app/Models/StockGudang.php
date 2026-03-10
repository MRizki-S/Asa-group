<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockGudang extends Model
{
    protected $table = 'stock_gudang';

    protected $fillable = [
        'barang_id',
        'stock_type',
        'ubs_id',
        'jumlah_stock',
        'minimal_stock'
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function ubs()
    {
        return $this->belongsTo(Ubs::class, 'ubs_id');
    }
}
