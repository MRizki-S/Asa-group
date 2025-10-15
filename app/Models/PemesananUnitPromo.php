<?php

namespace App\Models;

use App\Models\PemesananUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemesananUnitPromo extends Model
{
    use HasFactory;
    protected $table = 'pemesanan_unit_promo';
    protected $fillable = ['pemesanan_unit_id', 'nama_promo'];

    public function pemesananUnit() {
        return $this->belongsTo(PemesananUnit::class);
    }
}
