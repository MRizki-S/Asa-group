<?php

namespace App\Models;

use App\Models\Adendum;
use App\Models\PemesananUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubAdendumCaraBayar extends Model
{
    use HasFactory;

    protected $table = 'sub_adendum_cara_bayar';

    protected $fillable = [
        'adendum_id',
        'pemesanan_unit_id',
        'cara_bayar_lama',
        'cara_bayar_baru',
        'data_lama_json',
        'data_baru_json'
    ];

    protected $casts = [
        'data_lama_json' => 'array',
        'data_baru_json' => 'array',
    ];

    /**
     * Relasi ke addendum utama
     */
    public function addendum()
    {
        return $this->belongsTo(Adendum::class, 'adendum_id');
    }

    /**
     * Relasi ke pemesanan unit
     */
    public function pemesananUnit()
    {
        return $this->belongsTo(PemesananUnit::class, 'pemesanan_unit_id');
    }
}
