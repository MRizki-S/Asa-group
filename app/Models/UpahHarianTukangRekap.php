<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpahHarianTukangRekap extends Model
{
    protected $table = 'upah_harian_tukang_rekap';

    protected $fillable = [
        'upah_harian_tukang_id',
        'tukang_id',
        'total_upah_normal',
        'total_upah_lembur',
        'total_upah',
        'bon',
        'total_diterima',
        'keterangan',
    ];

    protected $casts = [
        'total_upah_normal' => 'decimal:2',
        'total_upah_lembur' => 'decimal:2',
        'total_upah'        => 'decimal:2',
        'bon'               => 'decimal:2',
        'total_diterima'    => 'decimal:2',
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
}
