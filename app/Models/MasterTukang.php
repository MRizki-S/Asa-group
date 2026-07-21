<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterTukang extends Model
{
    protected $table = 'master_tukang';

    protected $fillable = [
        'kode',
        'nama_tukang',
        'gaji_harian_default',
        'jam_kerja_default',
        'status',
    ];

    protected $casts = [
        'gaji_harian_default' => 'decimal:2',
        'jam_kerja_default' => 'integer',
        'status' => 'boolean',
    ];

    public function upahHarianDetails()
    {
        return $this->hasMany(
            UpahHarianTukangDetail::class,
            'tukang_id'
        );
    }

    public function pembangunanUnitTerminUpahHarian()
    {
        return $this->hasMany(
            PembangunanUnitTerminUpahHarian::class,
            'tukang_id'
        );
    }

    public function pembangunanKawasanTerminUpahHarian()
    {
        return $this->hasMany(
            PembangunanKawasanTerminUpahHarian::class,
            'tukang_id'
        );
    }
}
