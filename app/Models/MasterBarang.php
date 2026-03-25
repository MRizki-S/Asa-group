<?php

namespace App\Models;

use App\Models\BarangSatuanKonversi;
use App\Models\NotaBarangMasukDetail;
use Illuminate\Database\Eloquent\Model;

class MasterBarang extends Model
{
    protected $table = 'master_barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'base_unit_id',
        'is_stock'
    ];

    public function baseUnit()
    {
        return $this->belongsTo(MasterSatuan::class, 'base_unit_id');
    }

    public function satuanKonversi()
    {
        return $this->hasMany(BarangSatuanKonversi::class, 'barang_id');
    }

    public function stock()
    {
        return $this->hasMany(StockGudang::class, 'barang_id');
    }

    public function stockHub()
    {
        return $this->hasOne(StockGudang::class, 'barang_id')
            ->where('stock_type', 'HUB')
            ->whereNull('ubs_id');
    }

    public function notaDetails()
    {
        return $this->hasMany(NotaBarangMasukDetail::class, 'barang_id');
    }

    public function transferGudang()
    {
        return $this->hasMany(TransferGudangHubUbs::class, 'barang_id');
    }
}
