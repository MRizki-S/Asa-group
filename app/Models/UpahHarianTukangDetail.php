<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpahHarianTukangDetail extends Model
{
    protected $table = 'upah_harian_tukang_detail';

    protected $fillable = [
        'upah_harian_tukang_id',
        'tukang_id',
        'tanggal',
        'status_kehadiran',
        'gaji_harian_default_snapshot',
        'jam_default_snapshot',
        'nominal_harian_final',
        'jam_kerja',
        'keterangan',
    ];

    protected $casts = [
        'tanggal'                       => 'date',
        'status_kehadiran'              => 'boolean',
        'gaji_harian_default_snapshot'  => 'decimal:2',
        'nominal_harian_final'          => 'decimal:2',
        'jam_default_snapshot'          => 'integer',
        'jam_kerja'                     => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Pengajuan induk.
     */
    public function pengajuan()
    {
        return $this->belongsTo(
            UpahHarianTukang::class,
            'upah_harian_tukang_id'
        );
    }

    /**
     * Tukang.
     */
    public function tukang()
    {
        return $this->belongsTo(
            MasterTukang::class,
            'tukang_id'
        );
    }

    /**
     * Alokasi pekerjaan.
     */
    public function alokasi()
    {
        return $this->hasMany(
            UpahHarianTukangAlokasi::class,
            'upah_harian_tukang_detail_id'
        );
    }
}