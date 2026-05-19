<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KpiDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tahunAktif = date('Y');
        $pilihanTahun = [$tahunAktif - 1, $tahunAktif, $tahunAktif + 1];

        $tahun = $request->input('tahun', $tahunAktif);
        $jabatanId = $request->input('jabatan');

        // 1. Fetch all users of type 'karyawan' (with optional role/jabatan filter, excluding Superadmin)
        $usersRaw = DB::table('users')
            ->where('users.type', 'karyawan')
            ->leftJoin('ubs', 'users.perumahaan_id', '=', 'ubs.id')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', 'App\Models\User');
            })
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where(function ($query) {
                $query->where('roles.name', '!=', 'Superadmin')
                      ->orWhereNull('roles.name');
            })
            ->when($jabatanId, function ($query, $jabatanId) {
                return $query->where('roles.id', $jabatanId);
            })
            ->select(
                'users.id as user_id',
                'users.nama_lengkap',
                'users.is_global',
                'users.perumahaan_id',
                'ubs.nama_ubs',
                'roles.name as role_name'
            )
            ->orderBy('users.nama_lengkap', 'asc')
            ->get();

        // 2. Fetch all KPI component sums for the selected year
        $kpiScores = DB::table('kpi_user')
            ->join('kpi_user_komponen', 'kpi_user.id', '=', 'kpi_user_komponen.kpi_user_id')
            ->where('kpi_user.tahun', $tahun)
            ->select(
                'kpi_user.user_id',
                'kpi_user.bulan',
                DB::raw('SUM(kpi_user_komponen.nilai_akhir) as total_nilai')
            )
            ->groupBy('kpi_user.user_id', 'kpi_user.bulan')
            ->get();

        $monthMap = [
            '1' => 'januari', '01' => 'januari', 'januari' => 'januari',
            '2' => 'februari', '02' => 'februari', 'februari' => 'februari', 'febuari' => 'februari',
            '3' => 'maret', '03' => 'maret', 'maret' => 'maret',
            '4' => 'april', '04' => 'april', 'april' => 'april',
            '5' => 'mei', '05' => 'mei', 'mei' => 'mei',
            '6' => 'juni', '06' => 'juni', 'juni' => 'juni',
            '7' => 'juli', '07' => 'juli', 'juli' => 'juli',
            '8' => 'agustus', '08' => 'agustus', 'agustus' => 'agustus',
            '9' => 'september', '09' => 'september', 'september' => 'september',
            '10' => 'oktober', 'oktober' => 'oktober',
            '11' => 'november', 'november' => 'november',
            '12' => 'desember', 'desember' => 'desember',
        ];

        // Map KPI scores: [user_id => [bulan_baku => total_nilai]]
        $scoresMap = [];
        foreach ($kpiScores as $score) {
            $rawBulan = strtolower(trim($score->bulan));
            $bulanBaku = $monthMap[$rawBulan] ?? null;
            if ($bulanBaku) {
                $scoresMap[$score->user_id][$bulanBaku] = (float) $score->total_nilai;
            }
        }

        $roles = DB::table('roles')->select('id', 'name')->orderBy('name', 'asc')->get();

        $dashboardData = [];

        // 3. Build dashboard structure grouped by Jabatan/Role
        foreach ($usersRaw as $userRow) {
            // Determine Jabatan/Role name group
            $jabatanName = $userRow->role_name ?? 'LAINNYA';

            $nama = $userRow->nama_lengkap;

            // Initialize month scores with null
            $userBulanScores = array_fill_keys(array_values(array_unique($monthMap)), null);

            // Populate from scoresMap if exists
            if (isset($scoresMap[$userRow->user_id])) {
                foreach ($scoresMap[$userRow->user_id] as $bulanBaku => $scoreVal) {
                    $userBulanScores[$bulanBaku] = $scoreVal;
                }
            }

            if (!isset($dashboardData[$jabatanName])) {
                $dashboardData[$jabatanName] = [];
            }

            $dashboardData[$jabatanName][] = [
                'nama' => $nama,
                'jabatan' => $jabatanName,
                'bulan' => $userBulanScores
            ];
        }

        // 4. Calculate Quarter averages
        $quarters = [
            'q1' => ['januari', 'februari', 'maret'],
            'q2' => ['april', 'mei', 'juni'],
            'q3' => ['juli', 'agustus', 'september'],
            'q4' => ['oktober', 'november', 'desember'],
        ];

        foreach ($dashboardData as $jabatanName => &$users) {
            foreach ($users as &$data) {
                foreach ($quarters as $q => $months) {
                    $scores = [];
                    foreach ($months as $month) {
                        if (!is_null($data['bulan'][$month])) {
                            $scores[] = $data['bulan'][$month];
                        }
                    }
                    $data[$q] = count($scores) > 0 ? (array_sum($scores) / count($scores)) : null;
                }
            }
        }

        return view('kpi.dashboard.index', [
            'tahun' => $tahun,
            'pilihanTahun' => $pilihanTahun,
            'roles' => $roles,
            'dashboardData' => $dashboardData,
            'breadcrumbs' => [
                ['label' => 'Dashboard KPI', 'url' => route('kpi.dashboard.index')],
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $jabatanId = $request->input('jabatan');

        // 1. Fetch all users of type 'karyawan' (with optional role/jabatan filter, excluding Superadmin)
        $usersRaw = DB::table('users')
            ->where('users.type', 'karyawan')
            ->leftJoin('ubs', 'users.perumahaan_id', '=', 'ubs.id')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', 'App\Models\User');
            })
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where(function ($query) {
                $query->where('roles.name', '!=', 'Superadmin')
                      ->orWhereNull('roles.name');
            })
            ->when($jabatanId, function ($query, $jabatanId) {
                return $query->where('roles.id', $jabatanId);
            })
            ->select(
                'users.id as user_id',
                'users.nama_lengkap',
                'users.is_global',
                'users.perumahaan_id',
                'ubs.nama_ubs',
                'roles.name as role_name'
            )
            ->orderBy('users.nama_lengkap', 'asc')
            ->get();

        // 2. Fetch all KPI component sums for the selected year
        $kpiScores = DB::table('kpi_user')
            ->join('kpi_user_komponen', 'kpi_user.id', '=', 'kpi_user_komponen.kpi_user_id')
            ->where('kpi_user.tahun', $tahun)
            ->select(
                'kpi_user.user_id',
                'kpi_user.bulan',
                DB::raw('SUM(kpi_user_komponen.nilai_akhir) as total_nilai')
            )
            ->groupBy('kpi_user.user_id', 'kpi_user.bulan')
            ->get();

        $monthMap = [
            '1' => 'januari', '01' => 'januari', 'januari' => 'januari',
            '2' => 'februari', '02' => 'februari', 'februari' => 'februari', 'febuari' => 'februari',
            '3' => 'maret', '03' => 'maret', 'maret' => 'maret',
            '4' => 'april', '04' => 'april', 'april' => 'april',
            '5' => 'mei', '05' => 'mei', 'mei' => 'mei',
            '6' => 'juni', '06' => 'juni', 'juni' => 'juni',
            '7' => 'juli', '07' => 'juli', 'juli' => 'juli',
            '8' => 'agustus', '08' => 'agustus', 'agustus' => 'agustus',
            '9' => 'september', '09' => 'september', 'september' => 'september',
            '10' => 'oktober', 'oktober' => 'oktober',
            '11' => 'november', 'november' => 'november',
            '12' => 'desember', 'desember' => 'desember',
        ];

        // Map KPI scores: [user_id => [bulan_baku => total_nilai]]
        $scoresMap = [];
        foreach ($kpiScores as $score) {
            $rawBulan = strtolower(trim($score->bulan));
            $bulanBaku = $monthMap[$rawBulan] ?? null;
            if ($bulanBaku) {
                $scoresMap[$score->user_id][$bulanBaku] = (float) $score->total_nilai;
            }
        }

        $dashboardData = [];

        // 3. Build dashboard data grouped by Jabatan/Role
        foreach ($usersRaw as $userRow) {
            $jabatanName = $userRow->role_name ?? 'LAINNYA';

            $nama = $userRow->nama_lengkap;

            $userBulanScores = array_fill_keys(array_values(array_unique($monthMap)), null);

            if (isset($scoresMap[$userRow->user_id])) {
                foreach ($scoresMap[$userRow->user_id] as $bulanBaku => $scoreVal) {
                    $userBulanScores[$bulanBaku] = $scoreVal;
                }
            }

            if (!isset($dashboardData[$jabatanName])) {
                $dashboardData[$jabatanName] = [];
            }

            $dashboardData[$jabatanName][] = [
                'nama' => $nama,
                'jabatan' => $jabatanName,
                'bulan' => $userBulanScores
            ];
        }

        // 4. Calculate Quarter averages
        $quarters = [
            'q1' => ['januari', 'februari', 'maret'],
            'q2' => ['april', 'mei', 'juni'],
            'q3' => ['juli', 'agustus', 'september'],
            'q4' => ['oktober', 'november', 'desember'],
        ];

        foreach ($dashboardData as $jabatanName => &$users) {
            foreach ($users as &$data) {
                foreach ($quarters as $q => $months) {
                    $scores = [];
                    foreach ($months as $month) {
                        if (!is_null($data['bulan'][$month])) $scores[] = $data['bulan'][$month];
                    }
                    $data[$q] = count($scores) > 0 ? (array_sum($scores) / count($scores)) : null;
                }
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $sheet->mergeCells('A3:R3');
        $sheet->setCellValue('A3', 'Dashboard KPI ' . $tahun);
        $sheet->getStyle('A3:R3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFB2B2B2']
            ]
        ]);
        $sheet->getStyle('A3:R3')->applyFromArray($borderStyle);

        $headers = [
            'Nama Karyawan', 'Jabatan', 'Januari', 'Februari', 'Maret', 'AVG Q1',
            'April', 'Mei', 'Juni', 'AVG Q2', 'Juli', 'Agustus', 'September',
            'AVG Q3', 'Oktober', 'November', 'Desember', 'AVG Q4'
        ];

        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . '4', $header);
            $sheet->getColumnDimension($colIndex)->setAutoSize(true);
            $colIndex++;
        }

        $sheet->getStyle('A4:R4')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);

        $sheet->getStyle('F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00'); // Kuning
        $sheet->getStyle('J4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF00B050'); // Hijau
        $sheet->getStyle('N4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF8EA9DB'); // Biru
        $sheet->getStyle('R4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000'); // Merah (Teks putih)
        $sheet->getStyle('R4')->getFont()->getColor()->setARGB('FFFFFFFF'); // Teks Putih untuk Q4

        $sheet->getStyle('A4:R4')->applyFromArray($borderStyle);

        $rowNum = 5;
        foreach ($dashboardData as $jabatanName => $users) {
            // Add Jabatan group subheader in Excel
            $sheet->mergeCells("A{$rowNum}:R{$rowNum}");
            $sheet->setCellValue("A{$rowNum}", strtoupper($jabatanName));
            $sheet->getStyle("A{$rowNum}:R{$rowNum}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFEAEAEA']
                ]
            ]);
            $sheet->getStyle("A{$rowNum}:R{$rowNum}")->applyFromArray($borderStyle);
            $rowNum++;

            foreach ($users as $user) {
                $sheet->setCellValue('A' . $rowNum, $user['nama']);
                $sheet->setCellValue('B' . $rowNum, $user['jabatan']);

                $sheet->setCellValue('C' . $rowNum, $user['bulan']['januari']);
                $sheet->setCellValue('D' . $rowNum, $user['bulan']['februari']);
                $sheet->setCellValue('E' . $rowNum, $user['bulan']['maret']);
                $sheet->setCellValue('F' . $rowNum, $user['q1']);

                $sheet->setCellValue('G' . $rowNum, $user['bulan']['april']);
                $sheet->setCellValue('H' . $rowNum, $user['bulan']['mei']);
                $sheet->setCellValue('I' . $rowNum, $user['bulan']['juni']);
                $sheet->setCellValue('J' . $rowNum, $user['q2']);

                $sheet->setCellValue('K' . $rowNum, $user['bulan']['juli']);
                $sheet->setCellValue('L' . $rowNum, $user['bulan']['agustus']);
                $sheet->setCellValue('M' . $rowNum, $user['bulan']['september']);
                $sheet->setCellValue('N' . $rowNum, $user['q3']);

                $sheet->setCellValue('O' . $rowNum, $user['bulan']['oktober']);
                $sheet->setCellValue('P' . $rowNum, $user['bulan']['november']);
                $sheet->setCellValue('Q' . $rowNum, $user['bulan']['desember']);
                $sheet->setCellValue('R' . $rowNum, $user['q4']);

                $sheet->getStyle('F' . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
                $sheet->getStyle('J' . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF00B050');
                $sheet->getStyle('N' . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF8EA9DB');
                $sheet->getStyle('R' . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                $sheet->getStyle('R' . $rowNum)->getFont()->getColor()->setARGB('FFFFFFFF');

                $sheet->getStyle("F{$rowNum}:R{$rowNum}")->getNumberFormat()->setFormatCode('#,##0.00');

                $sheet->getStyle('C' . $rowNum . ':R' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $rowNum++;
            }
        }

        if ($rowNum > 5) {
            $sheet->getStyle('A4:R' . ($rowNum - 1))->applyFromArray($borderStyle);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = "Dashboard_KPI_{$tahun}.xlsx";

        if (ob_get_length()) ob_end_clean();

        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
