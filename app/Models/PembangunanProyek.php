<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyek extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_proyek';
    protected $guarded = [];

    public function pengawas()
    {
        return $this->belongsTo(User::class, 'pengawas_unit');
    }

    public function orders()
    {
        return $this->hasMany(PembangunanProyekBarangOrder::class, 'pembangunan_proyek_id');
    }

    public function upah()
    {
        return $this->hasMany(PembangunanProyekUpah::class, 'pembangunan_proyek_id');
    }
    
    public function pengajuanUpah()
    {
        return $this->hasMany(PembangunanProyekUpahPengajuan::class, 'pembangunan_proyek_id');
    }

    public function pembangunanProyekBahan()
    {
        return $this->hasMany(PembangunanProyekBahan::class, 'pembangunan_proyek_id');
    }
}