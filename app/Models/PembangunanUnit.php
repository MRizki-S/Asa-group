<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnit extends Model
{
    protected $table = "pembangunan_unit";
    protected $fillable = [
        'unit_id',
        'perumahaan_id',
        'tahap_id',
        'pengawas_id',
        'spv_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_pembangunan',
        'subcon',
        'status_serah_terima',
        'qc_container_id'
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function tahap()
    {
        return $this->belongsTo(Tahap::class);
    }

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class);
    }

    public function qcContainer()
    {
        return $this->belongsTo(MasterQcContainer::class, 'qc_container_id');
    }

    public function pengawas()
    {
        return $this->belongsTo(User::class, 'pengawas_id');
    }

    public function spv()
    {
        return $this->belongsTo(User::class, 'spv_id');
    }

    public function pengajuan()
    {
        return $this->hasOne(PengajuanPembangunanUnit::class);
    }

    public function pembangunanUnitQc()
    {
        return $this->hasMany(PembangunanUnitQc::class)->orderBy('is_servis', 'asc')->orderBy('id', 'asc');
    }

    public function pembangunanUnitRapBahan()
    {
        return $this->hasMany(PembangunanUnitRapBahan::class);
    }
    public function pembangunanUnitRapUpah()
    {
        return $this->hasMany(PembangunanUnitRapUpah::class);
    }

    public function pembangunanUnitUpah()
    {
        return $this->hasMany(PembangunanUnitUpah::class, 'pembangunan_unit_id');
    }

    public function pembangunanUnitBahan()
    {
        return $this->hasMany(PembangunanUnitBahan::class, 'pembangunan_unit_id');
    }

    public function returns()
    {
        return $this->hasMany(PembangunanUnitBarangReturn::class, 'pembangunan_unit_id');
    }

    public function terminUpahHarian()
    {
        return $this->hasMany(PembangunanUnitTerminUpahHarian::class, 'pembangunan_unit_id');
    }

    public function getTotalProgresAttribute()
    {
        $qcs = $this->pembangunanUnitQc->where('is_servis', false);
        if ($qcs->count() == 0) return 0;

        $totalPersen = 0;
        foreach ($qcs as $qc) {
            $totalPersen += $qc->persentase;
        }
        return round($totalPersen / $qcs->count());
    }
}
