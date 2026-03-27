<?php

namespace App\Models;

use App\Models\MasterBarang;
use Illuminate\Database\Eloquent\Model;

class MasterSatuan extends Model
{
    protected $table = 'master_satuan';

    protected $fillable = [
        'nama'
    ];


    public function barangKonversi()
    {
        return $this->hasMany(BarangSatuanKonversi::class, 'satuan_id');
    }

    public function barangBase()
    {
        return $this->hasMany(MasterBarang::class, 'base_unit_id');
    }

    public function masterRapBahan(){
        return $this->hasMany(MasterRapBahan::class);
    }
}
