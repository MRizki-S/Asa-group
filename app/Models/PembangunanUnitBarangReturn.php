<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembangunanUnitBarangReturn extends Model
{
    protected $table = 'pembangunan_unit_barang_return';

    protected $fillable = [
        'pembangunan_unit_id',
        'pembangunan_unit_qc_id',
        'nomor_return',
        'tanggal_return',
        'catatan',
        'status',
        'created_by',
        'acc_by',
        'acc_at',
        'alasan_tolak',
    ];

    protected $casts = [
        'tanggal_return' => 'datetime',
        'acc_at' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(PembangunanUnitBarangReturnDetail::class, 'return_id');
    }

    public function pembangunanUnit(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnit::class, 'pembangunan_unit_id');
    }

    public function qc(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnitQc::class, 'pembangunan_unit_qc_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acc_by');
    }

}
