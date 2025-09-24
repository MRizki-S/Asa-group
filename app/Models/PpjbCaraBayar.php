<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpjbCaraBayar extends Model
{
    protected $table = 'ppjb_cara_bayar';

    protected $fillable = [
        'jumlah_cicilan',
        'minimal_dp',
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
}
