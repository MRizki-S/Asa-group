<?php

namespace App\Models;

use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\NotaBarangMasuk;
use Illuminate\Database\Eloquent\Model;

class NotaBarangMasukDetail extends Model
{
    protected $table = 'nota_barang_masuk_detail';

    protected $fillable = [
        'nota_id',
        'barang_id',
        'merk',
        'jumlah_input',
        'satuan_id',
        'jumlah_base',
        'harga_satuan',
        'harga_satuan_base',
        'harga_total',
        'jumlah_sisa'
    ];

    protected $casts = [
        'jumlah_input' => 'decimal:3',
        'jumlah_base' => 'decimal:3',
        'jumlah_sisa' => 'decimal:3',
        'harga_satuan' => 'decimal:2',
        'harga_total' => 'decimal:2'
    ];

    public function nota()
    {
        return $this->belongsTo(NotaBarangMasuk::class, 'nota_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }
}
