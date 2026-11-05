<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanLainBarangReturn extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_lain_barang_returns';
    protected $guarded = [];

    protected $casts = [
        'tanggal_return' => 'datetime',
        'acc_at'         => 'datetime',
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

    public function order()
    {
        return $this->belongsTo(PembangunanLainBarangOrder::class, 'order_id');
    }

    public function ubs()
    {
        return $this->belongsTo(Ubs::class, 'ubs_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pembuat()
    {
        return $this->createdBy();
    }

    public function accBy()
    {
        return $this->belongsTo(User::class, 'acc_by');
    }

    public function details()
    {
        return $this->hasMany(PembangunanLainBarangReturnDetail::class, 'return_id');
    }
}

