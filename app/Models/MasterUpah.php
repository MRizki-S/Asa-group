<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterUpah extends Model
{
    protected $table = 'master_upah';
    protected $fillable = ['nama_upah'];

    public function rapUpah()
    {
        return $this->hasMany(MasterRapUpah::class);
    }
}
