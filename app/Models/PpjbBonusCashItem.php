<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpjbBonusCashItem extends Model
{
    protected $table = 'ppjb_bonus_cash_items';
    protected $fillable = ['batch_id', 'nama_bonus', 'nominal_bonus'];

    // ===== RELATIONS =====

    public function batch()
    {
        return $this->belongsTo(PpjbBonusCashBatch::class, 'batch_id');
    }
}
