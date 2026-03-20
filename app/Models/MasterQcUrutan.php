<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterQcUrutan extends Model
{
    protected $table = 'master_qc_urutan';
    protected $fillable = ['master_qc_container_id', 'qc_ke', 'nama_qc'];

    public function qcContainer()
    {
        return $this->belongsTo(MasterQcContainer::class);
    }

    public function tugas()
    {
        return $this->hasMany(MasterQcTugas::class);
    }

    public function rapBahan()
    {
        return $this->hasMany(MasterRapBahan::class);
    }

    public function rapUpah()
    {
        return $this->hasMany(MasterRapUpah::class);
    }

    public function pembangunanUnitQc(){
        return $this->hasOne(PembangunanUnitQc::class);
    }

}
