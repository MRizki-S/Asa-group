<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiIndicator;
use App\Models\KpiReviewRequest;
use App\Models\KpiUser;
use App\Models\KpiUserKomponen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = KpiUser::where('status', '!=', 'final')
            ->whereHas('reviewRequests', function ($query) {
                $query->whereNull('direspon_pada');
            })
            ->with(['karyawan', 'details.tasks', 'reviewRequests' => function ($q) {
                $q->whereNull('direspon_pada')->latest();
            }])
            ->latest()
            ->get();

        return view('kpi.review.index', [
            'reviews' => $reviews,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI', 'url' => route('kpi.user.index')],
                ['label' => 'Review Penilaian KPI', 'url' => '#']
            ],
        ]);
    }

    public function edit($id)
    {

        $kpiUser = KpiUser::with(['karyawan', 'details.tasks', 'reviewRequests'])->findOrFail($id);
        $indicators = KpiIndicator::all();
        $modeMapping = $indicators->pluck('tipe_indikator', 'tipe_perhitungan')->toArray();

        $bolehRequest = $kpiUser->reviewRequests->where('direspon_pada', null)->count() > 0 ? false : true;

        return view('kpi.review.edit', [
            'kpiUser' => $kpiUser,
            'indicators' => $indicators,
            'modeMapping' => $modeMapping,
            'bolehRequest' =>  $bolehRequest,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI', 'url' => route('kpi.user.index')],
                ['label' => 'Review Nilai: ' . $kpiUser->karyawan->nama, 'url' => '#']
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'skor_custom'   => 'required|array',
            'skor_custom.*' => 'in:0,70,100',
            'status'        => 'required|in:draft,final',
        ]);

        DB::transaction(function () use ($request, $id) {
            $kpiUser = KpiUser::findOrFail($id);

            KpiReviewRequest::where('kpi_user_id', $id)
                ->whereNull('direspon_pada')
                ->update(['direspon_pada' => now()]);

            foreach ($request->skor_custom as $detailId => $skor) {
                $detail = KpiUserKomponen::findOrFail($detailId);

                $detail->update([
                    'skor' => $skor,
                    'nilai_akhir' => ($detail->bobot / 100) * $skor,
                    'nilai_tetap' => true,
                    'is_review_khusus' => false,
                ]);
            }

            $kpiUser->update([
                'status'      => $request->status,
            ]);
        });

        return redirect()->route('kpi.review.index')->with('success', 'Review materialitas dan status KPI berhasil diperbarui.');
    }
}
