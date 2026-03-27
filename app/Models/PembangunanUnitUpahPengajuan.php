<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembangunanUnitUpahPengajuan extends Model
{
    protected $table = 'pembangunan_unit_upah_pengajuan';

    protected $fillable = [
        'pembangunan_unit_id',
        'pembangunan_unit_qc_id',
        'pembangunan_unit_rap_upah_id',
        'nama_upah',
        'nominal_diajukan',
        'catatan_pengawas',
        'status_pengajuan',
        'tanggal_diajukan',
    ];

    protected $casts = [
        'tanggal_diajukan' => 'datetime',
        'nominal_diajukan' => 'decimal:2',
    ];

    /**
     * Relasi ke Pembangunan Unit
     */
    public function pembangunanUnit(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnit::class, 'pembangunan_unit_id');
    }

    /**
     * Relasi ke Quality Control Unit
     */
    public function pembangunanUnitQc(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnitQc::class, 'pembangunan_unit_qc_id');
    }

    /**
     * Relasi ke Snapshot RAP Upah
     */
    public function rapUpah(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnitRapUpah::class, 'pembangunan_unit_rap_upah_id');
    }

    /**
     * Helper untuk mendapatkan warna label status di View
     */
    public function getStatusStyleAttribute(): string
    {
        return match ($this->status_pengajuan) {
            'diajukan' => 'bg-amber-100 text-amber-600',
            'disetujui_mgr_produksi' => 'bg-blue-100 text-blue-600',
            'disetujui_mgr_dukungan' => 'bg-purple-100 text-purple-600',
            'disetujui_akuntan' => 'bg-green-100 text-green-600',
            'ditolak_mgr_produksi', 'ditolak_mgr_dukungan', 'ditolak_akuntan' => 'bg-red-100 text-red-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * Helper untuk merapikan teks status (menghilangkan underscore)
     */
    public function getStatusLabelAttribute(): string
    {
        return str_replace('_', ' ', ucfirst($this->status_pengajuan));
    }
}
