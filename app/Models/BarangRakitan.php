<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangRakitan extends Model
{
    protected $table = 'barang_rakitan';

    protected $fillable = [
        'barang_hasil_id',
        'satuan_hasil_id',
        'qty_hasil',
        'qty_hasil_base',
        'status',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'qty_hasil' => 'decimal:3',
        'qty_hasil_base' => 'decimal:3',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(BarangRakitanDetail::class, 'barang_rakitan_id');
    }

    public function produksi(): HasMany
    {
        return $this->hasMany(ProduksiBarangRakitan::class, 'barang_rakitan_id');
    }

    public function barangHasil(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_hasil_id');
    }

    public function satuanHasil(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_hasil_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
