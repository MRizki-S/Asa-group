<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterAgentFee extends Model
{
    use HasFactory;

    protected $table = 'master_agent_fee';

    protected $fillable = [
        'judul_fee',
        'nominal',
        'status_pengajuan',
        'diajukan_oleh',
        'disetujui_oleh',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    // 🔗 Relasi
    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
