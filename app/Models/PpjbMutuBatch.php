<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpjbMutuBatch extends Model
{
    protected $table    = 'ppjb_mutu_batch';
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
        return $this->hasMany(PpjbMutuItem::class, 'batch_id');
    }
}
