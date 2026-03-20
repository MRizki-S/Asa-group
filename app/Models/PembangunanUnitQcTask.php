<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnitQcTask extends Model
{
    protected $table = 'pembangunan_unit_qc_task';
    protected $fillable = [
        'pembangunan_unit_qc_id',
        'tugas',
        'selesai',
        'keterangan_selesai',
    ];

    public function pembangunanUnitQc(){
        return $this->belongsTo(PembangunanUnitQc::class, 'pembangunan_unit_qc_id');
    }
}
