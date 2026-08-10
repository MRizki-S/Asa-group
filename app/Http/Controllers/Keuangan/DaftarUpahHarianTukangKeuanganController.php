<?php

namespace App\Http\Controllers\Keuangan;

use App\Exports\UpahHarianTukangExport;
use App\Http\Controllers\Controller;
use App\Models\PembangunanKawasanTerminUpahHarian;
use App\Models\PembangunanProyekTerminUpahHarian;
use App\Models\PembangunanUnitTerminUpahHarian;
use App\Models\UpahHarianTukang;
use App\Services\PengajuanUpahHarianTukangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DaftarUpahHarianTukangKeuanganController extends Controller
{
    public function __construct(protected PengajuanUpahHarianTukangService $service) {}
    public function index(Request $request)
    {
        $pageActive = 'DaftarPengajuanUpahHarianKeuangan';

        // Filter referensi: 'all' (default), 'perumahan' (ABM), 'mangoon'
        $referensi = $request->input('referensi', 'all');

        $pengajuan = UpahHarianTukang::query()
            ->with(['createdBy', 'rekap'])
            ->when($referensi === 'perumahan', function ($query) {
                $query->where('jenis_referensi', 'perumahan');
            })
            ->when($referensi === 'mangoon', function ($query) {
                $query->where('jenis_referensi', 'mangoon');
            })
            ->where('status', 'diajukan')
            ->latest()
            ->get();

        return view('keuangan.daftar-upahHarian.index', [
            'pageActive' => $pageActive,
            'pengajuan' => $pengajuan,
            'referensi' => $referensi,
            'breadcrumbs' => [
                [
                    'label' => 'Daftar Pengajuan Upah Harian',
                    'url' => route('keuangan.daftarUpahHarian.index'),
                ],
            ],
        ]);
    }

    public function detail(Request $request, $id)
    {
        $pageActive = 'DaftarPengajuanUpahHarianKeuangan';

        $pengajuan = UpahHarianTukang::with(['createdBy', 'approvedBy', 'rekap'])->findOrFail($id);
        $isAbm = $pengajuan->jenis_referensi === 'perumahan';

        $rekapMap = $pengajuan->rekap->keyBy('tukang_id');

        $detailPerTukang = $pengajuan->details()
            ->with([
                'tukang',
                'alokasi:id,upah_harian_tukang_detail_id,jenis,jam_kerja,subtotal,referensi_jenis,referensi_id',
            ])
            ->orderBy('tukang_id')
            ->get()
            ->groupBy('tukang_id')
            ->map(function ($rows) use ($rekapMap) {
                $tukang = $rows->first()->tukang;
                $rekap = $rekapMap->get($tukang->id ?? null);
                return [
                    'tukang' => $tukang,
                    'details' => $rows,
                    'bon' => $rekap ? (float) $rekap->bon : 0,
                ];
            });

        $unitLabels    = $this->service->pembangunanUnits()->keyBy('id');
        $kawasanLabels = $this->service->pembangunanKawasans()->keyBy('id');
        $proyekLabels  = $this->service->pembangunanProyeks()->keyBy('id');

        return view('keuangan.daftar-upahHarian.detail', [
            'pageActive'     => $pageActive,
            'pengajuan'      => $pengajuan,
            'isAbm'          => $isAbm,
            'detailPerTukang'=> $detailPerTukang,
            'unitLabels'     => $unitLabels,
            'kawasanLabels'  => $kawasanLabels,
            'proyekLabels'   => $proyekLabels,
            'breadcrumbs' => [
                [
                    'label' => $isAbm ? 'Daftar Pengajuan Upah ABM' : 'Daftar Pengajuan Upah Mangoon',
                    'url'   => route('keuangan.daftarUpahHarian.index'),
                ],
                [
                    'label' => 'Detail: ' . $pengajuan->nomor_upah_harian,
                    'url'   => '#',
                ],
            ],
        ]);
    }

    public function updateBon(Request $request, $id)
    {
        $request->validate([
            'tukang_id' => 'required',
            'bon' => 'required|numeric|min:0',
        ]);

        $pengajuan = UpahHarianTukang::findOrFail($id);

        $rekap = \App\Models\UpahHarianTukangRekap::where('upah_harian_tukang_id', $pengajuan->id)
            ->where('tukang_id', $request->tukang_id)
            ->first();

        if (!$rekap) {
            return response()->json([
                'success' => false,
                'message' => 'Data rekap tidak ditemukan untuk tukang ini.',
            ], 404);
        }

        $bon = (float) $request->bon;
        $totalDiterima = (float) $rekap->total_upah - $bon;

        $rekap->update([
            'bon' => $bon,
            'total_diterima' => $totalDiterima,
        ]);

        return response()->json([
            'success' => true,
            'bon' => $bon,
            'total_diterima' => $totalDiterima,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $id = $request->input('id');
        $pengajuan = UpahHarianTukang::with(['createdBy', 'approvedBy', 'rekap'])->findOrFail($id);

        $rekapMap = $pengajuan->rekap->keyBy('tukang_id');

        $detailPerTukang = $pengajuan->details()
            ->with([
                'tukang',
                'alokasi:id,upah_harian_tukang_detail_id,jenis,jam_kerja,subtotal,referensi_jenis,referensi_id',
            ])
            ->orderBy('tukang_id')
            ->get()
            ->groupBy('tukang_id')
            ->map(function ($rows) use ($rekapMap) {
                $tukang = $rows->first()->tukang;
                $rekap = $rekapMap->get($tukang->id ?? null);
                return [
                    'tukang' => $tukang,
                    'details' => $rows,
                    'bon' => $rekap ? (float) $rekap->bon : 0,
                ];
            });

        $label = $pengajuan->jenis_referensi === 'perumahan' ? 'ABM' : 'Mangoon';
        $filename = 'Detail_Upah_Harian_Tukang_' . $label . '_' . str_replace('/', '-', $pengajuan->nomor_upah_harian) . '_' . now()->format('Ymd') . '.xlsx';

        return Excel::download(
            new UpahHarianTukangExport($pengajuan, $detailPerTukang),
            $filename
        );
    }


    // acc pengajuan upah
    public function accPengajuan(Request $request, $upahHarianTukang)
    {
        $pengajuan = UpahHarianTukang::where('nomor_upah_harian', $upahHarianTukang)
            ->orWhere('id', $upahHarianTukang)
            ->firstOrFail();

        if ($pengajuan->status === 'disetujui') {
            return back()->with('error', 'Pengajuan sudah pernah disetujui.');
        }

        // 1. Validasi status == diajukan
        if ($pengajuan->status !== 'diajukan') {
            return back()->with('error', 'Status pengajuan tidak valid untuk disetujui (harus diajukan).');
        }

        // Ambil details dengan alokasi
        $details = $pengajuan->details()->with('alokasi')->get();

        // Validasi Detail kosong
        if ($details->isEmpty()) {
            return back()->with('error', 'Pengajuan tidak memiliki detail upah.');
        }

        // 2. Validasi seluruh data rekap sudah ada
        $rekapTukang = $pengajuan->rekap()->pluck('tukang_id')->unique();
        $detailTukang = $details->pluck('tukang_id')->unique();

        if ($rekapTukang->isEmpty()) {
            return back()->with('error', 'Pengajuan masih memiliki data yang belum lengkap (rekap kosong).');
        }

        if ($rekapTukang->diff($detailTukang)->isNotEmpty() || $detailTukang->diff($rekapTukang)->isNotEmpty()) {
            return back()->with('error', 'Pengajuan masih memiliki data yang belum lengkap (rekap tidak sesuai dengan data tukang).');
        }

        // Validasi detail, alokasi, dan keberadaan referensi
        foreach ($details as $detail) {
            if ($detail->status_kehadiran) {
                // Semua detail hadir memiliki alokasi normal
                $normalAlokasi = $detail->alokasi->where('jenis', 'normal');
                if ($normalAlokasi->isEmpty()) {
                    return back()->with('error', 'Pengajuan masih memiliki data yang belum lengkap (detail hadir tanggal ' . $detail->tanggal->translatedFormat('d F Y') . ' tidak memiliki alokasi normal).');
                }

                // Jam normal sesuai / total jam normal = jam kerja
                $totalJamNormal = $normalAlokasi->sum('jam_kerja');
                if ($totalJamNormal !== $detail->jam_kerja) {
                    return back()->with('error', 'Pengajuan masih memiliki data yang belum lengkap (total jam normal pada tanggal ' . $detail->tanggal->translatedFormat('d F Y') . ' [' . $totalJamNormal . ' jam] tidak sesuai dengan jam kerja [' . $detail->jam_kerja . ' jam]).');
                }

                // Tidak ada alokasi kosong
                foreach ($detail->alokasi as $alokasi) {
                    if (empty($alokasi->referensi_jenis) || empty($alokasi->referensi_id) || $alokasi->jam_kerja <= 0) {
                        return back()->with('error', 'Pengajuan masih memiliki data yang belum lengkap (terdapat alokasi kosong atau tidak valid).');
                    }

                    if (!in_array($alokasi->referensi_jenis, ['pembangunan_unit', 'pembangunan_kawasan', 'pembangunan_proyek'])) {
                        return back()->with('error', 'Pengajuan masih memiliki data yang belum lengkap (jenis referensi alokasi tidak valid).');
                    }

                    // Kalau ada lembur maka subtotal dan tarif_per_jam ada
                    if (
                        $alokasi->jenis === 'lembur' &&
                        ($alokasi->subtotal === null || $alokasi->subtotal <= 0 ||
                            $alokasi->tarif_per_jam === null || $alokasi->tarif_per_jam <= 0)
                    ) {
                        return back()->with('error', 'Pengajuan masih memiliki data yang belum lengkap (alokasi lembur pada tanggal ' . $detail->tanggal->translatedFormat('d F Y') . ' tidak memiliki tarif atau subtotal nominal).');
                    }

                    // Validasi referensi benar-benar ada di database
                    $refModel = match ($alokasi->referensi_jenis) {
                        'pembangunan_unit' => \App\Models\PembangunanUnit::find($alokasi->referensi_id),
                        'pembangunan_kawasan' => \App\Models\PembangunanKawasan::find($alokasi->referensi_id),
                        'pembangunan_proyek' => \App\Models\PembangunanProyek::find($alokasi->referensi_id),
                        default => null,
                    };

                    if (!$refModel) {
                        return back()->with('error', 'Pengajuan memiliki alokasi ke referensi yang tidak valid atau sudah dihapus (' . $alokasi->referensi_jenis . ' ID: ' . $alokasi->referensi_id . ').');
                    }
                }
            }
        }

        // 3. Database Transaction & Commit (Approve + Generate Termin)
        DB::transaction(function () use ($pengajuan, $details) {
            $pengajuan->update([
                'status' => 'disetujui',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            foreach ($details as $detail) {
                if ($detail->status_kehadiran) {
                    foreach ($detail->alokasi as $alokasi) {
                        $referensiJenis = $alokasi->referensi_jenis;
                        $referensiId = $alokasi->referensi_id;

                        // Menghitung nominal termin berdasarkan jenis alokasi (menggunakan jam pembagi default)
                        $nominal = 0;
                        if ($alokasi->jenis === 'normal') {
                            $jamPembagi = $detail->jam_default_snapshot;
                            if ($jamPembagi <= 0) {
                                $jamPembagi = 8;
                            }
                            $tarifPerJam = $detail->nominal_harian_final / $jamPembagi;
                            $nominal = round($tarifPerJam * $alokasi->jam_kerja, 2);
                        } else {
                            $nominal = round($alokasi->subtotal, 2);
                        }

                        $model = match ($referensiJenis) {
                            'pembangunan_unit'
                            => PembangunanUnitTerminUpahHarian::class,
                            'pembangunan_kawasan'
                            => PembangunanKawasanTerminUpahHarian::class,
                            'pembangunan_proyek'
                            => PembangunanProyekTerminUpahHarian::class,
                            default => null,
                        };

                        if ($model) {
                            $foreignKey = match ($referensiJenis) {
                                'pembangunan_unit' => 'pembangunan_unit_id',
                                'pembangunan_kawasan' => 'pembangunan_kawasan_id',
                                'pembangunan_proyek' => 'pembangunan_proyek_id',
                            };

                            $dataTermin = [
                                $foreignKey => $referensiId,
                                'upah_harian_tukang_alokasi_id' => $alokasi->id,
                                'tanggal' => $detail->tanggal,
                                'tukang_id' => $detail->tukang_id,
                                'jenis' => $alokasi->jenis,
                                'jam_kerja' => $alokasi->jam_kerja,
                                'nominal' => $nominal,
                            ];

                            // Cegah duplicate termin berdasarkan upah_harian_tukang_alokasi_id
                            if (!$model::where('upah_harian_tukang_alokasi_id', $alokasi->id)->exists()) {
                                $model::create($dataTermin);
                            }
                        }
                    }
                }
            }
        });

        // 4. Kirim Notifikasi WhatsApp (Dilakukan di luar transaction agar aman dari timeout API)
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');
        $label = $pengajuan->jenis_referensi === 'perumahan' ? 'ABM' : 'Mangoon';
        $nomor = $pengajuan->nomor_upah_harian;
        $mulai = $pengajuan->tanggal_mulai ? $pengajuan->tanggal_mulai->translatedFormat('d F Y') : '-';
        $selesai = $pengajuan->tanggal_selesai ? $pengajuan->tanggal_selesai->translatedFormat('d F Y') : '-';
        $approvedBy = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Keuangan';
        $approvedAt = now()->translatedFormat('d F Y H:i');

        $message = "📢 *Persetujuan Upah Harian Tukang*\n\n" .
            "Pengajuan upah harian tukang {$label} berikut telah disetujui oleh {$approvedBy}.\n\n" .
            "• *Nomor Pengajuan:* {$nomor}\n" .
            "• *Periode:* {$mulai} s/d {$selesai}\n" .
            "• *Tanggal ACC:* {$approvedAt} WIB\n\n" .
            "Terima kasih.";

        try {
            $notificationGroup = app(\App\Services\NotificationGroupService::class);
            $notificationGroup->send($groupId, $message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Exception saat mengirim WhatsApp ACC: ' . $e->getMessage());
        }

        return redirect()->route('keuangan.daftarUpahHarian.index')
            ->with('success', 'Pengajuan upah harian tukang ' . $pengajuan->nomor_upah_harian . ' berhasil disetujui.');
    }
}
