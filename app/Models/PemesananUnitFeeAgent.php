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
        'agent_id',
        'agent_fee_id',
        'nama_fee_snapshot',
        'jenis_fee_snapshot',
        'nilai_fee_snapshot',
        'nominal_fee',
    ];

    protected $casts = [
        'nilai_fee_snapshot' => 'decimal:2',
        'nominal_fee' => 'decimal:2',
    ];

    // 🔗 Relasi
    public function pemesanan()
    {
        return $this->belongsTo(PemesananUnit::class, 'pemesanan_unit_id');
    }

    public function agent()
    {
        return $this->belongsTo(MasterAgent::class, 'agent_id');
    }

    public function fee()
    {
        return $this->belongsTo(MasterAgentFee::class, 'agent_fee_id');
    }
}
