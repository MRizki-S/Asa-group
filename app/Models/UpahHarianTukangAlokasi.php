<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpahHarianTukangAlokasi extends Model
{
    protected $table = 'upah_harian_tukang_alokasi';

    protected $fillable = [
        'upah_harian_tukang_detail_id',
        'referensi_jenis',
        'referensi_id',
        'jenis',
        'jam_kerja',
        'tarif_per_jam',
        'subtotal',
        'keterangan',
    ];

    protected $casts = [
        'tarif_per_jam' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'jam_kerja' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Detail harian tukang.
     */
    public function detail()
    {
        return $this->belongsTo(
            UpahHarianTukangDetail::class,
            'upah_harian_tukang_detail_id'
        );
    }

    /**
     * Termin Upah Harian Pembangunan Unit.
     */
    public function pembangunanUnitTerminUpahHarian()
    {
        return $this->hasMany(
            PembangunanUnitTerminUpahHarian::class,
            'upah_harian_tukang_alokasi_id'
        );
    }

    /**
     * Termin Upah Harian Pembangunan Kawasan.
     */
    public function pembangunanKawasanTerminUpahHarian()
    {
        return $this->hasMany(
            PembangunanKawasanTerminUpahHarian::class,
            'upah_harian_tukang_alokasi_id'
        );
    }

    /**
     * Termin Upah Harian Pembangunan Proyek.
     */
    public function pembangunanProyekTerminUpahHarian()
    {
        return $this->hasMany(
            PembangunanProyekTerminUpahHarian::class,
            'upah_harian_tukang_alokasi_id'
        );
    }

    // helper 
    public function pembangunanUnit()
    {
        if ($this->referensi_jenis !== 'pembangunan_unit') {
            return null;
        }

        return PembangunanUnit::find($this->referensi_id);
    }

    public function pembangunanKawasan()
    {
        if ($this->referensi_jenis !== 'pembangunan_kawasan') {
            return null;
        }

        return PembangunanKawasan::find($this->referensi_id);
    }

    // helper
    public function getReferensi()
    {
        return match ($this->referensi_jenis) {
            'pembangunan_unit' => $this->pembangunanUnit(),
            'pembangunan_kawasan' => $this->pembangunanKawasan(),
            'pembangunan_proyek' => $this->pembangunanProyek(),
            default => null,
        };
    }
}