<?php
namespace App\Http\Controllers\Marketing;

use App\Models\Perumahaan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PemesananUnit;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationPribadiService;
use App\Models\PengajuanPembatalanPemesananUnit;

class PengajuanPembatalanController extends Controller
{

    protected NotificationPribadiService $notification;

    public function __construct(NotificationPribadiService $notification)
    {
        $this->notification = $notification;
    }

    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

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
        $perumahaanId = $this->currentPerumahaanId();
        $user         = Auth::user();

        $namaPerumahaan      = 'Global';
        $pengajuanPembatalan = collect();

        // 🔹 Query dasar (biar tidak duplikat)
        $query = PengajuanPembatalanPemesananUnit::with([
            'pemesananUnit.perumahaan',
            'pemesananUnit.unit',
            'pemesananUnit.customer',
            'pemesananUnit.sales',
            'diajukanOleh',
            'disetujuiPemasaranOleh',
            'disetujuiKeuanganOleh',
        ])
        ->where('status_pengajuan', 'pending')
        ->orderByDesc('created_at');

        // 🔸 Filter tambahan kalau login sebagai SALES
        if ($user->hasRole('Sales')) {
            $query->where('diajukan_oleh', $user->id);
        }

        if ($user->is_global) {
            // 🌐 Jika user global → ambil semua & group by perumahaan
            $pengajuanPembatalan = $query->get()->groupBy('pemesananUnit.perumahaan_id');
        } else {
            // 🏠 Jika bukan global → filter hanya perumahaan user
            $pengajuanPembatalan = $query
                ->whereHas('pemesananUnit', function ($q) use ($perumahaanId) {
                    $q->where('perumahaan_id', $perumahaanId);
                })
                ->get();

            // Ambil nama perumahaan
            $perumahaan = Perumahaan::find($perumahaanId);
            if ($perumahaan) {
                $namaPerumahaan = $perumahaan->nama_perumahaan;
            }
        }

        return view('marketing.pengajuan-pembatalanPemesanan.index', [
            'breadcrumbs'         => [
                [
                    'label' => 'Pengajuan Pembatalan Pemesanan Unit' .
                    ($user->is_global ? ' (Global)' : ' - ' . $namaPerumahaan),
                    'url'   => '',
                ],
            ],
            'isGlobal'            => $user->is_global,
            'pengajuanPembatalan' => $pengajuanPembatalan,
            'namaPerumahaan'      => $namaPerumahaan,
        ]);
    }

    public function show($id)
    {
        // Ambil pengajuan pembatalan beserta relasi pemesanan dan data terkait
        $pengajuanPembatalan = PengajuanPembatalanPemesananUnit::with([
            'pemesananUnit.perumahaan',
            'pemesananUnit.unit',
            'pemesananUnit.customer',
            'pemesananUnit.sales',
        ])->findOrFail($id);

        // Ambil data pemesanan terkait
        $pemesanan = $pengajuanPembatalan->pemesananUnit;

        // dd($pengajuanPembatalan);
        return view('marketing.pengajuan-pembatalanPemesanan.show', [
            'pengajuanPembatalan' => $pengajuanPembatalan,
            'breadcrumbs'         => [
                [
                    'label' => 'Pengajuan Pembatalan Pemesanan Unit',
                    'url'   => route('marketing.pengajuan-pembatalan.listPengajuan'),
                ],
                [
                    'label' => 'Unit ' . ($pemesanan->unit->nama_unit ?? '-'),
                    'url'   => '',
                ],
            ],
        ]);
    }

    // update keputusan dari manager pemasaran
    public function keputusanPemasaran(Request $request, $id)
    {
        $request->validate([
            'status_mgr_pemasaran'  => 'required|in:acc,tolak',
            'catatan_mgr_pemasaran' => 'nullable|string|max:1000',
        ]);

        $pengajuan = PengajuanPembatalanPemesananUnit::findOrFail($id);

        // Pastikan hanya manager pemasaran yang bisa akses
        if (! Auth::user()->hasRole('Manager Pemasaran')) {
            abort(403, 'Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

        // Update data keputusan
        $pengajuan->update([
            'status_mgr_pemasaran'     => $request->status_mgr_pemasaran,
            'catatan_mgr_pemasaran'    => $request->catatan_mgr_pemasaran,
            'tanggal_respon_pemasaran' => now(), // jika nama kolom sudah diganti
            'disetujui_pemasaran_oleh' => Auth::id(),
        ]);

        return redirect()
            ->route('marketing.pengajuan-pembatalan.show', $id)
            ->with('success', 'Keputusan Manager Pemasaran berhasil disimpan.');
    }

    // 🔹 Keputusan Manager Keuangan
    public function keputusanKeuangan(Request $request, $id)
    {
        $request->validate([
            'status_mgr_keuangan'  => 'required|in:acc,tolak',
            'catatan_mgr_keuangan' => 'required|string',
        ]);
        // dd($request->all());
        $pengajuan = PengajuanPembatalanPemesananUnit::findOrFail($id);

        $pengajuan->update([
            'status_mgr_keuangan'     => $request->status_mgr_keuangan,
            'catatan_mgr_keuangan'    => $request->catatan_mgr_keuangan,
            'disetujui_keuangan_oleh' => Auth::id(),
            'tanggal_respon_keuangan' => now(),
            'pengecualian_potongan'   => $request->has('pengecualian_potongan'),
            'status_pengajuan'        => $request->status_mgr_keuangan,
        ]);

        // 🔹 Jika disetujui (ACC), ubah status pemesanan unit menjadi "batal"
        if ($request->status_mgr_keuangan === 'acc') {
            $pemesanan = PemesananUnit::find($pengajuan->pemesanan_unit_id);

            if ($pemesanan) {
                $pemesanan->update([
                    'status_pemesanan' => 'batal',
                ]);
            }
        }

        // TODO: logika tambahan nanti untuk potongan keuangan bisa ditaruh di sini

        return redirect()
            ->back()
            ->with('success', 'Keputusan Manager Keuangan berhasil disimpan.');
    }
}
