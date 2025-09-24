<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PpjbPromo extends Model
{
    protected $table = 'ppjb_promo';

    protected $fillable = [
        'tipe',
        'nama_promo',
        'slug',
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

        static::creating(function ($promo) {
            if (empty($promo->slug)) {
                $promo->slug = Str::slug($promo->nama_promo);
            }
        });

        static::updating(function ($promo) {
            if ($promo->isDirty('nama_promo')) {
                $promo->slug = Str::slug($promo->nama_promo);
            }
        });
    }
}
