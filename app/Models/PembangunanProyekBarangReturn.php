<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekBarangReturn extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_proyek_barang_returns';
    protected $guarded = [];

    public function proyek()
    {
        return $this->belongsTo(PembangunanProyek::class, 'pembangunan_proyek_id');
    }

    public function order()
    {
        return $this->belongsTo(PembangunanProyekBarangOrder::class, 'order_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(PembangunanProyekBarangReturnDetail::class, 'return_id');
    }
}