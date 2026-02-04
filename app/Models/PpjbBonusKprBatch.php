<?php

namespace App\Models;

use App\Models\User;
use App\Models\Perumahaan;
use App\Models\PpjbBonusKprItem;
use Illuminate\Database\Eloquent\Model;

class PpjbBonusKprBatch extends Model
{
     protected $table    = 'ppjb_bonus_kpr_batch';
    protected $fillable = [
        'perumahaan_id', 'status_aktif', 'status_pengajuan', 'diajukan_oleh', 'disetujui_oleh',
        'tanggal_pengajuan', 'tanggal_acc', 'catatan_penolakan',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_acc'       => 'datetime',
    ];

    // ===== RELATIONS =====

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function penyetuju()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function items()
    {
        return $this->hasMany(PpjbBonusKprItem::class, 'batch_id');
    }

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class, 'perumahaan_id');
    }
}
