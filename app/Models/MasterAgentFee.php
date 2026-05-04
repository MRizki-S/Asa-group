<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterAgentFee extends Model
{
    use HasFactory;

    protected $table = 'master_agent_fee';

    protected $fillable = [
        'nama_fee',
        'jenis_fee', // fix / persen
        'nilai_fee',
        'is_active',
    ];

    protected $casts = [
        'nilai_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // 🔗 Relasi
    public function pemesananFees()
    {
        return $this->hasMany(PemesananUnitFeeAgent::class, 'agent_fee_id');
    }
}
