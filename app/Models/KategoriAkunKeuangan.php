<?php

namespace App\Models;

use App\Models\AkunKeuangan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriAkunKeuangan extends Model
{
    use HasFactory;

    protected $table = 'kategori_akun_keuangan';

    protected $fillable = [
        'kode',
        'nama',
        'normal_balance',
        'laporan',
    ];


    // Akun
    public function akunKeuangan()
    {
        return $this->hasMany(AkunKeuangan::class, 'kategori_akun_id');
    }
}
