<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanUpahPengajuan extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_kawasan_upah_pengajuan';
    protected $guarded = [];

    public function kawasan()
    {
        return $this->belongsTo(PembangunanKawasan::class, 'pembangunan_kawasan_id');
    }

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