<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanTerminUpahHarian extends Model
{
    protected $table = 'pembangunan_kawasan_termin_upah_harian';

    protected $fillable = [
        'pembangunan_kawasan_id',
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

    public function pembangunanKawasan()
    {
        return $this->belongsTo(
            PembangunanKawasan::class,
            'pembangunan_kawasan_id'
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