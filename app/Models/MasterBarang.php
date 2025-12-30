<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBarang extends Model
{
    use HasFactory;

    protected $table = 'master_barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'satuan',
        'created_by',
    ];

    /**
     * Relasi ke user pembuat data
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke stock barang (1 barang = 1 stock)
     */
    public function stock()
    {
        return $this->hasOne(StockBarang::class, 'barang_id');
    }

    /**
     * Relasi ke detail nota barang masuk
     */
    public function notaMasukDetail()
    {
        return $this->hasMany(NotaBarangMasukDetail::class, 'barang_id');
    }
}
