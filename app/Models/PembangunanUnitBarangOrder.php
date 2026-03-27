<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembangunanUnitBarangOrder extends Model
{
    protected $table = 'pembangunan_unit_barang_order';

    protected $fillable = [
        'pembangunan_unit_id',
        'pembangunan_unit_qc_id',
        'jenis_order',
        'catatan',
        'tanggal_diajukan',
        'status_order',
        'tanggal_selesai',
        'created_by'
    ];

    protected $casts = [
        'tanggal_diajukan' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(PembangunanUnitBarangOrderDetail::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pembangunanUnit(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnit::class, 'pembangunan_unit_id');
    }

    public function qc(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnitQc::class, 'pembangunan_unit_qc_id');
    }
}
