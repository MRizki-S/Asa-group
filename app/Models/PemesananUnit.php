<?php
namespace App\Models;

use App\Models\PemesananUnitCaraBayar;
use App\Models\PemesananUnitCash;
use App\Models\PemesananUnitCicilan;
use App\Models\PemesananUnitDataDiri;
use App\Models\PemesananUnitKeterlambatan;
use App\Models\PemesananUnitKpr;
use App\Models\PemesananUnitMutu;
use App\Models\PemesananUnitPembatalan;
use App\Models\PemesananUnitPromo;
use App\Models\PengajuanPembatalanPemesananUnit;
use App\Models\Perumahaan;
use App\Models\Tahap;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemesananUnit extends Model
{
    use HasFactory;
    protected $table = 'pemesanan_unit';

    protected $fillable = [
        'perumahaan_id', 'no_pemesanan', 'tahap_id', 'unit_id', 'customer_id', 'sales_id',
        'tanggal_pemesanan', 'cara_bayar', 'status_pengajuan', 'status_pemesanan',
        'harga_normal', 'harga_cash', 'total_tagihan', 'sisa_tagihan',
    ];

    protected $casts = [
        'tanggal_pemesanan' => 'date',
    ];

    // 🔹 Generate no_pemesanan otomatis saat membuat pemesanan unit
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pemesanan) {
            // 🔹 Ambil tracker untuk perumahaan terkait
            $tracker = NomorPemesananTracker::firstOrCreate(
                ['perumahaan_id' => $pemesanan->perumahaan_id],
                ['last_number' => 0]
            );

            // 🔹 Naikkan nomor terakhir
            $tracker->last_number += 1;
            $tracker->save();

            // 🔹 Format jadi 4 digit
            $pemesanan->no_pemesanan = str_pad($tracker->last_number, 4, '0', STR_PAD_LEFT);
        });
    }

    // 🔗 Relasi
    public function dataDiri()
    {return $this->hasOne(PemesananUnitDataDiri::class);}
    public function cash()
    {return $this->hasOne(PemesananUnitCash::class);}
    public function kpr()
    {return $this->hasOne(PemesananUnitKpr::class);}
    public function promo()
    {return $this->hasMany(PemesananUnitPromo::class);}
    public function mutu()
    {return $this->hasMany(PemesananUnitMutu::class);}
    public function caraBayar()
    {return $this->hasOne(PemesananUnitCaraBayar::class);}
    public function keterlambatan()
    {return $this->hasOne(PemesananUnitKeterlambatan::class);}
    public function pembatalan()
    {return $this->hasOne(PemesananUnitPembatalan::class);}

    // relasi ke master
    public function perumahaan()
    {return $this->belongsTo(Perumahaan::class);}

    public function tahap()
    {return $this->belongsTo(Tahap::class);}

    public function unit()
    {return $this->belongsTo(Unit::class);}

    public function customer()
    {return $this->belongsTo(User::class, 'customer_id');}

    public function sales()
    {return $this->belongsTo(User::class, 'sales_id');}
    public function cicilan()
    {
        return $this->hasMany(PemesananUnitCicilan::class);
    }

    public function pengajuanPembatalan()
    {
        return $this->hasOne(PengajuanPembatalanPemesananUnit::class);
    }

}
