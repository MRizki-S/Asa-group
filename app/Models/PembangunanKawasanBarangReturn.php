<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanBarangReturn extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_kawasan_barang_returns';
    protected $guarded = [];

    public function kawasan()
    {
        return $this->belongsTo(PembangunanKawasan::class, 'pembangunan_kawasan_id');
    }

    public function periode()
    {
        return $this->belongsTo(PembangunanKawasanPeriode::class, 'pembangunan_kawasan_periode_id');
    }

    public function order()
    {
        return $this->belongsTo(PembangunanKawasanBarangOrder::class, 'order_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function accBy()
    {
        return $this->belongsTo(User::class, 'acc_by');
    }

    public function details()
    {
        return $this->hasMany(PembangunanKawasanBarangReturnDetail::class, 'return_id');
    }
}