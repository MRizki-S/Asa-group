<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanKawasanUpah extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_kawasan_upah';
    protected $guarded = [];

    public function kawasan()
    {
        return $this->belongsTo(PembangunanKawasan::class, 'pembangunan_kawasan_id');
    }
}