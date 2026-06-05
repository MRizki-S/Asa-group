<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRapUpah extends Model
{
    protected $table = 'master_rap_upah';
    protected $fillable = ['type_id', 'master_qc_container_id', 'master_qc_urutan_id', 'master_upah_id', 'nominal_standar'];

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function qcContainer()
    {
        return $this->belongsTo(MasterQcContainer::class);
    }

    public function urutan()
    {
        return $this->belongsTo(MasterQcUrutan::class, 'master_qc_urutan_id');
    }

    public function masterUpah()
    {
        return $this->belongsTo(MasterUpah::class);
    }

    public function pembangunanUnitRapUpah(){
        return $this->hasOne(PembangunanUnitRapUpah::class);
    }
}
