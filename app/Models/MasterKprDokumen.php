<?php

namespace App\Models;

use App\Models\MasterBank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterKprDokumen extends Model
{
    use HasFactory;
    protected $table = 'master_kpr_dokumen';
    protected $fillable = ['bank_id', 'nama_dokumen', 'wajib', 'kategori'];

    public function bank() {
        return $this->belongsTo(MasterBank::class, 'bank_id');
    }
}

