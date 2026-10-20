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

class StockBarangExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell
{
    protected $stocks;
    protected $selectedDbUbs;
    protected $titleGudang;
    protected $cariMaterial;

    public function __construct($stocks, $selectedDbUbs, $titleGudang, $cariMaterial = null)
    {
        $this->stocks = $stocks;
        $this->selectedDbUbs = $selectedDbUbs;
        $this->titleGudang = $titleGudang;
        $this->cariMaterial = $cariMaterial;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        $headers = [
            'KODE BARANG',
            'NAMA BARANG',
            'SATUAN',
        ];

        if ($this->selectedDbUbs && $this->selectedDbUbs->count() > 1) {
            foreach ($this->selectedDbUbs as $ubs) {
                $headers[] = 'STOK ' . strtoupper($ubs->kode_ubs);
            }
            $headers[] = 'TOTAL STOK';
        } elseif ($this->selectedDbUbs && $this->selectedDbUbs->count() === 1) {
            $singleUbs = $this->selectedDbUbs->first();
            $headers[] = 'STOK (' . strtoupper($singleUbs->kode_ubs) . ')';
            $headers[] = 'MINIMAL STOK';
        } else {
            $headers[] = 'TOTAL STOK';
        }

        return $headers;
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->stocks as $stock) {
            $defaultKonversi = $stock->satuanKonversi->where('is_default', true)->first();
            
            if ($defaultKonversi) {
                $satuanNama = $defaultKonversi->satuan->nama ?? 'Unknown';
                $konversiRate = (float)$defaultKonversi->konversi_ke_base;
            } else {
                $satuanNama = $stock->baseUnit->nama ?? '-';
                $konversiRate = 1;
            }

            if ($konversiRate <= 0) {
                $konversiRate = 1;
            }

            $row = [
                $stock->kode_barang,
                $stock->nama_barang,
                $satuanNama,
            ];

            if ($this->selectedDbUbs && $this->selectedDbUbs->count() > 1) {
                foreach ($this->selectedDbUbs as $ubs) {
                    $s = $stock->stock->firstWhere('ubs_id', $ubs->id);
                    $qty = $s ? (float)$s->jumlah_stock : 0;
                    $row[] = $this->formatNumber($qty / $konversiRate);
                }
                $totalQty = (float)$stock->stock->sum('jumlah_stock');
                $row[] = $this->formatNumber($totalQty / $konversiRate);
            } elseif ($this->selectedDbUbs && $this->selectedDbUbs->count() === 1) {
                $singleUbs = $this->selectedDbUbs->first();
                $s = $stock->stock->firstWhere('ubs_id', $singleUbs->id);
                $qty = $s ? (float)$s->jumlah_stock : 0;
                $minQty = $s ? (float)$s->minimal_stock : 0;
                $row[] = $this->formatNumber($qty / $konversiRate);
                $row[] = $this->formatNumber($minQty / $konversiRate);
            } else {
                $totalQty = (float)$stock->stock->sum('jumlah_stock');
                $row[] = $this->formatNumber($totalQty / $konversiRate);
            }

            $data[] = $row;
        }

        return $data;
    }

    private function formatNumber($val): string
    {
        $formatted = number_format((float)$val, 2, ',', '.');
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, ',');
        return $formatted ?: '0';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // ===== JUDUL =====
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->mergeCells("A3:{$lastColumn}3");

        $sheet->setCellValue('A1', 'LAPORAN STOCK BARANG');
        $sheet->setCellValue('A2', 'Gudang: ' . $this->titleGudang);

        $subTitle = 'Tanggal: ' . now()->format('d-m-Y H:i');
        if (!empty($this->cariMaterial)) {
            $subTitle .= ' | Filter Pencarian: "' . $this->cariMaterial . '"';
        }
        $sheet->setCellValue('A3', $subTitle);

        $sheet->getStyle("A1:A3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A1')->getFont()->setSize(15)->setBold(true);
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true);

        // ===== HEADER =====
        $sheet->getStyle("A6:{$lastColumn}6")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        if ($lastRow >= 7) {
            // ===== ZEBRA =====
            for ($i = 7; $i <= $lastRow; $i++) {
                if ($i % 2 == 0) {
                    $sheet->getStyle("A$i:{$lastColumn}$i")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F9F9F9');
                }
            }

            // ===== BORDER =====
            $sheet->getStyle("A6:{$lastColumn}{$lastRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            // ===== ALIGN =====
            // Kolom A (Kode) & C (Satuan): Center
            $sheet->getStyle("A7:A{$lastRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("C7:C{$lastRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Kolom D s.d. lastColumn (Angka stok): Right
            $sheet->getStyle("D7:{$lastColumn}{$lastRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        } else {
            $sheet->getStyle("A6:{$lastColumn}6")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        }

        return [];
    }
}