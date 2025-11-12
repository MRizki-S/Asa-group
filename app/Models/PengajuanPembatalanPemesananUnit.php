<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanPembatalanPemesananUnit extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_pembatalan_pemesanan_unit';

    protected $fillable = [
        'pemesanan_unit_id',
        'alasan_pembatalan',
        'alasan_detail',
        'bukti_pembatalan',
        'pengecualian_potongan',
        'status_pengajuan',

        // verifikasi berjenjang
        'status_mgr_pemasaran',
        'catatan_mgr_pemasaran',
        'status_mgr_keuangan',
        'catatan_mgr_keuangan',

        'diajukan_oleh',
        'disetujui_pemasaran_oleh',
        'disetujui_keuangan_oleh',

        'tanggal_pengajuan',
        'tanggal_respon_pemasaran',
        'tanggal_respon_keuangan',
    ];

    // 🔹 Relasi ke tabel pemesanan unit
    public function pemesananUnit()
    {
        return $this->belongsTo(PemesananUnit::class);
    }

    // 🔹 Relasi ke user yang mengajukan (sales)
    public function diajukanOleh()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    // 🔹 Relasi ke manager pemasaran
    public function disetujuiPemasaranOleh()
    {
        return $this->belongsTo(User::class, 'disetujui_pemasaran_oleh');
    }

    // 🔹 Relasi ke manager keuangan
    public function disetujuiKeuanganOleh()
    {
        return $this->belongsTo(User::class, 'disetujui_keuangan_oleh');
    }
}
