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
        $devisiId = $request->input('devisi');

        // 1. Fetch all employees (karyawan) (with optional role/jabatan filter, excluding Superadmin)
        $usersRaw = DB::table('karyawan')
            ->leftJoin('ubs', 'karyawan.ubs_id', '=', 'ubs.id')
            ->leftJoin('roles', 'karyawan.role_id', '=', 'roles.id')
            ->leftJoin('devisi', 'roles.devisi_id', '=', 'devisi.id')
            ->where(function ($query) {
                $query->where('roles.name', '!=', 'Superadmin')
                      ->orWhereNull('roles.name');
            })
            ->when($jabatanId, function ($query, $jabatanId) {
                return $query->where('roles.id', $jabatanId);
            })
            ->when($devisiId, function ($query, $devisiId) {
                return $query->where('roles.devisi_id', $devisiId);
            })
            ->select(
                'karyawan.id as user_id',
                'karyawan.nama as nama_lengkap',
                'karyawan.ubs_id',
                'ubs.nama_ubs',
                'roles.name as role_name',
                'devisi.nama_devisi'
            )
            ->orderBy('karyawan.nama', 'asc')
            ->get();

        // 2. Fetch all KPI component sums for the selected year
        $kpiScores = DB::table('kpi_user')
            ->join('kpi_user_komponen', 'kpi_user.id', '=', 'kpi_user_komponen.kpi_user_id')
            ->where('kpi_user.tahun', $tahun)
            ->where('kpi_user.status', 'final')
            ->select(
                'kpi_user.karyawan_id as user_id',
                'kpi_user.bulan',
                DB::raw('SUM(kpi_user_komponen.nilai_akhir) as total_nilai')
            )
            ->groupBy('kpi_user.karyawan_id', 'kpi_user.bulan')
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
                $scoresMap[$score->user_id][$bulanBaku] = round((float) $score->total_nilai);
            }
        }

        $roles = DB::table('roles')->select('id', 'name')->orderBy('name', 'asc')->get();
        $devisis = DB::table('devisi')->select('id', 'nama_devisi')->orderBy('nama_devisi', 'asc')->get();

        $dashboardData = [];

        // 3. Build dashboard structure grouped by Devisi
        foreach ($usersRaw as $userRow) {
            // Determine Devisi name group
            $devisiName = $userRow->nama_devisi ?? 'LAINNYA';

            $nama = $userRow->nama_lengkap;

            // Initialize month scores with null
            $userBulanScores = array_fill_keys(array_values(array_unique($monthMap)), null);

            // Populate from scoresMap if exists
            if (isset($scoresMap[$userRow->user_id])) {
                foreach ($scoresMap[$userRow->user_id] as $bulanBaku => $scoreVal) {
                    $userBulanScores[$bulanBaku] = $scoreVal;
                }
            }

            if (!isset($dashboardData[$devisiName])) {
                $dashboardData[$devisiName] = [];
            }

            $dashboardData[$devisiName][] = [
                'nama' => $nama,
                'jabatan' => $userRow->role_name ?? 'LAINNYA',
                'bulan' => $userBulanScores
            ];
        }

        // Sort devisi names alphabetically, keeping LAINNYA at the end
        uksort($dashboardData, function ($a, $b) {
            if ($a === 'LAINNYA') return 1;
            if ($b === 'LAINNYA') return -1;
            return strcasecmp($a, $b);
        });

        // 4. Calculate Quarter averages
        $quarters = [
            'q1' => ['januari', 'februari', 'maret'],
            'q2' => ['april', 'mei', 'juni'],
            'q3' => ['juli', 'agustus', 'september'],
            'q4' => ['oktober', 'november', 'desember'],
        ];

        foreach ($dashboardData as $devisiName => &$users) {
            foreach ($users as &$data) {
                foreach ($quarters as $q => $months) {
                    $scores = [];
                    foreach ($months as $month) {
                        if (!is_null($data['bulan'][$month])) {
                            $scores[] = $data['bulan'][$month];
                        }
                    }
                    $data[$q] = count($scores) > 0 ? round(array_sum($scores) / count($scores)) : null;
                }
            }
            // Default sort users in devisi by Q3 score descending (highest first, null at bottom)
            usort($users, function ($a, $b) {
                $valA = $a['q3'];
                $valB = $b['q3'];
                if ($valA === null && $valB === null) return 0;
                if ($valA === null) return 1;
                if ($valB === null) return -1;
                if ($valA == $valB) return 0;
                return ($valA > $valB) ? -1 : 1;
            });
        }

        unset($users);
        unset($data);

        return view('kpi.dashboard.index', [
            'tahun' => $tahun,
            'pilihanTahun' => $pilihanTahun,
            'roles' => $roles,
            'devisis' => $devisis,
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
        $devisiId = $request->input('devisi');

        // 1. Fetch all employees (karyawan) (with optional role/jabatan filter, excluding Superadmin)
        $usersRaw = DB::table('karyawan')
            ->leftJoin('ubs', 'karyawan.ubs_id', '=', 'ubs.id')
            ->leftJoin('roles', 'karyawan.role_id', '=', 'roles.id')
            ->leftJoin('devisi', 'roles.devisi_id', '=', 'devisi.id')
            ->where(function ($query) {
                $query->where('roles.name', '!=', 'Superadmin')
                      ->orWhereNull('roles.name');
            })
            ->when($jabatanId, function ($query, $jabatanId) {
                return $query->where('roles.id', $jabatanId);
            })
            ->when($devisiId, function ($query, $devisiId) {
                return $query->where('roles.devisi_id', $devisiId);
            })
            ->select(
                'karyawan.id as user_id',
                'karyawan.nama as nama_lengkap',
                'karyawan.ubs_id',
                'ubs.nama_ubs',
                'roles.name as role_name',
                'devisi.nama_devisi'
            )
            ->orderBy('karyawan.nama', 'asc')
            ->get();

        // 2. Fetch all KPI component sums for the selected year
        $kpiScores = DB::table('kpi_user')
            ->join('kpi_user_komponen', 'kpi_user.id', '=', 'kpi_user_komponen.kpi_user_id')
            ->where('kpi_user.tahun', $tahun)
            ->where('kpi_user.status', 'final')
            ->select(
                'kpi_user.karyawan_id as user_id',
                'kpi_user.bulan',
                DB::raw('SUM(kpi_user_komponen.nilai_akhir) as total_nilai')
            )
            ->groupBy('kpi_user.karyawan_id', 'kpi_user.bulan')
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
                $scoresMap[$score->user_id][$bulanBaku] = round((float) $score->total_nilai);
            }
        }

        $dashboardData = [];

        // 3. Build dashboard data grouped by Devisi
        foreach ($usersRaw as $userRow) {
            $devisiName = $userRow->nama_devisi ?? 'LAINNYA';

            $nama = $userRow->nama_lengkap;

            $userBulanScores = array_fill_keys(array_values(array_unique($monthMap)), null);

            if (isset($scoresMap[$userRow->user_id])) {
                foreach ($scoresMap[$userRow->user_id] as $bulanBaku => $scoreVal) {
                    $userBulanScores[$bulanBaku] = $scoreVal;
                }
            }

            if (!isset($dashboardData[$devisiName])) {
                $dashboardData[$devisiName] = [];
            }

            $dashboardData[$devisiName][] = [
                'nama' => $nama,
                'jabatan' => $userRow->role_name ?? 'LAINNYA',
                'bulan' => $userBulanScores
            ];
        }

        // Sort devisi names alphabetically, keeping LAINNYA at the end
        uksort($dashboardData, function ($a, $b) {
            if ($a === 'LAINNYA') return 1;
            if ($b === 'LAINNYA') return -1;
            return strcasecmp($a, $b);
        });

        // 4. Calculate Quarter averages
        $quarters = [
            'q1' => ['januari', 'februari', 'maret'],
            'q2' => ['april', 'mei', 'juni'],
            'q3' => ['juli', 'agustus', 'september'],
            'q4' => ['oktober', 'november', 'desember'],
        ];

        foreach ($dashboardData as $devisiName => &$users) {
            foreach ($users as &$data) {
                foreach ($quarters as $q => $months) {
                    $scores = [];
                    foreach ($months as $month) {
                        if (!is_null($data['bulan'][$month])) $scores[] = $data['bulan'][$month];
                    }
                    $data[$q] = count($scores) > 0 ? round(array_sum($scores) / count($scores)) : null;
                }
            }
            // Default sort users in devisi by Q3 score descending (highest first, null at bottom)
            usort($users, function ($a, $b) {
                $valA = $a['q3'];
                $valB = $b['q3'];
                if ($valA === null && $valB === null) return 0;
                if ($valA === null) return 1;
                if ($valB === null) return -1;
                if ($valA == $valB) return 0;
                return ($valA > $valB) ? -1 : 1;
            });
        }
        unset($users);
        unset($data);

        // Sort devisi names alphabetically, but ensure 'LAINNYA' is always at the very bottom
        uksort($dashboardData, function ($a, $b) {
            if ($a === 'LAINNYA') return 1;
            if ($b === 'LAINNYA') return -1;
            return strcasecmp($a, $b);
        });

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

        $is2026 = ($tahun == 2026);
        $lastColumn = $is2026 ? 'J' : 'R';

        $sheet->mergeCells("A3:{$lastColumn}3");
        $sheet->setCellValue('A3', 'Dashboard KPI ' . $tahun);
        $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
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
        $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray($borderStyle);

        if ($is2026) {
            $headers = [
                'Nama Karyawan', 'Jabatan', 'Juli', 'Agustus', 'September',
                'AVG Q3', 'Oktober', 'November', 'Desember', 'AVG Q4'
            ];
        } else {
            $headers = [
                'Nama Karyawan', 'Jabatan', 'Januari', 'Februari', 'Maret', 'AVG Q1',
                'April', 'Mei', 'Juni', 'AVG Q2', 'Juli', 'Agustus', 'September',
                'AVG Q3', 'Oktober', 'November', 'Desember', 'AVG Q4'
            ];
        }

        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . '4', $header);
            $sheet->getColumnDimension($colIndex)->setAutoSize(true);
            $colIndex++;
        }

        $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);

        if ($is2026) {
            $sheet->getStyle('F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF8EA9DB'); // Biru
            $sheet->getStyle('J4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000'); // Merah (Teks putih)
            $sheet->getStyle('J4')->getFont()->getColor()->setARGB('FFFFFFFF');
        } else {
            $sheet->getStyle('F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00'); // Kuning
            $sheet->getStyle('J4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF00B050'); // Hijau
            $sheet->getStyle('N4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF8EA9DB'); // Biru
            $sheet->getStyle('R4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000'); // Merah (Teks putih)
            $sheet->getStyle('R4')->getFont()->getColor()->setARGB('FFFFFFFF'); // Teks Putih untuk Q4
        }

        $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray($borderStyle);

        $rowNum = 5;
        foreach ($dashboardData as $devisiName => $devisiUsers) {
            // Add Devisi group subheader in Excel
            $sheet->mergeCells("A{$rowNum}:{$lastColumn}{$rowNum}");
            $sheet->setCellValue("A{$rowNum}", strtoupper($devisiName));
            $sheet->getStyle("A{$rowNum}:{$lastColumn}{$rowNum}")->applyFromArray([
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
            $sheet->getStyle("A{$rowNum}:{$lastColumn}{$rowNum}")->applyFromArray($borderStyle);
            $rowNum++;

            foreach ($devisiUsers as $user) {
                $sheet->setCellValue('A' . $rowNum, $user['nama']);
                $sheet->setCellValue('B' . $rowNum, $user['jabatan']);

                if ($is2026) {
                    $sheet->setCellValue('C' . $rowNum, $user['bulan']['juli']);
                    $sheet->setCellValue('D' . $rowNum, $user['bulan']['agustus']);
                    $sheet->setCellValue('E' . $rowNum, $user['bulan']['september']);
                    $sheet->setCellValue('F' . $rowNum, $user['q3']);

                    $sheet->setCellValue('G' . $rowNum, $user['bulan']['oktober']);
                    $sheet->setCellValue('H' . $rowNum, $user['bulan']['november']);
                    $sheet->setCellValue('I' . $rowNum, $user['bulan']['desember']);
                    $sheet->setCellValue('J' . $rowNum, $user['q4']);

                    $sheet->getStyle('F' . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF8EA9DB');
                    $sheet->getStyle('J' . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
                    $sheet->getStyle('J' . $rowNum)->getFont()->getColor()->setARGB('FFFFFFFF');
                } else {
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
                }

                $sheet->getStyle("C{$rowNum}:{$lastColumn}{$rowNum}")->getNumberFormat()->setFormatCode('#,##0.##');

                $sheet->getStyle('C' . $rowNum . ':' . $lastColumn . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $rowNum++;
            }
        }

        if ($rowNum > 5) {
            $sheet->getStyle("A4:{$lastColumn}" . ($rowNum - 1))->applyFromArray($borderStyle);
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
