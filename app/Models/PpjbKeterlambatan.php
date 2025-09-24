<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PpjbKeterlambatan extends Model
{
    protected $table = 'ppjb_keterlambatan';

    protected $fillable = [
        'persentase_denda',
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
