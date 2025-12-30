<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaBarangMasukDetail extends Model
{
    use HasFactory;

    protected $table = 'nota_barang_masuk_detail';

    protected $fillable = [
        'nota_id',
        'barang_id',
        'merk',
        'jumlah_masuk',
        'harga_satuan',
        'harga_total',
        'jumlah_sisa',
    ];

    /**
     * Relasi ke nota barang masuk
     */
    public function nota()
    {
        return $this->belongsTo(NotaBarangMasuk::class, 'nota_id');
    }

    /**
     * Relasi ke master barang
     */
    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
