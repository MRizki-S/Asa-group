<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MarketingAnggaranPromosi extends Model
{
    use HasFactory;

    protected $table = 'marketing_anggaran_promosi';

    protected $fillable = [
        'perumahaan_id',
        'tahun',
        'quarter',
        'target_anggaran',
        'realisasi_anggaran',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'target_anggaran' => 'decimal:2',
        'realisasi_anggaran' => 'decimal:2',
    ];

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class, 'perumahaan_id');
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