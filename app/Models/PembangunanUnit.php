<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnit extends Model
{
    protected $table = "pembangunan_unit";
    protected $fillable = [
        'unit_id', 'perumahaan_id', 'tahap_id', 'pengawas_id', 'tanggal_mulai', 'tanggal_selesai', 'status_pembangunan',
        'status_serah_terima', 'qc_container_id'
    ];

    public function unit(){
        return $this->belongsTo(Unit::class);
    }

    public function tahap(){
        return $this->belongsTo(Tahap::class);
    }

    public function perumahaan(){
        return $this->belongsTo(Perumahaan::class);
    }

    public function qcContainer(){
        return $this->belongsTo(MasterQcContainer::class, 'qc_container_id');
    }

    public function pengawas(){
        return $this->belongsTo(User::class, 'pengawas_id');
    }

    public function pengajuan(){
        return $this->hasOne(PengajuanPembangunanUnit::class);
    }

    public function pembangunanUnitQc(){
        return $this->hasMany(PembangunanUnitQc::class);
    }

    public function pembangunanUnitRapBahan(){
        return $this->hasMany(PembangunanUnitRapBahan::class);
    }
    public function pembangunanUnitRapUpah(){
        return $this->hasMany(PembangunanUnitRapUpah::class);
    }

    public function getTotalProgresAttribute() {
        $qcs = $this->pembangunanUnitQc;
        if ($qcs->count() == 0) return 0;

        $totalPersen = 0;
        foreach($qcs as $qc) {
            $totalPersen += $qc->persentase;
        }
        return round($totalPersen / $qcs->count());
    }
}
