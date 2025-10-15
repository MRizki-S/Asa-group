<?php

namespace App\Models;

use App\Models\PemesananUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemesananUnitPembatalan extends Model
{
    use HasFactory;
    protected $table = 'pemesanan_unit_pembatalan';
    protected $fillable = ['pemesanan_unit_id', 'persentase_potongan'];

    public function pemesananUnit() {
        return $this->belongsTo(PemesananUnit::class);
    }
}
