<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\MasterQcContainer;
use App\Models\PembangunanUnit;
use App\Models\PengajuanPembangunanUnit;
use App\Models\Perumahaan;
use App\Models\Unit;
use App\Models\User;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermintaanDibangunController extends Controller
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
        $user = Auth::user();
        $query = PengajuanPembangunanUnit::with(['perumahaan', 'pembangunanUnit', 'pembangunanUnit.unit', 'pembangunanUnit.tahap', 'pembangunanUnit.qcContainer', 'pembangunanUnit.spv', 'pembangunanUnit.pengawas', 'diajukanOleh'])
            ->where('perumahaan_id', $this->currentPerumahaanId());

        if ($user->hasRole('SPV Drafting, Teknis & Estimasi')) {
            $query->whereHas('pembangunanUnit', function ($q) use ($user) {
                $q->where('spv_id', $user->id);
            });
        }

        $allPengajuan = $query->latest()->get();

        $allPengawas = User::select('id', 'nama_lengkap')->role('Pengawas Unit')->orderBy('nama_lengkap', 'asc')->get();
        $allSpv = User::select('id', 'nama_lengkap')->role('SPV Drafting, Teknis & Estimasi')->orderBy('nama_lengkap', 'asc')->get();

        $allQcContainer = MasterQcContainer::all();

        return view('produksi.permintaan-dibangun.index', [
            'allPengajuan' => $allPengajuan,
            'allQcContainer' => $allQcContainer,
            'allPengawas' => $allPengawas,
            'allSpv' => $allSpv,
            'breadcrumbs' => [['label' => 'Permintaan Dibangun', 'url' => route('produksi.pengajuanPembangunanUnit.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pengajuan = PengajuanPembangunanUnit::findOrFail($id);
        $pembangunan = $pengajuan->pembangunanUnit;

        // Check if status is "dibangun" (proses)
        if ($pengajuan->status_pengajuan !== 'dibangun') {
            return redirect()->route('produksi.pengajuanPembangunanUnit.index')->with('error', 'Hanya pengajuan dengan status dibangun yang dapat diedit.');
        }

        $user = Auth::user();
        // Check if user is SPV Drafting, Teknis & Estimasi and if this unit is assigned to them
        if ($user->hasRole('SPV Drafting, Teknis & Estimasi') && $pembangunan->spv_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit pembangunan ini.');
        }

        $allPerumahaan = Perumahaan::all();
        $allPengawas = User::select('id', 'nama_lengkap')->role('Pengawas Unit')->orderBy('nama_lengkap', 'asc')->get();
        $allSpv = User::select('id', 'nama_lengkap')->role('SPV Drafting, Teknis & Estimasi')->orderBy('nama_lengkap', 'asc')->get();
        $allQcContainer = MasterQcContainer::all();

        return view('produksi.permintaan-dibangun.edit', [
            'pembangunan' => $pembangunan,
            'pengajuan' => $pengajuan,
            'allPerumahaan' => $allPerumahaan,
            'allPengawas' => $allPengawas,
            'allSpv' => $allSpv,
            'allQcContainer' => $allQcContainer,
            'breadcrumbs' => [
                ['label' => 'Permintaan Dibangun', 'url' => route('produksi.pengajuanPembangunanUnit.index')],
                ['label' => 'Edit ' . ($pembangunan->unit->nama_unit ?? 'Unit'), 'url' => '#'],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pengajuan = PengajuanPembangunanUnit::findOrFail($id);
        $pembangunan = $pengajuan->pembangunanUnit;

        // Check if status is "dibangun" (proses)
        if ($pengajuan->status_pengajuan !== 'dibangun') {
            return redirect()->route('produksi.pengajuanPembangunanUnit.index')->with('error', 'Hanya pengajuan dengan status dibangun yang dapat diupdate.');
        }

        $user = Auth::user();
        // Check if user is SPV Drafting, Teknis & Estimasi and if this unit is assigned to them
        if ($user->hasRole('SPV Drafting, Teknis & Estimasi') && $pembangunan->spv_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate pembangunan ini.');
        }

        $validated = $request->validate([
            'pengawas_id' => 'required|integer|exists:users,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $pembangunan->update([
            'pengawas_id' => $validated['pengawas_id'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
        ]);

        return redirect()->route('produksi.pengajuanPembangunanUnit.index')->with('success', 'Pembangunan Unit berhasil diperbarui.');
    }

    public function sendGroupMessage($pembangunan)
    {
        $pembangunan->load(['unit.tahap.perumahaan']);

        $unit = $pembangunan->unit;
        $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';

        $groupId = env('FONNTE_ID_GROUP_PERMINTAAN_DIBANGUN');

        $namaTahap = $unit->tahap->nama_tahap ?? '-';
        $namaUnit = $unit->nama_unit ?? '-';
        $pengaju = Auth::user()->nama_lengkap ?? Auth::user()->name;

        $messageGroup = view('notifications.whatsapp.permintaan_dibangun', [
            'namaPerumahan' => $namaPerumahan,
            'namaTahap' => $namaTahap,
            'namaUnit' => $namaUnit,
            'peminta' => $pengaju,
            'tanggal' => now()->format('d/m/Y H:i') . ' WIB'
        ])->render();

        if ($groupId) {
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

            $unit = Unit::find($validated['unit_id']);
            $unit->update([
                'status_pembangunan' => 'diajukan'
            ]);

            DB::commit();

            $this->sendGroupMessage($pembangunan);

            return redirect()->back()->with('success', 'Data Pengajuan Pembangunan Unit berhasil ditambahkan!');
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pengajuanPembangunanUnit = PengajuanPembangunanUnit::findOrFail($id);

        if ($pengajuanPembangunanUnit->status_pengajuan == 'dibangun') {
            return back()->with('error', 'Gagal membatalkan! Data ini sudah dalam tahap pembangunan.');
        }

        try {
            DB::beginTransaction();

            $pembangunan = $pengajuanPembangunanUnit->pembangunanUnit;
            if ($pembangunan) {
                $this->sendCancelNotification($pembangunan);

                $unit = $pembangunan->unit;
                if ($unit) {
                    $unit->update([
                        'status_pembangunan' => 'belum dibangun'
                    ]);
                }
                $pembangunan->delete();
            }

            DB::commit();

            return redirect()->route('produksi.pengajuanPembangunanUnit.index')->with('success', 'Pengajuan Pembangunan unit berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan pengajuan: ' . $e->getMessage());
        }
    }

    public function sendCancelNotification($pembangunan)
    {
        if (!$pembangunan) return;

        $pembangunan->load(['unit.tahap.perumahaan']);

        $unit = $pembangunan->unit;
        if (!$unit) return;

        $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';
        $namaTahap = $unit->tahap->nama_tahap ?? '-';
        $namaUnit = $unit->nama_unit ?? '-';
        $pembatal = Auth::user()->nama_lengkap ?? Auth::user()->name;

        $groupId = env('FONNTE_ID_GROUP_BATAL_PERMINTAAN_DIBANGUN');

        $messageGroup = view('notifications.whatsapp.batal_permintaan_dibangun', [
            'namaPerumahan' => $namaPerumahan,
            'namaTahap' => $namaTahap,
            'namaUnit' => $namaUnit,
            'pembatal' => $pembatal,
            'tanggal' => now()->format('d/m/Y H:i') . ' WIB'
        ])->render();

        if ($groupId) {
            try {
                $this->notificationGroup->send($groupId, $messageGroup);
            } catch (\Exception $e) {
                Log::error('Fonnte Cancel Notification Error: ' . $e->getMessage());
            }
        }
    }

    public function getUnitsByTahap(string $tahapId)
    {
        try {
            $currentUnitId = request()->query('current_unit_id');

            $units = Unit::where('tahap_id', $tahapId)
                ->where(function ($query) use ($currentUnitId) {
                    $query->whereIn('status_pembangunan', ['belum dibangun', 'selesai dibangun']);
                    if ($currentUnitId) {
                        $query->orWhere('id', $currentUnitId);
                    }
                })
                ->select('id', 'nama_unit')
                ->get();

            return response()->json($units);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
