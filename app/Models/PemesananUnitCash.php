<?php

namespace App\Models;

use App\Models\PemesananUnit;
use Illuminate\Database\Eloquent\Model;
use App\Models\PemesananUnitCashDokumen;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemesananUnitCash extends Model
{
    use HasFactory;
    protected $table = 'pemesanan_unit_cash';
    protected $fillable = [
        'pemesanan_unit_id', 'harga_rumah', 'luas_kelebihan', 'nominal_kelebihan', 'harga_jadi'
    ];

    public function pemesananUnit() {
        return $this->belongsTo(PemesananUnit::class);
    }

    public function dokumen() {
        return $this->hasMany(PemesananUnitCashDokumen::class);
    }
}

