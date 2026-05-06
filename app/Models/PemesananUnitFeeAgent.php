<?php

namespace App\Models;

use App\Models\MasterAgent;
use App\Models\MasterAgentFee;
use App\Models\PemesananUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemesananUnitFeeAgent extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_unit_fee_agent';

    protected $fillable = [
        'pemesanan_unit_id',
        'master_agent_fee_id',
        'nominal_snapshot',
    ];

    protected $casts = [
        'nominal_snapshot' => 'decimal:2',
    ];

    // 🔗 Relasi
    public function pemesanan()
    {
        return $this->belongsTo(PemesananUnit::class, 'pemesanan_unit_id');
    }

    public function masterAgentFee()
    {
        return $this->belongsTo(MasterAgentFee::class, 'master_agent_fee_id');
    }
}
