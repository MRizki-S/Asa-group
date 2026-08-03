<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\UpahHarianTukang;
use App\Services\PengajuanUpahHarianTukangService;
use Illuminate\Http\Request;

class RiwayatUpahHarianTukangController extends Controller
{
    public function __construct(protected PengajuanUpahHarianTukangService $service) {}
    public function index(Request $request)
    {
        $pageActive = 'RiwayatUpahHarianKeuangan';

        // Filter referensi: 'all' (default), 'perumahan' (ABM), 'mangoon'
        $referensi = $request->input('referensi', 'all');
        $dari      = $request->input('dari');     // format: Y-m-d
        $sampai    = $request->input('sampai');   // format: Y-m-d

        $pengajuan = UpahHarianTukang::query()
            ->with(['createdBy', 'rekap'])
            ->when($referensi === 'perumahan', fn($q) => $q->where('jenis_referensi', 'perumahan'))
            ->when($referensi === 'mangoon',   fn($q) => $q->where('jenis_referensi', 'mangoon'))
            ->when($dari,    fn($q) => $q->whereDate('tanggal_mulai', '>=', $dari))
            ->when($sampai,  fn($q) => $q->whereDate('tanggal_selesai', '<=', $sampai))
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->latest()
            ->get();

        return view('keuangan.daftar-upahHarian.riwayat.index', [
            'pageActive' => $pageActive,
            'pengajuan'  => $pengajuan,
            'referensi'  => $referensi,
            'dari'       => $dari,
            'sampai'     => $sampai,
            'breadcrumbs' => [
                [
                    'label' => 'Riwayat Upah Harian Tukang',
                    'url'   => route('keuangan.riwayatUpahHarian.index'),
                ],
            ],
        ]);
    }

    // function detail riwayat upah harian tukang
     public function detail(Request $request, $id)
    {
        $pageActive = 'RiwayatUpahHarianKeuangan';

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

        return view('keuangan.daftar-upahHarian.riwayat.detail', [
            'pageActive'     => $pageActive,
            'pengajuan'      => $pengajuan,
            'isAbm'          => $isAbm,
            'detailPerTukang'=> $detailPerTukang,
            'unitLabels'     => $unitLabels,
            'kawasanLabels'  => $kawasanLabels,
            'proyekLabels'   => $proyekLabels,
            'breadcrumbs' => [
                [
                    'label' => 'Riwayat Upah Harian Tukang',
                    'url'   => route('keuangan.riwayatUpahHarian.index'),
                ],
            ],
        ]);
    }

}
