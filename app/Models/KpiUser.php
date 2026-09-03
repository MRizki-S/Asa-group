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
        'status'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function details()
    {
        return $this->hasMany(KpiUserKomponen::class);
    }

    public function getTotalNilaiAttribute()
    {
        return round($this->details()->sum('nilai_akhir') ?? 0);
    }

    public function reviewRequests()
    {
        return $this->hasMany(KpiReviewRequest::class, 'kpi_user_id');
    }
}
