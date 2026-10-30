<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiIndicator;
use App\Models\KpiKomponen;
use App\Models\KpiUser;
use App\Models\User;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Role;
use App\Models\KpiUserKomponen;
use App\Services\NotificationPribadiService;

use function Symfony\Component\Clock\now;

class KpiUserController extends Controller
{
    protected NotificationPribadiService $notificationPribadi;

    public function __construct(NotificationPribadiService $notificationPribadi)
    {
        $this->notificationPribadi = $notificationPribadi;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bulanFilter = $request->has('bulan') ? $request->get('bulan') : date('m');
        $tahunFilter = $request->get('tahun', date('Y')) ?? Carbon::now()->year;
        $roleFilter = $request->get('role');
        $statusFilter = $request->get('status');
        $devisiFilter = $request->get('devisi');

        $query = KpiUser::with(['karyawan', 'details', 'karyawan.role.devisi', 'finalizedBy'])
            ->where('tahun', (int) $tahunFilter);

        if ($bulanFilter !== null && $bulanFilter !== '') {
            $query->where('bulan', (int) $bulanFilter);
        }

        $user = auth()->user();
        $isManagerReviewer = $this->isManagerReviewer($user);

        if ($isManagerReviewer) {
            $allowedStatuses = ['menunggu_review', 'final'];
            if ($request->filled('status') && in_array($statusFilter, $allowedStatuses)) {
                $query->where('status', $statusFilter);
            } else {
                $query->whereIn('status', $allowedStatuses);
            }
        } else {
            if ($request->filled('status')) {
                $query->where('status', $statusFilter);
            }
        }

        if ($request->filled('role')) {
            $query->whereHas('karyawan.role', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        if ($request->filled('devisi')) {
            $query->whereHas('karyawan.role', function ($q) use ($devisiFilter) {
                $q->where('devisi_id', $devisiFilter);
            });
        }

        $allKpiUser = $query->latest()->get();

        $roles = Role::all();
        $devisis = \App\Models\Devisi::orderBy('nama_devisi')->get();

        return view('kpi.user-kpi.index', [
            'allKpiUser'        => $allKpiUser,
            'bulanFilter'       => $bulanFilter,
            'tahunFilter'       => $tahunFilter,
            'roles'             => $roles,
            'devisis'           => $devisis,
            'isManagerReviewer' => $isManagerReviewer,
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
        if ($this->isManagerReviewer(auth()->user())) {
            return redirect()->route('kpi.user.index')->with('error', 'Manajer tidak memiliki akses untuk membuat data inisialisasi KPI.');
        }

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

        // Ambil karyawan yang punya role ini
        $users = Karyawan::where('role_id', $roleId)->get(['id', 'nama as nama_lengkap']);

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
        if ($this->isManagerReviewer(auth()->user())) {
            return redirect()->route('kpi.user.index')->with('error', 'Manajer tidak memiliki akses untuk membuat data inisialisasi KPI.');
        }

        $request->validate([
            'role_id' => 'required',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:karyawan,id',
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
                // Cek Duplikat per Karyawan per Periode
                $exists = KpiUser::where('karyawan_id', $userId)
                    ->where('bulan', $request->bulan)
                    ->where('tahun', $request->tahun)
                    ->exists();

                if ($exists) {
                    $user = Karyawan::find($userId);
                    $errors[] = "Karyawan {$user->nama} sudah memiliki data pada periode ini.";
                    continue;
                }

                // 1. Simpan Header KPI User
                $kpiUser = KpiUser::create([
                    'karyawan_id' => $userId,
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
        $kpiUser = KpiUser::with(['karyawan', 'details', 'details.tasks', 'finalizedBy'])->findOrFail($id);

        if ($this->isManagerReviewer(auth()->user()) && in_array($kpiUser->status, ['draft', 'revisi'])) {
            return redirect()->route('kpi.user.index')->with('error', 'Data KPI ini belum diajukan atau sedang direvisi oleh Admin.');
        }

        $indicators = KpiIndicator::all();
        $modeMapping = $indicators->pluck('tipe_indikator', 'tipe_perhitungan')->toArray();

        return view('kpi.user-kpi.edit', [
            'kpiUser' => $kpiUser,
            'indicators' => $indicators,
            'modeMapping' => $modeMapping,
            'isReadOnly' => true,
            'isManager' => false,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI', 'url' => route('kpi.user.index')],
                ['label' => 'Detail Nilai: ' . $kpiUser->karyawan->nama, 'url' => '#']
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kpiUser = KpiUser::with(['karyawan', 'details', 'details.tasks', 'finalizedBy'])->findOrFail($id);

        if ($kpiUser->isFinal()) {
            return redirect()->route('kpi.user.show', $id);
        }

        $user = auth()->user();
        if ($this->isManagerReviewer($user) && in_array($kpiUser->status, ['draft', 'revisi'])) {
            return redirect()->route('kpi.user.index')->with('error', 'Data KPI ini belum diajukan atau sedang direvisi oleh Admin.');
        }

        $isManager = $user->can('kpi.kpi-user.review');

        // Jika status menunggu_review dan user bukan manager (misal admin biasa), kunci ke mode read-only
        $isReadOnly = false;
        if ($kpiUser->isMenungguReview() && !$isManager) {
            $isReadOnly = true;
        }

        $indicators = KpiIndicator::all();
        $modeMapping = $indicators->pluck('tipe_indikator', 'tipe_perhitungan')->toArray();

        $pageTitle = ($isManager && $kpiUser->isMenungguReview() ? 'Review Nilai: ' : 'Input Nilai: ') . $kpiUser->karyawan->nama;

        return view('kpi.user-kpi.edit', [
            'kpiUser' => $kpiUser,
            'indicators' => $indicators,
            'modeMapping' => $modeMapping,
            'isReadOnly' => $isReadOnly,
            'isManager' => $isManager,
            'breadcrumbs' => [
                ['label' => 'Penilaian KPI', 'url' => route('kpi.user.index')],
                ['label' => $pageTitle, 'url' => '#']
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $kpiUser = KpiUser::findOrFail($id);

        if ($kpiUser->isFinal()) {
            return back()->withErrors(['status' => 'Penilaian KPI ini sudah difinalisasi dan tidak dapat diubah lagi.'])->withInput();
        }

        $user = auth()->user();
        if ($this->isManagerReviewer($user) && in_array($kpiUser->status, ['draft', 'revisi'])) {
            return back()->withErrors(['status' => 'Data KPI ini belum diajukan atau sedang direvisi oleh Admin.'])->withInput();
        }

        $isManager = $user->can('kpi.kpi-user.review');
        $actionType = $request->input('action_type', 'save_draft');

        // Validasi hak akses saat status menunggu_review
        if ($kpiUser->isMenungguReview() && !$isManager) {
            return back()->withErrors(['review' => 'Penilaian ini sedang dalam proses review oleh manajer dan tidak dapat diubah.'])->withInput();
        }

        // Aturan validasi
        $rules = [
            'task' => 'required|array',
            'catatan' => 'nullable|array',
            'is_review_khusus' => 'nullable|array',
            'alasan_review_khusus' => 'nullable|array',
            'skor_custom' => 'nullable|array',
            'catatan_revisi' => 'nullable|string',
        ];

        if ($actionType === 'minta_revisi') {
            $rules['catatan_revisi'] = 'required|string';
        }

        $request->validate($rules);

        // Tentukan target status berdasarkan aksi dan hak akses
        $targetStatus = $kpiUser->status;

        if ($actionType === 'finalisasi') {
            if (!$user->can('kpi.kpi-user.finalisasi')) {
                return back()->withErrors(['status' => 'Anda tidak memiliki hak akses untuk memfinalisasi penilaian KPI ini.'])->withInput();
            }
            $targetStatus = 'final';
        } elseif ($actionType === 'minta_revisi') {
            if (!$user->can('kpi.kpi-user.minta-revisi')) {
                return back()->withErrors(['status' => 'Anda tidak memiliki hak akses untuk meminta revisi penilaian KPI ini.'])->withInput();
            }
            $targetStatus = 'revisi';
        } elseif ($actionType === 'simpan_draft_review') {
            if (!$isManager) {
                return back()->withErrors(['status' => 'Aksi ini hanya dapat dilakukan oleh Manajer.'])->withInput();
            }
            $targetStatus = 'menunggu_review';
        } elseif ($actionType === 'posting_manager') {
            if ($this->isManagerReviewer($user)) {
                return back()->withErrors(['status' => 'Aksi pengajuan ini hanya dapat dilakukan oleh Admin pembuat penilaian.'])->withInput();
            }
            $targetStatus = 'menunggu_review';
        } elseif ($actionType === 'save_draft') {
            $targetStatus = $kpiUser->status === 'revisi' ? 'revisi' : 'draft';
        }

        $indicators = KpiIndicator::all();
        $modeMapping = $indicators->pluck('tipe_indikator', 'tipe_perhitungan')->toArray();

        DB::transaction(function () use ($request, $kpiUser, $indicators, $modeMapping, $isManager, $targetStatus) {
            foreach ($request->task as $komponenId => $tasks) {
                $userKomponen = $kpiUser->details()->where('id', $komponenId)->first();
                if (!$userKomponen) {
                    continue;
                }

                $tipePerhitungan = $userKomponen->komponen->tipe_perhitungan;
                $currentMode = $modeMapping[$tipePerhitungan] ?? 'range';
                $taskCount = count($tasks);

                $totalTargetKomponen = 0;
                $totalTercapaiKomponen = 0;
                $totalNilaiDariSelect = 0;

                foreach ($tasks as $taskId => $values) {
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
                        'updated_at' => now(),
                    ]);

                    $totalTargetKomponen += $target;
                    $totalTercapaiKomponen += $tercapai;
                    $totalNilaiDariSelect += ($currentMode === 'select' ? $nilaiSelect : 0);
                }

                // Cek override skor dari Manager (Hanya berlaku saat status menunggu_review dan di-review oleh Manager)
                $customSkor = $request->input("skor_custom.{$komponenId}");
                $hasCustomSkor = $isManager && $kpiUser->isMenungguReview() && ($customSkor !== null && $customSkor !== '' && $customSkor !== 'auto');

                if ($hasCustomSkor) {
                    $skor = (float) $customSkor;
                    $nilaiTetap = true;
                    $persenKepatuhan = $skor;
                    if ($tipePerhitungan === 'PROPORSIONAL' || $tipePerhitungan === 'SELISIH_STOK') {
                        $persenKepatuhan = $totalTargetKomponen > 0 ? ($totalTercapaiKomponen / $totalTargetKomponen) * 100 : 0;
                    }
                } else {
                    // Saat Admin menginput/mengedit draft, nilai dihitung otomatis murni dari task
                    $nilaiTetap = false;
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
                    } elseif ($tipePerhitungan === 'PROPORSIONAL') {
                        $persenKepatuhan = $totalTargetKomponen > 0 ? ($totalTercapaiKomponen / $totalTargetKomponen) * 100 : 0;
                        $skor = min(100, $persenKepatuhan);
                    } else {
                        $persenKepatuhan = $totalTargetKomponen > 0 ? ($totalTercapaiKomponen / $totalTargetKomponen) * 100 : 0;
                        $skor = $this->lookupSkor($indicators, $tipePerhitungan, $persenKepatuhan);
                    }
                }

                $isKhusus = isset($request->is_review_khusus[$komponenId]) && $request->is_review_khusus[$komponenId] == '1';
                $alasanKhusus = $isKhusus ? ($request->alasan_review_khusus[$komponenId] ?? null) : null;

                $userKomponen->update([
                    'total_target' => $totalTargetKomponen,
                    'total_tercapai' => $totalTercapaiKomponen,
                    'kepatuhan_percent' => round($persenKepatuhan, 2),
                    'skor' => round($skor, 2),
                    'nilai_akhir' => round(($userKomponen->bobot / 100) * $skor, 2),
                    'catatan_tambahan' => $request->catatan[$komponenId] ?? null,
                    'is_review_khusus' => $isKhusus,
                    'alasan_review_khusus' => $alasanKhusus,
                    'nilai_tetap' => $nilaiTetap,
                ]);
            }

            $updateHeader = ['status' => $targetStatus];

            if ($targetStatus === 'final') {
                $updateHeader['finalized_by'] = auth()->id();
                $updateHeader['finalized_at'] = now();
            } elseif ($targetStatus === 'revisi') {
                $updateHeader['catatan_revisi'] = $request->input('catatan_revisi');
            }

            $kpiUser->update($updateHeader);
        });

        // Helper nama bulan bahasa Indonesia
        $daftarBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $namaBulan = $daftarBulan[(int) $kpiUser->bulan] ?? date('F', mktime(0, 0, 0, $kpiUser->bulan, 1, $kpiUser->tahun));
        $periode = "{$namaBulan} {$kpiUser->tahun}";

        // WhatsApp Notification
        if ($targetStatus === 'menunggu_review' && $actionType === 'posting_manager') {
            $kpiUser->refresh();
            $namaKaryawan = $kpiUser->karyawan->nama;
            $jabatanKaryawan = $kpiUser->karyawan->role?->name ?? '-';

            $komponenNol = $kpiUser->details->where('skor', 0)->pluck('nama_komponen')->toArray();
            $komponenKhusus = $kpiUser->details->where('is_review_khusus', true)->pluck('nama_komponen')->toArray();
            $semuaKomponen = array_unique(array_merge($komponenNol, $komponenKhusus));
            $catatanKomponen = !empty($semuaKomponen) ? implode(', ', $semuaKomponen) : 'Normal';

            $message = view('notifications.whatsapp.kpi.posting_review', [
                'namaKaryawan'    => $namaKaryawan,
                'jabatanKaryawan' => $jabatanKaryawan,
                'periode'         => $periode,
                'skorAkhir'       => $kpiUser->total_nilai,
                'catatanKomponen' => $catatanKomponen,
            ])->render();

            try {
                $noHpManager = env('FONNTE_NO_MANAGER_STRATEGI', '6289685813512');
                if (!empty($noHpManager)) {
                    $this->notificationPribadi->sendWhatsApp($noHpManager, $message);
                }
            } catch (\Throwable $e) {
                \Log::error('Error kirim notif WA KPI Review: ' . $e->getMessage());
            }

            return redirect()->route('kpi.user.index')->with('success', 'Penilaian KPI berhasil disimpan & diajukan ke Manajer untuk review.');
        }

        if ($targetStatus === 'revisi') {
            $namaKaryawan = $kpiUser->karyawan->nama;
            $jabatanKaryawan = $kpiUser->karyawan->role?->name ?? '-';
            $catatan = $request->input('catatan_revisi');
            $rolePengembali = auth()->user()->roles->first()->name ?? 'Manajer Strategi & Kepatuhan';

            $message = view('notifications.whatsapp.kpi.minta_revisi', [
                'namaKaryawan'    => $namaKaryawan,
                'jabatanKaryawan' => $jabatanKaryawan,
                'periode'         => $periode,
                'catatanRevisi'   => $catatan,
                'rolePengembali'  => $rolePengembali,
            ])->render();

            try {
                $noHpAdmin = env('FONNTE_NO_ADMIN_EKSEKUTIF', '6282119916732');
                if (!empty($noHpAdmin)) {
                    $this->notificationPribadi->sendWhatsApp($noHpAdmin, $message);
                }
            } catch (\Throwable $e) {
                \Log::error('Error kirim notif WA KPI Revisi: ' . $e->getMessage());
            }

            return redirect()->route('kpi.user.index')->with('success', 'Penilaian KPI berhasil dikembalikan ke Admin untuk direvisi.');
        }

        if ($targetStatus === 'final') {
            $kpiUser->refresh();
            $namaKaryawan = $kpiUser->karyawan->nama;
            $jabatanKaryawan = $kpiUser->karyawan->role?->name ?? '-';
            $namaManajer = auth()->user()->nama_lengkap ?? 'Manajer Strategi & Kepatuhan';

            $message = view('notifications.whatsapp.kpi.finalisasi', [
                'namaKaryawan'    => $namaKaryawan,
                'jabatanKaryawan' => $jabatanKaryawan,
                'periode'         => $periode,
                'nilaiAkhir'      => $kpiUser->total_nilai,
                'namaManajer'     => $namaManajer,
            ])->render();

            try {
                $noHpAdmin = env('FONNTE_NO_ADMIN_EKSEKUTIF', '6282119916732');
                if (!empty($noHpAdmin)) {
                    $this->notificationPribadi->sendWhatsApp($noHpAdmin, $message);
                }
            } catch (\Throwable $e) {
                \Log::error('Error kirim notif WA KPI Finalisasi: ' . $e->getMessage());
            }

            return redirect()->route('kpi.user.index')->with('success', 'Penilaian KPI berhasil disetujui & difinalisasi.');
        }

        if ($actionType === 'simpan_draft_review') {
            return redirect()->back()->with('success', 'Draft review KPI berhasil disimpan (status tetap Menunggu Review).');
        }

        return redirect()->back()->with('success', 'Draft penilaian KPI berhasil disimpan.');
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
        $kpiUser = KpiUser::findOrFail($id);

        if ($this->isManagerReviewer(auth()->user())) {
            return redirect()->route('kpi.user.index')->with('error', 'Manajer tidak memiliki akses untuk menghapus data KPI.');
        }

        if ($kpiUser->isFinal()) {
            return redirect()->route('kpi.user.index')->with('error', 'Penilaian KPI yang sudah difinalisasi tidak dapat dihapus.');
        }

        $kpiUser->delete();

        return redirect()->route('kpi.user.index')->with('success', 'User kpi berhasil dihapus.');
    }

    /**
     * Memeriksa apakah user saat ini bertindak sebagai Reviewer (Manajer Strategi & Kepatuhan)
     */
    private function isManagerReviewer($user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasRole(['Superadmin', 'superadmin'])) {
            return false;
        }

        return $user->hasRole(['MANAJER STRATEGI & KEPATUHAN', 'Manager Strategi & Kepatuhan']) || 
            ($user->can('kpi.kpi-user.review') && !$user->can('kpi.kpi-user.create'));
    }
}
