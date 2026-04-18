<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiReviewRequest extends Model
{
    protected $guarded = ['id'];

    public function kpiUser()
    {
        return $this->belongsTo(KpiUser::class);
    }
}
