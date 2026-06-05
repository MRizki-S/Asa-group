<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembangunanUnitUpah extends Model
{
    protected $table = 'pembangunan_unit_upah';

    protected $fillable = [
        'pembangunan_unit_id',
        'pembangunan_unit_qc_id',
        'nama_upah',
        'total_nominal',
    ];

    protected $casts = [
        'total_nominal' => 'decimal:2',
    ];

    public function pembangunanUnit()
    {
        return $this->belongsTo(PembangunanUnit::class, 'pembangunan_unit_id');
    }

    public function pembangunanUnitQc()
    {
        return $this->belongsTo(PembangunanUnitQc::class, 'pembangunan_unit_qc_id');
    }
}
