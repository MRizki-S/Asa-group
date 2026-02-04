<?php

namespace App\Models;

use App\Models\PpjbBonusKprBatch;
use Illuminate\Database\Eloquent\Model;

class PpjbBonusKprItem extends Model
{
    protected $table = 'ppjb_bonus_kpr_items';
    protected $fillable = ['batch_id', 'nama_bonus', 'nominal_bonus'];

    // ===== RELATIONS =====

    public function batch()
    {
        return $this->belongsTo(PpjbBonusKprBatch::class, 'batch_id');
    }
}
