<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\MasterQcContainer;
use App\Models\MasterQcTugas;
use App\Models\MasterQcUrutan;
use App\Models\MasterRapBahan;
use App\Models\MasterRapUpah;
use App\Models\MasterSatuan;
use App\Models\MasterUpah;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterQcRapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Mulai query dengan relasi type & urutan
        $query = MasterQcContainer::with(['type:id,nama_type,slug', 'urutan'])->latest('created_at');

        // ===== Filter Tahap (slug) =====
        if ($request->filled('typeFil')) {
            $slugTahap = $request->input('typeFil');
            $query->whereHas('type', function ($q) use ($slugTahap) {
                $q->where('slug', $slugTahap);
            });
        }

        // Ambil data setelah filter (atau semua jika tanpa filter)
        $allQcContainer = $query->get();

        $allType = Type::all();

        $typeSlug = $request->query('typeFil');
        return view('Produksi.master-qc-rap.index', [
            'allQcContainer' => $allQcContainer,
            'typeSlug' => $typeSlug,
            'allType' => $allType,
            'breadcrumbs' => [['label' => 'Master Qc Rap', 'url' => route('produksi.masterQcRap.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $allType = Type::all();
        $allUpah = MasterUpah::all();
        $allBarang = MasterBarang::with('satuanKonversi.satuan')->get();
        $allSatuan = MasterSatuan::all();

        return view('produksi.master-qc-rap.create', [
            'breadcrumbs' => [['label' => 'Master Qc Rap', 'url' => route('produksi.masterQcRap.index')], ['label' => 'Tambah Qc Rap', 'url' => route('produksi.masterQcRap.create')]],
            'allType' => $allType,
            'allUpah' => $allUpah,
            'allBarang' => $allBarang,
            'allSatuan' => $allSatuan,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:type,id',
            'nama_container' => 'required|string|max:255',
            'qc' => 'nullable|array',
            'qc.*.qc_ke' => 'required_with:qc|integer',
            'qc.*.nama_qc' => 'required_with:qc|string',
            'qc.*.tugas' => 'nullable|array',
            'bahan' => 'nullable|array',
            'upah' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $container = MasterQcContainer::create([
                'type_id' => $request->type_id,
                'nama_container' => $request->nama_container,
            ]);

            $urutanIds = [];
            if ($request->has('qc') && is_array($request->qc)) {
                foreach ($request->qc as $index => $qcItem) {
                    $urutan = MasterQcUrutan::create([
                        'master_qc_container_id' => $container->id,
                        'qc_ke' => $qcItem['qc_ke'],
                        'nama_qc' => $qcItem['nama_qc'],
                    ]);

                    if (isset($qcItem['tugas']) && is_array($qcItem['tugas'])) {
                        foreach ($qcItem['tugas'] as $deskripsi) {
                            if (!empty($deskripsi)) {
                                MasterQcTugas::create([
                                    'master_qc_urutan_id' => $urutan->id,
                                    'tugas' => $deskripsi,
                                ]);
                            }
                        }
                    }

                    $urutanIds[$index] = $urutan->id;
                }
            }

            if ($request->has('bahan')) {
                foreach ($request->bahan as $b) {
                    $targetUrutanId = $urutanIds[$b['urutan_idx']] ?? null;
                    MasterRapBahan::create([
                        'type_id' => $request->type_id,
                        'master_barang_id' => $b['barang_id'],
                        'master_qc_container_id' => $container->id,
                        'master_qc_urutan_id' => $targetUrutanId,
                        'jumlah_kebutuhan_standar' => $b['jumlah_kebutuhan_standar'],
                        'master_satuan_id' => $b['satuan_id'],
                    ]);
                }
            }

            if ($request->has('upah')) {
                foreach ($request->upah as $u) {
                    $targetUrutanId = $urutanIds[$u['urutan_idx']] ?? null;

                    MasterRapUpah::create([
                        'type_id' => $request->type_id,
                        'master_qc_container_id' => $container->id,
                        'master_qc_urutan_id' => $targetUrutanId,
                        'master_upah_id' => $u['master_upah_id'],
                        'nominal_standar' => $u['nominal_standar'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('produksi.masterQcRap.index')->with('success', 'Data Container berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $container = MasterQcContainer::with(['type', 'urutan.tugas', 'rapBahan.urutan', 'rapBahan.barang', 'rapBahan.satuan', 'rapUpah.urutan', 'rapUpah.masterUpah'])->findOrFail($id);

        return view('Produksi.master-qc-rap.detail', [
            'container' => $container,
            'breadcrumbs' => [['label' => 'Master Qc Rap', 'url' => route('produksi.masterQcRap.index')], ['label' => 'Detail', 'url' => route('produksi.masterQcRap.show', $id)]],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $container = MasterQcContainer::with(['urutan.tugas', 'rapBahan', 'rapUpah'])->findOrFail($id);
        $allType = Type::all();
        $allUpah = MasterUpah::all();

        $allBarang = MasterBarang::with('satuanKonversi.satuan')->get();
        $allSatuan = MasterSatuan::all();

        $breadcrumbs = [['label' => 'Master Qc Rap', 'url' => route('produksi.masterQcRap.index')], ['label' => 'Edit Qc Rap', 'url' => route('produksi.masterQcRap.edit', $id)]];

        return view('produksi.master-qc-rap.edit', compact('breadcrumbs', 'container', 'allType', 'allUpah', 'allBarang', 'allSatuan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'type_id' => 'required|exists:type,id',
            'nama_container' => 'required|string|max:255',
            'qc' => 'nullable|array',
            'bahan' => 'nullable|array',
            'upah' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $container = MasterQcContainer::findOrFail($id);

            $container->update([
                'type_id' => $request->type_id,
                'nama_container' => $request->nama_container,
            ]);

            foreach ($container->urutan as $oldUrutan) {
                $oldUrutan->tugas()->delete();
            }
            $container->urutan()->delete();
            $container->rapBahan()->delete();
            $container->rapUpah()->delete();

            $urutanIdsMap = [];

            if ($request->has('qc') && is_array($request->qc)) {
                foreach ($request->qc as $index => $qcItem) {
                    $urutan = MasterQcUrutan::create([
                        'master_qc_container_id' => $container->id,
                        'qc_ke' => $qcItem['qc_ke'],
                        'nama_qc' => $qcItem['nama_qc'],
                    ]);

                    $urutanIdsMap[$index] = $urutan->id;

                    if (isset($qcItem['tugas']) && is_array($qcItem['tugas'])) {
                        foreach ($qcItem['tugas'] as $deskripsi) {
                            if (!empty($deskripsi)) {
                                MasterQcTugas::create([
                                    'master_qc_urutan_id' => $urutan->id,
                                    'tugas' => $deskripsi,
                                ]);
                            }
                        }
                    }
                }
            }

            if ($request->has('bahan') && is_array($request->bahan)) {
                foreach ($request->bahan as $bahanItem) {
                    $newUrutanId = $urutanIdsMap[$bahanItem['urutan_idx']] ?? null;

                    if ($newUrutanId) {
                        MasterRapBahan::create([
                            'type_id' => $request->type_id,
                            'master_qc_container_id' => $container->id,
                            'master_qc_urutan_id' => $newUrutanId,
                            'master_barang_id' => $bahanItem['barang_id'] ?? 1,
                            'jumlah_kebutuhan_standar' => $bahanItem['jumlah_kebutuhan_standar'],
                            'master_satuan_id' => $bahanItem['satuan_id'],
                        ]);
                    }
                }
            }

            if ($request->has('upah') && is_array($request->upah)) {
                foreach ($request->upah as $upahItem) {
                    $newUrutanId = $urutanIdsMap[$upahItem['urutan_idx']] ?? null;

                    if ($newUrutanId) {
                        MasterRapUpah::create([
                            'type_id' => $request->type_id,
                            'master_qc_container_id' => $container->id,
                            'master_qc_urutan_id' => $newUrutanId,
                            'master_upah_id' => $upahItem['master_upah_id'],
                            'nominal_standar' => $upahItem['nominal_standar'],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('produksi.masterQcRap.index')->with('success', 'Data Master Berhasil Diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal Update: ' . $e->getMessage()]);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $masterQc = MasterQcContainer::findOrFail($id);
        $masterQc->delete();

        return redirect()->route('produksi.masterQcRap.index')->with('success', 'Container QC berhasil dihapus.');
    }
}
