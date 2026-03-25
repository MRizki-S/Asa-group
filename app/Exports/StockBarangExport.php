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
    protected $stocks, $ubsData, $titleGudang, $ubsId, $cariMaterial;

    public function __construct($stocks, $ubsData, $titleGudang, $ubsId, $cariMaterial)
    {
        $this->stocks = $stocks;
        $this->ubsData = $ubsData;
        $this->titleGudang = $titleGudang;
        $this->ubsId = $ubsId;
        $this->cariMaterial = $cariMaterial;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        $stockHeader = $this->ubsId === 'all' ? 'TOTAL STOK' : 'STOK';

        return [
            'KODE BARANG',
            'NAMA BARANG',
            'SATUAN',
            $stockHeader
        ];
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
            
            $stockVal = 0;
            if ($this->ubsId === 'all') {
                $stockVal = $stock->stock->sum('jumlah_stock');
            } elseif ($this->ubsId === 'hub') {
                $stockVal = $stock->stockHub->jumlah_stock ?? 0;
            } else {
                $s = $stock->stock->first();
                $stockVal = $s->jumlah_stock ?? 0;
            }
            
            $stockDisplay = $stockVal / $konversiRate;
            
            // Format angka seperti di UI (hilangkan ,00)
            $formattedStock = number_format($stockDisplay, 2, ',', '.');
            $formattedStock = rtrim($formattedStock, '0');
            $formattedStock = rtrim($formattedStock, ',');

            $data[] = [
                $stock->kode_barang,
                $stock->nama_barang,
                $satuanNama,
                $formattedStock
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // ===== JUDUL =====
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');

        $sheet->setCellValue('A1', 'LAPORAN STOCK BARANG');
        $sheet->setCellValue('A2', 'Gudang: ' . $this->titleGudang);
        $sheet->setCellValue('A3', 'Tanggal: ' . now()->format('d-m-Y H:i'));

        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);

        // ===== HEADER =====
        $sheet->getStyle('A6:D6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // ===== ZEBRA =====
        for ($i = 7; $i <= $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle("A$i:D$i")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F9F9F9');
            }
        }

        // ===== BORDER =====
        $sheet->getStyle('A6:D' . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // ===== ALIGN =====
        $sheet->getStyle('D7:D' . $lastRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }
}