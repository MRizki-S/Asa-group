<?php
namespace App\Models;

use App\Models\PemesananUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemesananUnitPembatalan extends Model
{
    use HasFactory;
    protected $table    = 'pemesanan_unit_pembatalan';
    protected $fillable = [
        'pemesanan_unit_id',
        'persentase_potongan',
        'nominal_potongan_kpr',
        'nominal_potongan_cash',
    ];
    public function pemesananUnit()
    {
        return $this->belongsTo(PemesananUnit::class);
    }
}
