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
    protected $rowsUmum, $rowsPenyesuaian, $saldoAwal, $periode, $saldoAkhirUmum, $saldoAkhirTotal, $akun, $ubsName, $normalBalance;

    public function __construct($rowsUmum, $rowsPenyesuaian, $saldoAwal, $periode, $saldoAkhirUmum, $saldoAkhirTotal, $akun, $ubsName = 'HUB (Pusat)', $normalBalance = 'debit')
    {
        $this->rowsUmum = $rowsUmum;
        $this->rowsPenyesuaian = $rowsPenyesuaian;
        $this->saldoAwal = $saldoAwal;
        $this->periode = $periode;
        $this->saldoAkhirUmum = $saldoAkhirUmum;
        $this->saldoAkhirTotal = $saldoAkhirTotal;
        $this->akun = $akun;
        $this->ubsName = $ubsName;
        $this->normalBalance = $normalBalance;
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
        $isKredit = in_array(strtolower($this->normalBalance), ['kredit', 'credit', 'cr']);
        $isDebitBalance = $isKredit ? $this->saldoAwal < 0 : $this->saldoAwal > 0;
        $isKreditBalance = $isKredit ? $this->saldoAwal > 0 : $this->saldoAwal < 0;

        // Baris Saldo Awal
        $data[] = [
            '',
            'SALDO AWAL',
            $isDebitBalance ? abs($this->saldoAwal) : '',
            $isKreditBalance ? abs($this->saldoAwal) : '',
            $this->saldoAwal
        ];

        $isHub = $this->ubsName === 'HUB (Pusat)' || $this->ubsName === 'HUB';

        // 1. Transaksi Umum
        foreach ($this->rowsUmum as $row) {
            $data[] = [
                \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') . ($isHub && isset($row->ubs_abbr) ? "\n/ " . $row->ubs_abbr : ''),
                $row->keterangan . "\n(" . $row->nomor_jurnal . ")",
                $row->debit > 0 ? $row->debit : 0,
                $row->kredit > 0 ? $row->kredit : 0,
                $row->saldo
            ];
        }

        // 2. Baris Saldo Akhir Sebelum Penyesuaian
        $data[] = [
            '',
            'SALDO SEBELUM PENYESUAIAN',
            '',
            '',
            $this->saldoAkhirUmum
        ];

        // 3. Transaksi Penyesuaian
        if ($this->rowsPenyesuaian->count() > 0) {
            // Header Section Penyesuaian
            $data[] = [
                '---',
                '--- DATA JURNAL PENYESUAIAN ---',
                '---',
                '---',
                '---'
            ];

            foreach ($this->rowsPenyesuaian as $row) {
                $data[] = [
                    \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') . ($isHub && isset($row->ubs_abbr) ? "\n/ " . $row->ubs_abbr : ''),
                    $row->keterangan . "\n(" . $row->nomor_jurnal . ")",
                    $row->debit > 0 ? $row->debit : 0,
                    $row->kredit > 0 ? $row->kredit : 0,
                    $row->saldo
                ];
            }
        }

        // 4. Baris Saldo Akhir Total
        $data[] = [
            '',
            'SALDO AKHIR PERIODE (FINAL)',
            '',
            '',
            $this->saldoAkhirTotal
        ];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // 1. Judul & Info Laporan
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');

        $sheet->setCellValue('A1', 'LAPORAN BUKU BESAR - ' . strtoupper($this->ubsName));
        $sheet->setCellValue('A2', 'Akun: ' . ($this->akun->kode_akun ?? '') . ' - ' . ($this->akun->nama_akun ?? 'Semua Akun'));
        $sheet->setCellValue('A3', 'Periode: ' . ($this->periode->nama_periode ?? 'Semua Periode'));

        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Wrap Text
        $sheet->getStyle('A7:B' . $lastRow)->getAlignment()->setWrapText(true);

        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2F5597'));
        $sheet->getStyle('A2:A3')->getFont()->setItalic(true);

        // Header Tabel
        $sheet->getStyle('A6:E6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Accounting Format
        $currencyFormat = '_("Rp"* #,##0_);_("Rp"* -#,##0_);_("Rp"* "-"??_);_(@_)';
        $sheet->getStyle('C7:E' . $lastRow)->getNumberFormat()->setFormatCode($currencyFormat);

        // Borders
        $sheet->getStyle('A6:E' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D9D9D9');
        $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Bold untuk baris spesial
        for ($i = 7; $i <= $lastRow; $i++) {
            $val = $sheet->getCell('B' . $i)->getValue();
            
            if (in_array($val, ['SALDO AWAL', 'SALDO SEBELUM PENYESUAIAN', 'SALDO AKHIR PERIODE (FINAL)'])) {
                $sheet->getStyle("A$i:E$i")->getFont()->setBold(true);
                $sheet->getStyle("A$i:E$i")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
            }

            if (strpos($val, 'JURNAL PENYESUAIAN') !== false) {
                $sheet->getStyle("A$i:E$i")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $sheet->getStyle("A$i:E$i")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('555555');
                $sheet->getStyle("A$i:E$i")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        return [];
    }
}