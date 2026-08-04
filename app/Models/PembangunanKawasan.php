<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasan extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_kawasan';
    protected $guarded = [];

    public function perumahan()
    {
        return $this->belongsTo(Perumahaan::class, 'perumahaan_id');
    }

    public function pengawas()
    {
        return $this->belongsTo(User::class, 'pengawas_id');
    }

    public function orders()
    {
        return $this->hasMany(PembangunanKawasanBarangOrder::class, 'pembangunan_kawasan_id');
    }

    public function upah()
    {
        return $this->hasMany(PembangunanKawasanUpah::class, 'pembangunan_kawasan_id');
    }
    
    public function pengajuanUpah()
    {
        return $this->hasMany(PembangunanKawasanUpahPengajuan::class, 'pembangunan_kawasan_id');
    }

    public function pembangunanKawasanBahan()
    {
        return $this->hasMany(PembangunanKawasanBahan::class, 'pembangunan_kawasan_id');
    }

    public function periodes()
    {
        return $this->hasMany(PembangunanKawasanPeriode::class, 'pembangunan_kawasan_id')->latest('created_at');
    }

    public function terminUpahHarian()
    {
        return $this->hasMany(PembangunanKawasanTerminUpahHarian::class, 'pembangunan_kawasan_id');
    }
}