<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanProyekTerminUpahHarian extends Model
{
    protected $table = 'pembangunan_proyek_termin_upah_harian';

    protected $fillable = [
        'pembangunan_proyek_id',
        'upah_harian_tukang_alokasi_id',
        'tanggal',
        'tukang_id',
        'jenis',
        'jam_kerja',
        'nominal',
    ];

    protected $casts = [
        'tanggal'   => 'date',
        'jam_kerja' => 'integer',
        'nominal'   => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Pembangunan Proyek.
     */
    public function pembangunanProyek()
    {
        return $this->belongsTo(
            PembangunanProyek::class,
            'pembangunan_proyek_id'
        );
    }

    /**
     * Alokasi Upah Harian.
     */
    public function alokasi()
    {
        return $this->belongsTo(
            UpahHarianTukangAlokasi::class,
            'upah_harian_tukang_alokasi_id'
        );
    }

    /**
     * Master Tukang.
     */
    public function tukang()
    {
        return $this->belongsTo(
            MasterTukang::class,
            'tukang_id'
        );
    }
}