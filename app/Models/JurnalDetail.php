<?php

namespace App\Models;

use App\Models\Jurnal;
use App\Models\AkunKeuangan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JurnalDetail extends Model
{
    use HasFactory;

    protected $table = 'jurnal_detail';

    public $timestamps = false;

    protected $fillable = [
        'jurnal_id',
        'akun_id',
        'debit',
        'kredit',
    ];

    protected $casts = [
        'debit'  => 'decimal:2',
        'kredit' => 'decimal:2',
    ];

    // relasi
    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class, 'jurnal_id');
    }

    public function akun()
    {
        return $this->belongsTo(AkunKeuangan::class, 'akun_id');
    }
}
