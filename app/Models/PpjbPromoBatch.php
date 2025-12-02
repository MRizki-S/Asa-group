<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpjbPromoBatch extends Model
{
    protected $table    = 'ppjb_promo_batch';
    protected $fillable = [
        'tipe', 'perumahaan_id' ,'status_aktif', 'status_pengajuan', 'diajukan_oleh',
        'disetujui_oleh', 'tanggal_pengajuan', 'tanggal_acc', 'catatan_penolakan',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_acc'       => 'datetime',
    ];

    // ===== RELATIONS =====

    /**
     * Batch ini diajukan oleh Project Manager
     */
    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    /**
     * Batch ini disetujui oleh manager keuangan
     */
    public function penyetuju()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Item promo yang ada dalam batch ini
     */
    public function items()
    {
        return $this->hasMany(PpjbPromoItem::class, 'batch_id');
    }

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class, 'perumahaan_id');
    }
}
