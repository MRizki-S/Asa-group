<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiKomponen;
use App\Models\KpiTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class KpiKomponenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Mulai query dengan relasi role & task
        $query = KpiKomponen::with(['role:id,name', 'tasks'])->latest();

        // Filter berdasarkan Role jika ada
        if ($request->filled('roleFil')) {
            $roleId = $request->input('roleFil');
            $query->where('role_id', $roleId);
        }

        $allKpi = $query->get();
        $allRoles = Role::all();
        $roleFilter = $request->query('roleFil');

        return view('kpi.master-kpi.index', [
            'allKpi' => $allKpi,
            'roleFilter' => $roleFilter,
            'allRoles' => $allRoles,
            'breadcrumbs' => [['label' => 'Master KPI', 'url' => route('kpi.komponen.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $allRoles = Role::all();
        $tipePerhitungan = ['KEPATUHAN', 'DEVIASI_BUDGET', 'SELISIH_STOK', 'KONDISI_LANGSUNG', 'AKKUMULASI_NILAI'];

        return view('kpi.master-kpi.create', [
            'allRoles' => $allRoles,
            'tipePerhitungan' => $tipePerhitungan,
            'breadcrumbs' => [
                ['label' => 'Master KPI', 'url' => route('kpi.komponen.index')],
                ['label' => 'Tambah Komponen', 'url' => '#']
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'nama_komponen' => 'required|string|max:255',
            'tipe_perhitungan' => 'required|in:KEPATUHAN,DEVIASI_BUDGET,SELISIH_STOK,KONDISI_LANGSUNG,AKKUMULASI_NILAI',
            'label_total' => 'required|string',
            'label_tercapai' => 'required|string',
            'label_tidak_tercapai' => 'required|string',
            'tasks' => 'required|array|min:1',
            'tasks.*' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $komponen = KpiKomponen::create([
                'role_id' => $request->role_id,
                'nama_komponen' => $request->nama_komponen,
                'tipe_perhitungan' => $request->tipe_perhitungan,
                'label_total' => $request->label_total,
                'label_tercapai' => $request->label_tercapai,
                'label_tidak_tercapai' => $request->label_tidak_tercapai,
                'is_active' => $request->has('is_active'),
            ]);

            foreach ($request->tasks as $taskName) {
                KpiTask::create([
                    'komponen_id' => $komponen->id,
                    'nama_task' => $taskName
                ]);
            }

            DB::commit();
            return redirect()->route('kpi.komponen.index')->with('success', 'Master KPI berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()])->withInput();
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
    public function edit($id)
    {
        // Mengambil data komponen beserta relasi tasks-nya
        $komponen = KpiKomponen::with('tasks')->findOrFail($id);

        $allRoles = Role::all();
        $tipePerhitungan = ['KEPATUHAN', 'DEVIASI_BUDGET', 'SELISIH_STOK', 'KONDISI_LANGSUNG', 'AKKUMULASI_NILAI'];

        return view('kpi.master-kpi.edit', [
            'komponen' => $komponen,
            'allRoles' => $allRoles,
            'tipePerhitungan' => $tipePerhitungan,
            'breadcrumbs' => [
                ['label' => 'Master KPI', 'url' => route('kpi.komponen.index')],
                ['label' => 'Edit Komponen', 'url' => '#']
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $komponen = KpiKomponen::findOrFail($id);
        $request['is_active'] = $request->has('is_active') ? true : false;
        $komponen->update($request->all());

        $komponen->tasks()->delete();
        foreach ($request->tasks as $taskName) {
            $komponen->tasks()->create(['nama_task' => $taskName]);
        }

        return redirect()->route('kpi.komponen.index')->with('success', 'Data diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $komponen = KpiKomponen::findOrFail($id);
        $komponen->delete();

        return redirect()->route('kpi.komponen.index')->with('success', 'Komponen kpi berhasil dihapus.');
    }
}
