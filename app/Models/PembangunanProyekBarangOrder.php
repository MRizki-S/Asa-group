<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekBarangOrder extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_proyek_barang_order';
    protected $guarded = [];

    public function proyek()
    {
        return $this->belongsTo(PembangunanProyek::class, 'pembangunan_proyek_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(PembangunanProyekBarangOrderDetail::class, 'order_id');
    }
    
    public function returns()
    {
        return $this->hasMany(PembangunanProyekBarangReturn::class, 'order_id');
    }
}