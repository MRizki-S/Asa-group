<?php

namespace App\Models;

use App\Models\Unit;
use App\Models\User;
use App\Models\Company;
use App\Models\Perumahaan;
use Illuminate\Database\Eloquent\Model;

class PembangunanUnit extends Model
{
    protected $table = 'pembangunan_unit';

    protected $fillable = [
        'unit_id',
        'perumahaan_id',
        'company_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_pembangunan',
        'status_serah_terima',
        'pengawas_id',
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
     * Relasi ke Pengawas (User)
     */
    public function pengawas()
    {
        return $this->belongsTo(User::class, 'pengawas_id');
    }
}
