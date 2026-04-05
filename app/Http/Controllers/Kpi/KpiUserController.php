<?php

namespace App\Http\Controllers\Kpi;

use App\Exports\KpiUserExport;
use App\Http\Controllers\Controller;
use App\Models\KpiKomponen;
use App\Models\KpiUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

use function Symfony\Component\Clock\now;

class KpiUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bulanFilter = $request->get('bulan', date('m')) ?? Carbon::now()->month;
        $tahunFilter = $request->get('tahun', date('Y')) ?? Carbon::now()->year;

        $allKpiUser = KpiUser::with(['user', 'details'])
            ->where('bulan', (int) $bulanFilter)
            ->where('tahun', (int) $tahunFilter)
            ->latest()
            ->get();

        return view('kpi.user-kpi.index', [
            'allKpiUser' => $allKpiUser,
            'bulanFilter' => $bulanFilter,
            'tahunFilter' => $tahunFilter,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI Karyawan', 'url' => '#']
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all(); // Mengambil semua role dari Spatie

        return view('kpi.user-kpi.create', [
            'roles' => $roles,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI', 'url' => route('kpi.user.index')],
                ['label' => 'Inisialisasi Penilaian', 'url' => '#']
            ],
        ]);
    }

    public function getRoleData($roleId)
    {
        $role = Role::find($roleId);
        if (!$role) return response()->json(['users' => [], 'komponen' => []]);

        // Ambil user yang punya role ini
        $users = User::role($role->name)->get(['id', 'nama_lengkap']);

        // Ambil komponen KPI yang aktif untuk role ini
        $komponen = KpiKomponen::where('role_id', $roleId)
            ->where('is_active', true)
            ->get(['id', 'nama_komponen']);

        return response()->json([
            'users' => $users,
            'komponen' => $komponen
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'komponen_ids' => 'required|array',
            'bobot' => 'required|array', // Validasi array bobot
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer',
        ]);

        // Tambahan: Validasi total bobot harus 100 sebelum proses DB
        // Hanya hitung bobot dari komponen yang dicentang saja
        $totalBobotInput = 0;
        foreach ($request->komponen_ids as $idComp) {
            $totalBobotInput += (int) ($request->bobot[$idComp] ?? 0);
        }

        if ($totalBobotInput !== 100) {
            return back()->withErrors(['bobot' => 'Total bobot komponen yang dipilih harus berjumlah 100%.'])->withInput();
        }

        $countSuccess = 0;
        $errors = [];

        DB::transaction(function () use ($request, &$countSuccess, &$errors) {
            foreach ($request->user_ids as $userId) {
                // Cek Duplikat per User per Periode
                $exists = KpiUser::where('user_id', $userId)
                    ->where('bulan', $request->bulan)
                    ->where('tahun', $request->tahun)
                    ->exists();

                if ($exists) {
                    $user = User::find($userId);
                    $errors[] = "User {$user->nama_lengkap} sudah memiliki data pada periode ini.";
                    continue;
                }

                // 1. Simpan Header KPI User
                $kpiUser = KpiUser::create([
                    'user_id' => $userId,
                    'bulan'   => $request->bulan,
                    'tahun'   => $request->tahun,
                    'status'  => 'draft',
                ]);

                // 2. Ambil Master Komponen yang dipilih
                $masterKomponen = KpiKomponen::whereIn('id', $request->komponen_ids)
                    ->with('tasks')
                    ->get();

                foreach ($masterKomponen as $komponen) {
                    // 3. Simpan Detail Komponen (Sertakan BOBOT dari request)
                    $userKomponen = $kpiUser->details()->create([
                        'komponen_id'    => $komponen->id,
                        'nama_komponen'  => $komponen->nama_komponen,
                        'bobot'          => $request->bobot[$komponen->id] ?? 0, // AMBIL BOBOT DARI INPUT
                        'total_target'   => 0,
                        'total_tercapai' => 0,
                        'skor'           => 0,
                        'nilai_akhir'    => 0,
                    ]);

                    // 4. Simpan Detail Task
                    foreach ($komponen->tasks as $task) {
                        $userKomponen->tasks()->create([
                            'task_id'   => $task->id,
                            'nama_task' => $task->nama_task,
                            'target'    => 0,
                            'tercapai'  => 0,
                            'nilai'     => 0,
                        ]);
                    }
                }
                $countSuccess++;
            }
        });

        // Jika ada error (misal sebagian duplikat) tapi ada yang sukses
        if (count($errors) > 0 && $countSuccess > 0) {
            return redirect()->route('kpi.user.index')
                ->with('success', "$countSuccess data berhasil dibuat, namun beberapa dilewati: " . implode(', ', $errors));
        }

        // Jika semuanya gagal karena duplikat
        if (count($errors) > 0 && $countSuccess == 0) {
            return back()->withErrors($errors)->withInput();
        }

        return redirect()->route('kpi.user.index')->with('success', "$countSuccess data penilaian berhasil diinisialisasi.");
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Mengambil data dengan relasi lengkap
        $kpiUser = KpiUser::with(['user', 'details.tasks'])->findOrFail($id);

        return view('kpi.user-kpi.show', [
            'kpiUser' => $kpiUser,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI', 'url' => route('kpi.user.index')],
                ['label' => 'Detail Penilaian: ' . $kpiUser->user->nama_lengkap, 'url' => '#']
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kpiUser = KpiUser::with(['user', 'details.tasks'])->findOrFail($id);

        return view('kpi.user-kpi.edit', [
            'kpiUser' => $kpiUser,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI', 'url' => route('kpi.user.index')],
                ['label' => 'Input Nilai: ' . $kpiUser->user->name, 'url' => '#']
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'task' => 'required|array',
            'catatan' => 'nullable|array',
            'status' => 'required|in:draft,final'
        ]);

        DB::transaction(function () use ($request, $id) {
            $kpiUser = KpiUser::findOrFail($id);

            foreach ($request->task as $komponenId => $tasks) {
                $totalTargetKomponen = 0;
                $totalTercapaiKomponen = 0;

                // 1. Update Detail Task
                foreach ($tasks as $taskId => $values) {
                    $target = $values['target'] ?? 0;
                    $tercapai = $values['tercapai'] ?? 0;

                    DB::table('kpi_user_task')->where('id', $taskId)->update([
                        'target' => $target,
                        'tercapai' => $tercapai,
                        'nilai' => $target > 0 ? ($tercapai / $target) * 100 : 0,
                        'updated_at' => now()
                    ]);

                    $totalTargetKomponen += $target;
                    $totalTercapaiKomponen += $tercapai;
                }

                // 2. Hitung Persentase Kepatuhan
                $persenKepatuhan = $totalTargetKomponen > 0 ? ($totalTercapaiKomponen / $totalTargetKomponen) * 100 : 0;

                // 3. Konversi ke Skor (Berdasarkan Aturan Spreadsheet)
                $skor = 0;
                if ($persenKepatuhan >= 100) $skor = 100;
                elseif ($persenKepatuhan >= 95) $skor = 85;
                elseif ($persenKepatuhan >= 90) $skor = 70;

                // 4. Hitung Nilai Berdasarkan Bobot (Contoh: (20 / 100) * 70 = 14)
                $userKomponen = $kpiUser->details()->where('id', $komponenId)->first();
                $bobot = $userKomponen->bobot;
                $nilaiAkhirPerKomponen = ($bobot / 100) * $skor;

                $userKomponen->update([
                    'total_target' => $totalTargetKomponen,
                    'total_tercapai' => $totalTercapaiKomponen,
                    'kepatuhan_percent' => $persenKepatuhan,
                    'skor' => $skor,
                    'nilai_akhir' => $nilaiAkhirPerKomponen, // Simpan 60, 20, atau 14
                    'catatan_tambahan' => $request->catatan[$komponenId] ?? null,
                ]);
            }

            $kpiUser->update(['status' => $request->status]);
        });

        return redirect()->route('kpi.user.index')->with('success', 'Penilaian berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = KpiUser::findOrFail($id);
        $user->delete();

        return redirect()->route('kpi.user.index')->with('success', 'User kpi berhasil dihapus.');
    }

    public function exportExcel($id)
    {
        $kpi = KpiUser::with('user')->findOrFail($id);
        $fileName = 'KPI_' . str_replace(' ', '_', $kpi->user->nama_lengkap) . '_' . $kpi->bulan . '_' . $kpi->tahun . '.xlsx';

        return Excel::download(new KpiUserExport($id), $fileName);
    }
}
