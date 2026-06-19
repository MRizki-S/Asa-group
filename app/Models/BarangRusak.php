<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangRusak extends Model
{
    protected $table = 'barang_rusak';

    protected $fillable = [
        'nomor_barang_rusak',
        'tgl_rusak',
        'stock_type',
        'ubs_id',
        'barang_id',
        'satuan_id',
        'qty_out',
        'qty_base',
        'status',
        'keterangan',
        'created_by',
        'posted_at',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
    ];

    protected $casts = [
        'tgl_rusak' => 'date',
        'qty_out' => 'decimal:3',
        'qty_base' => 'decimal:3',
        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function fifoDetails(): HasMany
    {
        return $this->hasMany(BarangRusakFifo::class, 'barang_rusak_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
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
}
