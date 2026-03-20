<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanPembangunanUnit extends Model
{
    protected $table = "pengajuan_pembangunan_unit";
    protected $fillable = [
        // 'unit_id',
        'perumahaan_id',
        'pembangunan_unit_id',
        // 'tahap_id',
        // 'qc_container_id',
        'diajukan_oleh',
        'direspon_oleh',
        'status_pengajuan',
        'tanggal_diajukan',
        'tanggal_direspon'
    ];

    // public function unit(){
    //     return $this->belongsTo(Unit::class);
    // }

    // public function tahap(){
    //     return $this->belongsTo(Tahap::class);
    // }

    public function pembangunanUnit(){
        return $this->belongsTo(PembangunanUnit::class);
    }

    public function perumahaan(){
        return $this->belongsTo(Perumahaan::class);
    }

    // public function qcContainer(){
    //     return $this->belongsTo(MasterQcContainer::class, 'qc_container_id');
    // }

    public function diajukanOleh(){
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function diresponOleh(){
        return $this->belongsTo(User::class, 'direspon_oleh');
    }


}
