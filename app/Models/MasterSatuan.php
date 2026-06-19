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

    public function rakitanHasil()
    {
        return $this->hasMany(BarangRakitan::class, 'satuan_hasil_id');
    }

    public function rakitanDetail()
    {
        return $this->hasMany(BarangRakitanDetail::class, 'satuan_id');
    }

    public function produksiRakitanHasil()
    {
        return $this->hasMany(ProduksiBarangRakitan::class, 'satuan_hasil_id');
    }

    public function produksiRakitanDetail()
    {
        return $this->hasMany(ProduksiBarangRakitanDetail::class, 'satuan_id');
    }
}
