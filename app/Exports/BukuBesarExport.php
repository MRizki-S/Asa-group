<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BukuBesarExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell
{
    protected $rows, $saldoAwal, $periode, $saldoAkhir, $akun;

    public function __construct($rows, $saldoAwal, $periode, $saldoAkhir, $akun)
    {
        $this->rows = $rows;
        $this->saldoAwal = $saldoAwal;
        $this->periode = $periode;
        $this->saldoAkhir = $saldoAkhir;
        $this->akun = $akun;
    }

    public function startCell(): string
    {
        return 'A6'; // Data tabel dimulai dari baris 6
    }

    public function headings(): array
    {
        return ['TANGGAL', 'KETERANGAN', 'DEBIT', 'KREDIT', 'SALDO'];
    }

    public function array(): array
    {
        $data = [];

        // Baris Saldo Awal
        $data[] = [
            '',
            'SALDO AWAL',
            '',
            '',
            $this->saldoAwal
        ];

        $currentSaldo = $this->saldoAwal;

        foreach ($this->rows as $row) {
            $currentSaldo += ($row->debit - $row->kredit);
            $data[] = [
                \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y'),
                $row->keterangan,
                $row->debit > 0 ? $row->debit : 0,
                $row->kredit > 0 ? $row->kredit : 0,
                $currentSaldo
            ];
        }

        // Baris Saldo Akhir
        $data[] = [
            '',
            'SALDO AKHIR',
            '',
            '',
            $this->saldoAkhir
        ];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // 1. Judul & Info Laporan (Posisikan di Tengah)
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');

        $sheet->setCellValue('A1', 'LAPORAN BUKU BESAR');
        $sheet->setCellValue('A2', 'Akun: ' . ($this->akun->kode_akun ?? '') . ' - ' . ($this->akun->nama_akun ?? 'Semua Akun'));
        $sheet->setCellValue('A3', 'Periode: ' . ($this->periode->nama_periode ?? 'Semua Periode'));

        // Styling Judul agar Rata Tengah (Center)  
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style Font Judul (Tetap Bold untuk Baris 1)
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2F5597'));
        $sheet->getStyle('A2:A3')->getFont()->setItalic(true);

        // --- SISA STYLE BERIKUTNYA TETAP SAMA ---

        // 2. Header Tabel (Baris 6)
        $sheet->getStyle('A6:E6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // 3. Zebra Striping
        for ($i = 7; $i <= $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle("A$i:E$i")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9F9F9');
            }
        }

        // 4. Format Accounting (Kolom C, D, E)
        $currencyFormat = '_("Rp"* #,##0_);_("Rp"* (#,##0);_("Rp"* "-"??_);_(@_)';
        $sheet->getStyle('C7:E' . $lastRow)->getNumberFormat()->setFormatCode($currencyFormat);

        // 5. Border & Alignment Data
        $sheet->getStyle('A6:E' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D9D9D9');
        $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 6. Bold untuk Saldo Awal dan Akhir
        $sheet->getStyle('A7:E7')->getFont()->setBold(true);
        $sheet->getStyle('A' . $lastRow . ':E' . $lastRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $lastRow . ':E' . $lastRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');

        return [];
    }
}