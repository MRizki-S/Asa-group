<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'devisi_id',
    ];

    /**
     * Relasi ke Devisi
     */
    public function devisi(): BelongsTo
    {
        return $this->belongsTo(Devisi::class, 'devisi_id');
    }
}
