<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\MasterQcContainer;
use App\Models\PembangunanUnit;
use App\Models\PembangunanUnitQc;
use App\Models\PembangunanUnitQcTask;
use App\Models\PembangunanUnitRapBahan;
use App\Models\PembangunanUnitRapUpah;
use App\Models\PengajuanPembangunanUnit;
use App\Models\Perumahaan;
use App\Models\User;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // Mulai query dengan relasi perumahaan & tahap
        $query = PembangunanUnit::with(['perumahaan:id,nama_perumahaan,slug', 'tahap:id,perumahaan_id,nama_tahap,slug', 'unit:id,blok_id,nama_unit', 'pengawas:id,nama_lengkap', 'qcContainer', 'pengajuan'])
            ->where('perumahaan_id', $perumahaanId)
            ->whereIn('status_pembangunan', ['proses', 'selesai', 'selesai dengan catatan'])
            ->latest('created_at');

        // ===== Filter Tahap (slug) =====
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
    public function sendAcceptNotification($pembangunan)
    {
        $pembangunan->load(['unit.tahap.perumahaan', 'pengawas']);

        $unit = $pembangunan->unit;
        $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';

        // Mapping group berdasarkan perumahan
        $groupMap = [
            'Asa Dreamland' => env('FONNTE_ID_GROUP_MARKETING_ADL'),
            'Lembah Hijau Residence' => env('FONNTE_ID_GROUP_MARKETING_LHR'),
        ];

        $groupId = $groupMap[$namaPerumahan] ?? null;

        $messageGroup = "✅ *PEMBANGUNAN UNIT DIMULAI*\n\n" . "Kabar baik! Pengajuan pembangunan unit berikut telah disetujui dan statusnya kini *Dalam Proses Pembangunan*.\n\n" . "```\n" . "📍 Perumahan : {$namaPerumahan}\n" . '🏠 Tahap     : ' . ($unit->tahap->nama_tahap ?? '-') . "\n" . '🔑 Unit      : ' . ($unit->nama_unit ?? '-') . "\n" . '👷 Pengawas  : ' . ($pembangunan->pengawas->nama_lengkap ?? '-') . "\n" . '📅 Estimasi  : ' . \Carbon\Carbon::parse($pembangunan->tanggal_mulai)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($pembangunan->tanggal_selesai)->format('d/m/Y') . "\n" . "```\n\n" . 'Instruksi kerja telah diteruskan ke Pengawas terkait. Semangat untuk tim lapangan! 🏗️✨';

        if ($groupId) {
            try {
                $this->notificationGroup->send($groupId, $messageGroup);
            } catch (\Exception $e) {
            }
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pengawas_id' => 'required|integer|exists:users,id',
            'qc_container_id' => 'required|exists:master_qc_container,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'pengajuan_id' => 'required|integer|exists:pengajuan_pembangunan_unit,id',
        ]);

        try {
            DB::beginTransaction();

            // Update Pengajuan
            $pengajuan = PengajuanPembangunanUnit::findOrFail($validated['pengajuan_id']);

            $pengajuan->update([
                'direspon_oleh' => Auth::user()->id,
                'status_pengajuan' => 'dibangun',
                'tanggal_direspon' => now(),
            ]);

            // Update Pembangunan
            $pembangunan = $pengajuan->pembangunanUnit;
            $pembangunan->update([
                'pengawas_id' => $validated['pengawas_id'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'qc_container_id' => $validated['qc_container_id'],
                'status_pembangunan' => 'proses',
            ]);

            $qcUrutan = $pembangunan->qcContainer->urutan;

            foreach ($qcUrutan as $key => $urutan) {
                $pembangunanUnitQc = PembangunanUnitQc::create([
                    'pembangunan_unit_id' => $pembangunan->id,
                    'master_qc_urutan_id' => $urutan->id,
                    'qc_urutan_ke' => $urutan->qc_ke,
                    'nama_qc' => $urutan->nama_qc,
                    'tanggal_mulai' => $key == 0 ? now() : null,
                    'tanggal_selesai' => null,
                ]);

                foreach ($urutan->tugas as $key => $task) {
                    PembangunanUnitQcTask::create([
                        'pembangunan_unit_qc_id' => $pembangunanUnitQc->id,
                        'tugas' => $task->tugas,
                        'selesai' => false,
                    ]);
                }

                foreach ($urutan->rapBahan as $key => $bahan) {
                    PembangunanUnitRapBahan::create([
                        'pembangunan_unit_id' => $pembangunan->id,
                        'pembangunan_unit_qc_id' => $pembangunanUnitQc->id,
                        'master_rap_bahan_id' => $bahan->id,
                        'barang_id' => $bahan->master_barang_id,
                        'nama_barang' => $bahan->barang->nama_barang,
                        'satuan_id' => $bahan->satuan->id,
                        'satuan' => $bahan->satuan->nama,
                        'jumlah_standar' => $bahan->jumlah_kebutuhan_standar,
                    ]);
                }

                foreach ($urutan->rapUpah as $key => $upah) {
                    PembangunanUnitRapUpah::create([
                        'pembangunan_unit_id' => $pembangunan->id,
                        'pembangunan_unit_qc_id' => $pembangunanUnitQc->id,
                        'master_rap_upah_id' => $upah->id,
                        'nama_upah' => $upah->masterUpah->nama_upah,
                        'nominal_standar' => $upah->nominal_standar,
                    ]);
                }
            }

            DB::commit();

            $this->sendAcceptNotification($pembangunan);

            return redirect()->route('produksi.pembangunanUnit.index')->with('success', 'Data Pengajuan Pembangunan Unit berhasil diassign!');
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
                            'faktor' => $konv->konversi_ke_base
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
