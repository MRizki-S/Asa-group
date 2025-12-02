<?php

namespace App\Models;

use App\Models\Unit;
use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class PengajuanPembangunanUnit extends Model
{
    protected $table = 'pengajuan_pembangunan_unit';

    protected $fillable = [
        'unit_id',
        'perumahaan_id',
        'company_id',
        'diajukan_oleh',
        'disetujui_oleh',
        'status_pengajuan',
        'tanggal_diajukan',
        'tanggal_direspon',
    ];

    /**
     * Relasi ke Unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relasi ke Perumahaan
     */
    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class);
    }

    /**
     * Relasi ke Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * User yang mengajukan
     */
    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    /**
     * User yang menyetujui
     */
    public function penyetuju()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
