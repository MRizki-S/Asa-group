<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProduksiBarangRakitan extends Model
{
    protected $table = 'produksi_barang_rakitan';

    protected $fillable = [
        'nomor_rakitan',
        'tanggal_rakitan',
        'stock_type',
        'ubs_id',
        'barang_rakitan_id',
        'barang_hasil_id',
        'satuan_hasil_id',
        'qty_hasil',
        'qty_hasil_base',
        'total_biaya',
        'harga_satuan',
        'harga_satuan_base',
        'status',
        'keterangan',
        'created_by',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'nota_barang_masuk_id',
    ];

    protected $casts = [
        'tanggal_rakitan' => 'date',
        'qty_hasil' => 'decimal:3',
        'qty_hasil_base' => 'decimal:3',
        'total_biaya' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'harga_satuan_base' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(ProduksiBarangRakitanDetail::class, 'produksi_barang_rakitan_id');
    }

    public function barangRakitan(): BelongsTo
    {
        return $this->belongsTo(BarangRakitan::class, 'barang_rakitan_id');
    }

    public function barangHasil(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_hasil_id');
    }

    public function satuanHasil(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_hasil_id');
    }

    public function ubs(): BelongsTo
    {
        return $this->belongsTo(Ubs::class, 'ubs_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function notaBarangMasuk(): BelongsTo
    {
        return $this->belongsTo(NotaBarangMasuk::class, 'nota_barang_masuk_id');
    }
}
