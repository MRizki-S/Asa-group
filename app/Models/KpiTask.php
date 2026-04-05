<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiTask extends Model
{
    protected $table = 'kpi_task';

    protected $fillable = [
        'komponen_id',
        'nama_task'
    ];

    public function komponen()
    {
        return $this->belongsTo(KpiKomponen::class, 'komponen_id');
    }
}
