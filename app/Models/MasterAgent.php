<?php

namespace App\Models;

use App\Models\MasterAgentFee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterAgent extends Model
{
    use HasFactory;

    protected $table = 'master_agent';

    protected $fillable = [
        'nama_agent',
        'no_hp',
        'alamat',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // 🔗 Relasi
    public function fees()
    {
        return $this->hasMany(MasterAgentFee::class, 'agent_id');
    }

    public function bookings()
    {
        return $this->hasMany(CustomerBooking::class, 'agent_id');
    }

    public function pemesananUnits()
    {
        return $this->hasMany(PemesananUnit::class, 'agent_id');
    }
}
