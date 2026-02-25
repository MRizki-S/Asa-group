<?php

namespace App\Models;

use App\Models\JurnalDetail;
use App\Models\KategoriAkunKeuangan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AkunKeuangan extends Model
{
    use HasFactory;

    protected $table = 'akun_keuangan';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'parent_id',
        'kategori_akun_id',
        'is_leaf',
    ];

    protected $casts = [
        'is_leaf' => 'boolean',
    ];



    // Parent (hirarki ke atas)
    public function parent()
    {
        return $this->belongsTo(AkunKeuangan::class, 'parent_id');
    }

    // Children (hirarki ke bawah)
    public function children()
    {
        return $this->hasMany(AkunKeuangan::class, 'parent_id');
    }

    // Kategori akun (ASET, BEBAN, dll)
    public function kategori()
    {
        return $this->belongsTo(KategoriAkunKeuangan::class, 'kategori_akun_id');
    }

    // Relasi ke jurnal detail
    public function jurnalDetails()
    {
        return $this->hasMany(JurnalDetail::class, 'akun_id');
    }
}
