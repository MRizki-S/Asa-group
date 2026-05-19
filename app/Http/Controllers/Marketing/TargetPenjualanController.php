<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Perumahaan;
use App\Models\MarketingTargetQuarter;
use App\Models\MarketingTargetBulan;
use App\Http\Requests\Marketing\StoreTargetPenjualanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TargetPenjualanController extends Controller
{

    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    public function index()
    {
        $user = Auth::user();
        $perumahaanId = $this->currentPerumahaanId();
        $perumahaan = Perumahaan::find($perumahaanId);

        $targets = MarketingTargetQuarter::with(['bulanan', 'updater'])
            ->where('perumahaan_id', $perumahaanId)
            ->orderBy('tahun', 'desc')
            ->orderBy('quarter', 'desc')
            ->get();

        return view('marketing.target-marketing.target-penjualan.index', [
            'perumahaan' => $perumahaan,
            'targets' => $targets,
            'breadcrumbs' => [
                [
                    'label' => 'Target Penjualan - ' . ($perumahaan->nama_perumahaan ?? '-'),
                    'url' => route('marketing.target-penjualan.index'),
                ],
            ],
        ]);
    }

    public function store(StoreTargetPenjualanRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $quarterName = 'Q' . $validated['quarter'];

            $targetQuarter = MarketingTargetQuarter::updateOrCreate(
                [
                    'perumahaan_id' => $validated['perumahaan_id'],
                    'tahun' => $validated['tahun'],
                    'quarter' => $quarterName,
                ],
                [
                    'target_penjualan_quarter' => $validated['target_penjualan_quarter'],
                    'updated_by' => Auth::id(),
                ]
            );

            if ($targetQuarter->wasRecentlyCreated) {
                $targetQuarter->update(['created_by' => Auth::id()]);
            }

            foreach ($validated['monthly_targets'] as $monthlyData) {
                MarketingTargetBulan::updateOrCreate(
                    [
                        'marketing_target_quarter_id' => $targetQuarter->id,
                        'bulan' => $monthlyData['bulan'],
                    ],
                    [
                        'target_penjualan_bulan' => $monthlyData['target'],
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Target penjualan berhasil disimpan.',
                'data' => $targetQuarter->load(['bulanan', 'updater'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan target: ' . $e->getMessage()
            ], 500);
        }
    }
}
