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
                    'label' => 'Transfer Stock',
                    'url' => route('gudang.transferStockBarang.create'),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ubs_id' => 'required|exists:ubs,id',
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
            $ubsId = $request->ubs_id;

            // Generate nomor transfer unik
            // Contoh format: TRF-Ymd-XXXX
            $nomorTransfer = 'TRF-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

            // Buat header Transfer Stock dengan Eloquent
            $transfer = TransferStock::create([
                'nomor_transfer' => $nomorTransfer,
                'tanggal_transfer' => $tanggalTransfer,
                'dari_stock_type' => 'HUB',
                'dari_ubs_id' => null,
                'ke_stock_type' => 'UBS',
                'ke_ubs_id' => $ubsId,
                'keterangan' => $request->keterangan ?? null,
                'created_by' => Auth::id(),
            ]);

            $transferId = $transfer->id;

            // 1. Ambil data UBS untuk notifikasi
            $ubs = Ubs::findOrFail($ubsId);
            $namaUbs = $ubs->nama_ubs;
            $kodeUbs = $ubs->kode_ubs;

            $itemsDetailText = "";
            $totalBarang = count($request->items);
            $no = 1;

            foreach ($request->items as $item) {
                $barangId = $item['barang_id'];
                $satuanId = $item['satuan_id'];
                $jumlahTransfer = $item['jumlah_masuk'];

                // 1. Ambil barang & konversi
                $barang = MasterBarang::with('baseUnit')->findOrFail($barangId);
                $namaBarang = $barang->nama_barang;
                $namaSatuanBase = $barang->baseUnit->nama ?? '-';

                $satuanInput = MasterSatuan::find($satuanId);
                $namaSatuanInput = $satuanInput->nama ?? '-';

                $konversi = BarangSatuanKonversi::where('barang_id', $barangId)
                    ->where('satuan_id', $satuanId)
                    ->value('konversi_ke_base') ?? 1;

                $jumlahBaseTransfer = $jumlahTransfer * $konversi;

                // 2. Ambil Stock HUB & Catat nilai lama
                $stockHub = StockGudang::where('barang_id', $barangId)
                    ->where('stock_type', 'HUB')
                    ->whereNull('ubs_id')
                    ->lockForUpdate()
                    ->first();

                if (!$stockHub || $stockHub->jumlah_stock < $jumlahBaseTransfer) {
                    throw new \Exception("Stok HUB untuk barang '{$namaBarang}' tidak mencukupi.");
                }

                $hubOld = (float) $stockHub->jumlah_stock;
                $stockHub->decrement('jumlah_stock', $jumlahBaseTransfer);
                $hubNew = $hubOld - $jumlahBaseTransfer;

                // 3. Ambil Stock UBS & Catat nilai lama
                $stockUbs = StockGudang::firstOrCreate(
                    [
                        'barang_id' => $barangId,
                        'stock_type' => 'UBS',
                        'ubs_id' => $ubsId
                    ],
                    ['jumlah_stock' => 0, 'minimal_stock' => 0]
                );

                $ubsOld = (float) $stockUbs->jumlah_stock;
                $stockUbs->increment('jumlah_stock', $jumlahBaseTransfer);
                $ubsNew = $ubsOld + $jumlahBaseTransfer;

                // 4. Catat detail Transfer Stock
                $namaBarang = MasterBarang::where('id', $barangId)->value('nama_barang');

                $transfer->details()->create([
                    'barang_id' => $barangId,
                    'qty' => $jumlahTransfer,
                    'satuan_id' => $satuanId,
                    'qty_base' => $jumlahBaseTransfer,
                    'nama_barang_snapshot' => $namaBarang,
                ]);

                // 5. Catat Ledger HUB & UBS
                StockLedger::create([
                    'tanggal' => $tanggalTransfer,
                    'barang_id' => $barangId,
                    'stock_type' => 'HUB',
                    'ubs_id' => null,
                    'tipe' => 'Keluar',
                    'ref_type' => 'TransferStock',
                    'ref_id' => $transferId,
                    'qty_masuk' => 0,
                    'qty_keluar' => $jumlahBaseTransfer,
                    'harga_satuan' => 0,
                    'created_by' => Auth::id(),
                ]);

                StockLedger::create([
                    'tanggal' => $tanggalTransfer,
                    'barang_id' => $barangId,
                    'stock_type' => 'UBS',
                    'ubs_id' => $ubsId,
                    'tipe' => 'Masuk',
                    'ref_type' => 'TransferStock',
                    'ref_id' => $transferId,
                    'qty_masuk' => $jumlahBaseTransfer,
                    'qty_keluar' => 0,
                    'harga_satuan' => 0,
                    'created_by' => Auth::id(),
                ]);

                // 7. Ambil info Satuan Default untuk tampilan Notifikasi
                $defaultSatuanKonv = BarangSatuanKonversi::with('satuan')
                    ->where('barang_id', $barangId)
                    ->where('is_default', 1)
                    ->first();

                $namaSatuanDefault = $defaultSatuanKonv->satuan->nama ?? $namaSatuanBase;
                $konvDefault = $defaultSatuanKonv->konversi_ke_base ?? 1;

                // Hitung angka tampilan (Stok dibagi konversi default)
                $txtHubOld = (float) ($hubOld / $konvDefault);
                $txtHubNew = (float) ($hubNew / $konvDefault);
                $txtUbsOld = (float) ($ubsOld / $konvDefault);
                $txtUbsNew = (float) ($ubsNew / $konvDefault);

                // 8. Bangun teks detail untuk WA
                $itemsDetailText .= "{$no}. {$namaBarang}\n";
                $itemsDetailText .= "   " . (float) $jumlahTransfer . " {$namaSatuanInput} (" . (float) $jumlahBaseTransfer . " {$namaSatuanBase})\n";
                $itemsDetailText .= "   HUB: {$txtHubOld} ➝ {$txtHubNew} ({$namaSatuanDefault})\n";
                $itemsDetailText .= "   " . ($kodeUbs ?? $namaUbs) . ": {$txtUbsOld} ➝ {$txtUbsNew} ({$namaSatuanDefault})\n\n";

                $no++;
            }

            $groupId = env('FONNTE_ID_GROUP_GUDANG_ABM');
            $tanggalFormat = date('d M Y', strtotime($tanggalTransfer));
            $messageGroup =
                "📦 *TRANSFER STOCK*\n\n" .
                "No: {$nomorTransfer}\n" .
                "Tanggal: {$tanggalFormat}\n\n" .
                "Dari: HUB\n" .
                "Ke: UBS " . ($kodeUbs ?? $namaUbs) . "\n\n" .
                "Total: {$totalBarang} barang\n\n" .
                "Detail:\n" .
                $itemsDetailText .
                "Keterangan: " . ($request->keterangan ?? '-') . "\n\n" .
                "Status: ✅ Berhasil";

            if ($groupId) {
                $this->notificationGroup->send($groupId, $messageGroup);
            }

            DB::commit();

            return redirect()->route('gudang.transferStockBarang.create')
                ->with('success', 'Transfer stok barang berhasil dilakukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses transfer: ' . $e->getMessage()])->withInput();
        }
    }

    // Get list of units for a barang AND its current stock in HUB
    public function getSatuanDanStok($barangId)
    {
        $satuans = BarangSatuanKonversi::with('satuan')
            ->where('barang_id', $barangId)
            ->orderByDesc('is_default')
            ->get();

        $stockHubBase = StockGudang::where('barang_id', $barangId)
            ->where('stock_type', 'HUB')
            ->whereNull('ubs_id')
            ->value('jumlah_stock') ?? 0;

        $result = $satuans->map(function($sat) use ($stockHubBase) {
            $konversi = (float)$sat->konversi_ke_base;
            $stockInUnit = $stockHubBase / $konversi;

            return [
                'id' => $sat->satuan_id,
                'nama' => $sat->satuan->nama ?? '-',
                'is_default' => $sat->is_default,
                'konversi_ke_base' => $konversi,
                'stock_hub_saat_ini' => floor($stockInUnit) == $stockInUnit
                    ? $stockInUnit
                    : number_format($stockInUnit, 2, ',', ''),
            ];
        });

        return response()->json($result);
    }


    // riwayat transfer stock barang 
    public function riwayatTransferStock(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $transferStocks = TransferStock::with(['toUbs', 'fromUbs'])
            ->whereMonth('tanggal_transfer', $bulan)
            ->whereYear('tanggal_transfer', $tahun)
            ->orderByDesc('created_at')
            ->get();

        return view('gudang.stock-barang.transfer-stock.riwayat-transfer', [
            'transferStocks' => $transferStocks,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'breadcrumbs' => [
                [
                    'label' => 'Stock Barang',
                    'url' => route('gudang.stockBarang.index'),
                ],
                [
                    'label' => 'Transfer Stock',
                    'url' => route('gudang.transferStockBarang.create'),
                ],
                [
                    'label' => 'Riwayat Transfer Stock',
                    'url' => route('gudang.transferStockBarang.riwayatTransferStock'),
                ],
            ],
        ]);
    }

    public function showRiwayatTransferStock($nomorTransfer)
    {
        $transfer = TransferStock::with(['toUbs', 'creator'])
            ->where('nomor_transfer', $nomorTransfer)
            ->firstOrFail();

        $details = $transfer->details()->with(['barang', 'satuan'])->get();

        return view('gudang.stock-barang.transfer-stock.detail-riwayat-transfer', [
            'transfer' => $transfer,
            'details' => $details,
            'breadcrumbs' => [
                ['label' => 'Riwayat Transfer', 'url' => route('gudang.transferStockBarang.riwayatTransferStock')],
                ['label' => 'Detail Transfer', 'url' => '#'],
            ]
        ]);
    }
}
