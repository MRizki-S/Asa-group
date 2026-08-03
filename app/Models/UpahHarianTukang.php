<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpahHarianTukang extends Model
{
    protected $table = 'upah_harian_tukang';

    protected $fillable = [
        'nomor_upah_harian',
        'jenis_referensi',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'alasan_penolakan',
    ];

    protected $casts = [
        'tanggal_mulai'  => 'date',
        'tanggal_selesai'=> 'date',
        'approved_at'    => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Detail harian setiap tukang.
     */
    public function details()
    {
        return $this->hasMany(
            UpahHarianTukangDetail::class,
            'upah_harian_tukang_id'
        );
    }

    /**
     * Rekap per-tukang (total normal, lembur, diterima).
     */
    public function rekap()
    {
        return $this->hasMany(
            UpahHarianTukangRekap::class,
            'upah_harian_tukang_id'
        );
    }

    /**
     * Staff yang membuat pengajuan.
     */
    public function createdBy()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * Manager yang menyetujui / menolak.
     */
    public function approvedBy()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }
}