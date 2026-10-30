<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiUser extends Model
{
    protected $table = 'kpi_user';

    protected $fillable = [
        'karyawan_id',
        'bulan',
        'tahun',
        'status',
        'catatan_revisi',
        'finalized_by',
        'finalized_at',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function finalizedBy()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function details()
    {
        return $this->hasMany(KpiUserKomponen::class);
    }

    public function getTotalNilaiAttribute()
    {
        return round($this->details()->sum('nilai_akhir') ?? 0);
    }

    // Helper status dokumen KPI
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isMenungguReview(): bool
    {
        return $this->status === 'menunggu_review';
    }

    public function isRevisi(): bool
    {
        return $this->status === 'revisi';
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }
}
