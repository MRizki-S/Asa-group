<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\UpahHarianTukang;
use App\Services\NotificationGroupService;
use App\Services\PengajuanUpahHarianTukangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanUpahHarianTukangController extends Controller
{
    protected PengajuanUpahHarianTukangService $service;
    protected NotificationGroupService $notificationGroup;
    /**
     * Inisialisasi controller dengan menyuntikkan PengajuanUpahHarianTukangService.
     */
    public function __construct(PengajuanUpahHarianTukangService $service, NotificationGroupService $notificationGroup)
    {
        $this->service = $service;
        $this->notificationGroup = $notificationGroup;
    }

    /**
     * Menampilkan daftar pengajuan upah harian tukang untuk jenis ABM (perumahan).
     */
    public function index()
    {
        return $this->renderIndex('perumahan');
    }

    /**
     * Menampilkan daftar pengajuan upah harian tukang untuk jenis Mangoon.
     */
    public function indexMangoon()
    {
        return $this->renderIndex('mangoon');
    }

    /**
     * Mengambil data pengajuan dan merender view index berdasarkan jenis referensi.
     */
    private function renderIndex(string $jenisReferensi)
    {
        $pengajuans = UpahHarianTukang::with(['createdBy', 'details'])
            ->where('jenis_referensi', $jenisReferensi)
            ->latest()
            ->get();

        $isAbm = $jenisReferensi === 'perumahan';

        return view('gudang.upah-harian-tukang.daftar-pengajuanUpah.index', [
            'pengajuans' => $pengajuans,
            'jenisReferensi' => $jenisReferensi,
            'isAbm' => $isAbm,
            'pageTitle' => $isAbm
                ? 'Daftar Pengajuan Upah Harian Tukang ABM'
                : 'Daftar Pengajuan Upah Harian Tukang Mangoon',
            'pageActive' => $isAbm ? 'DaftarPengajuanUpahABM' : 'DaftarPengajuanUpahMangoon',
            'breadcrumbs' => [
                [
                    'label' => $isAbm ? 'Daftar Pengajuan Upah ABM' : 'Daftar Pengajuan Upah Mangoon',
                    'url' => $this->indexRouteForJenis($jenisReferensi),
                ],
            ],
        ]);
    }

    /**
     * Menampilkan form pembuatan pengajuan upah harian tukang untuk jenis ABM.
     */
    public function create()
    {
        return $this->renderCreate('perumahan');
    }

    /**
     * Menampilkan form pembuatan pengajuan upah harian tukang untuk jenis Mangoon.
     */
    public function createMangoon()
    {
        return $this->renderCreate('mangoon');
    }

    /**
     * Mengambil data master pendukung dan merender view form pembuatan pengajuan.
     */
    private function renderCreate(string $jenisReferensi)
    {
        $isAbm = $jenisReferensi === 'perumahan';

        return view('gudang.upah-harian-tukang.daftar-pengajuanUpah.create', [
            'masterTukang'        => $this->service->masterTukangForJenis($jenisReferensi),
            'pembangunanUnits'    => $this->service->pembangunanUnits(),
            'pembangunanKawasans' => $this->service->pembangunanKawasans(),
            'pembangunanProyeks'  => $this->service->pembangunanProyeks(),
            'nomorUpah'           => $this->service->generateNomorUpah(),
            'jenisReferensi'      => $jenisReferensi,
            'isAbm'               => $isAbm,
            'storeDraftRoute'     => $isAbm
                ? route('gudang.pengajuanUpahHarianTukang.storeDraft')
                : route('gudang.pengajuanUpahHarianTukang.storeDraftMangoon'),
            'pageActive' => $isAbm ? 'DaftarPengajuanUpahABM' : 'DaftarPengajuanUpahMangoon',
            'breadcrumbs' => [
                [
                    'label' => $isAbm ? 'Daftar Pengajuan Upah ABM' : 'Daftar Pengajuan Upah Mangoon',
                    'url' => $this->indexRouteForJenis($jenisReferensi),
                ],
                [
                    'label' => $isAbm ? 'Buat Pengajuan ABM' : 'Buat Pengajuan Mangoon',
                    'url' => $isAbm
                        ? route('gudang.pengajuanUpahHarianTukang.create')
                        : route('gudang.pengajuanUpahHarianTukang.createMangoon'),
                ],
            ],
        ]);
    }

    /**
     * Menyimpan data pengajuan upah harian tukang ABM baru dengan status draft.
     */
    public function storeDraft(Request $request)
    {
        $request->validate(['payload' => 'required|string']);
        $data = $this->service->validateAndDecodePayload($request->payload, 'perumahan');
        $this->service->storeNewPengajuan($data, 'perumahan');

        return redirect($this->indexRouteForJenis('perumahan'))
            ->with('success', 'Pengajuan upah harian tukang berhasil disimpan sebagai draft.');
    }

    /**
     * Menyimpan data pengajuan upah harian tukang Mangoon baru dengan status draft.
     */
    public function storeDraftMangoon(Request $request)
    {
        $request->validate(['payload' => 'required|string']);
        $data = $this->service->validateAndDecodePayload($request->payload, 'mangoon');
        $this->service->storeNewPengajuan($data, 'mangoon');

        return redirect($this->indexRouteForJenis('mangoon'))
            ->with('success', 'Pengajuan upah harian tukang berhasil disimpan sebagai draft.');
    }

    /**
     * Menampilkan form edit untuk melanjutkan draft pengajuan upah harian tukang ABM.
     */
    public function edit($id)
    {
        return $this->renderEdit((int) $id, 'perumahan');
    }

    /**
     * Menampilkan form edit untuk melanjutkan draft pengajuan upah harian tukang Mangoon.
     */
    public function editMangoon($id)
    {
        return $this->renderEdit((int) $id, 'mangoon');
    }

    /**
     * Memvalidasi status draft dan merender view form edit pengajuan upah.
     */
    private function renderEdit(int $id, string $jenisReferensi)
    {
        $pengajuan = UpahHarianTukang::with(['details.alokasi', 'details.tukang'])->findOrFail($id);
        abort_if($pengajuan->jenis_referensi !== $jenisReferensi, 404);
        abort_if($pengajuan->status !== 'draft', 403, 'Hanya pengajuan berstatus draft yang dapat diedit.');

        $isAbm = $jenisReferensi === 'perumahan';

        return view('gudang.upah-harian-tukang.daftar-pengajuanUpah.editDraft', [
            'masterTukang' => $this->service->masterTukangForJenis($jenisReferensi),
            'pembangunanUnits' => $this->service->pembangunanUnits(),
            'pembangunanKawasans' => $this->service->pembangunanKawasans(),
            'pembangunanProyeks' => $this->service->pembangunanProyeks(),
            'nomorUpah' => $pengajuan->nomor_upah_harian,
            'jenisReferensi' => $jenisReferensi,
            'isAbm' => $isAbm,
            'isEdit' => true,
            'pengajuan' => $pengajuan,
            'existingTukangDetails' => $this->service->buildExistingData($pengajuan),
            'updateDraftRoute' => $isAbm
                ? route('gudang.pengajuanUpahHarianTukang.updateDraft', $pengajuan->id)
                : route('gudang.pengajuanUpahHarianTukang.updateDraftMangoon', $pengajuan->id),
            'submitDraftRoute' => $isAbm
                ? route('gudang.pengajuanUpahHarianTukang.submitDraft', $pengajuan->id)
                : route('gudang.pengajuanUpahHarianTukang.submitDraftMangoon', $pengajuan->id),
            'pageActive' => $isAbm ? 'DaftarPengajuanUpahABM' : 'DaftarPengajuanUpahMangoon',
            'breadcrumbs' => [
                [
                    'label' => $isAbm ? 'Daftar Pengajuan Upah ABM' : 'Daftar Pengajuan Upah Mangoon',
                    'url' => $this->indexRouteForJenis($jenisReferensi),
                ],
                [
                    'label' => 'Lanjutkan Draft: ' . $pengajuan->nomor_upah_harian,
                    'url' => '#',
                ],
            ],
        ]);
    }

    /**
     * Memperbarui data draft pengajuan upah harian tukang ABM (status tetap draft).
     */
    public function updateDraft(Request $request, int $id)
    {
        $request->validate(['payload' => 'required|string']);
        $data = $this->service->validateAndDecodePayload($request->payload, 'perumahan');
        $pengajuan = UpahHarianTukang::findOrFail($id);
        $this->service->updateExistingDraft($pengajuan, $data, 'perumahan');

        return redirect($this->indexRouteForJenis('perumahan'))
            ->with('success', 'Pengajuan upah harian tukang berhasil diperbarui sebagai draft.');
    }

    /**
     * Memperbarui data draft pengajuan upah harian tukang Mangoon (status tetap draft).
     */
    public function updateDraftMangoon(Request $request, int $id)
    {
        $request->validate(['payload' => 'required|string']);
        $data = $this->service->validateAndDecodePayload($request->payload, 'mangoon');
        $pengajuan = UpahHarianTukang::findOrFail($id);
        $this->service->updateExistingDraft($pengajuan, $data, 'mangoon');

        return redirect($this->indexRouteForJenis('mangoon'))
            ->with('success', 'Pengajuan upah harian tukang berhasil diperbarui sebagai draft.');
    }

    /**
     * Memperbarui data draft pengajuan upah harian tukang ABM dan mengubah statusnya menjadi diajukan.
     */
    public function submit(Request $request, int $id)
    {
        $request->validate(['payload' => 'required|string']);
        $data = $this->service->validateAndDecodePayload($request->payload, 'perumahan');
        $pengajuan = UpahHarianTukang::findOrFail($id);
        $this->service->submitPengajuan($pengajuan, $data, 'perumahan');

        $this->sendNotification($data, 'perumahan');

        return redirect($this->indexRouteForJenis('perumahan'))
            ->with('success', 'Pengajuan upah harian tukang berhasil diajukan.');
    }

    /**
     * Memperbarui data draft pengajuan upah harian tukang Mangoon dan mengubah statusnya menjadi diajukan.
     */
    public function submitMangoon(Request $request, int $id)
    {
        $request->validate(['payload' => 'required|string']);
        $data = $this->service->validateAndDecodePayload($request->payload, 'mangoon');
        $pengajuan = UpahHarianTukang::findOrFail($id);
        $this->service->submitPengajuan($pengajuan, $data, 'mangoon');

        $this->sendNotification($data, 'mangoon');

        return redirect($this->indexRouteForJenis('mangoon'))
            ->with('success', 'Pengajuan upah harian tukang berhasil diajukan.');
    }

    /**
     * Menghapus draft pengajuan upah harian tukang ABM.
     */
    public function destroy($id)
    {
        $pengajuan = UpahHarianTukang::findOrFail($id);
        if ($pengajuan->status !== 'draft') {
            abort(403, 'Hanya pengajuan berstatus draft yang dapat dihapus.');
        }

        $pengajuan->delete();

        return redirect($this->indexRouteForJenis('perumahan'))
            ->with('success', 'Draft pengajuan upah harian tukang berhasil dihapus.');
    }

    /**
     * Menghapus draft pengajuan upah harian tukang Mangoon.
     */
    public function destroyMangoon($id)
    {
        $pengajuan = UpahHarianTukang::findOrFail($id);
        if ($pengajuan->status !== 'draft') {
            abort(403, 'Hanya pengajuan berstatus draft yang dapat dihapus.');
        }

        $pengajuan->delete();

        return redirect($this->indexRouteForJenis('mangoon'))
            ->with('success', 'Draft pengajuan upah harian tukang berhasil dihapus.');
    }

    /**
     * Menampilkan halaman detail read-only pengajuan upah harian tukang ABM.
     */
    public function detail($id)
    {
        return $this->renderDetail((int) $id, 'perumahan');
    }

    /**
     * Menampilkan halaman detail read-only pengajuan upah harian tukang Mangoon.
     */
    public function detailMangoon($id)
    {
        return $this->renderDetail((int) $id, 'mangoon');
    }

    /**
     * Menyiapkan data dan merender view detail pengajuan upah harian tukang.
     */
    private function renderDetail(int $id, string $jenisReferensi)
    {
        $pengajuan = UpahHarianTukang::with([
            'details.tukang',
            'details.alokasi',
            'createdBy',
        ])->findOrFail($id);

        abort_if($pengajuan->jenis_referensi !== $jenisReferensi, 404);

        $isAbm = $jenisReferensi === 'perumahan';

        // Kelompokkan detail per tukang
        $detailPerTukang = $pengajuan->details
            ->sortBy('tanggal')
            ->groupBy('tukang_id')
            ->map(function ($details) {
                return [
                    'tukang'  => $details->first()->tukang,
                    'details' => $details->values(),
                ];
            })->values();

        // Build lookup label referensi
        $unitLabels    = $this->service->pembangunanUnits()->keyBy('id');
        $kawasanLabels = $this->service->pembangunanKawasans()->keyBy('id');
        $proyekLabels  = $this->service->pembangunanProyeks()->keyBy('id');

        return view('gudang.upah-harian-tukang.daftar-pengajuanUpah.detail', [
            'pengajuan'       => $pengajuan,
            'detailPerTukang' => $detailPerTukang,
            'unitLabels'      => $unitLabels,
            'kawasanLabels'   => $kawasanLabels,
            'proyekLabels'    => $proyekLabels,
            'isAbm'           => $isAbm,
            'pageActive'      => $isAbm ? 'DaftarPengajuanUpahABM' : 'DaftarPengajuanUpahMangoon',
            'breadcrumbs'     => [
                [
                    'label' => $isAbm ? 'Daftar Pengajuan Upah ABM' : 'Daftar Pengajuan Upah Mangoon',
                    'url'   => $this->indexRouteForJenis($jenisReferensi),
                ],
                [
                    'label' => 'Detail: ' . $pengajuan->nomor_upah_harian,
                    'url'   => '#',
                ],
            ],
        ]);
    }

    /**
     * Mendapatkan URL route index berdasarkan jenis referensi (ABM / Mangoon).
     */
    private function indexRouteForJenis(string $jenisReferensi): string
    {
        return $jenisReferensi === 'mangoon'
            ? route('gudang.pengajuanUpahHarianTukang.indexMangoon')
            : route('gudang.pengajuanUpahHarianTukang.index');
    }

    /**
     * Mengirimkan notifikasi WhatsApp ke grup dukungan layanan saat pengajuan diajukan.
     */
    private function sendNotification(array $data, string $jenisReferensi): void
    {
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');
        if (!$groupId) {
            return;
        }

        $label = $jenisReferensi === 'perumahan' ? 'ABM' : 'Mangoon';
        $nomor = $data['nomor_upah_harian'] ?? '';
        $mulai = isset($data['tanggal_mulai']) ? \Carbon\Carbon::parse($data['tanggal_mulai'])->translatedFormat('d F Y') : '';
        $selesai = isset($data['tanggal_selesai']) ? \Carbon\Carbon::parse($data['tanggal_selesai'])->translatedFormat('d F Y') : '';

        $message = "📢 *Pengajuan Upah Harian Tukang*\n\n" .
            "Terdapat pengajuan upah harian tukang {$label} yang telah diajukan dan menunggu persetujuan.\n\n" .
            "• *Nomor Pengajuan:* {$nomor}\n" .
            "• *Periode:* {$mulai} s/d {$selesai}\n\n" .
            "Mohon untuk melakukan pemeriksaan dan persetujuan melalui sistem.\n\n" .
            "Terima kasih.";

        try {
            $this->notificationGroup->send($groupId, $message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim notifikasi WhatsApp Pengajuan Upah: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    /**
     * Membatalkan pengajuan upah harian tukang ABM (mengembalikan ke draft).
     */
    public function cancel($id)
    {
        return $this->processCancel((int) $id, 'perumahan');
    }

    /**
     * Membatalkan pengajuan upah harian tukang Mangoon (mengembalikan ke draft).
     */
    public function cancelMangoon($id)
    {
        return $this->processCancel((int) $id, 'mangoon');
    }

    /**
     * Memproses pembatalan pengajuan upah harian tukang.
     */
    private function processCancel(int $id, string $jenisReferensi)
    {
        $pengajuan = UpahHarianTukang::findOrFail($id);
        abort_if($pengajuan->jenis_referensi !== $jenisReferensi, 404);

        if ($pengajuan->status !== 'diajukan') {
            return back()->with('error', 'Hanya pengajuan dengan status diajukan yang dapat dibatalkan.');
        }

        $pengajuan->update([
            'status' => 'draft',
        ]);

        $this->sendCancelNotification($pengajuan);

        $redirectRoute = $jenisReferensi === 'mangoon'
            ? route('gudang.pengajuanUpahHarianTukang.indexMangoon')
            : route('gudang.pengajuanUpahHarianTukang.index');

        return redirect($redirectRoute)->with('success', 'Pengajuan upah harian tukang berhasil dibatalkan dan dikembalikan ke draft.');
    }

    /**
     * Mengirimkan notifikasi WhatsApp pembatalan pengajuan upah.
     */
    private function sendCancelNotification(UpahHarianTukang $pengajuan): void
    {
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');
        $nomor   = $pengajuan->nomor_upah_harian;
        $user    = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Staf Gudang';

        $message = "❌ *Pembatalan Pengajuan Upah Harian Tukang*\n\n" .
            "Pengajuan Upah Harian Tukang dengan nomor *{$nomor}* telah *DIBATALKAN* oleh *{$user}* dan dikembalikan ke status Draft.";
        
        try {
            $this->notificationGroup->send($groupId, $message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim notifikasi WhatsApp Pembatalan: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }
}
