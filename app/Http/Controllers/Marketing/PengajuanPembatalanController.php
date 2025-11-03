<?php

namespace App\Http\Controllers\Marketing;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\PengajuanPembatalanPemesananUnit;

class PengajuanPembatalanController extends Controller
{
    public function store(Request $request)
    {
        // ✅ Validasi input
        $validated = $request->validate([
            'pemesanan_unit_id' => 'required|exists:pemesanan_unit,id',
            'alasan_pembatalan' => 'required|string',
            'alasan_detail'     => 'nullable|string|max:500',
            'bukti_pembatalan'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $fileName = null;

        try {
            // ✅ Mulai transaksi
            DB::beginTransaction();

            // ✅ Upload bukti pembatalan (jika ada)
            if ($request->hasFile('bukti_pembatalan')) {
                $file     = $request->file('bukti_pembatalan');
                $fileName = 'bukti-pembatalan-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('bukti_pembatalan', $fileName, 'public');
            }

            // ✅ Simpan ke database
            PengajuanPembatalanPemesananUnit::create([
                'pemesanan_unit_id'     => $validated['pemesanan_unit_id'],
                'alasan_pembatalan'     => $validated['alasan_pembatalan'],
                'alasan_detail'         => $validated['alasan_detail'] ?? null,
                'bukti_pembatalan'      => $fileName,
                'status_pengajuan'      => 'pending',
                'status_mgr_pemasaran'  => 'pending',
                'pengecualian_potongan' => 0,
                'diajukan_oleh'         => Auth::id(),
                'tanggal_pengajuan'     => now(),
            ]);

            // ✅ Commit transaksi
            DB::commit();

            return redirect()->back()->with('success', 'Pengajuan pembatalan berhasil dikirim ke Manager Pemasaran.');
        } catch (\Throwable $e) {
            // ❌ Jika gagal, rollback database
            DB::rollBack();

            // ❌ Hapus file jika sudah sempat di-upload
            if ($fileName && Storage::disk('public')->exists('bukti_pembatalan/' . $fileName)) {
                Storage::disk('public')->delete('bukti_pembatalan/' . $fileName);
            }

            // Kirim pesan error
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan pengajuan: ' . $e->getMessage());
        }
    }


    // halaman untuk list pengajuan pembatalan pemesanan unit
    public function listPengajuan()
    {
        // nanti di sini bisa ditambah query untuk list pembatalan
        return view('marketing.pengajuan-pembatalanPemesanan.index', [
            'breadcrumbs'        => [
                [
                    // 'label' => 'Pengajuan Pemesanan Unit' .
                    // ($user->is_global ? ' (Global)' : ' - ' . $namaPerumahaan),
                    'label' => 'Pengajuan Pembatalan Pemesanan Unit',
                    'url'   => '',
                ],
            ],
        ]);
    }
}
