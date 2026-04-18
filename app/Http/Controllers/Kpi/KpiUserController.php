<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiIndicator;
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
        $roleFilter = $request->get('role');
        $statusFilter = $request->get('status');

        $query = KpiUser::with(['user', 'details', 'user.roles'])
            ->where('bulan', (int) $bulanFilter)
            ->where('tahun', (int) $tahunFilter);

        if ($request->filled('status')) {
            $query->where('status', $statusFilter);
        }

        if ($request->filled('role')) {
            $query->whereHas('user.roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        $allKpiUser = $query->latest()->get();

        $roles = Role::all();

        return view('kpi.user-kpi.index', [
            'allKpiUser'  => $allKpiUser,
            'bulanFilter' => $bulanFilter,
            'tahunFilter' => $tahunFilter,
            'roles'       => $roles,
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

        $role = Role::find($request->role_id);
        $roleName = $role ? $role->name : '';
        $queryParams = [
            'bulan'  => $request->bulan,
            'tahun'  => $request->tahun,
            'role'   => $roleName,
        ];

        return redirect()->route('kpi.user.index', $queryParams)
            ->with('success', "$countSuccess data penilaian berhasil diinisialisasi.");
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kpiUser = KpiUser::with(['user', 'details.tasks'])->findOrFail($id);
        $indicators = KpiIndicator::all();
        $modeMapping = $indicators->pluck('tipe_indikator', 'tipe_perhitungan')->toArray();

        return view('kpi.user-kpi.edit', [
            'kpiUser' => $kpiUser,
            'indicators' => $indicators,
            'modeMapping' => $modeMapping,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI', 'url' => route('kpi.user.index')],
                ['label' => 'Input Nilai: ' . $kpiUser->user->name, 'url' => '#']
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kpiUser = KpiUser::with(['user', 'details', 'details.tasks', 'reviewRequests'])->findOrFail($id);
        $indicators = KpiIndicator::all();
        $modeMapping = $indicators->pluck('tipe_indikator', 'tipe_perhitungan')->toArray();

        $bolehRequest = $kpiUser->reviewRequests->whereNull('direspon_pada')->count() === 0
            && $kpiUser->details->whereNotNull('kepatuhan_percent')
            ->where('kepatuhan_percent', '<', 90)
            ->where('nilai_tetap', false)
            ->count() > 0
            && $kpiUser->details->count() > 0;

        $prosesReview = $kpiUser->reviewRequests->whereNull('direspon_pada')->count() > 0;

        return view('kpi.user-kpi.edit', [
            'kpiUser' => $kpiUser,
            'indicators' => $indicators,
            'modeMapping' => $modeMapping,
            'bolehRequest' =>  $bolehRequest,
            'prosesReview' => $prosesReview,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI', 'url' => route('kpi.user.index')],
                ['label' => 'Input Nilai: ' . $kpiUser->user->nama_lengkap, 'url' => '#']
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

        $indicators = KpiIndicator::all();
        $modeMapping = $indicators->pluck('tipe_indikator', 'tipe_perhitungan')->toArray();

        DB::transaction(function () use ($request, $id, $indicators, $modeMapping) {
            $kpiUser = KpiUser::findOrFail($id);

            foreach ($request->task as $komponenId => $tasks) {
                $totalTargetKomponen = 0;
                $totalTercapaiKomponen = 0;
                $totalNilaiDariSelect = 0;

                $userKomponen = $kpiUser->details()->where('id', $komponenId)->first();
                if ($userKomponen && $userKomponen->nilai_tetap) {
                    continue;
                }
                $tipePerhitungan = $userKomponen->komponen->tipe_perhitungan;
                $currentMode = $modeMapping[$tipePerhitungan] ?? 'range';
                $taskCount = count($tasks);

                foreach ($tasks as $taskId => $values) {
                    // dd($values);
                    $target = isset($values['target']) ? (float) str_replace('.', '', $values['target']) : 0;

                    if ($currentMode === 'select') {
                        $nilaiSelect = (float) ($values['nilai'] ?? 0);
                        $tercapai = 0;
                    } else {
                        $tercapai = isset($values['tercapai']) ? (float) str_replace('.', '', $values['tercapai']) : 0;
                        $nilaiSelect = null;
                    }

                    $alasan = ($currentMode === 'range' && $tercapai != $target)
                        ? ($values['alasan_tidak_tercapai'] ?? null)
                        : null;

                    DB::table('kpi_user_task')->where('id', $taskId)->update([
                        'target' => $target,
                        'tercapai' => $tercapai,
                        'nilai' => $nilaiSelect,
                        'alasan_tidak_tercapai' => $alasan,
                        'updated_at' => now()
                    ]);

                    $totalTargetKomponen += $target;
                    $totalTercapaiKomponen += $tercapai;
                    $totalNilaiDariSelect += ($currentMode === 'select' ? $nilaiSelect : 0);
                }

                $skor = 0;
                $persenKepatuhan = 0;

                if ($tipePerhitungan === 'KONDISI_LANGSUNG') {
                    $skor = $totalNilaiDariSelect;
                    $persenKepatuhan = $skor;
                } elseif ($tipePerhitungan === 'AKKUMULASI_NILAI') {
                    $persenKepatuhan = ($taskCount > 0) ? ($totalNilaiDariSelect / ($taskCount * 100)) * 100 : 0;
                    $skor = $this->lookupSkor($indicators, 'KEPATUHAN', $persenKepatuhan);
                } elseif ($tipePerhitungan === 'SELISIH_STOK') {
                    $selisihMurni = abs($totalTargetKomponen - $totalTercapaiKomponen);
                    $persenKepatuhan = $totalTargetKomponen > 0 ? ($selisihMurni / $totalTargetKomponen) * 100 : 0;

                    $skor = $this->lookupSkor($indicators, $tipePerhitungan, $persenKepatuhan);
                } else {
                    $persenKepatuhan = $totalTargetKomponen > 0 ? ($totalTercapaiKomponen / $totalTargetKomponen) * 100 : 0;
                    $skor = $this->lookupSkor($indicators, $tipePerhitungan, $persenKepatuhan);
                }

                $userKomponen->update([
                    'total_target' => $totalTargetKomponen,
                    'total_tercapai' => $totalTercapaiKomponen,
                    'kepatuhan_percent' => round($persenKepatuhan, 2),
                    'skor' => $skor,
                    'nilai_akhir' => ($userKomponen->bobot / 100) * $skor,
                    'catatan_tambahan' => $request->catatan[$komponenId] ?? null,
                ]);
            }

            $kpiUser->update(['status' => $request->status]);
        });

        return redirect()->back()->with('success', 'Data KPI berhasil diperbarui.');
    }
    /**
     * Fungsi pembantu untuk mencari skor berdasarkan range di database
     */
    private function lookupSkor($indicators, $tipe, $nilai)
    {
        $nilaiRounded = round((float)$nilai, 1);

        // Cari baris indikator yang tipenya sesuai dan nilainya masuk dalam range
        $rule = $indicators->where('tipe_perhitungan', $tipe)
            ->filter(function ($i) use ($nilaiRounded) {
                $bb = $i->batas_bawah !== null ? (float) $i->batas_bawah : -999999;
                $ba = $i->batas_atas !== null ? (float) $i->batas_atas : 999999;
                return $nilaiRounded >= $bb && $nilaiRounded <= $ba;
            })->first();

        return $rule ? $rule->skor : 0;
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
}
