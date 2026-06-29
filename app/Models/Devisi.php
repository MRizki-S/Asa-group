<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devisi extends Model
{
    protected $table = 'devisi';

    protected $fillable = [
        'nama_devisi',
    ];

    /**
     * Relasi ke Role
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'devisi_id');
    }
}
