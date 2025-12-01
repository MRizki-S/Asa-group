<?php

namespace App\Models;

use App\Models\User;
use App\Models\PemesananUnit;
use App\Models\SubAdendumCaraBayar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Adendum extends Model
{
    use HasFactory;

    protected $table = 'adendum';

    protected $fillable = [
        'pemesanan_unit_id',
        'jenis',
        'jenis_list',
        'status',
        'diajukan_oleh',
        'disetujui_oleh',
        'tanggal_adendum'
    ];

    protected $casts = [
        'jenis_list' => 'array',
        'tanggal_adendum' => 'datetime',
    ];

    /**
     * Relasi ke pemesanan unit
     */
    public function pemesananUnit()
    {
        return $this->belongsTo(PemesananUnit::class, 'pemesanan_unit_id');
    }

    /**
     * Relasi ke user yang mengajukan
     */
    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    /**
     * Relasi ke user yang menyetujui
     */
    public function penyetuju()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Relasi ke sub adendum cara bayar
     */
    public function subCaraBayar()
    {
        return $this->hasOne(SubAdendumCaraBayar::class, 'adendum_id');
    }
}
