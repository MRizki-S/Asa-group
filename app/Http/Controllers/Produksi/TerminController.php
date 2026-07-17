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
        $unit = PembangunanUnit::with(['unit', 'pembangunanUnitQc', 'pembangunanUnitRapUpah', 'pembangunanUnitUpah'])->findOrFail($id);

        $targetQc = $qcId ? $unit->pembangunanUnitQc->where('master_qc_urutan_id', $qcId) : $unit->pembangunanUnitQc;

        $laporan = $targetQc
            ->map(function ($qc) use ($unit) {
                $rap = $unit->pembangunanUnitRapUpah->where('pembangunan_unit_qc_id', $qc->id);
                $real = $unit->pembangunanUnitUpah->where('pembangunan_unit_qc_id', $qc->id);

                return [
                    'nama_qc' => $qc->nama_qc,
                    'total_rap' => $rap->sum('nominal_standar'),
                    'total_real' => $real->sum('total_nominal'),
                    'details' => $rap
                        ->map(function ($r) use ($real) {
                            $totalRealPerItem = $real->where('nama_upah', $r->nama_upah)->sum('total_nominal');

                            return [
                                'nama_upah' => $r->nama_upah,
                                'nominal_rap' => $r->nominal_standar,
                                'nominal_real' => $totalRealPerItem,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return view('produksi.pembangunan-unit.laporan.upah', compact('unit', 'laporan'));
    }

    public function laporanBahan(string $id, ?string $qcId = null)
    {
        $unit = \App\Models\PembangunanUnit::with(['unit', 'pembangunanUnitQc', 'pembangunanUnitRapBahan.barang.baseUnit', 'pembangunanUnitBahan'])->findOrFail($id);

        $targetQc = $qcId ? $unit->pembangunanUnitQc->where('master_qc_urutan_id', $qcId) : $unit->pembangunanUnitQc;

        $laporan = $targetQc
            ->map(function ($qc) use ($unit) {
                $rap = $unit->pembangunanUnitRapBahan->where('pembangunan_unit_qc_id', $qc->id);
                $real = $unit->pembangunanUnitBahan->where('pembangunan_unit_qc_id', $qc->id);

                $allBarangIds = $rap->pluck('barang_id')->merge($real->pluck('barang_id'))->unique();

                return [
                    'nama_qc' => $qc->nama_qc,
                    'total_harga_real' => $real->sum('harga_total_snapshot'),
                    'details' => $allBarangIds
                        ->map(function ($barangId) use ($rap, $real) {
                            $rapItems = $rap->where('barang_id', $barangId);
                            $realItems = $real->where('barang_id', $barangId);

                            $firstRap = $rapItems->first();
                            $firstReal = $realItems->first();

                            $namaBarang = $firstReal ? $firstReal->nama_barang : ($firstRap ? $firstRap->nama_barang : 'Tidak Diketahui');

                            $baseUnitName = $firstRap && $firstRap->barang && $firstRap->barang->baseUnit 
                                ? $firstRap->barang->baseUnit->nama 
                                : ($firstRap ? $firstRap->satuan : ($firstReal ? $firstReal->satuan : '-'));

                            return [
                                'barang_id' => $barangId,
                                'nama_barang' => $namaBarang,
                                'nama_barang_rap' => $firstRap ? $firstRap->nama_barang : '-',
                                'nama_barang_real' => $firstReal ? $firstReal->nama_barang : '-',
                                'qty_rap' => $rapItems->map(fn($r) => (float)$r->jumlah_standar * (float)$r->faktor_konversi)->sum(),
                                'satuan_rap' => $baseUnitName,
                                'qty_real' => $realItems->sum('jumlah_pakai'),
                                'satuan_real' => $firstReal ? $firstReal->satuan : $baseUnitName,
                                'harga_real' => $realItems->sum('harga_total_snapshot'),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return view('produksi.pembangunan-unit.laporan.bahan', compact('unit', 'laporan'));
    }

    public function exportLaporanTermin(string $id)
    {
        // 1. Load Data Unit beserta nested relation dikelompokkan per QC
        $unit = \App\Models\PembangunanUnit::with([
            'unit',
            'pembangunanUnitQc' => function ($query) {
                $query->orderBy('qc_urutan_ke', 'asc');
            },
            'pembangunanUnitQc.pembangunanUnitRapUpah',
            'pembangunanUnitQc.pembangunanUnitUpah',
            'pembangunanUnitQc.pembangunanUnitRapBahan.barang.baseUnit',
            'pembangunanUnitQc.pembangunanUnitBahan',
        ])->findOrFail($id);

        // 2. Inisialisasi Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Termin Per QC');

        // Styling Setup
        $styleHeaderQC = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF203764']], // Biru Gelap Utama
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ];

        $styleHeaderTable = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF305496']], // Biru Sekunder
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $styleSubHeaderTable = [
            'font' => ['bold' => true, 'italic' => true, 'color' => ['argb' => 'FF000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']], // Abu-abu Terang
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $styleBorderAll = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        // --- BAGIAN HEADER REPORT ---
        $sheet->setCellValue('A1', 'LAPORAN TERMIN PROYEK (DIKELOMPOKKAN PER TAHAP QC)');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'Unit');
        $sheet->setCellValue('B2', ': ' . ($unit->unit->nama_unit ?? '-'));
        $sheet->setCellValue('A3', 'Tgl Export');
        $sheet->setCellValue('B3', ': ' . date('d F Y H:i'));

        $row = 5; // Mulai iterasi konten di baris 5

        // Variabel untuk menyimpan akumulasi Grand Total Keseluruhan QC
        $grandTotalRapUpah = 0;
        $grandTotalRealUpah = 0;
        $grandTotalHargaBahan = 0;

        // 3. Iterasi Berdasarkan Tahapan QC
        foreach ($unit->pembangunanUnitQc as $indexQc => $qc) {
            // --- HEADER TAHAP QC ---
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", ' QC ' . ($indexQc + 1) . ': ' . strtoupper($qc->nama_qc));
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleHeaderQC);
            $row++;

            // --- SUB-HEADER 1: UPAH ---
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", '   A. rincian upah pekerjaan');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleSubHeaderTable);
            $row++;

            // Kolom Header Tabel Upah
            $headersUpah = ['NO', 'NAMA PEKERJAAN', 'BUDGET RAB (Rp)', 'REALISASI (Rp)', 'SELISIH (Rp)'];
            foreach (range('A', 'E') as $index => $col) {
                $sheet->setCellValue($col . $row, $headersUpah[$index]);
            }
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleHeaderTable);
            $row++;

            // Olah Data Upah spesifik untuk QC ini
            $rapUpah = $qc->pembangunanUnitRapUpah->groupBy('nama_upah');
            $realUpah = $qc->pembangunanUnitUpah->groupBy('nama_upah');
            $allUpahNames = $rapUpah->keys()->merge($realUpah->keys())->unique();

            $subTotalRapUpah = 0;
            $subTotalRealUpah = 0;
            $noUpah = 1;

            foreach ($allUpahNames as $name) {
                $rapNominal = isset($rapUpah[$name]) ? $rapUpah[$name]->sum('nominal_standar') : 0;
                $realNominal = isset($realUpah[$name]) ? $realUpah[$name]->sum('total_nominal') : 0;
                $selisih = $rapNominal - $realNominal;

                $subTotalRapUpah += $rapNominal;
                $subTotalRealUpah += $realNominal;

                $sheet->setCellValue("A{$row}", $noUpah++);
                $sheet->setCellValue("B{$row}", $name);
                $sheet->setCellValue("C{$row}", $rapNominal);
                $sheet->setCellValue("D{$row}", $realNominal);
                $sheet->setCellValue("E{$row}", $selisih);

                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderAll);
                $sheet
                    ->getStyle("C{$row}:E{$row}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
                $row++;
            }

            // Sub Total Upah Per QC
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("A{$row}", 'SUB TOTAL UPAH QC');
            $sheet->setCellValue("C{$row}", $subTotalRapUpah);
            $sheet->setCellValue("D{$row}", $subTotalRealUpah);
            $sheet->setCellValue("E{$row}", $subTotalRapUpah - $subTotalRealUpah);
            $sheet
                ->getStyle("A{$row}:E{$row}")
                ->applyFromArray($styleBorderAll)
                ->getFont()
                ->setBold(true);
            $sheet
                ->getStyle("C{$row}:E{$row}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
            $sheet
                ->getStyle("A{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Akumulasi ke Grand Total
            $grandTotalRapUpah += $subTotalRapUpah;
            $grandTotalRealUpah += $subTotalRealUpah;
            $row += 2; // Beri sedikit jarak sebelum masuk ke bahan

            // --- SUB-HEADER 2: BAHAN ---
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", '   B. rincian pemakaian bahan');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleSubHeaderTable);
            $row++;

            // Kolom Header Tabel Bahan
            $headersBahan = ['NO', 'NAMA BAHAN', 'QTY RAB', 'QTY REAL', 'TOTAL HARGA REAL (Rp)'];
            foreach (range('A', 'E') as $index => $col) {
                $sheet->setCellValue($col . $row, $headersBahan[$index]);
            }
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleHeaderTable);
            $row++;

            // Olah Data Bahan spesifik untuk QC ini
            $rapBahan = $qc->pembangunanUnitRapBahan->groupBy('barang_id');
            $realBahan = $qc->pembangunanUnitBahan->groupBy('barang_id');
            $allBarangIds = $rapBahan->keys()->merge($realBahan->keys())->unique();

            $subTotalHargaBahan = 0;
            $noBahan = 1;

            foreach ($allBarangIds as $barangId) {
                $rGroup = $rapBahan->get($barangId);
                $rlGroup = $realBahan->get($barangId);

                $firstRap = $rGroup ? $rGroup->first() : null;
                $firstReal = $rlGroup ? $rlGroup->first() : null;

                $namaBarang = $firstReal ? $firstReal->nama_barang : ($firstRap ? $firstRap->nama_barang : '-');
                
                $baseUnitName = $firstRap && $firstRap->barang && $firstRap->barang->baseUnit 
                    ? $firstRap->barang->baseUnit->nama 
                    : ($firstRap ? $firstRap->satuan : ($firstReal ? $firstReal->satuan : '-'));

                $qtyRap = $rGroup ? $rGroup->map(fn($r) => (float)$r->jumlah_standar * (float)$r->faktor_konversi)->sum() : 0;
                $qtyReal = $rlGroup ? $rlGroup->sum('jumlah_pakai') : 0;
                $hargaReal = $rlGroup ? $rlGroup->sum('harga_total_snapshot') : 0;

                $subTotalHargaBahan += $hargaReal;

                $qtyRapStr = $qtyRap > 0 ? floatval($qtyRap) . ' ' . $baseUnitName : '-';
                $qtyRealStr = $qtyReal > 0 ? floatval($qtyReal) . ' ' . ($firstReal ? $firstReal->satuan : $baseUnitName) : '-';

                $sheet->setCellValue("A{$row}", $noBahan++);
                $sheet->setCellValue("B{$row}", $namaBarang);
                $sheet->setCellValue("C{$row}", $qtyRapStr);
                $sheet->setCellValue("D{$row}", $qtyRealStr);
                $sheet->setCellValue("E{$row}", $hargaReal);

                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderAll);
                $sheet
                    ->getStyle("E{$row}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Tandai merah jika barang dipakai di luar perencanaan RAP QC ini
                if ($qtyRap == 0) {
                    $sheet
                        ->getStyle("B{$row}")
                        ->getFont()
                        ->getColor()
                        ->setARGB('FFFF0000');
                }
                $row++;
            }

            // Sub Total Bahan Per QC
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'SUB TOTAL BIAYA BAHAN QC');
            $sheet->setCellValue("E{$row}", $subTotalHargaBahan);
            $sheet
                ->getStyle("A{$row}:E{$row}")
                ->applyFromArray($styleBorderAll)
                ->getFont()
                ->setBold(true);
            $sheet
                ->getStyle("E{$row}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
            $sheet
                ->getStyle("A{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Akumulasi ke Grand Total
            $grandTotalHargaBahan += $subTotalHargaBahan;
            $row += 4; // Jarak agak lebar sebelum masuk ke Tahap QC berikutnya
        }

        // ==========================================
        // GRAND TOTAL KESELURUHAN (DI PALING BAWAH)
        // ==========================================
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue("A{$row}", 'SUMMARY / REKAPITULASI AKHIR GABUNGAN');
        $sheet
            ->getStyle("A{$row}:E{$row}")
            ->getFont()
            ->setBold(true)
            ->setSize(12);
        $row++;

        // Row Grand Total Upah
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI UPAH');
        $sheet->setCellValue("E{$row}", $grandTotalRealUpah);
        $sheet
            ->getStyle("A{$row}:E{$row}")
            ->applyFromArray($styleBorderAll)
            ->getFont()
            ->setBold(true);
        $sheet
            ->getStyle("E{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet
            ->getStyle("A{$row}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;

        // Row Grand Total Bahan
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI BAHAN');
        $sheet->setCellValue("E{$row}", $grandTotalHargaBahan);
        $sheet
            ->getStyle("A{$row}:E{$row}")
            ->applyFromArray($styleBorderAll)
            ->getFont()
            ->setBold(true);
        $sheet
            ->getStyle("E{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet
            ->getStyle("A{$row}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;

        // Row Grand Total Akhir (Upah + Bahan)
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL AKHIR REALISASI PROYEK (UPAH + BAHAN)');
        $sheet->setCellValue("E{$row}", $grandTotalRealUpah + $grandTotalHargaBahan);

        $styleGrandTotalFinal = [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2EFDA']], // Hijau muda soft
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleGrandTotalFinal);
        $sheet
            ->getStyle("E{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet
            ->getStyle("A{$row}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // --- Sizing Kolom Otomatis ---
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(28);

        // --- Output File ---
        $namaUnit = preg_replace('/[^A-Za-z0-9\-]/', '_', $unit->unit->nama_unit ?? 'Unit');
        $filename = "Laporan_Termin_Per_QC_{$namaUnit}.xlsx";

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ],
        );
    }



    public function exportLaporanTerminProyek(string $id)
    {
        $proyek = \App\Models\PembangunanProyek::with([
            'pengajuanUpah', 'upah', 'orders.details.barang.baseUnit', 'orders.details.satuanModel', 'pembangunanProyekBahan'
        ])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Termin Proyek');

        $styleHeaderTable = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF305496']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $styleSubHeaderTable = [
            'font' => ['bold' => true, 'italic' => true, 'color' => ['argb' => 'FF000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $styleBorderAll = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        $sheet->setCellValue('A1', 'LAPORAN TERMIN PROYEK');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'Proyek');
        $sheet->setCellValue('B2', ': ' . ($proyek->nama ?? '-'));
        $sheet->setCellValue('A3', 'Tgl Export');
        $sheet->setCellValue('B3', ': ' . date('d F Y H:i'));

        $row = 5;

        // A. UPAH
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", '   A. RINCIAN UPAH PEKERJAAN');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleSubHeaderTable);
        $row++;

        $headersUpah = ['NO', 'NAMA PEKERJAAN', 'NOMINAL (Rp)', 'KETERANGAN'];
        foreach (range('A', 'D') as $index => $col) {
            $sheet->setCellValue($col . $row, $headersUpah[$index]);
        }
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleHeaderTable);
        $row++;

        $realUpah = $proyek->upah;
        $subTotalRealUpah = 0; $noUpah = 1;
        foreach ($realUpah as $upah) {
            $realNominal = (float) $upah->total_nominal;
            $subTotalRealUpah += $realNominal;

            $sheet->setCellValue("A{$row}", $noUpah++);
            $sheet->setCellValue("B{$row}", $upah->nama_upah);
            $sheet->setCellValue("C{$row}", $realNominal);
            $sheet->setCellValue("D{$row}", '');

            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderAll);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'SUB TOTAL UPAH');
        $sheet->setCellValue("C{$row}", $subTotalRealUpah);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderAll)->getFont()->setBold(true);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row += 2;

        // B. BAHAN
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", '   B. RINCIAN PEMAKAIAN BAHAN');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleSubHeaderTable);
        $row++;

        $headersBahan = ['NO', 'NAMA BAHAN', 'QTY', 'TOTAL HARGA (Rp)'];
        foreach (range('A', 'D') as $index => $col) {
            $sheet->setCellValue($col . $row, $headersBahan[$index]);
        }
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleHeaderTable);
        $row++;

        $realBahan = $proyek->pembangunanProyekBahan;
        $allBarangIds = $realBahan->pluck('barang_id')->unique();

        $subTotalHargaBahan = 0; $noBahan = 1;
        foreach ($allBarangIds as $barangId) {
            $realItems = $realBahan->where('barang_id', $barangId);
            $firstReal = $realItems->first();
            $namaBarang = $firstReal ? $firstReal->nama_barang : '-';

            $qtyReal = $realItems->sum('jumlah_pakai');
            $hargaReal = $realItems->sum('harga_total_snapshot');
            $subTotalHargaBahan += $hargaReal;

            $sheet->setCellValue("A{$row}", $noBahan++);
            $sheet->setCellValue("B{$row}", $namaBarang);
            $sheet->setCellValue("C{$row}", $qtyReal . ' ' . ($firstReal ? $firstReal->satuan : '-'));
            $sheet->setCellValue("D{$row}", $hargaReal);

            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderAll);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'SUB TOTAL BAHAN');
        $sheet->setCellValue("D{$row}", $subTotalHargaBahan);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderAll)->getFont()->setBold(true);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row += 2;

        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI (UPAH + BAHAN)');
        $sheet->setCellValue("D{$row}", $subTotalRealUpah + $subTotalHargaBahan);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderAll)->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(28);

        $namaProyek = preg_replace('/[^A-Za-z0-9\-]/', '_', $proyek->nama ?? 'Proyek');
        $filename = "Laporan_Termin_Proyek_{$namaProyek}.xlsx";

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) { $writer->save('php://output'); }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function exportLaporanTerminKawasan(string $id)
    {
        $kawasan = \App\Models\PembangunanKawasan::with([
            'pengajuanUpah', 'upah', 'orders.details.barang.baseUnit', 'orders.details.satuanModel', 'pembangunanKawasanBahan'
        ])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Termin Kawasan');

        $styleHeaderTable = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF305496']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $styleSubHeaderTable = [
            'font' => ['bold' => true, 'italic' => true, 'color' => ['argb' => 'FF000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $styleBorderAll = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        $sheet->setCellValue('A1', 'LAPORAN TERMIN KAWASAN');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'Kawasan');
        $sheet->setCellValue('B2', ': ' . ($kawasan->nama ?? '-'));
        $sheet->setCellValue('A3', 'Tgl Export');
        $sheet->setCellValue('B3', ': ' . date('d F Y H:i'));

        $row = 5;

        // A. UPAH
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", '   A. RINCIAN UPAH PEKERJAAN');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleSubHeaderTable);
        $row++;

        $headersUpah = ['NO', 'NAMA PEKERJAAN', 'NOMINAL (Rp)', 'KETERANGAN'];
        foreach (range('A', 'D') as $index => $col) {
            $sheet->setCellValue($col . $row, $headersUpah[$index]);
        }
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleHeaderTable);
        $row++;

        $realUpah = $kawasan->upah;
        $subTotalRealUpah = 0; $noUpah = 1;
        foreach ($realUpah as $upah) {
            $realNominal = (float) $upah->total_nominal;
            $subTotalRealUpah += $realNominal;

            $sheet->setCellValue("A{$row}", $noUpah++);
            $sheet->setCellValue("B{$row}", $upah->nama_upah);
            $sheet->setCellValue("C{$row}", $realNominal);
            $sheet->setCellValue("D{$row}", '');

            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderAll);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'SUB TOTAL UPAH');
        $sheet->setCellValue("C{$row}", $subTotalRealUpah);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderAll)->getFont()->setBold(true);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row += 2;

        // B. BAHAN
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", '   B. RINCIAN PEMAKAIAN BAHAN');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleSubHeaderTable);
        $row++;

        $headersBahan = ['NO', 'NAMA BAHAN', 'QTY', 'TOTAL HARGA (Rp)'];
        foreach (range('A', 'D') as $index => $col) {
            $sheet->setCellValue($col . $row, $headersBahan[$index]);
        }
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleHeaderTable);
        $row++;

        $realBahan = $kawasan->pembangunanKawasanBahan;
        $allBarangIds = $realBahan->pluck('barang_id')->unique();

        $subTotalHargaBahan = 0; $noBahan = 1;
        foreach ($allBarangIds as $barangId) {
            $realItems = $realBahan->where('barang_id', $barangId);
            $firstReal = $realItems->first();
            $namaBarang = $firstReal ? $firstReal->nama_barang : '-';

            $qtyReal = $realItems->sum('jumlah_pakai');
            $hargaReal = $realItems->sum('harga_total_snapshot');
            $subTotalHargaBahan += $hargaReal;

            $sheet->setCellValue("A{$row}", $noBahan++);
            $sheet->setCellValue("B{$row}", $namaBarang);
            $sheet->setCellValue("C{$row}", $qtyReal . ' ' . ($firstReal ? $firstReal->satuan : '-'));
            $sheet->setCellValue("D{$row}", $hargaReal);

            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderAll);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'SUB TOTAL BAHAN');
        $sheet->setCellValue("D{$row}", $subTotalHargaBahan);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderAll)->getFont()->setBold(true);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row += 2;

        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI (UPAH + BAHAN)');
        $sheet->setCellValue("D{$row}", $subTotalRealUpah + $subTotalHargaBahan);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderAll)->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(28);

        $namaKawasan = preg_replace('/[^A-Za-z0-9\-]/', '_', $kawasan->nama ?? 'Kawasan');
        $filename = "Laporan_Termin_Kawasan_{$namaKawasan}.xlsx";

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) { $writer->save('php://output'); }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
