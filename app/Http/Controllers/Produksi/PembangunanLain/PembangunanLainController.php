<?php

namespace App\Http\Controllers\Produksi\PembangunanLain;

use App\Http\Controllers\Controller;
use App\Models\PembangunanLain;
use App\Models\PembangunanLainSub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class PembangunanLainController extends Controller
{
    /**
     * Tampilkan daftar proyek lain-lain dan sub-pekerjaannya
     */
    public function index(Request $request)
    {
        $statusFilter = $request->input('status', 'all');
        $search = $request->input('search');

        $query = PembangunanLain::with(['subs' => function ($q) {
            $q->withCount('orders')->latest();
        }])
        ->withCount(['subs', 'orders'])
        ->latest();

        if ($statusFilter !== 'all' && in_array($statusFilter, ['pending', 'proses', 'selesai'])) {
            $query->where('status', $statusFilter);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_proyek', 'like', "%{$search}%")
                  ->orWhereHas('subs', function ($subQ) use ($search) {
                      $subQ->where('nama_sub', 'like', "%{$search}%");
                  });
            });
        }

        $allPembangunanLain = $query->get();

        return view('produksi.pembangunan_lain.index', compact('allPembangunanLain', 'statusFilter', 'search'));
    }

    /**
     * Simpan Induk Proyek Baru beserta Sub-Pekerjaan pertamanya (Wajib minimal 1 sub)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_proyek'     => 'required|string|max:255',
            'status'          => 'nullable|in:pending,proses,selesai',
            'nama_sub'        => 'nullable|string|max:255',
            'catatan'         => 'nullable|string',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $proyek = PembangunanLain::create([
                'nama_proyek' => trim($request->nama_proyek),
                'status'      => $request->status ?? 'proses',
            ]);

            // Jika nama sub diisi, gunakan nama tersebut. Jika kosong, default sama dengan nama proyek.
            $subName = !empty($request->nama_sub) ? trim($request->nama_sub) : trim($request->nama_proyek);

            $proyek->subs()->create([
                'nama_sub'        => $subName,
                'status'          => $request->status ?? 'proses',
                'tanggal_mulai'   => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'catatan'         => $request->catatan,
            ]);

            DB::commit();

            return redirect()->route('produksi.pembangunanLain.index')
                ->with('success', "Proyek '{$proyek->nama_proyek}' berhasil dibuat bersama sub-pekerjaan awalnya.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat proyek: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update data Induk Proyek
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_proyek' => 'required|string|max:255',
            'status'      => 'required|in:pending,proses,selesai',
        ]);

        $proyek = PembangunanLain::findOrFail($id);

        $proyek->update([
            'nama_proyek' => trim($request->nama_proyek),
            'status'      => $request->status,
        ]);

        return redirect()->route('produksi.pembangunanLain.index')
            ->with('success', "Data proyek '{$proyek->nama_proyek}' berhasil diperbarui.");
    }

    /**
     * Hapus Induk Proyek (Hanya jika belum memiliki riwayat order barang)
     */
    public function destroy($id)
    {
        $proyek = PembangunanLain::withCount('orders')->findOrFail($id);

        if ($proyek->orders_count > 0) {
            return redirect()->back()
                ->with('error', "Proyek '{$proyek->nama_proyek}' tidak dapat dihapus karena sudah memiliki riwayat transaksi barang keluar.");
        }

        DB::beginTransaction();
        try {
            $proyek->subs()->delete();
            $proyek->delete();

            DB::commit();

            return redirect()->route('produksi.pembangunanLain.index')
                ->with('success', "Proyek '{$proyek->nama_proyek}' berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus proyek: ' . $e->getMessage());
        }
    }

    /**
     * Tambah Sub-Pekerjaan baru di bawah Induk Proyek
     */
    public function storeSub(Request $request, $id)
    {
        $request->validate([
            'nama_sub'        => 'required|string|max:255',
            'status'          => 'nullable|in:pending,proses,selesai',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'catatan'         => 'nullable|string',
        ]);

        $proyek = PembangunanLain::findOrFail($id);

        $sub = $proyek->subs()->create([
            'nama_sub'        => trim($request->nama_sub),
            'status'          => $request->status ?? 'proses',
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'catatan'         => $request->catatan,
        ]);

        return redirect()->route('produksi.pembangunanLain.index')
            ->with('success', "Sub-pekerjaan '{$sub->nama_sub}' berhasil ditambahkan ke '{$proyek->nama_proyek}'.");
    }

    /**
     * Update data Sub-Pekerjaan
     */
    public function updateSub(Request $request, $subId)
    {
        $request->validate([
            'nama_sub'        => 'required|string|max:255',
            'status'          => 'required|in:pending,proses,selesai',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'catatan'         => 'nullable|string',
        ]);

        $sub = PembangunanLainSub::findOrFail($subId);

        $sub->update([
            'nama_sub'        => trim($request->nama_sub),
            'status'          => $request->status,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'catatan'         => $request->catatan,
        ]);

        return redirect()->route('produksi.pembangunanLain.index')
            ->with('success', "Sub-pekerjaan '{$sub->nama_sub}' berhasil diperbarui.");
    }

    /**
     * Hapus Sub-Pekerjaan (Wajib menjaga minimal 1 sub per induk, dan cek riwayat order)
     */
    public function destroySub($subId)
    {
        $sub = PembangunanLainSub::withCount('orders')->findOrFail($subId);

        // 1. Cek syarat wajib: 1 Induk minimal memiliki 1 Sub
        $siblingCount = PembangunanLainSub::where('pembangunan_lain_id', $sub->pembangunan_lain_id)->count();
        if ($siblingCount <= 1) {
            return redirect()->back()
                ->with('error', "Sub-pekerjaan '{$sub->nama_sub}' tidak dapat dihapus karena setiap proyek wajib memiliki minimal 1 sub-pekerjaan.");
        }

        // 2. Cek apakah sudah pernah ada transaksi order barang
        if ($sub->orders_count > 0) {
            return redirect()->back()
                ->with('error', "Sub-pekerjaan '{$sub->nama_sub}' tidak dapat dihapus karena sudah memiliki riwayat transaksi barang keluar.");
        }

        $sub->delete();

        return redirect()->route('produksi.pembangunanLain.index')
            ->with('success', "Sub-pekerjaan '{$sub->nama_sub}' berhasil dihapus.");
    }

    /**
     * Export Laporan Termin Excel untuk seluruh Sub-Pekerjaan di bawah Induk Proyek
     */
    public function exportTermin($id)
    {
        $proyek = PembangunanLain::with([
            'subs' => function ($q) {
                $q->with('bahan.barang.baseUnit')->orderBy('id', 'asc');
            }
        ])->findOrFail($id);

        return $this->generateTerminSpreadsheet($proyek, $proyek->subs);
    }

    /**
     * Export Laporan Termin Excel untuk 1 Sub-Pekerjaan tertentu
     */
    public function exportTerminSub($subId)
    {
        $sub = PembangunanLainSub::with([
            'pembangunanLain',
            'bahan.barang.baseUnit',
        ])->findOrFail($subId);

        $proyek = $sub->pembangunanLain;

        return $this->generateTerminSpreadsheet($proyek, collect([$sub]), $sub);
    }

    /**
     * Generator Spreadsheet Laporan Termin Pembangunan Lain-lain
     */
    private function generateTerminSpreadsheet(PembangunanLain $proyek, $subs, ?PembangunanLainSub $singleSub = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Termin');

        // Font dasar Arial 10
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Styling Definitions
        $styleHeaderMain = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ];

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
            'startColor' => ['argb' => 'FFFAFAFA'], // Ultra-light background
        ];

        $currencyFormat = '"Rp "' . '#,##0';

        // Header Informasi
        $namaProyek = $proyek->nama_proyek ?? '-';
        $sheet->setCellValue('A1', 'LAPORAN TERMIN PEMBANGUNAN LAIN-LAIN');
        $sheet->getStyle('A1')->applyFromArray($styleHeaderMain);

        if ($singleSub) {
            $sheet->setCellValue('A2', "Proyek: {$namaProyek} | Sub-Pekerjaan: {$singleSub->nama_sub}");
        } else {
            $sheet->setCellValue('A2', "Proyek: {$namaProyek}");
        }
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF475569'));

        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i') . ' WIB');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new Color('FF64748B'));

        $row = 5;
        $grandTotalBahan = 0;
        $grandTotalUpah = 0;

        foreach ($subs as $sub) {
            $tglMulai = $sub->tanggal_mulai ? $sub->tanggal_mulai->format('d M Y') : '-';
            $tglSelesai = $sub->tanggal_selesai ? $sub->tanggal_selesai->format('d M Y') : 'Sekarang';
            $subTitle = " SUB-PEKERJAAN: " . strtoupper($sub->nama_sub) . " ({$tglMulai} s/d {$tglSelesai})";

            // Header Banner Sub-Pekerjaan
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", $subTitle);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($stylePeriodeHeader);
            $sheet->getRowDimension($row)->setRowHeight(24);
            $row++;

            $totalSubBahan = 0;
            $totalSubUpah = 0;

            // ==========================================
            // 1. PEMAKAIAN BAHAN
            // ==========================================
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", '   1. PEMAKAIAN BAHAN');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleCategoryHeader);
            $row++;

            $sheet->setCellValue("A{$row}", 'NO');
            $sheet->setCellValue("B{$row}", 'NAMA BAHAN');
            $sheet->setCellValue("C{$row}", 'JUMLAH REAL');
            $sheet->setCellValue("D{$row}", '');
            $sheet->setCellValue("E{$row}", 'HARGA REAL');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTableHeader);
            $row++;

            // Ambil data bahan yang jumlah pakainya > 0
            $bahanItems = $sub->bahan ? $sub->bahan->filter(function ($item) {
                return (float) $item->jumlah_pakai > 0;
            })->values() : collect();

            $noBahan = 1;
            foreach ($bahanItems as $bahan) {
                $namaBarang = $bahan->nama_barang ?? ($bahan->barang?->nama_barang ?? '-');
                $satuan = $bahan->satuan ?: ($bahan->barang?->baseUnit?->nama ?? '');
                $qtyReal = (float) $bahan->jumlah_pakai;
                $hargaReal = (float) $bahan->harga_total_snapshot;

                $totalSubBahan += $hargaReal;

                $sheet->setCellValue("A{$row}", $noBahan++);
                $sheet->setCellValue("B{$row}", $namaBarang);
                $sheet->setCellValue("C{$row}", $qtyReal > 0 ? $qtyReal . ' ' . $satuan : '-');
                $sheet->setCellValue("D{$row}", '');
                $sheet->setCellValue("E{$row}", $hargaReal);

                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin);
                $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $row++;
            }

            if ($bahanItems->isEmpty()) {
                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", 'Tidak ada data pemakaian bahan pada sub-pekerjaan ini.');
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
                $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            // ==========================================
            // 2. UPAH HARIAN TUKANG
            // ==========================================
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", '   2. UPAH HARIAN TUKANG');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleCategoryHeader);
            $row++;

            $sheet->setCellValue("A{$row}", 'NO');
            $sheet->setCellValue("B{$row}", 'Nomor Upah Harian');
            $sheet->setCellValue("C{$row}", 'Periode');
            $sheet->setCellValue("D{$row}", 'Nominal Upah');
            $sheet->setCellValue("E{$row}", 'HARGA REAL');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleTableHeader);
            $row++;

            // Upah Harian Tukang (Placeholder siap pakai, saat ini belum ada modul upah untuk pembangunan lain-lain)
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada data upah harian pada sub-pekerjaan ini.');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
            $sheet->getStyle("A{$row}:E{$row}")->getFill()->applyFromArray($bodyFill);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;

            // ==========================================
            // SUBTOTAL PER SUB-PEKERJAAN
            // ==========================================
            $totalSubOverall = $totalSubBahan + $totalSubUpah;
            $grandTotalBahan += $totalSubBahan;
            $grandTotalUpah += $totalSubUpah;

            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'SUBTOTAL REALISASI SUB-PEKERJAAN (' . strtoupper($sub->nama_sub) . ')');
            $sheet->setCellValue("E{$row}", $totalSubOverall);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($styleSubtotal);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row += 3; // Spasi antar blok Sub-Pekerjaan
        }

        if ($subs->isEmpty()) {
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'Belum ada riwayat sub-pekerjaan pembangunan lain-lain.');
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleBorderThin)->getFont()->setItalic(true);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row += 3;
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

        // Lebar Kolom
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(6);  // NO
        $sheet->getColumnDimension('B')->setAutoSize(false)->setWidth(38); // Nama Bahan / Nomor Upah Harian
        $sheet->getColumnDimension('C')->setAutoSize(false)->setWidth(30); // Qty / Periode
        $sheet->getColumnDimension('D')->setAutoSize(false)->setWidth(25); // Nominal Upah
        $sheet->getColumnDimension('E')->setAutoSize(false)->setWidth(28); // HARGA REAL

        $cleanProyek = preg_replace('/[^A-Za-z0-9\-]/', '_', $proyek->nama_proyek ?? 'Proyek');
        if ($singleSub) {
            $cleanSub = preg_replace('/[^A-Za-z0-9\-]/', '_', $singleSub->nama_sub ?? 'Sub');
            $filename = "Laporan_Termin_Pembangunan_Lain_{$cleanProyek}_{$cleanSub}.xlsx";
        } else {
            $filename = "Laporan_Termin_Pembangunan_Lain_{$cleanProyek}.xlsx";
        }

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

