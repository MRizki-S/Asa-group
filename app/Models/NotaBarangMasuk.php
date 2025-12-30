<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaBarangMasuk extends Model
{
    use HasFactory;

    protected $table = 'nota_barang_masuk';

    protected $fillable = [
        'nomor_nota',
        'tanggal_nota',
        'supplier',
        'cara_bayar',
        'created_by',
    ];

    /**
     * User pembuat nota
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Detail barang dalam nota
     */
    public function details()
    {
        return $this->hasMany(NotaBarangMasukDetail::class, 'nota_id');
    }
}
