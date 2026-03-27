<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\MasterQcContainer;
use App\Models\PembangunanUnit;
use App\Models\PengajuanPembangunanUnit;
use App\Models\Perumahaan;
use App\Models\User;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengajuanPembangunanUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */

    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global ? session('current_perumahaan_id', null) : $user->perumahaan_id;
    }

    public function index()
    {
        $allPengajuan = PengajuanPembangunanUnit::with(['perumahaan', 'pembangunanUnit', 'pembangunanUnit.unit', 'pembangunanUnit.tahap', 'pembangunanUnit.qcContainer', 'diajukanOleh'])
            ->where('perumahaan_id', $this->currentPerumahaanId())
            ->latest()
            ->get();

        $allPengawas = User::select('id', 'nama_lengkap')->role('Pengawas Proyek')->orderBy('nama_lengkap', 'asc')->get();

        $allQcContainer = MasterQcContainer::all();

        return view('produksi.pengajuan-pembangunan.index', [
            'allPengajuan' => $allPengajuan,
            'allQcContainer' => $allQcContainer,
            'allPengawas' => $allPengawas,
            'breadcrumbs' => [['label' => 'Pengajuan Pembangunan', 'url' => route('produksi.pengajuanPembangunanUnit.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    public function sendGroupMessage($pembangunan)
    {
        // Eager load relasi jika belum ada
        $pembangunan->load(['unit.tahap.perumahaan']);

        $unit = $pembangunan->unit;
        $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';

        // Mapping group berdasarkan perumahan
        $groupMap = [
            'Asa Dreamland' => env('FONNTE_ID_GROUP_MARKETING_ADL'),
            'Lembah Hijau Residence' => env('FONNTE_ID_GROUP_MARKETING_LHR'),
        ];

        $groupId = $groupMap[$namaPerumahan] ?? null;

        $namaTahap = $unit->tahap->nama_tahap ?? '-';
        $namaUnit = $unit->nama_unit ?? '-';
        $pengaju = Auth::user()->nama_lengkap ?? Auth::user()->name;

        // Pesan dengan konteks Permintaan Pembangunan oleh Project Manager
        $messageGroup = "🏗️ *PENGAJUAN PEMBANGUNAN UNIT*\n\n" . "Dear *Manager Produksi*, terdapat pengajuan pembangunan unit baru dari *Project Manager* yang perlu ditindaklanjuti.\n\n" . "```\n" . "📍 Perumahan : {$namaPerumahan}\n" . "🏠 Tahap     : {$namaTahap}\n" . "🔑 Unit      : {$namaUnit}\n" . "👤 Diajukan  : {$pengaju}\n" . '📅 Tanggal   : ' . now()->format('d/m/Y H:i') . " WIB\n" . "```\n\n" . 'Mohon untuk segera dicek pada sistem untuk proses persetujuan. Terima kasih! 🙏';

        if ($groupId) {
            // Gunakan try-catch agar jika wa gagal kirim, database tidak ikut rollback (opsional)
            try {
                $this->notificationGroup->send($groupId, $messageGroup);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'unit_id' => 'required|exists:unit,id',
                'perumahaan_id' => 'required|exists:perumahaan,id',
                'tahap_id' => 'required|exists:tahap,id',
            ],
            [
                'unit_id.exists' => 'Unit yang dipilih tidak valid.',
            ],
        );

        try {
            DB::beginTransaction();

            $pembangunan = PembangunanUnit::create([
                'unit_id' => $validated['unit_id'],
                'perumahaan_id' => $validated['perumahaan_id'],
                'tahap_id' => $validated['tahap_id'],
                'qc_container_id' => null,
                'tanggal_mulai' => null,
                'tanggal_selesai' => null,
            ]);

            PengajuanPembangunanUnit::create([
                'perumahaan_id' => $validated['perumahaan_id'],
                'pembangunan_unit_id' => $pembangunan->id,
                'diajukan_oleh' => Auth::user()->id,
                'tanggal_diajukan' => now(),
            ]);

            DB::commit();

            $this->sendGroupMessage($pembangunan);

            return redirect()->route('produksi.pengajuanPembangunanUnit.index')->with('success', 'Data Pengajuan Pembangunan Unit berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pengajuanPembangunanUnit = PengajuanPembangunanUnit::findOrFail($id);
        $pembangunan = $pengajuanPembangunanUnit->pembangunanUnit;

        $allPerumahaan = Perumahaan::select('id', 'nama_perumahaan', 'slug')->get();
        $allPengawas = User::select('id', 'nama_lengkap')->role('Pengawas Proyek')->orderBy('nama_lengkap', 'asc')->get();
        $allQcContainer = MasterQcContainer::select('id', 'nama_container')->get();

        return view('Produksi.pengajuan-pembangunan.edit', [
            'pembangunan' => $pembangunan,
            'allPerumahaan' => $allPerumahaan,
            'allPengawas' => $allPengawas,
            'allQcContainer' => $allQcContainer,
            'breadcrumbs' => [['label' => 'Pengajuan Pembangunan Unit', 'url' => route('produksi.pengajuanPembangunanUnit.index')], ['label' => 'Edit Pengajuan', 'url' => '#']],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pembangunan = PembangunanUnit::findOrFail($id);

        $validated = $request->validate([
            'unit_id' => 'required|exists:unit,id',
            'perumahaan_id' => 'required|exists:perumahaan,id',
            'tahap_id' => 'required|exists:tahap,id',
            'pengawas_id' => 'required|exists:users,id',
            'qc_container_id' => 'required|exists:master_qc_container,id',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required|after_or_equal:tanggal_mulai',
        ]);

        $pembangunan->update($validated);

        return redirect()->route('produksi.pengajuanPembangunanUnit.index')->with('success', 'Data pengajuan pembangunan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pengajuanPembangunanUnit = PengajuanPembangunanUnit::findOrFail($id);

        if ($pengajuanPembangunanUnit->status_pengajuan == 'dibangun') {
            return back()->with('error', 'Gagal menghapus! Data ini sudah dalam tahap pembangunan.');
        }

        $pengajuanPembangunanUnit->pembangunanUnit->delete();

        return redirect()->route('produksi.pengajuanPembangunanUnit.index')->with('success', 'Pengajuan Pembangunan unit berhasil dihapus.');
    }
}
