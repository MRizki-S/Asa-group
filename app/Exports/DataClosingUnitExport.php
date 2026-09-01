<?php

namespace App\Exports;

use App\Models\PemesananUnit;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DataClosingUnitExport implements FromArray, WithStyles, WithCustomStartCell
{
    protected $tahun;
    protected $bulan;
    protected $perumahaanId;
    protected $namaPerumahaan;
    protected $isAgent;
    protected $months = [];
    protected $title = '';

    protected $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    public function __construct($tahun, $bulan, $perumahaanId, $namaPerumahaan, $isAgent = false)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
        $this->perumahaanId = $perumahaanId;
        $this->namaPerumahaan = $namaPerumahaan;
        $this->isAgent = $isAgent;

        if ($this->isAgent === true || $this->isAgent === 'agent') {
            $prefix = 'Data Closing Unit Agent ';
        } elseif ($this->isAgent === 'all') {
            $prefix = 'Data Closing Unit All ';
        } else {
            $prefix = 'Data Closing Unit ';
        }

        if ($this->bulan !== '' && $this->bulan !== null && $this->bulan !== 'all') {
            $this->months = [(int)$this->bulan];
            $namaBulanIndo = $this->namaBulan[(int)$this->bulan] ?? '';
            $this->title = "{$prefix}{$namaBulanIndo} {$this->tahun}";
        } else {
            $this->months = range(1, 12);
            $this->title = "{$prefix}{$this->tahun}";
        }
    }

    public function startCell(): string
    {
        // Data starts at row 5 (Month name headers)
        return 'A5';
    }

    public function array(): array
    {
        $user = Auth::user();

        // Query matching approved orders not canceled
        $query = PemesananUnit::with(['unit', 'sales', 'agent'])
            ->whereIn('cara_bayar', ['kpr', 'cash'])
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $this->perumahaanId)
            ->whereDoesntHave('pengajuanPembatalan', function ($q) {
                $q->where('status_pengajuan', '!=', 'ditolak');
            });

        if ($this->isAgent === true || $this->isAgent === 'agent') {
            $query->where('source', 'agent');
        } elseif ($this->isAgent === false || $this->isAgent === 'internal') {
            $query->where(function ($q) {
                $q->where('source', 'internal')->orWhereNull('source');
            });
        }

        if ($this->tahun) {
            $query->whereYear('tanggal_pemesanan', $this->tahun);
        }

        if ($this->bulan !== '' && $this->bulan !== null && $this->bulan !== 'all') {
            $query->whereMonth('tanggal_pemesanan', (int) $this->bulan);
        }

        if (($this->isAgent === false || $this->isAgent === 'internal') && $user->hasAnyRole(['Marketing', 'STAF PENJUALAN (ADL)', 'STAF PENJUALAN (LHR)'])) {
            $query->where('sales_id', $user->id);
        }

        $items = $query->orderBy('tanggal_pemesanan', 'asc')->get();

        // Group data by month
        $monthData = [];
        foreach ($this->months as $m) {
            $monthData[$m] = [];
        }

        foreach ($items as $item) {
            $m = (int) \Carbon\Carbon::parse($item->tanggal_pemesanan)->format('n');
            if (isset($monthData[$m])) {
                if (count($monthData[$m]) < 30) {
                    $marketingName = ($item->source === 'agent' || ($this->isAgent === true && !$item->sales))
                        ? ($item->agent->nama_agent ?? '-')
                        : ($item->sales->nama_lengkap ?? $item->sales->username ?? '-');
                    
                    $monthData[$m][] = [
                        'unit' => $item->unit->nama_unit ?? '-',
                        'marketing' => $marketingName,
                    ];
                }
            }
        }

        $dataRows = [];

        // Row 5: Month names
        $monthNameRow = [];
        foreach ($this->months as $m) {
            $monthNameRow[] = $this->namaBulan[$m];
            $monthNameRow[] = ''; // for merging 2 columns
        }
        $dataRows[] = $monthNameRow;

        // Row 6: Sub-headers
        $subHeaderRow = [];
        $secondHeader = $this->isAgent ? 'Agent' : 'Marketing';
        foreach ($this->months as $m) {
            $subHeaderRow[] = 'Unit';
            $subHeaderRow[] = $secondHeader;
        }
        $dataRows[] = $subHeaderRow;

        // Row 7 to 36 (30 rows of data)
        for ($i = 0; $i < 30; $i++) {
            $row = [];
            foreach ($this->months as $m) {
                if (isset($monthData[$m][$i])) {
                    $row[] = $monthData[$m][$i]['unit'];
                    $row[] = $monthData[$m][$i]['marketing'];
                } else {
                    $row[] = '';
                    $row[] = '';
                }
            }
            $dataRows[] = $row;
        }

        return $dataRows;
    }

    public function styles(Worksheet $sheet)
    {
        $numMonths = count($this->months);
        $totalCols = $numMonths * 2;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        // Title row (Row 1)
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', $this->title);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Perumahan row (Row 2)
        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->setCellValue('A2', 'Perumahan: ' . ($this->namaPerumahaan ?? 'Semua Perumahan'));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 11, 'color' => ['rgb' => '595959']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Date row (Row 3)
        $sheet->mergeCells("A3:{$lastColLetter}3");
        $sheet->setCellValue('A3', 'Tanggal Export: ' . now()->format('d-m-Y H:i'));
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['size' => 9, 'color' => ['rgb' => '595959']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row heights
        $sheet->getRowDimension(5)->setRowHeight(28);
        $sheet->getRowDimension(6)->setRowHeight(20);

        // Styling headers
        for ($i = 0; $i < $numMonths; $i++) {
            $col1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(($i * 2) + 1);
            $col2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(($i * 2) + 2);
            $sheet->mergeCells("{$col1}5:{$col2}5");

            // Month header (Row 5)
            $sheet->getStyle("{$col1}5:{$col2}5")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Sub headers (Row 6)
            $sheet->getStyle("{$col1}6:{$col2}6")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '333333']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        // Table Borders (Thin borders from Row 5 to Row 36)
        $lastRow = $sheet->getHighestRow();
        if ($lastRow >= 5) {
            $sheet->getStyle("A5:{$lastColLetter}{$lastRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            
            // Align unit column to center and marketing/agent column to left
            for ($col = 1; $col <= $totalCols; $col++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                if ($col % 2 !== 0) {
                    $sheet->getStyle("{$colLetter}7:{$colLetter}{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } else {
                    $sheet->getStyle("{$colLetter}7:{$colLetter}{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
            }

            // Zebra striping for data rows (Row 7 to Row 36)
            for ($row = 7; $row <= $lastRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F9FAFB');
                }
            }
        }

        // Fixed consistent width for columns
        for ($col = 1; $col <= $totalCols; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            if ($col % 2 !== 0) {
                // Unit column
                $sheet->getColumnDimension($colLetter)->setWidth(15);
            } else {
                // Marketing/Agent column
                $sheet->getColumnDimension($colLetter)->setWidth(25);
            }
        }

        return [];
    }
}
