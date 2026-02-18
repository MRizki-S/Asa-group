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
    protected $rows, $tanggalMulai, $tanggalSelesai, $labelPeriode;

    public function __construct($rows, $tanggalMulai, $tanggalSelesai, $labelPeriode)
    {
        $this->rows = $rows;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->labelPeriode = $labelPeriode;
    }

    public function startCell(): string
    {
        return 'A6'; // Tabel dimulai dari baris 6 (karena baris 6 & 7 dipakai header bertingkat)
    }

    public function array(): array
    {
        $data = [];
        $totalSA_D = 0; $totalSA_K = 0;
        $totalM_D = 0;  $totalM_K = 0;
        $totalSK_D = 0; $totalSK_K = 0;

        foreach ($this->rows as $row) {
            $saD = $row->saldo_awal > 0 ? $row->saldo_awal : 0;
            $saK = $row->saldo_awal < 0 ? abs($row->saldo_awal) : 0;
            $skD = $row->saldo_akhir > 0 ? $row->saldo_akhir : 0;
            $skK = $row->saldo_akhir < 0 ? abs($row->saldo_akhir) : 0;

            $data[] = [
                $row->kode_akun,
                $row->nama_akun,
                $saD, $saK,
                $row->mutasi_debit, $row->mutasi_kredit,
                $skD, $skK
            ];

            $totalSA_D += $saD; $totalSA_K += $saK;
            $totalM_D += $row->mutasi_debit; $totalM_K += $row->mutasi_kredit;
            $totalSK_D += $skD; $totalSK_K += $skK;
        }

        // Row Total
        $data[] = [
            '', 'TOTAL AKHIR',
            $totalSA_D, $totalSA_K,
            $totalM_D, $totalM_K,
            $totalSK_D, $totalSK_K
        ];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // 1. JUDUL & PERIODE (Header Atas)
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:H3');

        $sheet->setCellValue('A1', 'NERACA SALDO');
        $sheet->setCellValue('A2', 'Periode: ' . ($this->labelPeriode ?? '-'));
        $sheet->setCellValue('A3', \Carbon\Carbon::parse($this->tanggalMulai)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($this->tanggalSelesai)->format('d M Y'));

        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->setColor(new Color('2F5597'));
        $sheet->getStyle('A2:A3')->getFont()->setItalic(true);

        // 2. NESTED HEADER (Baris 6 & 7)
        // Header Row 1
        $sheet->setCellValue('A6', 'KODE AKUN')->mergeCells('A6:A7');
        $sheet->setCellValue('B6', 'NAMA AKUN')->mergeCells('B6:B7');
        $sheet->setCellValue('C6', 'SALDO AWAL')->mergeCells('C6:D6');
        $sheet->setCellValue('E6', 'MUTASI')->mergeCells('E6:F6');
        $sheet->setCellValue('G6', 'SALDO AKHIR')->mergeCells('G6:H6');

        // Header Row 2
        $sheet->setCellValue('C7', 'DEBIT');
        $sheet->setCellValue('D7', 'KREDIT');
        $sheet->setCellValue('E7', 'DEBIT');
        $sheet->setCellValue('F7', 'KREDIT');
        $sheet->setCellValue('G7', 'DEBIT');
        $sheet->setCellValue('H7', 'KREDIT');

        // Styling Header (Warna & Alignment)
        $sheet->getStyle('A6:H7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]]
        ]);

        // 3. FORMAT DATA
        // Zebra Striping
        for ($i = 8; $i < $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle("A$i:H$i")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9F9F9');
            }
        }

        // Format Accounting (Kolom C sampai H)
        $accFormat = '"Rp" #,##0;-"Rp" #,##0;"";@';

        $sheet->getStyle('C8:H' . $lastRow)->getNumberFormat()->setFormatCode($accFormat);
        
        // Kode Akun sebagai Text
        $sheet->getStyle('A8:A' . $lastRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // 4. BORDER & TOTAL
        $sheet->getStyle('A6:H' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D9D9D9');
        
        $sheet->getStyle('A' . $lastRow . ':H' . $lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DDEBF7']],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_DOUBLE]]
        ]);

        // 5. FREEZE PANES (Header tetap terlihat)
        $sheet->freezePane('A8');

        return [];
    }
}