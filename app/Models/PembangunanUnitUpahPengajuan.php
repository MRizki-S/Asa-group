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
        'disetujui_mgr_produksi',
        'disetujui_mgr_dukungan',
        'disetujui_akuntan',
        'alasan_ditolak',
        'ditolak_pada',
    ];

    protected $casts = [
        'tanggal_diajukan' => 'datetime',
        'disetujui_mgr_produksi' => 'datetime',
        'disetujui_mgr_dukungan' => 'datetime',
        'disetujui_akuntan' => 'datetime',
        'ditolak_pada' => 'datetime',
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
        if (str_contains($this->status_pengajuan, 'ditolak')) {
            return 'bg-red-100 text-red-700 border-red-200';
        }

        return match ($this->status_pengajuan) {
            'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            default     => 'bg-amber-100 text-amber-700 border-amber-200',
        };
    }

    /**
     * Helper untuk merapikan teks status (menghilangkan underscore)
     */
    public function getStatusLabelAttribute(): string
    {
        if (str_contains($this->status_pengajuan, 'ditolak')) {
            return ucwords(str_replace('_', ' ', $this->status_pengajuan));
        }

        return match ($this->status_pengajuan) {
            'disetujui' => 'Disetujui',
            default     => 'Pending',
        };
    }
}
