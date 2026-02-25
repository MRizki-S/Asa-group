<?php

namespace App\Models;

use App\Models\StockGudangUbs;
use App\Models\Jurnal;
use Illuminate\Database\Eloquent\Model;

class Ubs extends Model
{
    protected $table = 'ubs';

    protected $fillable = ['nama_ubs', 'alamat', 'kode_ubs'];

    public function jurnal()
    {
        return $this->hasMany(Jurnal::class);
    }

    public function stockBarang()
    {
        return $this->hasMany(StockGudangUbs::class, 'ubs_id');
    }
}
