<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpjbMutuItem extends Model
{
    protected $table = 'ppjb_mutu_items';
    protected $fillable = ['batch_id', 'nama_mutu', 'nominal_mutu'];

    // ===== RELATIONS =====

    public function batch()
    {
        return $this->belongsTo(PpjbMutuBatch::class, 'batch_id');
    }
}
