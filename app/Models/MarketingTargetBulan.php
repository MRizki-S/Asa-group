<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MarketingTargetBulan extends Model
{
    use HasFactory;

    protected $table = 'marketing_target_bulan';

    protected $fillable = [
        'marketing_target_quarter_id',
        'bulan',
        'target_penjualan_bulan',
    ];

    public function quarter()
    {
        return $this->belongsTo(MarketingTargetQuarter::class, 'marketing_target_quarter_id');
    }
}