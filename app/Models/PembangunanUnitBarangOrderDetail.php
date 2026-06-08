<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembangunanUnitBarangOrderDetail extends Model
{
    protected $table = 'pembangunan_unit_barang_order_detail';

    protected $fillable = [
        'order_id',
        'barang_id',
        'satuan_id',
        'ubs_id',
        'jumlah_input',
        'nama_barang',
        'satuan',
        'jumlah_base',
        'jumlah_final',
        'nota_detail_id',
        'konfirmasi',
        'rap_bahan_id',
        'alasan_permintaan_tidak_sesuai_rap',
        'harga_satuan_snapshot',
        'harga_total_snapshot',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnitBarangOrder::class, 'order_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function rapBahan(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnitRapBahan::class, 'rap_bahan_id');
    }

    public function notaDetail(): BelongsTo
    {
        return $this->belongsTo(NotaBarangMasukDetail::class, 'nota_detail_id');
    }
}
