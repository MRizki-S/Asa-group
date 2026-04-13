<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiUserKomponen extends Model
{
    protected $table = 'kpi_user_komponen';

    protected $fillable = [
        'kpi_user_id',
        'komponen_id',
        'nama_komponen',
        'bobot',
        'total_target',
        'total_tercapai',
        'kepatuhan_percent',
        'skor',
        'nilai_akhir',
        'catatan_tambahan'
    ];

    public function kpiUser()
    {
        return $this->belongsTo(KpiUser::class);
    }

    public function komponen()
    {
        return $this->belongsTo(KpiKomponen::class, 'komponen_id');
    }

    public function tasks()
    {
        return $this->hasMany(KpiUserTask::class);
    }
}
