<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class KpiKomponen extends Model
{
    protected $table = "kpi_komponen";

    protected $fillable = [
        'role_id',
        'nama_komponen',
        'bobot',
        'tipe_perhitungan',
        'label_total',
        'label_tercapai',
        'label_tidak_tercapai',
        'is_active',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function tasks()
    {
        return $this->hasMany(KpiTask::class, 'komponen_id');
    }
}
