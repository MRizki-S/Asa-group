<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnitRapUpah extends Model
{
    protected $table = 'pembangunan_unit_rap_upah';
    protected $fillable = [
        'pembangunan_unit_id',
        'pembangunan_unit_qc_id',
        'master_rap_upah_id',
        'nama_upah',
        'nominal_standar'
    ];

    public function pembangunanUnit(){
        return $this->belongsTo(PembangunanUnit::class, 'pembangunan_unit_id');
    }

    public function pembangunanUnitQc(){
        return $this->belongsTo(PembangunanUnitQc::class, 'pembangunan_unit_qc_id');
    }

    public function masterRapUpah(){
        return $this->belongsTo(MasterRapUpah::class, 'master_rap_upah_id');
    }
}
