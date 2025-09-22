<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahapType extends Model
{
    protected $table = "tahap_type";
    protected $fillable = ['id','tahap_id', 'type_id'];

    public function tahap()
    {
        return $this->belongsTo(Tahap::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }
}
