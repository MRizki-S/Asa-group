<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemesananUnitBiayaTambahan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_unit_biaya_tambahan';

    protected $fillable = [
        'pemesanan_unit_id',
        'tipe',
        'keterangan',
        'nominal',
        'note',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function pemesananUnit()
    {
        return $this->belongsTo(PemesananUnit::class, 'pemesanan_unit_id');
    }
}
