<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpjbCaraBayar extends Model
{
    protected $table = 'ppjb_cara_bayar';

    protected $fillable = [
        'perumahaan_id',
        'jumlah_cicilan',
        'minimal_dp',
        'jenis_pembayaran',
        'nama_cara_bayar',
        'status_aktif',
        'status_pengajuan',
        'diajukan_oleh',
        'disetujui_oleh',
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
