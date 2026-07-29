<?php

namespace App\Http\Controllers\Produksi\PembangunanUnit;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\PembangunanUnit;
use App\Models\PembangunanUnitQcTask;
use App\Models\Perumahaan;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Symfony\Component\Clock\now;

class PembangunanUnitController extends Controller
{
    /**
     * Display a listing of the resource.
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

    public function index(Request $request)
    {
        $perumahaanId = $this->currentPerumahaanId();

        $user = Auth::user();
        if ($user->hasRole('Pengawas Unit')) {
            $query = PembangunanUnit::with(['perumahaan:id,nama_perumahaan,slug', 'tahap:id,perumahaan_id,nama_tahap,slug', 'unit:id,blok_id,nama_unit', 'pengawas:id,nama_lengkap', 'spv:id,nama_lengkap', 'qcContainer', 'pengajuan'])
                ->where('perumahaan_id', $perumahaanId)
                ->where('pengawas_id', $user->id)
                ->whereIn('status_pembangunan', ['proses', 'selesai', 'selesai dengan catatan'])
                ->latest('created_at');
        } elseif ($user->hasRole('SPV Drafting, Teknis & Estimasi')) {
            $query = PembangunanUnit::with(['perumahaan:id,nama_perumahaan,slug', 'tahap:id,perumahaan_id,nama_tahap,slug', 'unit:id,blok_id,nama_unit', 'pengawas:id,nama_lengkap', 'spv:id,nama_lengkap', 'qcContainer', 'pengajuan'])
                ->where('perumahaan_id', $perumahaanId)
                ->where('spv_id', $user->id)
                ->whereIn('status_pembangunan', ['proses', 'selesai', 'selesai dengan catatan'])
                ->latest('created_at');
        } else {
            $query = PembangunanUnit::with(['perumahaan:id,nama_perumahaan,slug', 'tahap:id,perumahaan_id,nama_tahap,slug', 'unit:id,blok_id,nama_unit', 'pengawas:id,nama_lengkap', 'spv:id,nama_lengkap', 'qcContainer', 'pengajuan'])
                ->where('perumahaan_id', $perumahaanId)
                ->whereIn('status_pembangunan', ['proses', 'selesai', 'selesai dengan catatan'])
                ->latest('created_at');
        }

        if ($request->filled('tahapFil')) {
            $slugTahap = $request->input('tahapFil');
            $query->whereHas('tahap', function ($q) use ($slugTahap) {
                $q->where('slug', $slugTahap);
            });
        }

        $allPembangunanUnit = $query->get()->map(function ($unit) {
            $totalQc = $unit->pembangunanUnitQc->count();
            $sumProgressQc = 0;

            foreach ($unit->pembangunanUnitQc as $qc) {
                $qcProgress = $qc->total_task > 0 ? ($qc->task_selesai_count / $qc->total_task) * 100 : 0;

                $qc->persentase_qc = round($qcProgress, 2);
                $sumProgressQc += $qcProgress;
            }

            $unit->total_progres = $totalQc > 0 ? round($sumProgressQc / $totalQc, 2) : 0;

            return $unit;
        });

        $perumahaan = Perumahaan::select('id', 'slug')->where('id', $perumahaanId)->first();

        $tahapSlug = $request->query('tahapFil');
        return view('produksi.pembangunan-unit.index', [
            'allPembangunanUnit' => $allPembangunanUnit,
            'perumahaanSlug' => $perumahaan->slug,
            'tahapSlug' => $tahapSlug,
            'breadcrumbs' => [['label' => 'Pembangunan Unit', 'url' => route('produksi.pembangunanUnit.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = PembangunanUnit::with(['unit', 'tahap', 'perumahaan', 'pengawas', 'spv', 'pembangunanUnitQc.pembangunanUnitQcTask', 'pembangunanUnitQc.pembangunanUnitRapBahan', 'pembangunanUnitQc.pembangunanUnitRapUpah', 'pembangunanUnitQc.pembangunanUnitRapBahan.barang'])->findOrFail($id);

        // Get already ordered base quantities per RAP item
        $orderedQuantities = \App\Models\PembangunanUnitBarangOrderDetail::query()
            ->join('pembangunan_unit_barang_order as o', 'o.id', '=', 'pembangunan_unit_barang_order_detail.order_id')
            ->where('o.pembangunan_unit_id', $id)
            ->select('pembangunan_unit_barang_order_detail.rap_bahan_id', \Illuminate\Support\Facades\DB::raw('SUM(pembangunan_unit_barang_order_detail.jumlah_base) as total_ordered_base'))
            ->groupBy('pembangunan_unit_barang_order_detail.rap_bahan_id')
            ->pluck('total_ordered_base', 'rap_bahan_id')
            ->toArray();

        // Get already requested wages per RAP upah item
        $orderedUpah = \App\Models\PembangunanUnitUpahPengajuan::query()
            ->where('pembangunan_unit_id', $id)
            ->whereNull('ditolak_pada')
            ->select('pembangunan_unit_rap_upah_id', \Illuminate\Support\Facades\DB::raw('SUM(nominal_diajukan) as total_ordered_upah'))
            ->groupBy('pembangunan_unit_rap_upah_id')
            ->pluck('total_ordered_upah', 'pembangunan_unit_rap_upah_id')
            ->toArray();

        foreach ($data->pembangunanUnitQc as $qc) {
            foreach ($qc->pembangunanUnitRapBahan as $rapBahan) {
                $rapBahan->total_ordered_base = (float) ($orderedQuantities[$rapBahan->id] ?? 0);
            }
            foreach ($qc->pembangunanUnitRapUpah as $rapUpah) {
                $rapUpah->total_ordered_upah = (float) ($orderedUpah[$rapUpah->id] ?? 0);
            }
        }
        $allBarang = MasterBarang::with(['baseUnit', 'satuanKonversi.satuan'])
            ->select('id', 'kode_barang', 'nama_barang', 'base_unit_id', 'is_stock')
            ->get()
            ->map(function ($barang) {
                $availableSatuan = $barang->satuanKonversi->map(function ($konv) {
                    return [
                        'id' => $konv->satuan_id,
                        'nama' => $konv->satuan->nama,
                        'faktor' => $konv->konversi_ke_base,
                        'is_default' => (bool) $konv->is_default
                    ];
                });

                if ($availableSatuan->isEmpty() && $barang->baseUnit) {
                    $availableSatuan = collect([
                        [
                            'id' => $barang->baseUnit->id,
                            'nama' => $barang->baseUnit->nama,
                            'faktor' => 1,
                            'is_default' => true
                        ]
                    ]);
                }

                return [
                    'id' => $barang->id,
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'is_stock' => (bool) $barang->is_stock,
                    'available_satuan' => $availableSatuan
                ];
            });

        return view('produksi.pembangunan-unit.show', [
            'data' => $data,
            'allBarang' => $allBarang,
            'breadcrumbs' => [['label' => 'Pembangunan Unit', 'url' => route('produksi.pembangunanUnit.index')], ['label' => 'Detail ' . $data->unit->nama_unit, 'url' => '#']],
        ]);
    }

    public function updateTask(Request $request, $id)
    {
        $task = PembangunanUnitQcTask::findOrFail($id);
        $qc = $task->pembangunanUnitQc;
        $unit = $qc->pembangunanUnit;

        if (in_array($unit->status_pembangunan, ['selesai', 'selesai dengan catatan'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pembangunan unit ini sudah selesai, data tidak dapat diubah lagi.'
            ], 403);
        }

        $task->update([
            'keterangan_selesai' => $request->keterangan_selesai,
            'selesai' => in_array($request->keterangan_selesai, ['sesuai', 'sesuai dengan catatan']) ? 1 : 0,
        ]);

        $allTasksInQc = $qc->pembangunanUnitQcTask;
        $totalTasks = $allTasksInQc->count();
        $completedTasks = $allTasksInQc->where('selesai', 1)->count();

        $barColor = 'bg-blue-600';
        if ($completedTasks === $totalTasks) {
            $hasNotes = $allTasksInQc->where('keterangan_selesai', 'sesuai dengan catatan')->count() > 0;
            $barColor = $hasNotes ? 'bg-yellow-500' : 'bg-green-500';
        }

        // We do NOT update the unit status_pembangunan automatically to selesai!
        // We keep it as 'proses' unless it's manually submitted.
        $newStatus = 'proses';
        $unit->update([
            'status_pembangunan' => $newStatus
        ]);

        $unitTable = $unit->unit;
        if ($unitTable) {
            $unitTable->update([
                'status_pembangunan' => 'dalam pembangunan'
            ]);
        }

        return response()->json([
            'success' => true,
            'new_qc_percentage' => $qc->persentase,
            'new_total_percentage' => $unit->total_progres,
            'unit_status' => $newStatus,
            'qc_bar_color' => $barColor
        ]);
    }

    public function updateSerahTerima(Request $request, $id)
    {
        $request->validate([
            'status_serah_terima' => 'required|in:pending,siap_serah_terima,siap_lpa',
        ]);

        try {
            $pembangunan = \App\Models\PembangunanUnit::findOrFail($id);

            $pembangunan->update([
                'status_serah_terima' => $request->status_serah_terima
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status serah terima berhasil diperbarui.',
                'new_status' => $pembangunan->status_serah_terima
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pembangunanUnit = PembangunanUnit::findOrFail($id);

        if (in_array($pembangunanUnit->status_pembangunan, ['selesai', 'selesai dengan catatan'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Pembangunan unit ini sudah selesai, status tidak dapat diubah lagi.'], 422);
            }
            return redirect()->back()->with('error', 'Pembangunan unit ini sudah selesai, status tidak dapat diubah lagi.');
        }

        if ($pembangunanUnit->total_progres < 100) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Progress pembangunan baru ' . $pembangunanUnit->total_progres . '%. Harus 100% untuk bisa diselesaikan.'], 422);
            }
            return redirect()->back()->with('error', 'Status selesai hanya dapat diaktifkan jika progres pembangunan sudah 100%.');
        }

        // Determine if there are notes in any task to decide whether it is "selesai" or "selesai dengan catatan"
        $hasNotes = PembangunanUnitQcTask::whereHas('pembangunanUnitQc', function ($query) use ($pembangunanUnit) {
            $query->where('pembangunan_unit_id', $pembangunanUnit->id);
        })->where('keterangan_selesai', 'sesuai dengan catatan')->exists();

        $newStatus = $hasNotes ? 'selesai dengan catatan' : 'selesai';

        $pembangunanUnit->update([
            'status_pembangunan' => $newStatus
        ]);

        if ($pembangunanUnit->pengajuan) {
            $pembangunanUnit->pengajuan->update([
                'status_pengajuan' => 'selesai'
            ]);
        }

        $unitTable = $pembangunanUnit->unit;
        if ($unitTable) {
            $unitTable->update([
                'status_pembangunan' => 'selesai dibangun'
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status_pembangunan' => $newStatus,
                'message' => 'Status pembangunan unit berhasil diperbarui menjadi ' . $newStatus . '.'
            ]);
        }

        return redirect()->back()->with('success', 'Status pembangunan unit berhasil diperbarui menjadi selesai.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {}

    public function updateTaskNote(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string'
        ]);

        $task = PembangunanUnitQcTask::findOrFail($id);
        $unit = $task->pembangunanUnitQc->pembangunanUnit;
        if (in_array($unit->status_pembangunan, ['selesai', 'selesai dengan catatan'])) {
            return redirect()->back()->with('error', 'Unit ini sudah selesai dibangun, catatan tidak dapat diubah.');
        }

        $task->update([
            'catatan' => $request->catatan
        ]);

        return back()->with('success', 'Berhasil memperbarui catatan');
    }
}
