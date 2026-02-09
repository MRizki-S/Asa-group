<?php

namespace App\Models;

use App\Models\User;
use App\Models\JurnalDetail;
use App\Models\PeriodeKeuangan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jurnal extends Model
{
    use HasFactory;

    protected $table = 'jurnal';

    protected $fillable = [
        'nomor_jurnal',
        'tanggal',
        'periode_id',
        'jenis_jurnal',
        'status',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // relasi
    public function details()
    {
        return $this->hasMany(JurnalDetail::class, 'jurnal_id');
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeKeuangan::class, 'periode_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* =====================
     * SCOPES
     * ===================== */

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /* =====================
     * HELPERS
     * ===================== */

    public function totalDebit()
    {
        return $this->details()->sum('debit');
    }

    public function totalKredit()
    {
        return $this->details()->sum('kredit');
    }

    public function isBalanced(): bool
    {
        return $this->totalDebit() == $this->totalKredit();
    }
}
