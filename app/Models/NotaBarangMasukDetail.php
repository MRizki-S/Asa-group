<?php

namespace App\Models;

use App\Models\MasterBarang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    protected $casts = [
        'jumlah_masuk' => 'decimal:2',
        'jumlah_sisa'  => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'harga_total'  => 'decimal:2',
    ];

    // RELASI

    // Detail milik 1 nota
    public function nota()
    {
        return $this->belongsTo(NotaBarangMasuk::class, 'nota_id');
    }

    // Detail untuk 1 barang
    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    // Kalau nanti kamu pakai barang_keluar_fifo
    // public function barangKeluarFifo()
    // {
    //     return $this->hasMany(BarangKeluarFifo::class, 'nota_detail_id');
    // }
}
