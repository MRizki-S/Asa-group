<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MasterKprDokumen;
use App\Models\PemesananUnit;
use App\Models\Perumahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagePemesananAgentController extends Controller
{
    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $perumahaanId = $this->currentPerumahaanId();

        // ambil nama perumahaan
        $namaPerumahaan = null;
        if ($perumahaanId) {
            $namaPerumahaan = Perumahaan::where('id', $perumahaanId)->value('nama_perumahaan');
        }

        // Filter Tahun dan Bulan berdasarkan tanggal_pemesanan (Default: Tahun ini, Semua Bulan)
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', '');

        // query pemesanan unit kpr (Agent)
        $pemesananKprQuery = PemesananUnit::with([
            'customer',
            'sales',
            'agent',
            'unit.blok',
            'unit.pembangunanUnit.pembangunanUnitQc',
            'kpr.dokumen',
            'kpr.bank',
            'kpr.pemesananUnit',
        ])
            ->where('cara_bayar', 'kpr')
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $perumahaanId)
            ->where('source', 'agent')
            // ⛔ Filter sama juga untuk cash
            ->whereDoesntHave('pengajuanPembatalan', function ($q) {
                $q->where('status_pengajuan', '!=', 'ditolak');
            });

        if ($tahun) {
            $pemesananKprQuery->whereYear('tanggal_pemesanan', $tahun);
        }
        if ($bulan !== '' && $bulan !== null && $bulan !== 'all') {
            $pemesananKprQuery->whereMonth('tanggal_pemesanan', (int) $bulan);
        }

        $pemesananKpr = $pemesananKprQuery
            ->orderBy('tanggal_pemesanan', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // query pemesanan unit cash (Agent)
        $pemesananCashQuery = PemesananUnit::with([
            'customer',
            'sales',
            'agent',
            'unit.blok',
            'unit.pembangunanUnit.pembangunanUnitQc',
            'cash.dokumen',
            'cash.pemesananUnit',
        ])
            ->where('cara_bayar', 'cash')
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $perumahaanId)
            ->where('source', 'agent')
            ->whereDoesntHave('pengajuanPembatalan', function ($q) {
                $q->where('status_pengajuan', '!=', 'ditolak');
            });

        if ($tahun) {
            $pemesananCashQuery->whereYear('tanggal_pemesanan', $tahun);
        }
        if ($bulan !== '' && $bulan !== null && $bulan !== 'all') {
            $pemesananCashQuery->whereMonth('tanggal_pemesanan', (int) $bulan);
        }

        $pemesananCash = $pemesananCashQuery
            ->orderBy('tanggal_pemesanan', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // hitung kelengkapan berkas dari pemesanan kpr dan cash
        foreach ($pemesananKpr as $item) {
            $item->kelengkapan_berkas = '-';
            $item->total_dokumen = 0;
            $item->dokumen_lengkap = 0;

            if ($item->kpr && $item->kpr->bank_id) {
                $bankId = $item->kpr->bank_id;

                $total = MasterKprDokumen::where('bank_id', $bankId)->count();
                $lengkap = $item->kpr->dokumen->where('status', 1)->count();

                $item->total_dokumen = $total;
                $item->dokumen_lengkap = $lengkap;
                $item->kelengkapan_berkas = "{$lengkap} dari {$total}";
            }
        }

        foreach ($pemesananCash as $item) {
            $item->kelengkapan_berkas = '-';
            $item->total_dokumen = 0;
            $item->dokumen_lengkap = 0;

            if ($item->cash) {
                $total = $item->cash->dokumen->count();
                $lengkap = $item->cash->dokumen->where('status', 1)->count();

                $item->total_dokumen = $total;
                $item->dokumen_lengkap = $lengkap;
                $item->kelengkapan_berkas = "{$lengkap} dari {$total}";
            }
        }

        // return view
        return view('marketing.manage-pemesanan-agent.index', [
            'pemesananKpr' => $pemesananKpr,
            'pemesananCash' => $pemesananCash,
            'namaPerumahaanAktif' => $namaPerumahaan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'breadcrumbs' => [
                [
                    'label' => 'Manage Pemesanan Agent - ' . ($namaPerumahaan ?? '-'),
                    'url' => route('marketing.managePemesananAgent.index'),
                ],
            ],
        ]);
    }

    public function show($id)
    {
        $pengajuan = PemesananUnit::with([
            'customer',
            'sales',
            'perumahaan',
            'tahap',
            'unit',
            'dataDiri',
            'cash',
            'kpr.bank',
            'caraBayar',
            'cicilan',
            'promo',
            'keterlambatan',
            'pembatalan',
            'bonusCash',
            'bonusKpr',
            'feeAgent.masterAgentFee',
            'agent',
        ])->findOrFail($id);

        return view('marketing.manage-pemesanan.show', [
            'pengajuan'     => $pengajuan,
            'keterlambatan' => $pengajuan->keterlambatan,
            'pembatalan'    => $pengajuan->pembatalan,
            'bonusCash'     => $pengajuan->bonusCash,
            'bonusKpr'      => $pengajuan->bonusKpr,
            'isAgent'       => true,
            'backUrl'       => route('marketing.managePemesananAgent.index'),
            'pageActive'    => 'ManagePemesananAgent',
            'breadcrumbs'   => [
                ['label' => 'Kelola Pemesanan Agent', 'url' => route('marketing.managePemesananAgent.index')],
                ['label' => 'Detail Pemesanan Unit: ' . ($pengajuan->unit->nama_unit ?? '-'), 'url' => '#'],
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', '');
        $source = $request->input('source', 'agent');

        $perumahaanId = $this->currentPerumahaanId();
        $namaPerumahaan = null;
        if ($perumahaanId) {
            $namaPerumahaan = Perumahaan::where('id', $perumahaanId)->value('nama_perumahaan');
        }

        $namaBulan = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $periode = '(' . $tahun . ')';
        if ($bulan !== '' && $bulan !== null && $bulan !== 'all') {
            $bulanName = $namaBulan[(int) $bulan] ?? $bulan;
            $periode = '(' . $bulanName . ' ' . $tahun . ')';
        }

        $prefix = match ($source) {
            'internal' => 'Data-Closing-Unit',
            'all'      => 'Data-Closing-Unit-All',
            default    => 'Data-Closing-Unit-Agent',
        };

        $perumahaanSuffix = $namaPerumahaan ? '-' . $namaPerumahaan : '';
        $fileName = $prefix . $perumahaanSuffix . ' ' . $periode . '.xlsx';

        // Check if custom columns are selected
        $columns = $request->input('columns', []);
        if (is_string($columns)) {
            $columns = explode(',', $columns);
        }
        $columns = array_filter(array_map('trim', $columns));

        if (!empty($columns)) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\CustomClosingUnitExport($tahun, $bulan, $perumahaanId, $namaPerumahaan, $columns, $source),
                $fileName
            );
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\DataClosingUnitExport($tahun, $bulan, $perumahaanId, $namaPerumahaan, $source),
            $fileName
        );
    }
}
