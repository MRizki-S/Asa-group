<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Type extends Model
{
    protected $table    = "type";
    protected $fillable = ['perumahaan_id', 'nama_type', 'slug', 'luas_bangunan', 'luas_tanah', 'harga_dasar', 'harga_diajukan', 'status_pengajuan', 'diajukan_oleh', 'disetujui_oleh', 'tanggal_pengajuan', 'tanggal_acc', 'catatan_penolakan'];

    protected static function booted()
    {
        static::creating(function ($type) {
            $type->slug = Str::slug($type->nama_type) . '-' . Str::random(5);
        });
    }

    public function tahaps()
    {
        return $this->belongsToMany(Tahap::class, 'tahap_type', 'type_id', 'tahap_id');
    }

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class);
    }

    public function tahapType()
    {
        return $this->hasMany(TahapType::class);
    }

    public function unit()
    {
        return $this->hasMany(Unit::class);
    }
}
