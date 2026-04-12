<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiUser extends Model
{
    protected $table = 'kpi_user';

    protected $fillable = [
        'user_id',
        'bulan',
        'tahun',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(KpiUserKomponen::class);
    }

    public function getTotalNilaiAttribute()
    {
        return $this->details()->sum('nilai_akhir') ?? 0;
    }
}
