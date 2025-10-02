<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpjbPromoItem extends Model
{
    protected $table    = 'ppjb_promo_items';
    protected $fillable = ['batch_id', 'nama_promo'];

    // ===== RELATIONS =====

    /**
     * Batch induk dari promo ini
     */
    public function batch()
    {
        return $this->belongsTo(PpjbPromoBatch::class, 'batch_id');
    }

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class, 'perumahaan_id');
    }
}
