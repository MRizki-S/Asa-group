<?php

namespace App\Models;

use App\Models\PemesananUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemesananUnitPromoAdmin extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_unit_promo_admin';

    protected $fillable = [
        'pemesanan_unit_id',
        'nama_promo',
    ];

    public function pemesananUnit()
    {
        return $this->belongsTo(PemesananUnit::class, 'pemesanan_unit_id');
    }
}
