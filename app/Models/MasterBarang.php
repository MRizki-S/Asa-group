<?php

namespace App\Models;

use App\Models\StockGudangHub;
use App\Models\TransferGudangHubUbs;
use App\Models\NotaBarangMasukDetail;
use Illuminate\Database\Eloquent\Model;

class MasterBarang extends Model
{
    protected $table = 'master_barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'satuan',
        'created_by',
    ];

    public function stockHub()
    {
        return $this->hasOne(StockGudangHub::class, 'barang_id');
    }

    public function stockUbs()
    {
        return $this->hasMany(StockGudangUbs::class, 'barang_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
