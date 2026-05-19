<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class NeracaSaldoExport implements FromArray, WithStyles, ShouldAutoSize, WithCustomStartCell
{
    protected $rows, $tanggalMulai, $tanggalSelesai, $labelPeriode, $ubsName;

    public function __construct($rows, $tanggalMulai, $tanggalSelesai, $labelPeriode, $ubsName)
    {
        $this->rows = $rows;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->labelPeriode = $labelPeriode;
        $this->ubsName = $ubsName;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function array(): array
    {
        $data = [];
        $totals = array_fill(0, 18, 0);

        foreach ($this->rows as $row) {
            $rowValues = [
                $row->ns_awal_sa_debit,   $row->ns_awal_sa_kredit,
                $row->ns_awal_mut_debit,  $row->ns_awal_mut_kredit,
                $row->ns_awal_sak_debit,  $row->ns_awal_sak_kredit,

                $row->ns_adj_sa_debit,    $row->ns_adj_sa_kredit,
                $row->ns_adj_mut_debit,   $row->ns_adj_mut_kredit,
                $row->ns_adj_sak_debit,   $row->ns_adj_sak_kredit,

                $row->ns_akhir_sa_debit,  $row->ns_akhir_sa_kredit,
                $row->ns_akhir_mut_debit, $row->ns_akhir_mut_kredit,
                $row->ns_akhir_sak_debit, $row->ns_akhir_sak_kredit
            ];

            $data[] = array_merge([$row->kode_akun, $row->nama_akun], $rowValues);

            foreach ($rowValues as $idx => $val) {
                $totals[$idx] += $val;
            }
        }

        // Row Total
        $data[] = array_merge(['', 'TOTAL KESELURUHAN'], $totals);

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // 1. JUDUL
        $sheet->mergeCells('A1:T1');
        $sheet->mergeCells('A2:T2');
        $sheet->mergeCells('A3:T3');

        $sheet->setCellValue('A1', 'NERACA SALDO (DETAIL PENYESUAIAN) - ' . strtoupper($this->ubsName));
        $sheet->setCellValue('A2', 'Periode: ' . ($this->labelPeriode ?? '-'));
        $sheet->setCellValue('A3', \Carbon\Carbon::parse($this->tanggalMulai)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($this->tanggalSelesai)->format('d M Y'));

        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->setColor(new Color('2F5597'));
        $sheet->getStyle('A2:A3')->getFont()->setItalic(true);

        // 2. NESTED HEADERS (Row 5, 6, 7)
        // Row 5: Main Sections
        $sheet->setCellValue('C5', 'NERACA SALDO (SEBELUM PENYESUAIAN)')->mergeCells('C5:H5');
        $sheet->setCellValue('I5', 'PROSES PENYESUAIAN (JURNAL JP)')->mergeCells('I5:N5');
        $sheet->setCellValue('O5', 'NERACA SALDO AKHIR (FINAL)')->mergeCells('O5:T5');

        $sheet->getStyle('C5:T5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C5:H5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
        $sheet->getStyle('I5:N5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E7E6E6');
        $sheet->getStyle('O5:T5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');

        // Row 6: Sub Concepts
        $sheet->setCellValue('A6', 'KODE')->mergeCells('A6:A7');
        $sheet->setCellValue('B6', 'NAMA AKUN')->mergeCells('B6:B7');

        $concepts = ['Saldo Awal', 'Mutasi', 'Saldo Akhir'];
        $cols = ['C', 'E', 'G', 'I', 'K', 'M', 'O', 'Q', 'S'];
        $idx = 0;
        foreach (['C', 'I', 'O'] as $startCol) {
            foreach ($concepts as $concept) {
                $c = $cols[$idx++];
                $nextC = chr(ord($c) + 1);
                $sheet->setCellValue($c . '6', $concept)->mergeCells($c . '6:' . $nextC . '6');
            }
        }

        // Row 7: D/K
        for ($i = ord('C'); $i <= ord('T'); $i++) {
            $col = chr($i);
            $sheet->setCellValue($col . '7', ($i % 2 !== 0) ? 'DEBIT' : 'KREDIT');
        }

        // Styling full header range
        $sheet->getStyle('A5:T7')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet->getStyle('A6:B7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2F5597');
        $sheet->getStyle('A6:B7')->getFont()->setColor(new Color('FFFFFF'));

        // 3. DATA STYLING
        $accFormat = '#,##0;[Red]-#,##0;""';
        $sheet->getStyle('C8:T' . $lastRow)->getNumberFormat()->setFormatCode($accFormat);

        // Zebra Striping & Alignment
        for ($i = 8; $i <= $lastRow; $i++) {
            if ($i % 2 == 0 && $i < $lastRow) {
                $sheet->getStyle("A$i:T$i")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9F9F9');
            }
        }
        $sheet->getStyle('A8:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('C8:T' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Highlight Final Result columns (O-T)
        $sheet->getStyle('O8:T' . ($lastRow - 1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F9FF');

        // 4. BORDER & TOTAL
        // Full Table Border
        $sheet->getStyle('A5:T' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('BFBFBF');

        // Total Row Styling
        $sheet->getStyle('A' . $lastRow . ':T' . $lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DDEBF7']],
        ]);
        $sheet->getStyle('A' . $lastRow . ':T' . $lastRow)->getBorders()->getTop()->setBorderStyle(Border::BORDER_DOUBLE);

        $sheet->freezePane('C8');

        return [];
    }
}