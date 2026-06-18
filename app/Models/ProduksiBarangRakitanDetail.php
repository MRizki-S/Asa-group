<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProduksiBarangRakitanDetail extends Model
{
    protected $table = 'produksi_barang_rakitan_detail';

    protected $fillable = [
        'produksi_barang_rakitan_id',
        'barang_bahan_id',
        'satuan_id',
        'qty_pakai',
        'qty_pakai_base',
        'harga_total',
    ];

    protected $casts = [
        'qty_pakai' => 'decimal:3',
        'qty_pakai_base' => 'decimal:3',
        'harga_total' => 'decimal:2',
    ];

    public function fifoDetails(): HasMany
    {
        return $this->hasMany(ProduksiBarangRakitanFifo::class, 'produksi_barang_rakitan_detail_id');
    }

    public function produksiBarangRakitan(): BelongsTo
    {
        return $this->belongsTo(ProduksiBarangRakitan::class, 'produksi_barang_rakitan_id');
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
