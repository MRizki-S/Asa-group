<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBarang extends Model
{
    use HasFactory;

    protected $table = 'stock_barang';

    protected $fillable = [
        'barang_id',
        'jumlah_stock',
    ];

    /**
     * Relasi ke master barang
     */
    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
