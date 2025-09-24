<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerBooking extends Model
{
    protected $fillable = [
        'user_id',
        'perumahaan_id',
        'tahap_id',
        'unit_id',
        'slug',
        'tanggal_booking',
        'tanggal_expired',
    ];

    protected static function booted()
    {
        static::creating(function ($booking) {
            if ($booking->unit) {
                $slugBase = Str::slug($booking->unit->nama_unit);
            } else {
                $slugBase = 'booking';
            }

            $booking->slug = $slugBase . '-' . $booking->user_id . '-' . Str::random(5);
        });

        static::updating(function ($booking) {
            if ($booking->isDirty('unit_id')) {
                if ($booking->unit) {
                    $slugBase = Str::slug($booking->unit->nama_unit);
                } else {
                    $slugBase = 'booking';
                }

                $booking->slug = $slugBase . '-' . $booking->user_id . '-' . Str::random(5);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function perumahaan()
    {
        return $this->belongsTo(Perumahaan::class);
    }

    public function tahap()
    {
        return $this->belongsTo(Tahap::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
