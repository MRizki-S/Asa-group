<?php

namespace App\Models;

use App\Models\Ubs;
use App\Models\MasterBarang;
use Illuminate\Database\Eloquent\Model;

class StockGudangUbs extends Model
{
    protected $table = 'stock_gudang_ubs';

    protected $fillable = [
        'barang_id',
        'ubs_id',
        'jumlah_stock',
        'minimal_stock',
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

