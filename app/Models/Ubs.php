<?php

namespace App\Models;

use App\Models\StockGudangUbs;
use Illuminate\Database\Eloquent\Model;

class Ubs extends Model
{
    protected $table = 'ubs';

    protected $fillable = [
        'nama_ubs',
        'alamat',
    ];

    public function stockBarang()
    {
        return $this->hasMany(StockGudangUbs::class, 'ubs_id');
    }
}

