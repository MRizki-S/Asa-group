<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangRakitanDetail extends Model
{
    protected $table = 'barang_rakitan_detail';

    protected $fillable = [
        'barang_rakitan_id',
        'barang_bahan_id',
        'satuan_id',
        'qty',
        'qty_base',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'qty_base' => 'decimal:3',
    ];

    public function barangRakitan(): BelongsTo
    {
        return $this->belongsTo(BarangRakitan::class, 'barang_rakitan_id');
    }

    public function barangBahan(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_bahan_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }
}
