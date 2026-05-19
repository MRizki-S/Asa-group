<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MarketingTargetQuarter extends Model
{
    use HasFactory;

    protected $table = 'marketing_target_quarter';

    protected $fillable = [
        'perumahaan_id',
        'tahun',
        'quarter',
        'target_penjualan_quarter',
        'created_by',
        'updated_by',
    ];

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class, 'perumahaan_id');
    }

    public function bulanan()
    {
        return $this->hasMany(MarketingTargetBulan::class, 'marketing_target_quarter_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}