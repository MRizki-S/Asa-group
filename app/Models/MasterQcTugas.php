<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterQcTugas extends Model
{
    protected $table = 'master_qc_tugas';
    protected $fillable = ['master_qc_urutan_id', 'tugas'];

    public function urutan()
    {
        return $this->belongsTo(MasterQcUrutan::class);
    }
}
