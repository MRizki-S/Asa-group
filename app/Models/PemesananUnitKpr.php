<?php

namespace App\Models;

use App\Models\PemesananUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemesananUnitKpr extends Model
{
    use HasFactory;
    protected $table = 'pemesanan_unit_kpr';
    protected $fillable = [
        'pemesanan_unit_id', 'dp_rumah_induk', 'dp_dibayarkan_pembeli',
        'sbum_dari_pemerintah', 'luas_kelebihan', 'nominal_kelebihan',
        'total_dp', 'harga_kpr', 'harga_total', 'status_kpr', 'bank_id'
    ];

    protected $casts = [
        'status_kpr' => 'string'
    ];

    public function pemesananUnit() {
        return $this->belongsTo(PemesananUnit::class);
    }

    public function dokumen() {
        return $this->hasMany(PemesananUnitKprDokumen::class);
    }
}

