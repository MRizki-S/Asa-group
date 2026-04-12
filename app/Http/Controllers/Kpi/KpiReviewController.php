<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
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
            ->with(['user', 'details.tasks'])
            ->latest()
            ->get();

        return view('kpi.review.index', [
            'reviews' => $reviews,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI', 'url' => route('kpi.user.index')],
                ['label' => 'Review Materialitas (Skor 0)', 'url' => '#']
            ],
        ]);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'skor_custom' => 'required|array',
            'skor_custom.*' => 'in:0,70',
        ]);

        DB::transaction(function () use ($request, $id) {
            $kpiUser = KpiUser::findOrFail($id);

            foreach ($request->skor_custom as $detailId => $skor) {
                $detail = KpiUserKomponen::findOrFail($detailId);
                $detail->update([
                    'skor' => $skor,
                    'nilai_akhir' => ($detail->bobot / 100) * $skor,
                    'catatan_tambahan' => $detail->catatan_tambahan . " (Materialitas: $skor)"
                ]);
            }

            $kpiUser->update(['total_nilai' => $kpiUser->details()->sum('nilai_akhir')]);
        });

        return back()->with('success', 'Skor materialitas berhasil diperbarui.');
    }

    public function sendNotif($id)
    {
        $kpiUser = KpiUser::with(['user', 'details'])->findOrFail($id);

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
