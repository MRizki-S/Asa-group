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

        // Solid black borders
        $borderBlackColor = 'FF000000'; // Black (#000000)

        $styleQcHeader = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A8A']], // Dark Blue
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleCategoryHeader = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']], // Pure Yellow #FFFF00
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleTableHeader = [
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']], // Very Light Gray
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleBorderThin = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleSubtotal = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']], // Light Amber
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $currencyFormat = '"Rp "' . '#,##0';

        // --- BAGIAN HEADER PERUMAHAN & UNIT ---
        $namaPerumahan = $unit->unit->tahap->perumahaan->nama_perumahaan ?? '-';
        $namaUnit = $unit->unit->nama_unit ?? '-';

        $sheet->setCellValue('A1', 'LAPORAN TERMIN PEMBANGUNAN UNIT');
        $sheet->getStyle('A1')->applyFromArray($styleHeaderMain);

        $sheet->setCellValue('A2', "Perumahan: {$namaPerumahan} | Unit: {$namaUnit}");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF475569'));

        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i') . ' WIB');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        $row = 5;
        $grandTotalUpah = 0;
        $grandTotalBahan = 0;

        // Pisahkan QC Reguler dan QC Servis
        $regularQcs = $unit->pembangunanUnitQc->where('is_servis', false)->values();
        $servisQcs = $unit->pembangunanUnitQc->where('is_servis', true)->values();

        // Subtle background fill for table body rows
        $bodyFill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFFAFAFA'] // Clean ultra-light background
        ];

        // Soft Light Red Fill untuk SEL JUMLAH REAL jika melebihi RAB
        $redFill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFFEE2E2'] // Soft Light Red (#FEE2E2)
        ];

        // =========================================================================
        // 1. LOOPING QC REGULER
        // =========================================================================
        foreach ($regularQcs as $indexQc => $qc) {
            $qcTitle = ' QC ' . ($indexQc + 1) . ': ' . strtoupper($qc->nama_qc);

            // --- BARIS QC HEADER ---
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", $qcTitle);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleQcHeader);
            $sheet->getRowDimension($row)->setRowHeight(24);
            $row++;

            $totalQcBahan = 0;
            $totalQcUpah = 0;

            // ==========================================
            // 1. BAHAN RAP
            // ==========================================
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", '   1. BAHAN RAP');
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
                
                // Highlight HANYA KOLOM JUMLAH REAL (D) jika real > RAB
                if ($qtyReal > $qtyRap && $qtyRap > 0) {
                    $sheet->getStyle("D{$row}")->getFill()->applyFromArray($redFill);
                }

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

            // ==========================================
            // 2. BAHAN DILUAR RAP
            // ==========================================
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", '   2. BAHAN DILUAR RAP');
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
                $sheet->setCellValue("A{$row}", 'Tidak ada data bahan diluar RAP.');
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
                $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            // ==========================================
            // 3. UPAH BORONGAN
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

            // ==========================================
            // SUBTOTAL PER QC
            // ==========================================
            $totalQcOverall = $totalQcBahan + $totalQcUpah;
            $grandTotalBahan += $totalQcBahan;
            $grandTotalUpah += $totalQcUpah;

            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'SUBTOTAL REALISASI QC ' . ($indexQc + 1));
            $sheet->setCellValue("E{$row}", $totalQcOverall);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleSubtotal);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row += 3;
        }

        // =========================================================================
        // 2. TABEL AKUMULASI UPAH HARIAN TUKANG (PERSIS UPAHHARIAN.PNG)
        // =========================================================================
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue("A{$row}", 'AKUMULASI UPAH HARIAN TUKANG');
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleQcHeader);
        $sheet->getRowDimension($row)->setRowHeight(24);
        $row++;

        $sheet->setCellValue("A{$row}", 'NO');
        $sheet->setCellValue("B{$row}", 'Nomor Upah Harian');
        $sheet->setCellValue("C{$row}", 'Periode');
        $sheet->setCellValue("D{$row}", 'Nominal Upah');
        $sheet->setCellValue("E{$row}", 'HARGA REAL');
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTableHeader);
        $row++;

        // Untuk sementara data upah harian kosong
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue("A{$row}", 'Tidak ada data upah harian.');
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
        $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'SUBTOTAL REALISASI SERVIS');
        $sheet->setCellValue("E{$row}", 0);
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleSubtotal);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row += 3;

        // =========================================================================
        // 3. BLOK TOTAL BIAYA KESELURUHAN (BAHAN + UPAH) PERTAMA
        // =========================================================================
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
        $row += 4;

        // =========================================================================
        // 4. TABEL SERVIS (SERVIS UNIT)
        // =========================================================================
        $totalServisBahan = 0;
        foreach ($servisQcs as $qc) {
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", 'SERVIS');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleQcHeader);
            $sheet->getRowDimension($row)->setRowHeight(24);
            $row++;

            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", '   RINCIAN ORDER BAHAN SERVIS');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleCategoryHeader);
            $row++;

            $sheet->setCellValue("A{$row}", 'NO');
            $sheet->setCellValue("B{$row}", 'NAMA BAHAN');
            $sheet->setCellValue("C{$row}", 'JUMLAH RAB');
            $sheet->setCellValue("D{$row}", 'JUMLAH REAL');
            $sheet->setCellValue("E{$row}", 'HARGA REAL');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTableHeader);
            $row++;

            $realBahanGroup = $qc->pembangunanUnitBahan->groupBy('barang_id');
            $noServisBahan = 1;

            foreach ($realBahanGroup as $barangId => $rlGroup) {
                $firstReal = $rlGroup->first();
                $namaBarang = $firstReal ? $firstReal->nama_barang : '-';
                $qtyReal = $rlGroup->sum('jumlah_pakai');
                $hargaReal = $rlGroup->sum('harga_total_snapshot');
                $totalServisBahan += $hargaReal;

                $sheet->setCellValue("A{$row}", $noServisBahan++);
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

            if ($realBahanGroup->isEmpty()) {
                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", 'Belum ada order bahan servis.');
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
                $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'SUBTOTAL REALISASI SERVIS');
            $sheet->setCellValue("E{$row}", $totalServisBahan);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleSubtotal);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row += 3;
        }

        // =========================================================================
        // 5. BLOK TOTAL BIAYA KESELURUHAN + SERVIS UNIT (PALING BAWAH)
        // =========================================================================
        $grandTotalBahanWithServis = $grandTotalBahan + $totalServisBahan;

        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI BAHAN');
        $sheet->setCellValue("E{$row}", $grandTotalBahanWithServis);
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

        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL BIAYA KESELURUHAN (BAHAN + UPAH) + Servis Unit');
        $sheet->setCellValue("E{$row}", $grandTotalBahanWithServis + $grandTotalUpah);
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
            'pengawas', 'pembangunanProyekBahan'
        ])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Termin Proyek');

        // Font dasar Arial 10
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Styling Definitions
        $styleHeaderMain = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ];

        // Solid black borders
        $borderBlackColor = 'FF000000';

        // Header Proyek: Biru Dongker (#1E3A8A)
        $styleProyekHeader = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A8A']], // Dark Blue #1E3A8A
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleCategoryHeader = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']], // Pure Yellow #FFFF00
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleTableHeader = [
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']], // Very Light Gray
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleBorderThin = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleSubtotal = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']], // Light Amber
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $bodyFill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFFAFAFA'] // Ultra-light background
        ];

        $currencyFormat = '"Rp "' . '#,##0';

        // Header Informasi
        $namaProyek = $proyek->nama ?? '-';
        $namaPengawas = $proyek->pengawas->nama_lengkap ?? $proyek->pengawas->name ?? '-';

        $sheet->setCellValue('A1', 'LAPORAN TERMIN PEMBANGUNAN PROYEK (KONTRAKTOR)');
        $sheet->getStyle('A1')->applyFromArray($styleHeaderMain);

        $sheet->setCellValue('A2', "Nama Proyek: {$namaProyek} | Pengawas: {$namaPengawas}");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF475569'));

        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i') . ' WIB');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        $row = 5;
        $totalBahan = 0;
        $totalUpah = 0;

        // --- BARIS HEADER PROYEK (WARNA BIRU DONGKER) ---
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", ' PROYEK: ' . strtoupper($namaProyek));
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleProyekHeader);
        $sheet->getRowDimension($row)->setRowHeight(24);
        $row++;

        // ==========================================
        // 1. PEMAKAIAN BAHAN
        // ==========================================
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", '   1. PEMAKAIAN BAHAN');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleCategoryHeader);
        $row++;

        $sheet->setCellValue("A{$row}", 'NO');
        $sheet->setCellValue("B{$row}", 'NAMA BAHAN');
        $sheet->setCellValue("C{$row}", 'JUMLAH REAL');
        $sheet->setCellValue("D{$row}", 'HARGA REAL');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleTableHeader);
        $row++;

        $realBahan = $proyek->pembangunanProyekBahan;
        $realBahanGroup = $realBahan->groupBy('barang_id');
        $noBahan = 1;

        foreach ($realBahanGroup as $barangId => $rlGroup) {
            $firstReal = $rlGroup->first();
            $namaBarang = $firstReal ? $firstReal->nama_barang : '-';
            $qtyReal = $rlGroup->sum('jumlah_pakai');
            $hargaReal = $rlGroup->sum('harga_total_snapshot');

            $totalBahan += $hargaReal;

            $sheet->setCellValue("A{$row}", $noBahan++);
            $sheet->setCellValue("B{$row}", $namaBarang);
            $sheet->setCellValue("C{$row}", $qtyReal > 0 ? (float)$qtyReal . ' ' . ($firstReal ? $firstReal->satuan : '') : '-');
            $sheet->setCellValue("D{$row}", $hargaReal);

            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderThin);
            $sheet->getStyle("A{$row}:D{$row}")->getFill()->applyFromArray($bodyFill);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row++;
        }

        if ($realBahanGroup->isEmpty()) {
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada data pemakaian bahan.');
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
            $sheet->getStyle("A{$row}:D{$row}")->getFill()->applyFromArray($bodyFill);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // ==========================================
        // 2. UPAH HARIAN
        // ==========================================
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", '   2. UPAH HARIAN');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleCategoryHeader);
        $row++;

        $sheet->setCellValue("A{$row}", 'NO');
        $sheet->setCellValue("B{$row}", 'KETERANGAN UPAH HARIAN');
        $sheet->setCellValue("C{$row}", 'JENIS UPAH');
        $sheet->setCellValue("D{$row}", 'NOMINAL REAL');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleTableHeader);
        $row++;

        // Placeholder Upah Harian proyek
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'Tidak ada data upah harian.');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->applyFromArray($bodyFill);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        // ==========================================
        // GRAND TOTAL KESELURUHAN PROYEK
        // ==========================================
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI BAHAN');
        $sheet->setCellValue("D{$row}", $totalBahan);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleSubtotal);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $row++;

        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI UPAH');
        $sheet->setCellValue("D{$row}", $totalUpah);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleSubtotal);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $row++;

        $grandTotalFinalStyle = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']], // Dark Emerald Green
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF15803D']]],
        ];

        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL BIAYA KESELURUHAN (BAHAN + UPAH)');
        $sheet->setCellValue("D{$row}", $totalBahan + $totalUpah);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($grandTotalFinalStyle);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension($row)->setRowHeight(24);

        // Pengaturan lebar kolom presisi (4 Kolom: A, B, C, D)
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(6); // NO
        $sheet->getColumnDimension('B')->setAutoSize(false)->setWidth(42); // Nama Bahan / Keterangan
        $sheet->getColumnDimension('C')->setAutoSize(false)->setWidth(22); // Jumlah Real / Jenis Upah
        $sheet->getColumnDimension('D')->setAutoSize(false)->setWidth(28); // Harga Real / Nominal

        $namaProyekClean = preg_replace('/[^A-Za-z0-9\-]/', '_', $proyek->nama ?? 'Proyek');
        $filename = "Laporan_Termin_Proyek_{$namaProyekClean}.xlsx";

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
            ]
        );
    }

    public function exportLaporanTerminKawasan(string $id)
    {
        $kawasan = \App\Models\PembangunanKawasan::with([
            'perumahan', 'pengawas', 'periodes', 'pembangunanKawasanBahan'
        ])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Termin Kawasan');

        // Font dasar Arial 10
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Styling Definitions
        $styleHeaderMain = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ];

        // Solid black borders
        $borderBlackColor = 'FF000000';

        $stylePeriodeHeader = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A8A']], // Dark Blue
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleCategoryHeader = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']], // Pure Yellow #FFFF00
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleTableHeader = [
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']], // Very Light Gray
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleBorderThin = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $styleSubtotal = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']], // Light Amber
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $borderBlackColor]]],
        ];

        $bodyFill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFFAFAFA'] // Ultra-light background
        ];

        $currencyFormat = '"Rp "' . '#,##0';

        // Header Informasi
        $namaPerumahan = $kawasan->perumahan->nama_perumahaan ?? '-';
        $namaKawasan = $kawasan->nama ?? '-';

        $sheet->setCellValue('A1', 'LAPORAN TERMIN PEMBANGUNAN KAWASAN');
        $sheet->getStyle('A1')->applyFromArray($styleHeaderMain);

        $sheet->setCellValue('A2', "Perumahan: {$namaPerumahan} | Kawasan: {$namaKawasan}");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF475569'));

        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i') . ' WIB');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        $row = 5;
        $grandTotalBahan = 0;
        $grandTotalUpah = 0;

        $periodes = $kawasan->periodes->sortBy('created_at')->values();

        // Loop Per Periode
        foreach ($periodes as $indexPeriode => $periode) {
            $tglMulai = $periode->tanggal_mulai ? \Carbon\Carbon::parse($periode->tanggal_mulai)->format('d M Y') : '-';
            $tglSelesai = $periode->tanggal_selesai ? \Carbon\Carbon::parse($periode->tanggal_selesai)->format('d M Y') : 'Sekarang';
            $periodeTitle = " {$tglMulai} s/d {$tglSelesai}";

            // Header Banner Periode
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", $periodeTitle);
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($stylePeriodeHeader);
            $sheet->getRowDimension($row)->setRowHeight(24);
            $row++;

            $totalPeriodeBahan = 0;
            $totalPeriodeUpah = 0;

            // ==========================================
            // 1. PEMAKAIAN BAHAN
            // ==========================================
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", '   1. PEMAKAIAN BAHAN');
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleCategoryHeader);
            $row++;

            $sheet->setCellValue("A{$row}", 'NO');
            $sheet->setCellValue("B{$row}", 'NAMA BAHAN');
            $sheet->setCellValue("C{$row}", 'JUMLAH REAL');
            $sheet->setCellValue("D{$row}", 'HARGA REAL');
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleTableHeader);
            $row++;

            // Filter bahan kawasan sesuai periode_id (atau tanggal jika periode_id null)
            $bahanPeriode = $kawasan->pembangunanKawasanBahan->filter(function($item) use ($periode) {
                if (isset($item->pembangunan_kawasan_periode_id) && $item->pembangunan_kawasan_periode_id) {
                    return $item->pembangunan_kawasan_periode_id == $periode->id;
                }
                $created = \Carbon\Carbon::parse($item->created_at);
                $pMulai = \Carbon\Carbon::parse($periode->tanggal_mulai);
                $pSelesai = $periode->tanggal_selesai ? \Carbon\Carbon::parse($periode->tanggal_selesai)->endOfDay() : \Carbon\Carbon::now();
                return $created->gte($pMulai) && $created->lte($pSelesai);
            });

            $realBahanGroup = $bahanPeriode->groupBy('barang_id');
            $noBahan = 1;

            foreach ($realBahanGroup as $barangId => $rlGroup) {
                $firstReal = $rlGroup->first();
                $namaBarang = $firstReal ? $firstReal->nama_barang : '-';
                $qtyReal = $rlGroup->sum('jumlah_pakai');
                $hargaReal = $rlGroup->sum('harga_total_snapshot');

                $totalPeriodeBahan += $hargaReal;

                $sheet->setCellValue("A{$row}", $noBahan++);
                $sheet->setCellValue("B{$row}", $namaBarang);
                $sheet->setCellValue("C{$row}", $qtyReal > 0 ? (float)$qtyReal . ' ' . ($firstReal ? $firstReal->satuan : '') : '-');
                $sheet->setCellValue("D{$row}", $hargaReal);

                $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderThin);
                $sheet->getStyle("A{$row}:D{$row}")->getFill()->applyFromArray($bodyFill);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $row++;
            }

            if ($realBahanGroup->isEmpty()) {
                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->setCellValue("A{$row}", 'Tidak ada data pemakaian bahan pada periode ini.');
                $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
                $sheet->getStyle("A{$row}:D{$row}")->getFill()->applyFromArray($bodyFill);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            // ==========================================
            // 2. UPAH HARIAN
            // ==========================================
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", '   2. UPAH HARIAN');
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleCategoryHeader);
            $row++;

            $sheet->setCellValue("A{$row}", 'NO');
            $sheet->setCellValue("B{$row}", 'KETERANGAN UPAH HARIAN');
            $sheet->setCellValue("C{$row}", 'JENIS UPAH');
            $sheet->setCellValue("D{$row}", 'NOMINAL REAL');
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleTableHeader);
            $row++;

            // Placeholder Upah Harian kawasan
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada data upah harian.');
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
            $sheet->getStyle("A{$row}:D{$row}")->getFill()->applyFromArray($bodyFill);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;

            // ==========================================
            // SUBTOTAL PER PERIODE
            // ==========================================
            $totalPeriodeOverall = $totalPeriodeBahan + $totalPeriodeUpah;
            $grandTotalBahan += $totalPeriodeBahan;
            $grandTotalUpah += $totalPeriodeUpah;

            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->setCellValue("A{$row}", 'SUBTOTAL REALISASI PERIODE (' . $tglMulai . ' s/d ' . $tglSelesai . ')');
            $sheet->setCellValue("D{$row}", $totalPeriodeOverall);
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleSubtotal);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row += 4; // Spasi antar blok Periode
        }

        if ($periodes->isEmpty()) {
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'Belum ada riwayat periode pembangunan kawasan.');
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row += 2;
        }

        // ==========================================
        // GRAND TOTAL KESELURUHAN KAWASAN
        // ==========================================
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI BAHAN');
        $sheet->setCellValue("D{$row}", $grandTotalBahan);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleSubtotal);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $row++;

        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL REALISASI UPAH');
        $sheet->setCellValue("D{$row}", $grandTotalUpah);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleSubtotal);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $row++;

        $grandTotalFinalStyle = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']], // Dark Emerald Green
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF15803D']]],
        ];

        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL BIAYA KESELURUHAN (BAHAN + UPAH)');
        $sheet->setCellValue("D{$row}", $grandTotalBahan + $grandTotalUpah);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($grandTotalFinalStyle);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension($row)->setRowHeight(24);

        // Pengaturan lebar kolom presisi (4 Kolom: A, B, C, D)
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(6); // NO
        $sheet->getColumnDimension('B')->setAutoSize(false)->setWidth(42); // Nama Bahan / Keterangan
        $sheet->getColumnDimension('C')->setAutoSize(false)->setWidth(22); // Jumlah Real / Jenis Upah
        $sheet->getColumnDimension('D')->setAutoSize(false)->setWidth(28); // Harga Real / Nominal

        $namaKawasanClean = preg_replace('/[^A-Za-z0-9\-]/', '_', $kawasan->nama ?? 'Kawasan');
        $filename = "Laporan_Termin_Kawasan_{$namaKawasanClean}.xlsx";

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
            ]
        );
    }
}
