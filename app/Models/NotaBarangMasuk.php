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
        'jenis_nota',
        'supplier_id',
        'cara_bayar',
        'stock_type',
        'ubs_id',
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

    // supplier
    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }

    // ubs
    public function ubs()
    {
        return $this->belongsTo(Ubs::class, 'ubs_id');
    }

    // detail barang
    public function details()
    {
        return $this->hasMany(NotaBarangMasukDetail::class, 'nota_id');
    }

    public function produksiBarangRakitan()
    {
        return $this->hasOne(ProduksiBarangRakitan::class, 'nota_barang_masuk_id');
    }
}
