<?php
namespace App\Models;

use App\Models\MasterBank;
use App\Models\PemesananUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemesananUnitKpr extends Model
{
    use HasFactory;
    protected $table    = 'pemesanan_unit_kpr';
    protected $fillable = [
        'pemesanan_unit_id', 'dp_rumah_induk', 'dp_dibayarkan_pembeli',
        'sbum_dari_pemerintah', 'luas_kelebihan', 'nominal_kelebihan',
        'total_dp', 'harga_kpr', 'harga_total', 'status_kpr', 'bank_id',
    ];

    protected $casts = [
        'status_kpr' => 'string',
    ];

    public function pemesananUnit()
    {
        return $this->belongsTo(PemesananUnit::class);
    }

    public function dokumen()
    {
        return $this->hasMany(PemesananUnitKprDokumen::class);
    }

    public function bank()
    {
        return $this->belongsTo(MasterBank::class, 'bank_id');
    }

}
