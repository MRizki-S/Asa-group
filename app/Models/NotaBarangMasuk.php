<?php

namespace App\Models;

use App\Models\User;
use App\Models\NotaBarangMasukDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    protected $casts = [
        'tanggal_nota' => 'date',
    ];

    // RELASI

    // 1 nota punya banyak detail
    public function details()
    {
        return $this->hasMany(NotaBarangMasukDetail::class, 'nota_id');
    }

    // User pembuat nota
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
