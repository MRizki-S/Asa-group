<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekBarangReturnFifo extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_proyek_barang_return_fifo';
    protected $guarded = [];

    public function returnDetail()
    {
        return $this->belongsTo(PembangunanProyekBarangReturnDetail::class, 'return_detail_id');
    }
}
