<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanBarangOrder extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_kawasan_barang_order';
    protected $guarded = [];

    public function kawasan()
    {
        return $this->belongsTo(PembangunanKawasan::class, 'pembangunan_kawasan_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(PembangunanKawasanBarangOrderDetail::class, 'order_id');
    }
    
    public function returns()
    {
        return $this->hasMany(PembangunanKawasanBarangReturn::class, 'order_id');
    }
}