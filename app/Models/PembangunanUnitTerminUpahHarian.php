<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnitTerminUpahHarian extends Model
{
    protected $table = 'pembangunan_unit_termin_upah_harian';

    protected $fillable = [
        'pembangunan_unit_id',
        'upah_harian_tukang_alokasi_id',
        'tanggal',
        'tukang_id',
        'jenis',
        'jam_kerja',
        'nominal',
    ];

    protected $casts = [
        'tanggal'   => 'date',
        'nominal'   => 'decimal:2',
        'jam_kerja' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function pembangunanUnit()
    {
        return $this->belongsTo(
            PembangunanUnit::class,
            'pembangunan_unit_id'
        );
    }

    public function alokasi()
    {
        return $this->belongsTo(
            UpahHarianTukangAlokasi::class,
            'upah_harian_tukang_alokasi_id'
        );
    }

    public function tukang()
    {
        return $this->belongsTo(
            MasterTukang::class,
            'tukang_id'
        );
    }
}