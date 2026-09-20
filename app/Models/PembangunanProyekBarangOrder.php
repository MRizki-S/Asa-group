<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekBarangOrder extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_proyek_barang_order';
    protected $guarded = [];
    
    protected $casts = [
        'tanggal_diajukan' => 'datetime',
        'tanggal_gudang' => 'datetime',
        'tanggal_spv' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function proyek()
    {
        return $this->belongsTo(PembangunanProyek::class, 'pembangunan_proyek_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function gudangBy()
    {
        return $this->belongsTo(User::class, 'gudang_by');
    }

    public function spvBy()
    {
        return $this->belongsTo(User::class, 'spv_by');
    }

    public function accBy()
    {
        return $this->belongsTo(User::class, 'acc_by');
    }

    public function accUser()
    {
        return $this->belongsTo(User::class, 'acc_by');
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