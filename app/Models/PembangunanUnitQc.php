<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnitQc extends Model
{
    protected $table = 'pembangunan_unit_qc';
    protected $fillable = [
        'pembangunan_unit_id',
        'master_qc_urutan_id',
        'qc_urutan_ke',
        'nama_qc',
        'tanggal_mulai',
        'tanggal_selesai'
    ];

    public function pembangunanUnit()
    {
        return $this->belongsTo(PembangunanUnit::class, 'pembangunan_unit_id');
    }

    public function masterQc()
    {
        return $this->belongsTo(MasterQcUrutan::class, 'master_qc_urutan_id');
    }

    public function pembangunanUnitQcTask()
    {
        return $this->hasMany(PembangunanUnitQcTask::class);
    }

    public function pembangunanUnitRapBahan()
    {
        return $this->hasMany(PembangunanUnitRapBahan::class);
    }
    public function pembangunanUnitRapUpah()
    {
        return $this->hasMany(PembangunanUnitRapUpah::class);
    }

    public function getPersentaseAttribute()
    {
        $total = $this->pembangunanUnitQcTask->count();
        if ($total == 0) return 0;
        $selesai = $this->pembangunanUnitQcTask->where('selesai', 1)->count();
        return round(($selesai / $total) * 100);
    }
}
