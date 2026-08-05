<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferStock extends Model
{
    protected $table = 'transfer_stock';

    protected $fillable = [
        'nomor_transfer',
        'tanggal_transfer',
        'dari_stock_type',
        'dari_ubs_id',
        'ke_stock_type',
        'ke_ubs_id',
        'status',
        'approved_by',
        'approved_at',
        'keterangan',
        'created_by'
    ];

    protected $casts = [
        'tanggal_transfer' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(TransferStockDetail::class, 'transfer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fromUbs()
    {
        return $this->belongsTo(Ubs::class, 'dari_ubs_id');
    }

    public function toUbs()
    {
        return $this->belongsTo(Ubs::class, 'ke_ubs_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
