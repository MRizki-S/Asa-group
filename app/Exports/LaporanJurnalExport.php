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

class LaporanJurnalExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell
{
    protected $rows, $totalDebit, $totalKredit, $filters, $periode;

    public function __construct($rows, $totalDebit, $totalKredit, $filters, $periode)
    {
        $this->rows = $rows;
        $this->totalDebit = $totalDebit;
        $this->totalKredit = $totalKredit;
        $this->filters = $filters;
        $this->periode = $periode;
    }

    public function startCell(): string
    {
        return 'A4'; // Data dimulai dari baris 4, baris 1-3 untuk judul
    }

    public function headings(): array
    {
        return ['Tanggal', 'Kode Akun', 'Nama Akun', 'Debit', 'Kredit', 'Keterangan'];
    }

    public function array(): array
    {
        $data = [];
        $lastJurnalId = null;

        foreach ($this->rows as $row) {
            $data[] = [
                $row->jurnal_id !== $lastJurnalId ? $row->tanggal->format('d-m-Y') : '',
                $row->kode_akun,
                $row->nama_akun,
                $row->debit > 0 ? $row->debit : 0,
                $row->kredit > 0 ? $row->kredit : 0,
                $row->jurnal_id !== $lastJurnalId ? $row->keterangan : '',
            ];
            $lastJurnalId = $row->jurnal_id;
        }

        $data[] = ['', '', 'TOTAL', $this->totalDebit, $this->totalKredit, $this->totalDebit == $this->totalKredit ? 'SEIMBANG' : 'TIDAK SEIMBANG'];
        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // 1. Tambahkan Judul Laporan
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'LAPORAN JURNAL UMUM');

        // 2. Tambahkan Info Periode/Filter
        $sheet->mergeCells('A2:F2');
        $subTitle = "Periode: " . ($this->periode->nama_periode ?? '-');
        if(!empty($this->filters['tanggalStart'])) {
            $subTitle = "Tanggal: " . $this->filters['tanggalStart'] . " s/d " . ($this->filters['tanggalEnd'] ?? 'Sekarang');
        }
        $sheet->setCellValue('A2', $subTitle);

        $lastRow = $sheet->getHighestRow();

        // Styling Judul
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Styling Header Tabel (Baris 4)
        $sheet->getStyle('A4:F4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Format Angka & Border untuk isi tabel
        $sheet->getStyle('A4:F' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('D5:E' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');

        // Styling Baris Total
        $sheet->getStyle('A' . $lastRow . ':F' . $lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']]
        ]);

        $lastRow = $sheet->getHighestRow();

    // Mengatur kolom B dari baris 4 sampai baris terakhir agar rata kiri
    $sheet->getStyle('B4:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    $sheet->getStyle('B5:B' . $lastRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

    return [];
    }
}
