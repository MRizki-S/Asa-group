<?php

namespace App\Exports;

use App\Models\UpahHarianTukang;
use App\Models\PembangunanUnit;
use App\Models\PembangunanKawasan;
use App\Models\PembangunanProyek;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UpahHarianTukangExport implements
    FromArray,
    WithStyles,
    WithCustomStartCell
{
    protected $pengajuan;
    protected $detailPerTukang;
    protected $unitLabels;
    protected $kawasanLabels;
    protected $proyekLabels;
    protected $dates;
    protected $mergeRanges = [];
    protected $absentRanges = [];

    public function __construct(UpahHarianTukang $pengajuan, $detailPerTukang)
    {
        $this->pengajuan = $pengajuan;
        $this->detailPerTukang = $detailPerTukang;

        // Generate date list between tanggal_mulai and tanggal_selesai
        $startDate = \Carbon\Carbon::parse($pengajuan->tanggal_mulai);
        $endDate = \Carbon\Carbon::parse($pengajuan->tanggal_selesai);
        $this->dates = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $this->dates[] = $date->copy();
        }

        // Load reference labels
        $this->loadLabels();
    }

    private function perumahanPrefix(?string $perumahanName): string
    {
        if (!$perumahanName) {
            return '';
        }

        $lowerName = strtolower($perumahanName);
        if ($lowerName === 'asa dreamland') {
            return 'ADL - ';
        }

        if ($lowerName === 'lembah hijau residence') {
            return 'LHR - ';
        }

        $abbr = '';
        foreach (explode(' ', $perumahanName) as $word) {
            $abbr .= strtoupper(substr($word, 0, 1));
        }

        return $abbr ? $abbr . ' - ' : '';
    }

    private function loadLabels()
    {
        $this->unitLabels = PembangunanUnit::with(['unit', 'perumahaan'])->get()->mapWithKeys(function ($item) {
            $perumahanName = $item->perumahaan->nama_perumahaan ?? '';
            $prefix = $this->perumahanPrefix($perumahanName);
            return [$item->id => $prefix . ($item->unit->nama_unit ?? '-')];
        });

        $this->kawasanLabels = PembangunanKawasan::with('perumahan')->get()->mapWithKeys(function ($item) {
            $perumahanName = $item->perumahan->nama_perumahaan ?? '';
            $prefix = $this->perumahanPrefix($perumahanName);
            return [$item->id => $prefix . ($item->nama ?? 'Kawasan #' . $item->id)];
        });

        $this->proyekLabels = PembangunanProyek::all()->mapWithKeys(function ($item) {
            return [$item->id => $item->nama ?? 'Proyek #' . $item->id];
        });
    }

    private function getReferensiLabel($jenis, $id)
    {
        if ($jenis === 'pembangunan_unit') {
            return $this->unitLabels[$id] ?? 'Unit #' . $id;
        }
        if ($jenis === 'pembangunan_kawasan') {
            return $this->kawasanLabels[$id] ?? 'Kawasan #' . $id;
        }
        if ($jenis === 'pembangunan_proyek') {
            return $this->proyekLabels[$id] ?? 'Proyek #' . $id;
        }
        return ucfirst(str_replace('_', ' ', $jenis)) . ' #' . $id;
    }

    private function formatAllocationCell($label, $normalHours, $lemburHours)
    {
        if ($normalHours <= 0 && $lemburHours <= 0) {
            return '';
        }

        $hours = $normalHours + $lemburHours;

        return $label . ' (' . $hours . ' JAM)';
    }

    private function formatRupiah($amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }

    private function buildAllocationLines($allocations): array
    {
        return $allocations
            ->groupBy(fn($alokasi) => $alokasi->referensi_jenis . '_' . $alokasi->referensi_id)
            ->map(function ($rows) {
                $first = $rows->first();
                $label = $this->getReferensiLabel($first->referensi_jenis, $first->referensi_id);
                $hours = $rows->sum('jam_kerja');

                return $this->formatAllocationCell($label, $hours, 0);
            })
            ->values()
            ->all();
    }

    private function buildLemburLines($details, float $totalLembur): array
    {
        if ($totalLembur <= 0) {
            return [''];
        }

        $lines = [(float) $totalLembur];
        $allocationLines = $details
            ->flatMap(fn($detail) => $detail->alokasi->where('jenis', 'lembur'))
            ->groupBy(fn($alokasi) => $alokasi->referensi_jenis . '_' . $alokasi->referensi_id)
            ->map(function ($rows) {
                $first = $rows->first();
                $label = $this->getReferensiLabel($first->referensi_jenis, $first->referensi_id);
                $hours = $rows->sum('jam_kerja');

                return $this->formatAllocationCell($label, $hours, 0);
            })
            ->values()
            ->all();

        return array_merge($lines, $allocationLines);
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function array(): array
    {
        $data = [];
        $currentRow = 8;
        $totalUpahNormalAccumulated = 0;
        $totalLemburAccumulated = 0;
        $totalBonAccumulated = 0;
        $totalSisaUpahAccumulated = 0;

        foreach ($this->detailPerTukang as $row) {
            $tukang = $row['tukang'];
            $details = $row['details'];
            $bon = $row['bon'];

            $hariMasuk = $details->where('status_kehadiran', true)->count();
            $upahNormal = $details->sum('nominal_harian_final');
            $totalLembur = $details->sum(fn($d) => $d->alokasi->where('jenis', 'lembur')->sum('subtotal'));
            $grandTotal = $upahNormal + $totalLembur;
            $sisaUpah = $grandTotal - $bon;
            $totalUpahNormalAccumulated += $upahNormal;
            $totalLemburAccumulated += $totalLembur;
            $totalBonAccumulated += $bon;
            $totalSisaUpahAccumulated += $sisaUpah;

            $detailsByDate = $details->keyBy(fn($detail) => $detail->tanggal->format('Y-m-d'));
            $dateLines = [];
            $absentDateIndexes = [];
            foreach ($this->dates as $date) {
                $dateIndex = count($dateLines);
                $detail = $detailsByDate->get($date->format('Y-m-d'));

                if (!$detail) {
                    $dateLines[] = [''];
                    continue;
                }

                if (!$detail->status_kehadiran) {
                    $dateLines[] = ['X'];
                    $absentDateIndexes[] = $dateIndex;
                    continue;
                }

                $normalAllocations = $detail->alokasi->where('jenis', 'normal');
                $dailyHours = (float) ($detail->jam_kerja ?: $normalAllocations->sum('jam_kerja') ?: $detail->jam_default_snapshot);
                $lines = [
                    $this->formatRupiah($detail->nominal_harian_final) . ' (' . $dailyHours . ' Jam Kerja)',
                ];

                $dateLines[] = array_merge($lines, $this->buildAllocationLines($normalAllocations));
            }

            $lemburLines = $this->buildLemburLines($details, (float) $totalLembur);
            $numRows = max(
                1,
                count($lemburLines),
                ...array_map('count', $dateLines)
            );

            if ($numRows > 1) {
                $endRow = $currentRow + $numRows - 1;
                $this->mergeRanges[] = "A{$currentRow}:A{$endRow}";
                $this->mergeRanges[] = "B{$currentRow}:B{$endRow}";

                $firstSummaryColIndex = 3 + count($this->dates);
                foreach ([$firstSummaryColIndex, $firstSummaryColIndex + 1, $firstSummaryColIndex + 3, $firstSummaryColIndex + 4] as $c) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                    $this->mergeRanges[] = "{$colLetter}{$currentRow}:{$colLetter}{$endRow}";
                }
            }

            foreach ($absentDateIndexes as $dateIndex) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $dateIndex);
                $endRow = $currentRow + $numRows - 1;

                if ($numRows > 1) {
                    $this->mergeRanges[] = "{$colLetter}{$currentRow}:{$colLetter}{$endRow}";
                }

                $this->absentRanges[] = "{$colLetter}{$currentRow}:{$colLetter}{$endRow}";
            }

            for ($rowIndex = 0; $rowIndex < $numRows; $rowIndex++) {
                $rowData = [];

                if ($rowIndex === 0) {
                    $rowData[] = $tukang->kode ?? '-';
                    $rowData[] = $tukang->nama_tukang ?? '-';
                } else {
                    $rowData[] = '';
                    $rowData[] = '';
                }

                foreach ($dateLines as $lines) {
                    $rowData[] = $lines[$rowIndex] ?? '';
                }

                if ($rowIndex === 0) {
                    $rowData[] = $hariMasuk . ' Hari';
                    $rowData[] = (float) $upahNormal;
                } else {
                    $rowData[] = '';
                    $rowData[] = '';
                }

                $rowData[] = $lemburLines[$rowIndex] ?? '';

                if ($rowIndex === 0) {
                    $rowData[] = (float) $bon;
                    $rowData[] = (float) $sisaUpah;
                } else {
                    $rowData[] = '';
                    $rowData[] = '';
                }

                $data[] = $rowData;
                $currentRow++;
            }
        }

        $totalRow = array_fill(0, 2 + count($this->dates) + 5, '');
        $totalRow[2 + count($this->dates)] = 'TOTAL';
        $totalRow[2 + count($this->dates) + 1] = (float) $totalUpahNormalAccumulated;
        $totalRow[2 + count($this->dates) + 2] = (float) $totalLemburAccumulated;
        $totalRow[2 + count($this->dates) + 3] = (float) $totalBonAccumulated;
        $totalRow[2 + count($this->dates) + 4] = (float) $totalSisaUpahAccumulated;
        $data[] = $totalRow;

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $totalColsIndex = 2 + count($this->dates) + 5;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColsIndex);
        $firstDayCol = 'C';
        $lastDayCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + count($this->dates));
        $jumlahHadirCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + count($this->dates));
        $totalUpahCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4 + count($this->dates));
        $lemburCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5 + count($this->dates));
        $bonCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(6 + count($this->dates));
        $sisaUpahCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + count($this->dates));

        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->mergeCells("A3:{$lastColLetter}3");
        $sheet->mergeCells("A4:{$lastColLetter}4");
        $sheet->mergeCells("A6:A7");
        $sheet->mergeCells("B6:B7");
        $sheet->mergeCells("{$firstDayCol}6:{$lastDayCol}6");
        $sheet->mergeCells("{$jumlahHadirCol}6:{$jumlahHadirCol}7");
        $sheet->mergeCells("{$totalUpahCol}6:{$totalUpahCol}7");
        $sheet->mergeCells("{$lemburCol}6:{$lemburCol}7");
        $sheet->mergeCells("{$bonCol}6:{$bonCol}7");
        $sheet->mergeCells("{$sisaUpahCol}6:{$sisaUpahCol}7");

        $titleSuffix = $this->pengajuan->jenis_referensi === 'perumahan' ? 'ABM (Perumahan)' : 'Mangoon';
        $sheet->setCellValue('A1', 'RINCIAN ABSENSI TUKANG ' . strtoupper($titleSuffix));
        $sheet->setCellValue('A2', 'Periode: ' . $this->pengajuan->tanggal_mulai->translatedFormat('d F Y') . ' - ' . $this->pengajuan->tanggal_selesai->translatedFormat('d F Y'));
        $sheet->setCellValue('A3', 'Dibuat Oleh: ' . ($this->pengajuan->createdBy->nama_lengkap ?? $this->pengajuan->createdBy->name ?? '-'));
        $sheet->setCellValue('A4', 'Status Pengajuan: ' . ucfirst($this->pengajuan->status));
        $sheet->setCellValue('A6', 'KODE TUKANG');
        $sheet->setCellValue('B6', 'NAMA TUKANG');
        $sheet->setCellValue("{$firstDayCol}6", 'Periode');

        foreach ($this->dates as $index => $date) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $index);
            $sheet->setCellValue("{$col}7", $date->translatedFormat('l (d M)'));
        }

        $sheet->setCellValue("{$jumlahHadirCol}6", 'JUMLAH HADIR');
        $sheet->setCellValue("{$totalUpahCol}6", 'TOTAL UPAH NORMAL');
        $sheet->setCellValue("{$lemburCol}6", 'LEMBUR');
        $sheet->setCellValue("{$bonCol}6", 'BON');
        $sheet->setCellValue("{$sisaUpahCol}6", 'SISA UPAH');

        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2F5597'));
        $sheet->getStyle('A2:A4')->getFont()->setItalic(true)->setSize(11);

        $sheet->getStyle("A6:{$lastColLetter}7")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $sheet->getStyle("A6:{$lastColLetter}{$lastRow}")->getAlignment()->setWrapText(true);

        foreach ($this->mergeRanges as $range) {
            $sheet->mergeCells($range);
        }

        foreach ($this->absentRanges as $range) {
            $sheet->getStyle($range)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6B8B7']],
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        $sheet->getStyle("A6:{$lastColLetter}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('000000');
        $sheet->getStyle("A8:{$lastColLetter}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("A8:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B8:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$jumlahHadirCol}8:{$jumlahHadirCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $currencyFormat = '_("Rp"* #,##0_);_("Rp"* -#,##0_);_("Rp"* "-"??_);_(@_)';
        $sheet->getStyle("{$totalUpahCol}8:{$sisaUpahCol}{$lastRow}")->getNumberFormat()->setFormatCode($currencyFormat);

        $sheet->getRowDimension(6)->setRowHeight(28);
        $sheet->getRowDimension(7)->setRowHeight(24);
        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(24);
        foreach ($this->dates as $index => $date) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $index);
            $sheet->getColumnDimension($col)->setWidth(36);
        }
        $sheet->getColumnDimension($jumlahHadirCol)->setWidth(18);
        $sheet->getColumnDimension($totalUpahCol)->setWidth(20);
        $sheet->getColumnDimension($lemburCol)->setWidth(34);
        $sheet->getColumnDimension($bonCol)->setWidth(16);
        $sheet->getColumnDimension($sisaUpahCol)->setWidth(18);
        $sheet->freezePane('C8');

        for ($i = 8; $i <= $lastRow; $i++) {
            $colA = $sheet->getCell('A' . $i)->getValue();
            if ($colA !== '' && $colA !== null) {
                $sheet->getStyle("A{$i}:{$lastColLetter}{$i}")->getFont()->setBold(true);
                $sheet->getStyle("{$firstDayCol}{$i}:{$lastDayCol}{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        $sheet->getStyle("A{$lastRow}:{$lastColLetter}{$lastRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:{$lastColLetter}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');

        return [];
    }
}
