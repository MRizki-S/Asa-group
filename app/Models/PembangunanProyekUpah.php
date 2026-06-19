<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembangunanProyekUpah extends Model
{
    use HasFactory;

    protected $table = 'pembangunan_proyek_upah';
    protected $guarded = [];

    public function proyek()
    {
        return $this->belongsTo(PembangunanProyek::class, 'pembangunan_proyek_id');
    }
}