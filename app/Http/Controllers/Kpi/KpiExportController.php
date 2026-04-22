<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiUser;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KpiExportController extends Controller
{
    public function exportById($id)
    {
        $kpi = KpiUser::with(['details', 'user'])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('KPI ' . substr($kpi->user->nama_lengkap, 0, 20));

        $row = 1;

        $headerData = [
            ['NAMA',    ': ' . $kpi->user->nama_lengkap],
            ['JABATAN', ': ' . ($kpi->user->getRoleNames()->implode(', ') ?: '-')],
            ['DIVISI',  ': -'],
            ['BULAN',   ': ' . date('F Y', mktime(0, 0, 0, $kpi->bulan, 1, $kpi->tahun))],
        ];

        foreach ($headerData as [$label, $value]) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->mergeCells("B{$row}:E{$row}");
            $sheet->setCellValue("B{$row}", $value);

            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:E{$row}")->getFont()->setName('Arial')->setSize(10);
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;
        }

        $row++;

        $headers = ['NO', 'KOMPONEN KPI', 'BOBOT (%)', 'SKOR', 'NILAI AKHIR'];
        $cols = ['A', 'B', 'C', 'D', 'E'];

        foreach ($headers as $i => $headerText) {
            $sheet->setCellValue($cols[$i] . $row, $headerText);
        }

        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
            'font' => ['bold' => true, 'name' => 'Arial', 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF3F3F3'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        $totalAkhir = 0;
        foreach ($kpi->details as $index => $detail) {
            $nilaiAkhir = (float) $detail->nilai_akhir;
            $totalAkhir += $nilaiAkhir;

            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $detail->nama_komponen);
            $sheet->setCellValue("C{$row}", $detail->bobot);
            $sheet->setCellValue("D{$row}", $detail->skor);
            $sheet->setCellValue("E{$row}", $nilaiAkhir);

            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'font'      => ['name' => 'Arial', 'size' => 10],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            foreach (['A', 'C', 'D', 'E'] as $centerCol) {
                $sheet->getStyle("{$centerCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $row++;
        }

        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL NILAI KPI');
        $sheet->setCellValue("C{$row}", 100);
        $sheet->setCellValue("D{$row}", '');
        $sheet->setCellValue("E{$row}", $totalAkhir);

        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
            'font' => ['bold' => true, 'name' => 'Arial', 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFD9EAF7'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $fileName = 'KPI_' . str_replace(' ', '_', $kpi->user->nama_lengkap) . '_' . $kpi->bulan . '_' . $kpi->tahun . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'kpi_ids'   => 'required|array|min:1',
            'kpi_ids.*' => 'exists:kpi_user,id',
            'bulan'     => 'nullable',
            'tahun'     => 'nullable',
        ]);

        $kpiList = KpiUser::with(['details', 'user'])
            ->whereIn('id', $request->kpi_ids)
            ->get();

        if ($kpiList->isEmpty()) {
            return back()->with('error', 'Tidak ada data KPI yang ditemukan.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data KPI Karyawan');

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(16);

        $row = 1;

        foreach ($kpiList as $kpi) {
            $headerData = [
                ['NAMA',    ': ' . $kpi->user->nama_lengkap],
                ['JABATAN', ': ' . ($kpi->user->getRoleNames()->implode(', ') ?: '-')],
                ['DIVISI',  ': -'],
                ['BULAN',   ': ' . date('F Y', mktime(0, 0, 0, $kpi->bulan, 1, $kpi->tahun))],
            ];

            foreach ($headerData as [$label, $value]) {
                $sheet->setCellValue("A{$row}", $label);
                $sheet->mergeCells("B{$row}:E{$row}");
                $sheet->setCellValue("B{$row}", $value);

                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:E{$row}")->getFont()->setName('Arial')->setSize(10);
                $sheet->getRowDimension($row)->setRowHeight(18);
                $row++;
            }

            $row++;

            $headers = ['NO', 'KOMPONEN KPI', 'BOBOT (%)', 'SKOR', 'NILAI AKHIR'];
            $cols = ['A', 'B', 'C', 'D', 'E'];

            foreach ($headers as $i => $headerText) {
                $sheet->setCellValue($cols[$i] . $row, $headerText);
            }

            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'font' => ['bold' => true, 'name' => 'Arial', 'size' => 10],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF3F3F3'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;

            $totalAkhir = 0;
            foreach ($kpi->details as $detailIndex => $detail) {
                $nilaiAkhir  = (float) $detail->nilai_akhir;
                $totalAkhir += $nilaiAkhir;

                $sheet->setCellValue("A{$row}", $detailIndex + 1);
                $sheet->setCellValue("B{$row}", $detail->nama_komponen);
                $sheet->setCellValue("C{$row}", $detail->bobot);
                $sheet->setCellValue("D{$row}", $detail->skor);
                $sheet->setCellValue("E{$row}", $nilaiAkhir);

                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'font'      => ['name' => 'Arial', 'size' => 10],
                    'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                ]);

                foreach (['A', 'C', 'D', 'E'] as $centerCol) {
                    $sheet->getStyle("{$centerCol}{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }
                $row++;
            }

            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("A{$row}", 'TOTAL NILAI KPI');
            $sheet->setCellValue("C{$row}", 100);
            $sheet->setCellValue("D{$row}", '');
            $sheet->setCellValue("E{$row}", $totalAkhir);

            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'font' => ['bold' => true, 'name' => 'Arial', 'size' => 10],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD9EAF7'], // Biru muda untuk total
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                ],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $row += 3;
        }

        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $bulan   = (int)$request->bulan ?? date('n');
        $tahun   = (int)$request->tahun ?? date('Y');
        $periode = date('F_Y', mktime(0, 0, 0, $bulan, 1, $tahun));
        $filename = "Rekap_KPI_Karyawan_{$periode}.xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
