<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnit;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TerminController extends Controller
{
    public function laporanUpah(string $id, ?string $qcId = null)
    {
        $unit = PembangunanUnit::with([
            'unit',
            'pembangunanUnitQc',
            'pembangunanUnitRapUpah',
            'pembangunanUnitUpah'
        ])->findOrFail($id);

        $targetQc = $qcId
            ? $unit->pembangunanUnitQc->where('master_qc_urutan_id', $qcId)
            : $unit->pembangunanUnitQc;

        $laporan = $targetQc->map(function ($qc) use ($unit) {
            $rap = $unit->pembangunanUnitRapUpah->where('pembangunan_unit_qc_id', $qc->id);
            $real = $unit->pembangunanUnitUpah->where('pembangunan_unit_qc_id', $qc->id);

            return [
                'nama_qc'    => $qc->nama_qc,
                'total_rap'  => $rap->sum('nominal_standar'),
                'total_real' => $real->sum('total_nominal'),
                'details'    => $rap->map(function ($r) use ($real) {
                    $totalRealPerItem = $real->where('nama_upah', $r->nama_upah)->sum('total_nominal');

                    return [
                        'nama_upah'    => $r->nama_upah,
                        'nominal_rap'  => $r->nominal_standar,
                        'nominal_real' => $totalRealPerItem,
                    ];
                })->values()
            ];
        })->values();

        return view('produksi.pembangunan-unit.laporan.upah', compact('unit', 'laporan'));
    }

    public function laporanBahan(string $id, ?string $qcId = null)
    {
        $unit = \App\Models\PembangunanUnit::with([
            'unit',
            'pembangunanUnitQc',
            'pembangunanUnitRapBahan',
            'pembangunanUnitBahan'
        ])->findOrFail($id);

        $targetQc = $qcId
            ? $unit->pembangunanUnitQc->where('master_qc_urutan_id', $qcId)
            : $unit->pembangunanUnitQc;

        $laporan = $targetQc->map(function ($qc) use ($unit) {
            $rap = $unit->pembangunanUnitRapBahan->where('pembangunan_unit_qc_id', $qc->id);
            $real = $unit->pembangunanUnitBahan->where('pembangunan_unit_qc_id', $qc->id);

            $allBarangIds = $rap->pluck('barang_id')
                ->merge($real->pluck('barang_id'))
                ->unique();

            return [
                'nama_qc'          => $qc->nama_qc,
                'total_harga_real' => $real->sum('harga_total_snapshot'),
                'details'          => $allBarangIds->map(function ($barangId) use ($rap, $real) {
                    $rapItems = $rap->where('barang_id', $barangId);
                    $realItems = $real->where('barang_id', $barangId);

                    $firstRap = $rapItems->first();
                    $firstReal = $realItems->first();

                    $namaBarang = $firstReal ? $firstReal->nama_barang : ($firstRap ? $firstRap->nama_barang : 'Tidak Diketahui');

                    return [
                        'barang_id'        => $barangId,
                        'nama_barang'      => $namaBarang,
                        'nama_barang_rap'  => $firstRap ? $firstRap->nama_barang : '-',
                        'nama_barang_real' => $firstReal ? $firstReal->nama_barang : '-',
                        'qty_rap'          => $rapItems->sum('jumlah_standar'),
                        'satuan_rap'       => $firstRap ? $firstRap->satuan : '-',
                        'qty_real'         => $realItems->sum('jumlah_pakai'),
                        'satuan_real'      => $firstReal ? $firstReal->satuan : '-',
                        'harga_real'       => $realItems->sum('harga_total_snapshot'),
                    ];
                })->values()
            ];
        })->values();

        return view('produksi.pembangunan-unit.laporan.bahan', compact('unit', 'laporan'));
    }

    public function exportLaporanTermin(string $id)
    {
        // 1. Load Data Unit dan Semua Relasinya
        $unit = \App\Models\PembangunanUnit::with([
            'unit',
            'pembangunanUnitRapUpah',
            'pembangunanUnitUpah',
            'pembangunanUnitRapBahan',
            'pembangunanUnitBahan'
        ])->findOrFail($id);

        // 2. Olah Data Upah (Gabungan Semua QC)
        $rapUpah = $unit->pembangunanUnitRapUpah->groupBy('nama_upah');
        $realUpah = $unit->pembangunanUnitUpah->groupBy('nama_upah');
        $allUpahNames = $rapUpah->keys()->merge($realUpah->keys())->unique();

        $dataUpah = $allUpahNames->map(function ($name) use ($rapUpah, $realUpah) {
            $rapNominal = isset($rapUpah[$name]) ? $rapUpah[$name]->sum('nominal_standar') : 0;
            $realNominal = isset($realUpah[$name]) ? $realUpah[$name]->sum('total_nominal') : 0;
            return [
                'nama'    => $name,
                'rap'     => $rapNominal,
                'real'    => $realNominal,
                'selisih' => $rapNominal - $realNominal
            ];
        })->values();

        // 3. Olah Data Bahan (Gabungan Semua QC)
        $rapBahan = $unit->pembangunanUnitRapBahan->groupBy('barang_id');
        $realBahan = $unit->pembangunanUnitBahan->groupBy('barang_id');
        $allBarangIds = $rapBahan->keys()->merge($realBahan->keys())->unique();

        $dataBahan = $allBarangIds->map(function ($barangId) use ($rapBahan, $realBahan) {
            $rGroup = $rapBahan->get($barangId);
            $rlGroup = $realBahan->get($barangId);

            $firstRap = $rGroup ? $rGroup->first() : null;
            $firstReal = $rlGroup ? $rlGroup->first() : null;

            return [
                'nama_barang' => $firstReal ? $firstReal->nama_barang : ($firstRap ? $firstRap->nama_barang : '-'),
                'qty_rap'     => $rGroup ? $rGroup->sum('jumlah_standar') : 0,
                'satuan_rap'  => $firstRap ? $firstRap->satuan : '-',
                'qty_real'    => $rlGroup ? $rlGroup->sum('jumlah_pakai') : 0,
                'satuan_real' => $firstReal ? $firstReal->satuan : '-',
                'harga_real'  => $rlGroup ? $rlGroup->sum('harga_total_snapshot') : 0,
            ];
        })->values();

        // 4. Inisialisasi Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Termin');

        // Styling Default Setup
        $styleHeaderTable = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E78']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $styleBorderAll = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        // --- BAGIAN HEADER REPORT ---
        $sheet->setCellValue('A1', 'LAPORAN TERMIN PROYEK (GABUNGAN KESELURUHAN)');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'Unit');
        $sheet->setCellValue('B2', ': ' . ($unit->unit->nama_unit ?? '-'));
        $sheet->setCellValue('A3', 'Tgl');
        $sheet->setCellValue('B3', ': ' . date('d F Y H:i'));

        $row = 5; // Mulai tabel di baris 5

        // ==========================================
        // TABEL 1: REKAPITULASI UPAH
        // ==========================================
        $sheet->setCellValue("A{$row}", 'I. REKAPITULASI UPAH');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $headersUpah = ['NO', 'NAMA PEKERJAAN', 'BUDGET RAP (Rp)', 'REALISASI (Rp)', 'SELISIH (Rp)'];
        foreach (range('A', 'E') as $index => $col) {
            $sheet->setCellValue($col . $row, $headersUpah[$index]);
        }
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleHeaderTable);
        $row++;

        $totalRapUpah = 0;
        $totalRealUpah = 0;
        foreach ($dataUpah as $i => $upah) {
            $totalRapUpah += $upah['rap'];
            $totalRealUpah += $upah['real'];

            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $upah['nama']);
            $sheet->setCellValue("C{$row}", $upah['rap']);
            $sheet->setCellValue("D{$row}", $upah['real']);
            $sheet->setCellValue("E{$row}", $upah['selisih']);

            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderAll);
            $sheet->getStyle("C{$row}:E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Total Upah
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL UPAH');
        $sheet->setCellValue("C{$row}", $totalRapUpah);
        $sheet->setCellValue("D{$row}", $totalRealUpah);
        $sheet->setCellValue("E{$row}", $totalRapUpah - $totalRealUpah);
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderAll);
        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}:E{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $row += 3; // Jarak antar tabel

        // ==========================================
        // TABEL 2: REKAPITULASI BAHAN
        // ==========================================
        $sheet->setCellValue("A{$row}", 'II. REKAPITULASI BAHAN');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $headersBahan = ['NO', 'NAMA BAHAN', 'QTY RAP', 'QTY REAL', 'TOTAL HARGA REAL (Rp)'];
        foreach (range('A', 'E') as $index => $col) {
            $sheet->setCellValue($col . $row, $headersBahan[$index]);
        }
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleHeaderTable);
        $row++;

        $totalHargaBahan = 0;
        foreach ($dataBahan as $i => $bahan) {
            $totalHargaBahan += $bahan['harga_real'];

            $qtyRapStr = $bahan['qty_rap'] > 0 ? floatval($bahan['qty_rap']) . ' ' . $bahan['satuan_rap'] : '-';
            $qtyRealStr = $bahan['qty_real'] > 0 ? floatval($bahan['qty_real']) . ' ' . $bahan['satuan_real'] : '-';

            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $bahan['nama_barang']);
            $sheet->setCellValue("C{$row}", $qtyRapStr);
            $sheet->setCellValue("D{$row}", $qtyRealStr);
            $sheet->setCellValue("E{$row}", $bahan['harga_real']);

            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderAll);
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');

            // Tandai merah jika di luar RAP
            if ($bahan['qty_rap'] == 0) {
                $sheet->getStyle("B{$row}")->getFont()->getColor()->setARGB('FFFF0000');
            }
            $row++;
        }

        // Total Bahan
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL BIAYA BAHAN REALISASI');
        $sheet->setCellValue("E{$row}", $totalHargaBahan);
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderAll);
        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $row += 3;

        // ==========================================
        // GRAND TOTAL
        // ==========================================
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI (UPAH + BAHAN)');
        $sheet->setCellValue("E{$row}", $totalRealUpah + $totalHargaBahan);

        $styleGrandTotal = [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2EFDA']], // Hijau muda
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleGrandTotal);
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // --- Sizing Kolom Otomatis ---
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(25);

        // --- Output File ---
        $namaUnit = preg_replace('/[^A-Za-z0-9\-]/', '_', $unit->unit->nama_unit ?? 'Unit');
        $filename = "Laporan_Termin_{$namaUnit}.xlsx";

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
