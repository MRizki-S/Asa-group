<?php

namespace App\Models;

use App\Models\User;
use App\Models\PemesananUnitCash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemesananUnitCashDokumen extends Model
{
    use HasFactory;
    protected $table = 'pemesanan_unit_cash_dokumen';
    protected $fillable = [
        'pemesanan_unit_cash_id', 'nama_dokumen', 'status', 'tanggal_update', 'updated_by'
    ];

    protected $casts = ['tanggal_update' => 'datetime'];

    public function cash() {
        return $this->belongsTo(PemesananUnitCash::class, 'pemesanan_unit_cash_id');
    }

      // 🔗 Relasi ke User (yang terakhir mengupdate)
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

