<?php

namespace App\Models;

use App\Models\Jurnal;
use Illuminate\Database\Eloquent\Model;

class Ubs extends Model
{
    protected $table = 'ubs';

    protected $fillable = ['nama_ubs', 'alamat'];

    public function jurnal()
    {
        return $this->hasMany(Jurnal::class);
    }
}
