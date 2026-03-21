<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRapBahan extends Model
{
    protected $table = 'master_rap_bahan';
    protected $fillable = ['type_id', 'master_qc_container_id', 'master_qc_urutan_id', 'master_barang_id', 'jumlah_kebutuhan_standar', 'master_satuan_id'];


    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function qcContainer()
    {
        return $this->belongsTo(MasterQcContainer::class);
    }

    public function barang(){
        return $this->belongsTo(MasterBarang::class, 'master_barang_id');
    }

    public function satuan(){
        return $this->belongsTo(MasterSatuan::class, 'master_satuan_id');
    }

    public function urutan()
    {
        return $this->belongsTo(MasterQcUrutan::class, 'master_qc_urutan_id');
    }

    public function pembangunanUnitRapBahan(){
        return $this->hasOne(PembangunanUnitRapBahan::class);
    }
}
