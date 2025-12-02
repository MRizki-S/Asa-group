<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'nama',
        'slug',
        'tipe',
        'is_global',
    ];

    /**
     * Relasi ke perumahaan
     */
    public function perumahaan(): HasMany
    {
        return $this->hasMany(Perumahaan::class, 'company_id');
    }

    /**
     * Relasi ke users
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id');
    }
}
