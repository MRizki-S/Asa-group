<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PeriodeKeuangan extends Model
{
    use HasFactory;

    protected $table = 'periode_keuangan';

    protected $fillable = [
        'nama_periode',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    

    public function jurnal()
    {
        return $this->hasMany(Jurnal::class, 'periode_id');
    }
}
