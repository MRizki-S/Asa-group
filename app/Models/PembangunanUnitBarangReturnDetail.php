<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembangunanUnitBarangReturnDetail extends Model
{
    protected $table = 'pembangunan_unit_barang_return_detail';

    protected $fillable = [
        'return_id',
        'barang_id',
        'nama_barang',
        'satuan_id',
        'satuan',
        'jumlah_input',
        'jumlah_base',
        'jumlah_layak_base',
        'jumlah_rusak_base',
        'harga_satuan_snapshot',
        'harga_total_snapshot',
        'keterangan',
    ];

    public function barangReturn(): BelongsTo
    {
        return $this->belongsTo(PembangunanUnitBarangReturn::class, 'return_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuanModel(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function fifos(): HasMany
    {
        return $this->hasMany(PembangunanUnitBarangReturnFifo::class, 'return_detail_id');
    }
}
