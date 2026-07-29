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
            'unit.tahap.perumahaan',
            'pembangunanUnitQc' => function ($query) {
                $query->orderBy('qc_urutan_ke', 'asc');
            },
            'pembangunanUnitQc.pembangunanUnitRapUpah',
            'pembangunanUnitQc.pembangunanUnitUpah',
            'pembangunanUnitQc.pembangunanUnitRapBahan.barang.baseUnit',
            'pembangunanUnitQc.pembangunanUnitBahan.barang',
        ])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Termin');

        // Font dasar Arial
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Styling definitions
        $styleHeaderMain = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ];

        // Soft & clean light borders
        $lightBorderColor = 'FFCBD5E1'; // Soft Gray (#CBD5E1)

        $styleQcHeader = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A8A']], // Dark Blue
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1E3A8A']]],
        ];

        $styleCategoryHeader = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF1E293B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']], // Soft Gray
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $lightBorderColor]]],
        ];

        $styleTableHeader = [
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']], // Very Light Gray
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $lightBorderColor]]],
        ];

        $styleBorderThin = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $lightBorderColor]]],
        ];

        $styleSubtotal = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']], // Light Amber
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFCD34D']]],
        ];

        $currencyFormat = '"Rp "' . '#,##0';

        // --- BAGIAN HEADER PERUMAHAN & UNIT ---
        $namaPerumahan = $unit->unit->tahap->perumahaan->nama_perumahaan ?? '-';
        $namaUnit = $unit->unit->nama_unit ?? '-';

        $sheet->setCellValue('A1', 'LAPORAN TERMIN PROYEK & PEMBANGUNAN UNIT');
        $sheet->getStyle('A1')->applyFromArray($styleHeaderMain);

        $sheet->setCellValue('A2', "Perumahan: {$namaPerumahan} | Unit: {$namaUnit}");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF475569'));

        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i') . ' WIB');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        $row = 5;
        $grandTotalUpah = 0;
        $grandTotalBahan = 0;

        foreach ($unit->pembangunanUnitQc as $indexQc => $qc) {
            $qcTitle = $qc->is_servis 
                ? ' SERVIS' 
                : ' QC ' . ($indexQc + 1) . ': ' . strtoupper($qc->nama_qc);

            // --- BARIS QC HEADER ---
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", $qcTitle);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleQcHeader);
            $sheet->getRowDimension($row)->setRowHeight(24);
            $row++;

            $totalQcBahan = 0;
            $totalQcUpah = 0;

            // Subtle background fill for table body rows
            $bodyFill = [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFAFAFA'] // Clean ultra-light background
            ];

            if (!$qc->is_servis) {
                // ==========================================
                // 1. KATEGORI BAHAN (RAP)
                // ==========================================
                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", '   1. RINCIAN BAHAN RAP');
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleCategoryHeader);
                $row++;

                $sheet->setCellValue("A{$row}", 'NO');
                $sheet->setCellValue("B{$row}", 'NAMA BAHAN');
                $sheet->setCellValue("C{$row}", 'JUMLAH RAB');
                $sheet->setCellValue("D{$row}", 'JUMLAH REAL');
                $sheet->setCellValue("E{$row}", 'HARGA REAL');
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTableHeader);
                $row++;

                $rapBahanGroup = $qc->pembangunanUnitRapBahan->groupBy('barang_id');
                $realBahanGroup = $qc->pembangunanUnitBahan->groupBy('barang_id');
                $rapBarangIds = $rapBahanGroup->keys();

                $noBahan = 1;
                foreach ($rapBarangIds as $barangId) {
                    $rGroup = $rapBahanGroup->get($barangId);
                    $rlGroup = $realBahanGroup->get($barangId);

                    $firstRap = $rGroup ? $rGroup->first() : null;
                    $firstReal = $rlGroup ? $rlGroup->first() : null;

                    $namaBarang = $firstRap ? $firstRap->nama_barang : ($firstReal ? $firstReal->nama_barang : '-');
                    $baseUnitName = $firstRap && $firstRap->barang && $firstRap->barang->baseUnit 
                        ? $firstRap->barang->baseUnit->nama 
                        : ($firstRap ? $firstRap->satuan : ($firstReal ? $firstReal->satuan : '-'));

                    $qtyRap = $rGroup ? $rGroup->map(fn($r) => (float)$r->jumlah_standar * (float)$r->faktor_konversi)->sum() : 0;
                    $qtyReal = $rlGroup ? $rlGroup->sum('jumlah_pakai') : 0;
                    $hargaReal = $rlGroup ? $rlGroup->sum('harga_total_snapshot') : 0;

                    $totalQcBahan += $hargaReal;

                    $sheet->setCellValue("A{$row}", $noBahan++);
                    $sheet->setCellValue("B{$row}", $namaBarang);
                    $sheet->setCellValue("C{$row}", $qtyRap > 0 ? (float)$qtyRap . ' ' . $baseUnitName : '-');
                    $sheet->setCellValue("D{$row}", $qtyReal > 0 ? (float)$qtyReal . ' ' . ($firstReal ? $firstReal->satuan : $baseUnitName) : '-');
                    $sheet->setCellValue("E{$row}", $hargaReal);

                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin);
                    $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
                    $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $row++;
                }
                if ($rapBarangIds->isEmpty()) {
                    $sheet->mergeCells("A{$row}:E{$row}");
                    $sheet->setCellValue("A{$row}", 'Tidak ada data bahan RAP.');
                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
                    $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $row++;
                }
                $row++; // Spasi antar kategori
            } else {
                $rapBahanGroup = collect();
                $realBahanGroup = $qc->pembangunanUnitBahan->groupBy('barang_id');
                $rapBarangIds = collect();
            }

            // ==========================================
            // 2. KATEGORI BAHAN DILUAR RAP (Atau Bahan Servis)
            // ==========================================
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", $qc->is_servis ? '   RINCIAN ORDER BAHAN SERVIS' : '   2. RINCIAN BAHAN DILUAR RAP');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleCategoryHeader);
            $row++;

            $sheet->setCellValue("A{$row}", 'NO');
            $sheet->setCellValue("B{$row}", 'NAMA BAHAN');
            $sheet->setCellValue("C{$row}", 'JUMLAH RAB');
            $sheet->setCellValue("D{$row}", 'JUMLAH REAL');
            $sheet->setCellValue("E{$row}", 'HARGA REAL');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTableHeader);
            $row++;

            $diluarRabBarangIds = $realBahanGroup->keys()->diff($rapBarangIds);
            $noExtraBahan = 1;

            foreach ($diluarRabBarangIds as $barangId) {
                $rlGroup = $realBahanGroup->get($barangId);
                $firstReal = $rlGroup ? $rlGroup->first() : null;

                $namaBarang = $firstReal ? $firstReal->nama_barang : '-';
                $qtyReal = $rlGroup ? $rlGroup->sum('jumlah_pakai') : 0;
                $hargaReal = $rlGroup ? $rlGroup->sum('harga_total_snapshot') : 0;

                $totalQcBahan += $hargaReal;

                $sheet->setCellValue("A{$row}", $noExtraBahan++);
                $sheet->setCellValue("B{$row}", $namaBarang);
                $sheet->setCellValue("C{$row}", '-');
                $sheet->setCellValue("D{$row}", $qtyReal > 0 ? (float)$qtyReal . ' ' . ($firstReal ? $firstReal->satuan : '') : '-');
                $sheet->setCellValue("E{$row}", $hargaReal);

                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin);
                $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $row++;
            }
            if ($diluarRabBarangIds->isEmpty()) {
                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", $qc->is_servis ? 'Belum ada order bahan servis.' : 'Tidak ada data bahan diluar RAP.');
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
                $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }
            $row++; // Spasi antar kategori

            if (!$qc->is_servis) {
                // ==========================================
                // 3. UPAH BORONGAN (QC Reguler Only)
                // ==========================================
                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", '   3. UPAH BORONGAN');
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleCategoryHeader);
                $row++;

                $sheet->setCellValue("A{$row}", 'NO');
                $sheet->setCellValue("B{$row}", 'NAMA UPAH BORONGAN');
                $sheet->setCellValue("C{$row}", 'NOMINAL RAB');
                $sheet->setCellValue("D{$row}", 'JENIS UPAH');
                $sheet->setCellValue("E{$row}", 'NOMINAL REAL');
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTableHeader);
                $row++;

                $rapUpah = $qc->pembangunanUnitRapUpah;
                $realUpah = $qc->pembangunanUnitUpah;

                $noUpah = 1;
                $boronganNames = $rapUpah->pluck('nama_upah')->merge($realUpah->pluck('nama_upah'))->unique();

                foreach ($boronganNames as $uName) {
                    $nomRab = $rapUpah->where('nama_upah', $uName)->sum('nominal_standar');
                    $nomReal = $realUpah->where('nama_upah', $uName)->sum('total_nominal');
                    $totalQcUpah += $nomReal;

                    $sheet->setCellValue("A{$row}", $noUpah++);
                    $sheet->setCellValue("B{$row}", $uName);
                    $sheet->setCellValue("C{$row}", $nomRab > 0 ? $nomRab : 0);
                    $sheet->setCellValue("D{$row}", 'Borongan');
                    $sheet->setCellValue("E{$row}", $nomReal);

                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin);
                    $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$row}:E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
                    $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $row++;
                }

                if ($boronganNames->isEmpty()) {
                    $sheet->mergeCells("A{$row}:E{$row}");
                    $sheet->setCellValue("A{$row}", 'Tidak ada data upah borongan.');
                    $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
                    $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $row++;
                }
                $row++; // Spasi antar kategori

                // ==========================================
                // 4. UPAH HARIAN (Placeholder Gudang)
                // ==========================================
                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", '   4. UPAH HARIAN (GUDANG)');
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleCategoryHeader);
                $row++;

                $sheet->setCellValue("A{$row}", 'NO');
                $sheet->setCellValue("B{$row}", 'KETERANGAN UPAH HARIAN');
                $sheet->setCellValue("C{$row}", 'NOMINAL RAB');
                $sheet->setCellValue("D{$row}", 'JENIS UPAH');
                $sheet->setCellValue("E{$row}", 'NOMINAL REAL');
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTableHeader);
                $row++;

                // Placeholder Upah Harian dari modul Gudang
                $sheet->setCellValue("A{$row}", 1);
                $sheet->setCellValue("B{$row}", 'Total Upah Harian (Gudang)');
                $sheet->setCellValue("C{$row}", '-');
                $sheet->setCellValue("D{$row}", 'Harian');
                $sheet->setCellValue("E{$row}", 0);

                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin);
                $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $row++;
                $row++; // Spasi sebelum subtotal
            }

            // ==========================================
            // SUBTOTAL PER QC
            // ==========================================
            $totalQcOverall = $totalQcBahan + $totalQcUpah;
            $grandTotalBahan += $totalQcBahan;
            $grandTotalUpah += $totalQcUpah;

            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'SUBTOTAL REALISASI ' . ($qc->is_servis ? 'SERVIS' : 'QC ' . ($indexQc + 1)));
            $sheet->setCellValue("E{$row}", $totalQcOverall);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleSubtotal);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row += 4; // Spasi lebih besar (4 baris) antar blok QC
        }

        // ==========================================
        // GRAND TOTAL KESELURUHAN
        // ==========================================
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI BAHAN');
        $sheet->setCellValue("E{$row}", $grandTotalBahan);
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleSubtotal);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $row++;

        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI UPAH');
        $sheet->setCellValue("E{$row}", $grandTotalUpah);
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleSubtotal);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $row++;

        $grandTotalFinalStyle = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']], // Dark Emerald Green
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF15803D']]],
        ];

        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL BIAYA KESELURUHAN (BAHAN + UPAH)');
        $sheet->setCellValue("E{$row}", $grandTotalBahan + $grandTotalUpah);
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($grandTotalFinalStyle);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension($row)->setRowHeight(24);

        // Pengaturan lebar kolom presisi (Fit & Rapi)
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(6); // Kolom NO ringkas & fit
        $sheet->getColumnDimension('B')->setAutoSize(false)->setWidth(38); // Nama Bahan / Upah
        $sheet->getColumnDimension('C')->setAutoSize(false)->setWidth(20); // Jumlah RAB
        $sheet->getColumnDimension('D')->setAutoSize(false)->setWidth(20); // Jumlah Real / Jenis Upah
        $sheet->getColumnDimension('E')->setAutoSize(false)->setWidth(25); // Harga Real / Nominal

        $namaUnitClean = preg_replace('/[^A-Za-z0-9\-]/', '_', $unit->unit->nama_unit ?? 'Unit');
        $filename = "Laporan_Termin_Per_QC_{$namaUnitClean}.xlsx";

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
