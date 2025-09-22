<?php
namespace App\Models;

use App\Models\TahapKualifikasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tahap extends Model
{
    protected $table    = 'tahap';
    protected $fillable = ['perumahaan_id', 'nama_tahap', 'slug'];

// override getRouteKeyName untuk menggunakan slug
    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function booted()
    {
        static::creating(function ($tahap) {
            $tahap->slug = Str::slug($tahap->nama_tahap) . '-' . Str::random(5);
        });
    }

    // relasi many to many  ke type lewat pivot table tahap_type
    // relasi many-to-many ke Type
    public function types()
    {
        return $this->belongsToMany(Type::class, 'tahap_type', 'tahap_id', 'type_id')
            ->withPivot('id'); // <-- tambahkan ini supaya pivot id ikut
    }

    // relasi many to many ke type lewat pivot tahap_kualifikasi
    public function kualifikasiBlok()
    {
        return $this->belongsToMany(
            KualifikasiBlok::class,           // model yang direlasikan
            'tahap_kualifikasi',              // nama tabel pivot
            'tahap_id',                       // foreign key di pivot untuk Tahap
            'kualifikasi_blok_id'             // foreign key di pivot untuk KualifikasiBlok
        )->withPivot(['id', 'nominal_tambahan']); // <-- kolom tambahan dari pivot
    }

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class);
    }

    public function tahapType()
    {
        return $this->hasMany(TahapType::class);
    }

    public function blok()
    {
        return $this->hasMany(Blok::class);
    }

    public function tahapKualifikasi()
    {
        return $this->hasMany(TahapKualifikasi::class);
    }

    public function unit()
    {
        return $this->hasMany(Unit::class);
    }
}
