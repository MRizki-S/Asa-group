<?php

namespace App\Models;

use App\Models\User;
use App\Models\MasterKprDokumen;
use App\Models\PemesananUnitKpr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemesananUnitKprDokumen extends Model
{
    use HasFactory;
    protected $table = 'pemesanan_unit_kpr_dokumen';
    protected $fillable = [
        'pemesanan_unit_kpr_id', 'master_kpr_dokumen_id', 'status',
        'tanggal_update', 'updated_by'
    ];

    protected $casts = ['tanggal_update' => 'datetime'];

    public function kpr() {
        return $this->belongsTo(PemesananUnitKpr::class, 'pemesanan_unit_kpr_id');
    }

    public function masterDokumen() {
        return $this->belongsTo(MasterKprDokumen::class, 'master_kpr_dokumen_id');
    }

    public function updatedBy()
{
    return $this->belongsTo(User::class, 'updated_by');
}
}
