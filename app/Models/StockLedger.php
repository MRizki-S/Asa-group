<?php

namespace App\Models;

use App\Models\MasterBarang;
use App\Models\Ubs;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model
{
    protected $table = 'stock_ledger';

    protected $fillable = [
        'tanggal',
        'barang_id',
        'stock_type',
        'ubs_id',
        'tipe',
        'ref_type',
        'ref_id',
        'qty_masuk',
        'qty_keluar',
        'harga_satuan',
        'created_by'
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'qty_masuk' => 'decimal:3',
        'qty_keluar' => 'decimal:3',
        'harga_satuan' => 'decimal:2'
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function ubs()
    {
        return $this->belongsTo(Ubs::class, 'ubs_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
