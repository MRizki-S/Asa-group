<?php

namespace App\Models;

use App\Models\PemesananUnit;
use App\Models\PemesananUnitCaraBayar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemesananUnitCicilan extends Model
{
    use HasFactory;
    protected $table = 'pemesanan_unit_cicilan';
    protected $fillable = [
        'pemesanan_unit_id', 'pembayaran_ke', 'tanggal_jatuh_tempo',
        'nominal', 'status_bayar', 'tanggal_pembayaran', 'is_active', 'adendum_id'
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_pembayaran'  => 'date'
    ];

    public function pemesananUnit() {
        return $this->belongsTo(PemesananUnit::class, 'pemesanan_unit_id');
    }
}
