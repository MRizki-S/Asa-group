<?php

namespace App\Models;

use App\Models\PemesananUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemesananUnitBonusCash extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_unit_bonus_cash';

    protected $fillable = [
        'pemesanan_unit_id',
        'nama_bonus',
        'nominal_bonus',
    ];

    public function pemesananUnit()
    {
        return $this->belongsTo(PemesananUnit::class);
    }
}
