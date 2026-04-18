<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiIndicator;
use App\Models\KpiReviewRequest;
use App\Models\KpiUser;
use App\Models\KpiUserKomponen;
use App\Models\User;
use App\Services\NotificationPribadiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiReviewController extends Controller
{

    protected NotificationPribadiService $notificationPribadi;

    public function __construct(NotificationPribadiService $notificationPribadi)
    {
        $this->notificationPribadi = $notificationPribadi;
    }

    public function index(Request $request)
    {
        $reviews = KpiUser::whereHas('details', function ($query) {
            $query->where('skor', 0);
        })
            ->whereHas('reviewRequests', function ($query) {
                $query->whereIn('id', function ($sub) {
                    $sub->selectRaw('max(id)')
                        ->from('kpi_review_requests')
                        ->groupBy('kpi_user_id');
                })->whereNull('direspon_pada');
            })
            ->with(['user', 'details.tasks', 'reviewRequests' => function ($q) {
                $q->latest();
            }])
            ->latest()
            ->get();

        return view('kpi.review.index', [
            'reviews' => $reviews,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI', 'url' => route('kpi.user.index')],
                ['label' => 'Review Materialitas', 'url' => '#']
            ],
        ]);
    }

    public function edit($id)
    {

        $kpiUser = KpiUser::with(['user', 'details.tasks', 'reviewRequests'])->findOrFail($id);
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
                ['label' => 'Review Nilai: ' . $kpiUser->user->nama_lengkap, 'url' => '#']
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'skor_custom'   => 'required|array',
            'skor_custom.*' => 'in:0,70',
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
                ]);
            }

            $kpiUser->update([
                'status'      => $request->status,
            ]);
        });

        return redirect()->route('kpi.review.index')->with('success', 'Review materialitas dan status KPI berhasil diperbarui.');
    }

    public function sendNotif($id)
    {
        $kpiUser = KpiUser::with(['user', 'details'])->findOrFail($id);

        $buatRequest = KpiReviewRequest::create([
            'kpi_user_id' => $id
        ]);

        // $manager = User::role('Manager Dukungan & Layanan')->first();

        // if (!$manager || !$manager->no_hp) {
        //     return;
        // }

        $namaKaryawan = $kpiUser->user->nama_lengkap;
        $periode = date('F Y', mktime(0, 0, 0, $kpiUser->bulan, 1, $kpiUser->tahun));

        $komponenNol = $kpiUser->details->where('skor', 0)->pluck('nama_komponen')->implode(', ');

        $message = "⚠️ *REQUEST REVIEW MATERIALITAS KPI*\n\n" .
            "Halo Bapak/Ibu Manajer, terdapat penilaian KPI karyawan yang membutuhkan review materialitas (Skor 0).\n\n" .
            "```\n" .
            "👤 Karyawan  : {$namaKaryawan}\n" .
            "📅 Periode   : {$periode}\n" .
            "📊 Komponen  : {$komponenNol}\n" .
            "```\n\n" .
            "Mohon segera melakukan pengecekan dan penyesuaian skor melalui dashboard sistem KPI. Terima Kasih. 🙏✨";

        try {
            // $this->notificationPribadi->sendWhatsApp($manager->no_hp, $message);
            $this->notificationPribadi->sendWhatsApp("083143952277", $message);
            return back()->with('success', 'Permintaan dikirim');
        } catch (\Exception $e) {
        }
    }
}
