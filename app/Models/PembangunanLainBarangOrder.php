<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanLainBarangOrder extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_lain_barang_order';
    protected $guarded = [];

    protected $casts = [
        'tanggal_diajukan' => 'datetime',
        'tanggal_spv'      => 'datetime',
        'tanggal_gudang'   => 'datetime',
        'tanggal_selesai'  => 'datetime',
    ];

    public function pembangunanLain()
    {
        return $this->belongsTo(PembangunanLain::class, 'pembangunan_lain_id');
    }

    public function proyek()
    {
        return $this->pembangunanLain();
    }

    public function sub()
    {
        return $this->belongsTo(PembangunanLainSub::class, 'pembangunan_lain_sub_id');
    }

    public function pembangunanLainSub()
    {
        return $this->sub();
    }

    public function pengawas()
    {
        return $this->belongsTo(User::class, 'pengawas_id');
    }

    public function ubs()
    {
        return $this->belongsTo(Ubs::class, 'ubs_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function spvBy()
    {
        return $this->belongsTo(User::class, 'spv_by');
    }

    public function gudangBy()
    {
        return $this->belongsTo(User::class, 'gudang_by');
    }

    public function accBy()
    {
        return $this->belongsTo(User::class, 'acc_by');
    }

    public function details()
    {
        return $this->hasMany(PembangunanLainBarangOrderDetail::class, 'order_id');
    }

    public function returns()
    {
        return $this->hasMany(PembangunanLainBarangReturn::class, 'order_id');
    }
}

