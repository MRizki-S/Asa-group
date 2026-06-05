<?php

namespace App\Models;

use App\Models\NotaBarangMasukDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotaBarangMasuk extends Model
{
    protected $table = 'nota_barang_masuk';

    protected $fillable = [
        'nomor_nota',
        'tanggal_nota',
        'supplier',
        'cara_bayar',
        'status',
        'created_by',
        'posted_at'
    ];

    protected $casts = [
        'tanggal_nota' => 'date',
        'posted_at' => 'datetime'
    ];


    // user pembuat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // detail barang
    public function details()
    {
        return $this->hasMany(NotaBarangMasukDetail::class, 'nota_id');
    }
}
