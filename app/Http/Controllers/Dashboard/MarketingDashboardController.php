<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MarketingAnggaranPromosi;
use App\Models\MarketingTargetBulan;
use App\Models\MarketingTargetQuarter;
use App\Models\PemesananUnit;
use App\Models\PemesananUnitKpr;
use App\Models\Perumahaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketingDashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = now();
        $currentYear = $now->year;
        $selectedYear = (int) $request->input('tahun', $currentYear);
        $selectedQuarter = (int) $request->input('quarter', $this->quarterFromMonth($now->month));
        $selectedQuarter = in_array($selectedQuarter, [1, 2, 3, 4], true) ? $selectedQuarter : $this->quarterFromMonth($now->month);

        $user = Auth::user();

        $currentPerumahaanId = $this->currentPerumahaanId();
        $perumahaan = $currentPerumahaanId ? Perumahaan::find($currentPerumahaanId) : null;

        if (!$perumahaan) {
            return view('dashboard.marketing.dashboardMarketing', [
                'perumahaan' => null,
                'selectedYear' => $selectedYear,
                'selectedQuarter' => $selectedQuarter,
                'currentYear' => $currentYear,
                'monthNames' => [],
                'quarterMonths' => [],
                'quarterMonthLabel' => '',
                'metrics' => [],
                'targetBulananMetrics' => [],
                'kprMetrics' => [],
                'kprBulananMetrics' => [],
                'breadcrumbs' => [],
                'years' => $this->generateYearList($currentYear),
                'quarters' => $this->generateQuarterList(),
            ])->with('error', 'Perumahaan tidak ditemukan atau Anda tidak memiliki akses.');
        }

        // Setup range tanggal
        $quarterRange = $this->quarterRange($selectedYear, $selectedQuarter);
        $quarterMonths = $this->quarterMonths($selectedQuarter);
        $monthNames = $this->monthNames();
        $quarterMonthLabel = collect($quarterMonths)
            ->map(fn($month) => $monthNames[$month])
            ->join(', ');
        $yearRange = [
            Carbon::create($selectedYear, 1, 1)->startOfDay(),
            Carbon::create($selectedYear, 12, 31)->endOfDay(),
        ];

        // Data target dan anggaran
        $targetQuarter = MarketingTargetQuarter::where('perumahaan_id', $currentPerumahaanId)
            ->where('tahun', $selectedYear)
            ->where('quarter', 'Q' . $selectedQuarter)
            ->first();

        $targetBulanan = MarketingTargetBulan::whereIn('bulan', $quarterMonths)
            ->whereHas('quarter', function ($query) use ($currentPerumahaanId, $selectedYear, $selectedQuarter) {
                $query->where('perumahaan_id', $currentPerumahaanId)
                    ->where('tahun', $selectedYear)
                    ->where('quarter', 'Q' . $selectedQuarter);
            })
            ->get()
            ->keyBy('bulan');

        $anggaranPromosi = MarketingAnggaranPromosi::where('perumahaan_id', $currentPerumahaanId)
            ->where('tahun', $selectedYear)
            ->where('quarter', 'Q' . $selectedQuarter)
            ->first();

        // 2. BULK FETCH PENJUALAN BULANAN (1 Query Saja)
        $penjualanBulananList = PemesananUnit::query()
            ->selectRaw('MONTH(tanggal_pemesanan) as bulan, COUNT(*) as total')
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_pengajuan', 'acc')
            ->whereIn(DB::raw('MONTH(tanggal_pemesanan)'), $quarterMonths)
            ->whereYear('tanggal_pemesanan', $selectedYear)
            ->groupBy(DB::raw('MONTH(tanggal_pemesanan)'))
            ->pluck('total', 'bulan')
            ->toArray();

        // Hitung total quarter langsung lewat PHP (Menghemat 1 Query DB)
        $penjualanQuarter = array_sum($penjualanBulananList);

        $targetBulananMetrics = [];
        foreach ($quarterMonths as $month) {
            $targetBulan = $targetBulanan->get($month);
            $totalPenjualanBulan = $penjualanBulananList[$month] ?? 0;

            $targetBulananMetrics[] = $this->makeMetric(
                'Bulan ' . $month . ' - ' . $monthNames[$month],
                (int) ($targetBulan->target_penjualan_bulan ?? 0),
                $totalPenjualanBulan,
                'Unit',
                'Jumlah penjualan ACC'
            );
        }

        // 3. BULK FETCH KPR BULANAN (2 Query Saja)
        $penjualanKprBulananList = PemesananUnit::query()
            ->selectRaw('MONTH(tanggal_pemesanan) as bulan, COUNT(*) as total')
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_pengajuan', 'acc')
            ->where('cara_bayar', 'kpr')
            ->whereIn(DB::raw('MONTH(tanggal_pemesanan)'), $quarterMonths)
            ->whereYear('tanggal_pemesanan', $selectedYear)
            ->groupBy(DB::raw('MONTH(tanggal_pemesanan)'))
            ->pluck('total', 'bulan')
            ->toArray();

        $realisasiKprBulananList = PemesananUnitKpr::query()
            ->selectRaw('MONTH(pemesanan_unit_kpr.tanggal_realisasi) as bulan, COUNT(*) as total')
            ->join('pemesanan_unit as p', 'pemesanan_unit_kpr.pemesanan_unit_id', '=', 'p.id')
            ->where('pemesanan_unit_kpr.status_kpr', 'realisasi')
            ->where('p.perumahaan_id', $currentPerumahaanId)
            ->where('p.status_pengajuan', 'acc')
            ->where('p.cara_bayar', 'kpr')
            ->whereIn(DB::raw('MONTH(pemesanan_unit_kpr.tanggal_realisasi)'), $quarterMonths)
            ->whereYear('pemesanan_unit_kpr.tanggal_realisasi', $selectedYear)
            ->groupBy(DB::raw('MONTH(pemesanan_unit_kpr.tanggal_realisasi)'))
            ->pluck('total', 'bulan')
            ->toArray();

        // Hitung quarter KPR langsung lewat PHP (Menghemat 2 Query DB)
        $penjualanKprQuarter = array_sum($penjualanKprBulananList);
        $realisasiKprQuarter = array_sum($realisasiKprBulananList);

        // KPR Tahunan (Tetap dibutuhkan query terpisah karena cakupan 1 tahun penuh)
        $penjualanKprTahun = $this->countAcceptedSales($currentPerumahaanId, $yearRange[0], $yearRange[1], 'kpr');
        $realisasiKprTahun = $this->countKprRealisasi($currentPerumahaanId, $yearRange[0], $yearRange[1]);

        $kprBulananMetrics = [];
        foreach ($quarterMonths as $month) {
            $totalPenjualanKprBulan = $penjualanKprBulananList[$month] ?? 0;
            $totalRealisasiKprBulan = $realisasiKprBulananList[$month] ?? 0;

            $kprBulananMetrics[] = $this->makeMetric(
                'Bulan ' . $month . ' - ' . $monthNames[$month],
                $totalPenjualanKprBulan,
                $totalRealisasiKprBulan,
                'Unit',
                'Realisasi KPR'
            );
        }

        return view('dashboard.marketing.dashboardMarketing', [
            'perumahaan' => $perumahaan,
            'selectedYear' => $selectedYear,
            'selectedQuarter' => $selectedQuarter,
            'currentYear' => $currentYear,
            'monthNames' => $monthNames,
            'quarterMonths' => $quarterMonths,
            'quarterMonthLabel' => $quarterMonthLabel,
            'metrics' => [
                'targetQuarter' => $this->makeMetric(
                    'Target Quarter',
                    (int) ($targetQuarter->target_penjualan_quarter ?? 0),
                    $penjualanQuarter,
                    'Unit',
                    'Jumlah penjualan ACC'
                ),
                'anggaranPromosi' => $this->makeMetric(
                    'Anggaran Promosi',
                    (float) ($anggaranPromosi->target_anggaran ?? 0),
                    (float) ($anggaranPromosi->realisasi_anggaran ?? 0),
                    'Rp',
                    'Realisasi anggaran'
                ),
            ],
            'targetBulananMetrics' => $targetBulananMetrics,
            'kprMetrics' => [
                'tahun' => $this->makeMetric('KPR Tahun', $penjualanKprTahun, $realisasiKprTahun, 'Unit', 'Realisasi KPR'),
                'quarter' => $this->makeMetric('KPR Quarter', $penjualanKprQuarter, $realisasiKprQuarter, 'Unit', 'Realisasi KPR'),
            ],
            'kprBulananMetrics' => $kprBulananMetrics,
            'breadcrumbs' => [
                [
                    'label' => 'Dashboard Marketing - ' . ($perumahaan->nama_perumahaan ?? '-'),
                    'url' => route('dashboard.marketing.index'),
                ],
            ],
            'years' => $this->generateYearList($currentYear),
            'quarters' => $this->generateQuarterList(),
        ]);
    }

    private function generateYearList(int $currentYear): array
    {
        $years = [];
        for ($i = $currentYear - 3; $i <= $currentYear + 3; $i++) {
            $years[] = $i;
        }
        return $years;
    }

    private function generateQuarterList(): array
    {
        return [
            ['value' => 1, 'label' => 'Q1'],
            ['value' => 2, 'label' => 'Q2'],
            ['value' => 3, 'label' => 'Q3'],
            ['value' => 4, 'label' => 'Q4'],
        ];
    }

    /**
     * Get the currently selected perumahaan ID for the authenticated user.
     */
    protected function currentPerumahaanId()
    {
        $user = Auth::user();

        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    /**
     * Make a dashboard metric array.
     */
    private function makeMetric(string $title, float|int $target, float|int $actual, string $unit, string $actualLabel): array
    {
        return [
            'title' => $title,
            'target' => $target,
            'actual' => $actual,
            'unit' => $unit,
            'actual_label' => $actualLabel,
            'percentage' => $target > 0 ? round(($actual / $target) * 100, 1) : 0,
        ];
    }

    private function countAcceptedSales(?int $perumahaanId, Carbon $start, Carbon $end, ?string $caraBayar = null): int
    {
        if (!$perumahaanId) {
            return 0;
        }

        return PemesananUnit::query()
            ->where('perumahaan_id', $perumahaanId)
            ->where('status_pengajuan', 'acc')
            ->when($caraBayar, fn($query) => $query->where('cara_bayar', $caraBayar))
            ->whereBetween('tanggal_pemesanan', [$start->toDateString(), $end->toDateString()])
            ->count();
    }

    private function countKprRealisasi(?int $perumahaanId, Carbon $start, Carbon $end): int
    {
        if (!$perumahaanId) {
            return 0;
        }

        return PemesananUnitKpr::query()
            ->where('status_kpr', 'realisasi')
            ->whereBetween('tanggal_realisasi', [$start->toDateString(), $end->toDateString()])
            ->whereHas('pemesananUnit', function ($query) use ($perumahaanId) {
                $query->where('perumahaan_id', $perumahaanId)
                    ->where('status_pengajuan', 'acc')
                    ->where('cara_bayar', 'kpr');
            })
            ->count();
    }

    private function quarterFromMonth(int $month): int
    {
        return (int) ceil($month / 3);
    }

    private function quarterRange(int $year, int $quarter): array
    {
        $startMonth = (($quarter - 1) * 3) + 1;
        $start = Carbon::create($year, $startMonth, 1)->startOfDay();

        return [$start, $start->copy()->addMonths(2)->endOfMonth()];
    }

    private function quarterMonths(int $quarter): array
    {
        $startMonth = (($quarter - 1) * 3) + 1;

        return [$startMonth, $startMonth + 1, $startMonth + 2];
    }

    private function monthRange(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();

        return [$start, $start->copy()->endOfMonth()];
    }

    private function monthNames(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }
}
