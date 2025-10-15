<?php

namespace App\Models;

use App\Models\PemesananUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemesananUnitDataDiri extends Model
{
    use HasFactory;
    protected $table = 'pemesanan_unit_data_diri';
    protected $fillable = [
        'pemesanan_unit_id', 'nama_pribadi', 'no_hp',
        'provinsi_code', 'provinsi_nama', 'kota_code', 'kota_nama',
        'kecamatan_code', 'kecamatan_nama', 'desa_code', 'desa_nama',
        'rt', 'rw', 'alamat_detail'
    ];

    public function pemesananUnit() {
        return $this->belongsTo(PemesananUnit::class);
    }
}

