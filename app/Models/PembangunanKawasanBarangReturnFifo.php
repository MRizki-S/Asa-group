<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanBarangReturnFifo extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_kawasan_barang_return_fifo';
    protected $guarded = [];

    public function returnDetail()
    {
        return $this->belongsTo(PembangunanKawasanBarangReturnDetail::class, 'return_detail_id');
    }
}
