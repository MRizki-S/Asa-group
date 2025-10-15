<?php

namespace App\Models;

use App\Models\MasterKprDokumen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterBank extends Model
{
    use HasFactory;
    protected $table = 'master_bank';
    protected $fillable = ['nama_bank', 'kode_bank'];

    public function dokumen() {
        return $this->hasMany(MasterKprDokumen::class, 'bank_id');
    }
}
