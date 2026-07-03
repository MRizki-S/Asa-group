<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Role;

class Karyawan extends Model
{
    protected $table = 'karyawan';

    protected $fillable = [
        'nama',
        'no_hp',
        'role_id',
        'ubs_id',
    ];

    /**
     * Relasi ke Role / Jabatan
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Relasi ke Unit Bisnis (UBS)
     */
    public function ubs(): BelongsTo
    {
        return $this->belongsTo(Ubs::class, 'ubs_id');
    }

    /**
     * Relasi ke Unit Bisnis (UBS) - Compatibility Alias
     */
    public function perumahaan(): BelongsTo
    {
        return $this->belongsTo(Ubs::class, 'ubs_id');
    }

    /**
     * Relasi ke User Login (1 karyawan bisa punya >1 akun user)
     */
    public function users()
    {
        return $this->hasMany(User::class, 'karyawan_id');
    }
}
