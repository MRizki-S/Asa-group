<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomorPemesananTracker extends Model
{
    use HasFactory;

    protected $table = 'nomor_pemesanan_tracker';

    protected $fillable = [
        'perumahaan_id',
        'last_number',
    ];
}
