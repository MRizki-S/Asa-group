<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiUserTask extends Model
{
    protected $table = 'kpi_user_task';

    protected $fillable = [
        'kpi_user_komponen_id',
        'task_id',
        'nama_task',
        'target',
        'tercapai',
        'nilai',
        'alasan_tidak_tercapai'
    ];

    public function kpiUserKomponen()
    {
        return $this->belongsTo(KpiUserKomponen::class, 'kpi_user_komponen_id');
    }

    public function kpiTask()
    {
        return $this->belongsTo(KpiTask::class, 'task_id');
    }
}
