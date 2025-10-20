<?php

namespace App\Models;

use App\Models\PemesananUnit;
use App\Models\PemesananUnitCicilan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemesananUnitCaraBayar extends Model
{
    use HasFactory;
    protected $table = 'pemesanan_unit_cara_bayar';
    protected $fillable = ['pemesanan_unit_id', 'jumlah_cicilan', 'minimal_dp'];

    public function pemesananUnit() {
        return $this->belongsTo(PemesananUnit::class);
    }
}

