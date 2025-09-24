<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PpjbMutu extends Model
{
    protected $table = 'ppjb_mutu';

    protected $fillable = [
        'nama_mutu',
        'slug',
        'nominal_mutu',
        'status_aktif',
        'status_pengajuan',
        'diajukan_oleh',
        'disetujui_oleh',
        'tanggal_pengajuan',
        'tanggal_acc',
        'catatan_penolakan',
    ];

    // === RELASI ===
    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    // === SLUG AUTO ===
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($mutu) {
            if (empty($mutu->slug)) {
                $mutu->slug = Str::slug($mutu->nama_mutu);
            }
        });

        static::updating(function ($mutu) {
            if ($mutu->isDirty('nama_mutu')) {
                $mutu->slug = Str::slug($mutu->nama_mutu);
            }
        });
    }
}
