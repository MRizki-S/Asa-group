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

        $query = PembangunanUnit::with(['perumahaan:id,nama_perumahaan,slug', 'tahap:id,perumahaan_id,nama_tahap,slug', 'unit:id,blok_id,nama_unit', 'pengawas:id,nama_lengkap', 'qcContainer', 'pengajuan'])
            ->where('perumahaan_id', $perumahaanId)
            ->whereIn('status_pembangunan', ['proses', 'selesai', 'selesai dengan catatan'])
            ->latest('created_at');

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
        return view('Produksi.pembangunan-unit.index', [
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
        $data = PembangunanUnit::with(['unit', 'tahap', 'perumahaan', 'pengawas', 'pembangunanUnitQc.pembangunanUnitQcTask', 'pembangunanUnitQc.pembangunanUnitRapBahan', 'pembangunanUnitQc.pembangunanUnitRapUpah', 'pembangunanUnitQc.pembangunanUnitRapBahan.barang'])->findOrFail($id);
        $allBarang = MasterBarang::with(['satuanKonversi.satuan'])
            ->select('id', 'kode_barang', 'nama_barang', 'is_stock')
            ->get()
            ->map(function ($barang) {
                return [
                    'id' => $barang->id,
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'is_stock' => (bool) $barang->is_stock,
                    'available_satuan' => $barang->satuanKonversi->map(function ($konv) {
                        return [
                            'id' => $konv->satuan_id,
                            'nama' => $konv->satuan->nama,
                            'faktor' => $konv->konversi_ke_base,
                            'is_default' => (bool) $konv->is_default
                        ];
                    })
                ];
            });

        return view('Produksi.pembangunan-unit.show', [
            'data' => $data,
            'allBarang' => $allBarang,
            'breadcrumbs' => [['label' => 'Pembangunan Unit', 'url' => route('produksi.pembangunanUnit.index')], ['label' => 'Detail ' . $data->unit->nama_unit, 'url' => '#']],
        ]);
    }

    public function updateTask(Request $request, $id)
    {
        $task = PembangunanUnitQcTask::findOrFail($id);

        $task->update([
            'keterangan_selesai' => $request->keterangan_selesai,
            'selesai' => in_array($request->keterangan_selesai, ['sesuai', 'sesuai dengan catatan']) ? 1 : 0,
        ]);

        $qc = $task->pembangunanUnitQc;
        $unit = $qc->pembangunanUnit;

        $allTasksInQc = $qc->pembangunanUnitQcTask;
        $totalTasks = $allTasksInQc->count();
        $completedTasks = $allTasksInQc->where('selesai', 1)->count();

        $barColor = 'bg-blue-600';
        if ($completedTasks === $totalTasks) {
            $hasNotes = $allTasksInQc->where('keterangan_selesai', 'sesuai dengan catatan')->count() > 0;
            $barColor = $hasNotes ? 'bg-yellow-500' : 'bg-green-500';
        }

        $allTasks = PembangunanUnitQcTask::whereHas('pembangunanUnitQc', function ($query) use ($unit) {
            $query->where('pembangunan_unit_id', $unit->id);
        })->get();

        $totalTasks = $allTasks->count();
        $completedTasks = $allTasks->where('selesai', 1)->count();

        $hasNotes = $allTasks->where('keterangan_selesai', 'sesuai dengan catatan')->count() > 0;

        if ($completedTasks === $totalTasks) {
            $newStatus = $hasNotes ? 'selesai dengan catatan' : 'selesai';
        } else {
            $newStatus = 'proses';
        }

        $unit->update([
            'status_pembangunan' => $newStatus
        ]);

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
    public function update(Request $request, string $id) {}

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
        $task->update([
            'catatan' => $request->catatan
        ]);

        return back()->with('success', 'Berhasil memperbarui catatan');
    }
}
