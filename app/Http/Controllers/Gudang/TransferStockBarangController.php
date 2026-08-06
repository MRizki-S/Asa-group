<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\StockGudang;
use App\Models\StockLedger;
use App\Models\Ubs;
use App\Services\NotificationGroupService;
use App\Models\TransferStock;
use App\Models\TransferStockDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferStockBarangController extends Controller
{

    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    // menampilkan daftar transfer stock
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $bulan  = (int) $request->get('bulan', now()->month);
        $tahun  = (int) $request->get('tahun', now()->year);

        $query = TransferStock::with(['fromUbs', 'toUbs', 'creator', 'approvedBy'])
            ->whereMonth('tanggal_transfer', $bulan)
            ->whereYear('tanggal_transfer', $tahun);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $transfers = $query->orderByDesc('created_at')->get();

        return view('gudang.stock-barang.transfer-stock.index-daftarTransfer', [
            'transfers'     => $transfers,
            'currentStatus' => $status,
            'bulan'         => $bulan,
            'tahun'         => $tahun,
            'breadcrumbs'   => [
                ['label' => 'Stock Barang', 'url' => route('gudang.stockBarang.index')],
                ['label' => 'Daftar Transfer Stock', 'url' => '#'],
            ],
        ]);
    }

    public function create()
    {
        $masterBarangs = MasterBarang::select('id', 'kode_barang', 'nama_barang')->get();
        $ubsList = Ubs::select('id', 'nama_ubs', 'kode_ubs')->get();

        return view('gudang.stock-barang.transfer-stock.transfer', [
            'masterBarangs' => $masterBarangs,
            'ubsList' => $ubsList,
            'breadcrumbs' => [
                [
                    'label' => 'Stock Barang',
                    'url' => route('gudang.stockBarang.index'),
                ],
                [
                    'label' => 'Daftar Transfer Stock',
                    'url' => route('gudang.transferStockBarang.daftar.index'),
                ],
                [
                    'label' => 'Transfer Stock',
                    'url' => '#',
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'dari_ubs_id' => 'required|exists:ubs,id',
            'ke_ubs_id' => 'required|exists:ubs,id|different:dari_ubs_id',
            'tanggal_transfer' => 'required|date',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.jumlah_masuk' => 'required|numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();

            $tanggalTransfer = $request->tanggal_transfer;
            $dariUbsId = $request->dari_ubs_id;
            $keUbsId = $request->ke_ubs_id;

            // Generate nomor transfer unik
            $prefix = 'TRF-' . now()->format('Ymd') . '-';
            $lastNomor = TransferStock::where('nomor_transfer', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('nomor_transfer')
                ->value('nomor_transfer');

            $nextSeq = $lastNomor ? (intval(substr($lastNomor, -4)) + 1) : 1;
            $nomorTransfer = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            // Buat header Transfer Stock dengan status pending
            $transfer = TransferStock::create([
                'nomor_transfer' => $nomorTransfer,
                'tanggal_transfer' => $tanggalTransfer,
                'dari_stock_type' => 'UBS',
                'dari_ubs_id' => $dariUbsId,
                'ke_stock_type' => 'UBS',
                'ke_ubs_id' => $keUbsId,
                'status' => 'pending',
                'keterangan' => $request->keterangan,
                'created_by' => Auth::id(),
            ]);

            $transferId = $transfer->id;

            $itemsText = '';
            $no = 1;
            foreach ($request->items as $item) {
                $barangId = $item['barang_id'];
                $satuanId = $item['satuan_id'];
                $jumlahTransfer = $item['jumlah_masuk'];

                $barang = MasterBarang::findOrFail($barangId);
                $namaBarang = $barang->nama_barang;

                $satuanObj = MasterSatuan::find($satuanId);
                $namaSatuan = $satuanObj->nama ?? '-';

                $konversi = BarangSatuanKonversi::where('barang_id', $barangId)
                    ->where('satuan_id', $satuanId)
                    ->value('konversi_ke_base') ?? 1;

                $jumlahBaseTransfer = $jumlahTransfer * $konversi;

                $transfer->details()->create([
                    'barang_id' => $barangId,
                    'qty' => $jumlahTransfer,
                    'satuan_id' => $satuanId,
                    'qty_base' => $jumlahBaseTransfer,
                    'nama_barang_snapshot' => $namaBarang,
                ]);

                $itemsText .= "{$no}. {$namaBarang} — " . (float)$jumlahTransfer . " {$namaSatuan}\n";
                $no++;
            }

            DB::commit();

            // Kirim notifikasi WA — Pengajuan Baru
            $groupId = env('FONNTE_ID_GROUP_GUDANG_STOCK');
            $fromUbs  = $transfer->fromUbs()->first();
            $toUbs    = $transfer->toUbs()->first();
            $pengaju  = Auth::user();
            $roleName = $pengaju->getRoleNames()->first() ?? '-';
            $tanggalFmt = \Carbon\Carbon::parse($tanggalTransfer)->translatedFormat('d M Y');

            $messageStore =
                "📦 *PENGAJUAN TRANSFER STOCK GUDANG*\n\n" .
                "No. Transfer : {$nomorTransfer}\n" .
                "Gudang Asal  : " . ($fromUbs->nama_ubs ?? '-') . " (" . ($fromUbs->kode_ubs ?? '-') . ")\n" .
                "Gudang Tujuan: " . ($toUbs->nama_ubs  ?? '-') . " (" . ($toUbs->kode_ubs  ?? '-') . ")\n" .
                "Tgl Diajukan : {$tanggalFmt}\n\n" .
                "*Barang yang Ditransfer:*\n" .
                $itemsText .
                "\nDiajukan Oleh: {$pengaju->nama_lengkap} ({$roleName})\n\n" .
                "⏳ Menunggu persetujuan SPV Logistik & Pengadaan.";

            if ($groupId) {
                $this->notificationGroup->send($groupId, $messageStore);
            }

            return redirect()->route('gudang.transferStockBarang.daftar.index')
                ->with('success', 'Pengajuan transfer stock berhasil dikirim dan menunggu persetujuan SPV.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal mengajukan transfer: ' . $e->getMessage()])->withInput();
        }
    }

    // Get list of units for a barang AND its current stock in selected UBS
    public function getSatuanDanStok($barangId, $ubsId)
    {
        $satuans = BarangSatuanKonversi::with('satuan')
            ->where('barang_id', $barangId)
            ->orderByDesc('is_default')
            ->get();

        $stockUbsBase = StockGudang::where('barang_id', $barangId)
            ->where('stock_type', 'UBS')
            ->where('ubs_id', $ubsId)
            ->value('jumlah_stock') ?? 0;

        $result = $satuans->map(function($sat) use ($stockUbsBase) {
            $konversi = (float)$sat->konversi_ke_base;
            $stockInUnit = $stockUbsBase / $konversi;

            return [
                'id' => $sat->satuan_id,
                'nama' => $sat->satuan->nama ?? '-',
                'is_default' => $sat->is_default,
                'konversi_ke_base' => $konversi,
                // frontend Alpine menggunakan property stock_hub_saat_ini untuk menampilkan stock gudang asal
                'stock_hub_saat_ini' => floor($stockInUnit) == $stockInUnit
                    ? $stockInUnit
                    : number_format($stockInUnit, 2, ',', ''),
            ];
        });

        return response()->json($result);
    }

    public function edit($nomorTransfer)
    {
        $transfer = TransferStock::with(['details.barang', 'details.satuan'])
            ->where('nomor_transfer', $nomorTransfer)
            ->where('status', 'ditolak')
            ->firstOrFail();

        $masterBarangs = MasterBarang::select('id', 'kode_barang', 'nama_barang')->get();
        $ubsList = Ubs::select('id', 'nama_ubs', 'kode_ubs')->get();

        // Siapkan data item untuk Alpine.js
        $existingItems = $transfer->details->map(function ($detail) use ($transfer) {
            // Ambil stock saat ini di UBS Asal untuk validasi max input
            $stockBase = StockGudang::where('barang_id', $detail->barang_id)
                ->where('stock_type', 'UBS')
                ->where('ubs_id', $transfer->dari_ubs_id)
                ->value('jumlah_stock') ?? 0;

            $konversi = DB::table('barang_satuan_konversi')
                ->where('barang_id', $detail->barang_id)
                ->where('satuan_id', $detail->satuan_id)
                ->value('konversi_ke_base') ?? 1;

            $stockInUnit = $stockBase / $konversi;

            return [
                'barang_id' => $detail->barang_id,
                'satuan_id' => $detail->satuan_id,
                'jumlah' => (float)$detail->qty,
                'stock_hub_saat_ini' => floor($stockInUnit) == $stockInUnit ? $stockInUnit : number_format($stockInUnit, 2, ',', ''),
                'satuanList' => DB::table('barang_satuan_konversi as bsk')
                    ->join('master_satuan as ms', 'ms.id', '=', 'bsk.satuan_id')
                    ->where('bsk.barang_id', $detail->barang_id)
                    ->select('ms.id', 'ms.nama', 'bsk.is_default')
                    ->get()
            ];
        });

        return view('gudang.stock-barang.transfer-stock.edit', [
            'transfer' => $transfer,
            'masterBarangs' => $masterBarangs,
            'ubsList' => $ubsList,
            'existingItems' => $existingItems,
            'breadcrumbs' => [
                ['label' => 'Stock Barang', 'url' => route('gudang.stockBarang.index')],
                ['label' => 'Daftar Stock Transfer', 'url' => route('gudang.transferStockBarang.daftar.index')],
                ['label' => 'Edit Pengajuan', 'url' => '#'],
            ],
        ]);
    }

    public function update(Request $request, $nomorTransfer)
    {
        $request->validate([
            'dari_ubs_id' => 'required|exists:ubs,id',
            'ke_ubs_id' => 'required|exists:ubs,id|different:dari_ubs_id',
            'tanggal_transfer' => 'required|date',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.jumlah_masuk' => 'required|numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();

            $transfer = TransferStock::where('nomor_transfer', $nomorTransfer)
                ->where('status', 'ditolak')
                ->firstOrFail();

            // Update Header & Reset status ke pending
            $transfer->update([
                'tanggal_transfer' => $request->tanggal_transfer,
                'dari_ubs_id' => $request->dari_ubs_id,
                'ke_ubs_id' => $request->ke_ubs_id,
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'keterangan' => $request->keterangan,
            ]);

            // Hapus detail lama
            $transfer->details()->delete();

            // Simpan detail baru
            $itemsTextUpdate = '';
            $noUpdate = 1;
            foreach ($request->items as $item) {
                $barangId = $item['barang_id'];
                $satuanId = $item['satuan_id'];
                $jumlahTransfer = $item['jumlah_masuk'];

                $barang = MasterBarang::findOrFail($barangId);
                $namaBarang = $barang->nama_barang;

                $satuanObj = MasterSatuan::find($satuanId);
                $namaSatuan = $satuanObj->nama ?? '-';

                $konversi = BarangSatuanKonversi::where('barang_id', $barangId)
                    ->where('satuan_id', $satuanId)
                    ->value('konversi_ke_base') ?? 1;

                $jumlahBaseTransfer = $jumlahTransfer * $konversi;

                $transfer->details()->create([
                    'barang_id' => $barangId,
                    'qty' => $jumlahTransfer,
                    'satuan_id' => $satuanId,
                    'qty_base' => $jumlahBaseTransfer,
                    'nama_barang_snapshot' => $namaBarang,
                ]);

                $itemsTextUpdate .= "{$noUpdate}. {$namaBarang} — " . (float)$jumlahTransfer . " {$namaSatuan}\n";
                $noUpdate++;
            }

            DB::commit();

            // Kirim notifikasi WA — Pengajuan Ulang
            $groupId = env('FONNTE_ID_GROUP_GUDANG_STOCK');
            $transfer->refresh();
            $pengajuUpdate  = Auth::user();
            $roleNameUpdate = $pengajuUpdate->getRoleNames()->first() ?? '-';
            $tglFmtUpdate   = \Carbon\Carbon::parse($request->tanggal_transfer)->translatedFormat('d M Y');

            $messageUpdate =
                "🔄 *PENGAJUAN KEMBALI TRANSFER STOCK*\n\n" .
                "No. Transfer : {$nomorTransfer}\n" .
                "Gudang Asal  : " . ($transfer->fromUbs->nama_ubs ?? '-') . " (" . ($transfer->fromUbs->kode_ubs ?? '-') . ")\n" .
                "Gudang Tujuan: " . ($transfer->toUbs->nama_ubs  ?? '-') . " (" . ($transfer->toUbs->kode_ubs  ?? '-') . ")\n" .
                "Tgl Diajukan : {$tglFmtUpdate}\n\n" .
                "*Barang yang Ditransfer:*\n" .
                $itemsTextUpdate .
                "\nDiajukan Ulang Oleh: {$pengajuUpdate->nama_lengkap} ({$roleNameUpdate})\n\n" .
                "⏳ Menunggu persetujuan SPV Logistik & Pengadaan.";

            if ($groupId) {
                $this->notificationGroup->send($groupId, $messageUpdate);
            }

            return redirect()->route('gudang.transferStockBarang.daftar.index')
                ->with('success', 'Pengajuan transfer #' . $nomorTransfer . ' berhasil diajukan kembali.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui pengajuan: ' . $e->getMessage()])->withInput();
        }
    }

    // ============================================
    // SPV APPROVAL ACTIONS
    // ============================================

    public function printPdf($nomorTransfer)
    {
        $transfer = TransferStock::with(['fromUbs', 'toUbs', 'creator', 'approvedBy', 'details.barang', 'details.satuan'])
            ->where('nomor_transfer', $nomorTransfer)
            ->firstOrFail();

        // Path logo ABM
        $imagePath = 'C:\\Users\\Asus TUF\\Documents\\Project Kantor\\internalAsaGroup - Ori\\public\\images\\logo\\logo-abm-ppjb.jpg';
        
        $base64Image = '';
        if (file_exists($imagePath)) {
            $imageData = file_get_contents($imagePath);
            $base64Image = 'data:image/jpeg;base64,' . base64_encode($imageData);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gudang.stock-barang.transfer-stock.pdf', [
            'transfer' => $transfer,
            'logoBase64' => $base64Image,
        ]);

        return $pdf->stream('Transfer-Stock-' . $transfer->nomor_transfer . '.pdf');
    }

    public function daftarShow($nomorTransfer)
    {
        $transfer = TransferStock::with(['fromUbs', 'toUbs', 'creator', 'approvedBy', 'details.barang', 'details.satuan'])
            ->where('nomor_transfer', $nomorTransfer)
            ->firstOrFail();

        return view('gudang.stock-barang.transfer-stock.show-daftarTransfer', [
            'transfer' => $transfer,
            'breadcrumbs' => [
                ['label' => 'Daftar Transfer Stock', 'url' => route('gudang.transferStockBarang.daftar.index')],
                ['label' => 'Detail Transfer Stock', 'url' => '#'],
            ],
        ]);
    }

    public function approvePengajuan($nomorTransfer)
    {
        try {
            DB::beginTransaction();

            $transfer = TransferStock::with(['details.barang', 'fromUbs', 'toUbs'])
                ->where('nomor_transfer', $nomorTransfer)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->firstOrFail();

            // Update status ke disetujui
            $transfer->update([
                'status' => 'disetujui',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            $dariUbsId = $transfer->dari_ubs_id;
            $keUbsId = $transfer->ke_ubs_id;
            $itemsDetailText = "";
            $totalBarang = count($transfer->details);
            $no = 1;

            foreach ($transfer->details as $detail) {
                $barangId = $detail->barang_id;
                $satuanId = $detail->satuan_id;
                $jumlahTransfer = $detail->qty;
                $jumlahBaseTransfer = $detail->qty_base;

                $barang = MasterBarang::with('baseUnit')->findOrFail($barangId);
                $namaBarang = $barang->nama_barang;
                $namaSatuanBase = $barang->baseUnit->nama ?? '-';

                $satuanInput = MasterSatuan::find($satuanId);
                $namaSatuanInput = $satuanInput->nama ?? '-';

                // 1. Kurangi stok di UBS Asal
                $stockDari = StockGudang::where('barang_id', $barangId)
                    ->where('stock_type', 'UBS')
                    ->where('ubs_id', $dariUbsId)
                    ->lockForUpdate()
                    ->first();

                if (!$stockDari || $stockDari->jumlah_stock < $jumlahBaseTransfer) {
                    throw new \Exception("Stok gudang asal ({$transfer->fromUbs->nama_ubs}) untuk barang '{$namaBarang}' tidak mencukupi.");
                }

                $dariOld = (float) $stockDari->jumlah_stock;
                $stockDari->decrement('jumlah_stock', $jumlahBaseTransfer);
                $dariNew = $dariOld - $jumlahBaseTransfer;

                // 2. Tambah stok di UBS Tujuan
                $stockKe = StockGudang::firstOrCreate(
                    [
                        'barang_id' => $barangId,
                        'stock_type' => 'UBS',
                        'ubs_id' => $keUbsId
                    ],
                    ['jumlah_stock' => 0, 'minimal_stock' => 0]
                );

                $keOld = (float) $stockKe->jumlah_stock;
                $stockKe->increment('jumlah_stock', $jumlahBaseTransfer);
                $keNew = $keOld + $jumlahBaseTransfer;

                // 3. Catat Ledger UBS Asal (Keluar)
                StockLedger::create([
                    'thumbnail' => null,
                    'tanggal' => $transfer->tanggal_transfer,
                    'barang_id' => $barangId,
                    'stock_type' => 'UBS',
                    'ubs_id' => $dariUbsId,
                    'tipe' => 'Keluar',
                    'ref_type' => 'TransferStock',
                    'ref_id' => $transfer->id,
                    'qty_masuk' => 0,
                    'qty_keluar' => $jumlahBaseTransfer,
                    'harga_satuan' => 0,
                    'created_by' => $transfer->created_by,
                    'keterangan' => "Transfer stock keluar ke UBS " . ($transfer->toUbs->kode_ubs ?? $transfer->toUbs->nama_ubs)
                ]);

                // 4. Catat Ledger UBS Tujuan (Masuk)
                StockLedger::create([
                    'thumbnail' => null,
                    'tanggal' => $transfer->tanggal_transfer,
                    'barang_id' => $barangId,
                    'stock_type' => 'UBS',
                    'ubs_id' => $keUbsId,
                    'tipe' => 'Masuk',
                    'ref_type' => 'TransferStock',
                    'ref_id' => $transfer->id,
                    'qty_masuk' => $jumlahBaseTransfer,
                    'qty_keluar' => 0,
                    'harga_satuan' => 0,
                    'created_by' => $transfer->created_by,
                    'keterangan' => "Transfer stock masuk dari UBS " . ($transfer->fromUbs->kode_ubs ?? $transfer->fromUbs->nama_ubs)
                ]);

                // Hitung konversi satuan default
                $defaultSatuanKonv = BarangSatuanKonversi::with('satuan')
                    ->where('barang_id', $barangId)
                    ->where('is_default', 1)
                    ->first();

                $namaSatuanDefault = $defaultSatuanKonv->satuan->nama ?? $namaSatuanBase;
                $konvDefault = $defaultSatuanKonv->konversi_ke_base ?? 1;

                $txtDariOld = (float) ($dariOld / $konvDefault);
                $txtDariNew = (float) ($dariNew / $konvDefault);
                $txtKeOld = (float) ($keOld / $konvDefault);
                $txtKeNew = (float) ($keNew / $konvDefault);

                $itemsDetailText .= "{$no}. {$namaBarang}\n";
                $itemsDetailText .= "   " . (float) $jumlahTransfer . " {$namaSatuanInput} (" . (float) $jumlahBaseTransfer . " {$namaSatuanBase})\n";
                $itemsDetailText .= "   " . ($transfer->fromUbs->kode_ubs ?? $transfer->fromUbs->nama_ubs) . ": {$txtDariOld} ➝ {$txtDariNew} ({$namaSatuanDefault})\n";
                $itemsDetailText .= "   " . ($transfer->toUbs->kode_ubs ?? $transfer->toUbs->nama_ubs) . ": {$txtKeOld} ➝ {$txtKeNew} ({$namaSatuanDefault})\n\n";

                $no++;
            }

            // Kirim notifikasi WA
            $groupId = env('FONNTE_ID_GROUP_GUDANG_STOCK');
            $tanggalFormat = $transfer->tanggal_transfer->format('d M Y');
            $messageGroup =
                "📦 *TRANSFER STOCK BARANG DISETUJUI*\n\n" .
                "No: {$nomorTransfer}\n" .
                "Tanggal: {$tanggalFormat}\n\n" .
                "Dari: UBS " . ($transfer->fromUbs->kode_ubs ?? $transfer->fromUbs->nama_ubs) . "\n" .
                "Ke: UBS " . ($transfer->toUbs->kode_ubs ?? $transfer->toUbs->nama_ubs) . "\n\n" .
                "Total: {$totalBarang} barang\n\n" .
                "Detail:\n" .
                $itemsDetailText .
                "Keterangan: " . ($transfer->keterangan ?? '-') . "\n" .
                "Disetujui Oleh: " . Auth::user()->username . "\n\n" .
                "Status: ✅ Berhasil";

            if ($groupId) {
                $this->notificationGroup->send($groupId, $messageGroup);
            }

            DB::commit();

            return redirect()->route('gudang.transferStockBarang.daftar.index')
                ->with('success', 'Transfer stock #' . $nomorTransfer . ' berhasil disetujui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui transfer: ' . $e->getMessage());
        }
    }

    public function rejectPengajuan($nomorTransfer)
    {
        try {
            $transfer = TransferStock::with(['fromUbs', 'toUbs'])
                ->where('nomor_transfer', $nomorTransfer)
                ->where('status', 'pending')
                ->firstOrFail();

            $penolak     = Auth::user();
            $roleNameTolak = $penolak->getRoleNames()->first() ?? '-';

            $transfer->update([
                'status'      => 'ditolak',
                'approved_by' => $penolak->id,
                'approved_at' => now(),
            ]);

            // Kirim notifikasi WA — Ditolak
            $groupId = env('FONNTE_ID_GROUP_GUDANG_STOCK');
            $messageTolak =
                "❌ *PENGAJUAN TRANSFER STOCK DITOLAK*\n\n" .
                "No. Transfer : {$nomorTransfer}\n" .
                "Gudang Asal  : " . ($transfer->fromUbs->nama_ubs ?? '-') . " (" . ($transfer->fromUbs->kode_ubs ?? '-') . ")\n" .
                "Gudang Tujuan: " . ($transfer->toUbs->nama_ubs  ?? '-') . " (" . ($transfer->toUbs->kode_ubs  ?? '-') . ")\n\n" .
                "Ditolak Oleh : {$penolak->nama_lengkap} ({$roleNameTolak})\n" .
                "Waktu Tolak  : " . now()->translatedFormat('d M Y, H:i') . " WIB\n\n" .
                "Admin Gudang dapat melakukan perbaikan dan mengajukan ulang.";

            if ($groupId) {
                $this->notificationGroup->send($groupId, $messageTolak);
            }

            return redirect()->route('gudang.transferStockBarang.daftar.index')
                ->with('success', 'Transfer stock #' . $nomorTransfer . ' berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak transfer: ' . $e->getMessage());
        }
    }

    /**
     * Hapus pengajuan transfer stock.
     * - Status pending  : hapus + kirim notifikasi WA ke group bahwa pengajuan dihapus
     * - Status ditolak  : hapus saja, tidak kirim notifikasi
     */
    public function destroy($nomorTransfer)
    {
        try {
            $transfer = TransferStock::with(['fromUbs', 'toUbs', 'creator'])
                ->where('nomor_transfer', $nomorTransfer)
                ->whereIn('status', ['pending', 'ditolak'])
                ->firstOrFail();

            $statusSebelum = $transfer->status;
            $fromUbs = $transfer->fromUbs;
            $toUbs   = $transfer->toUbs;

            // Hapus detail & header
            $transfer->details()->delete();
            $transfer->delete();

            // Kirim notifikasi WA hanya jika sebelumnya pending
            if ($statusSebelum === 'pending') {
                $groupId  = env('FONNTE_ID_GROUP_GUDANG_STOCK');
                $penghapus = Auth::user();
                $roleName  = $penghapus->getRoleNames()->first() ?? '-';

                $messageHapus =
                    "🗑️ *PENGAJUAN TRANSFER STOCK DIHAPUS*\n\n" .
                    "No. Transfer : {$nomorTransfer}\n" .
                    "Gudang Asal  : " . ($fromUbs->nama_ubs ?? '-') . " (" . ($fromUbs->kode_ubs ?? '-') . ")\n" .
                    "Gudang Tujuan: " . ($toUbs->nama_ubs  ?? '-') . " (" . ($toUbs->kode_ubs  ?? '-') . ")\n\n" .
                    "Dihapus Oleh : {$penghapus->nama_lengkap} ({$roleName})\n" .
                    "Waktu Hapus  : " . now()->translatedFormat('d M Y, H:i') . " WIB\n\n" .
                    "Pengajuan ini telah dibatalkan dan dihapus dari sistem.";

                if ($groupId) {
                    $this->notificationGroup->send($groupId, $messageHapus);
                }
            }

            return redirect()->route('gudang.transferStockBarang.daftar.index')
                ->with('success', 'Pengajuan transfer #' . $nomorTransfer . ' berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus pengajuan: ' . $e->getMessage());
        }
    }
}
