<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterQcContainer extends Model
{
    protected $table = 'master_qc_container';
    protected $fillable = ['nama_container', 'type_id'];

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function urutan()
    {
        return $this->hasMany(MasterQcUrutan::class);
    }

    public function rapBahan()
    {
        return $this->hasMany(MasterRapBahan::class);
    }

    public function rapUpah()
    {
        return $this->hasMany(MasterRapUpah::class);
    }
}
