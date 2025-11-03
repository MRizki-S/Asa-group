<?php
namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PpjbPembatalan extends Model
{
    protected $table = 'ppjb_pembatalan';

    protected $fillable = [
        'persentase_potongan',
        'nominal_potongan_kpr',
        'nominal_potongan_cash',
        'status_aktif',
        'status_pengajuan',
        'diajukan_oleh',
        'disetujui_oleh',
        'perumahaan_id',
    ];

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class, 'perumahaan_id');
    }
}
