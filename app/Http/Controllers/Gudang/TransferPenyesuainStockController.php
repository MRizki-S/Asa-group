<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\StockGudang;
use App\Models\StockLedger;
use App\Models\TransferStock;
use App\Models\Ubs;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferPenyesuainStockController extends Controller
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

        return view('gudang.stock-barang.transfer-penyesuain.create-penyesuain', [
            'masterBarangs' => $masterBarangs,
            'ubsList' => $ubsList,
            'breadcrumbs' => [
                [
                    'label' => 'Stock Barang',
                    'url' => route('gudang.stockBarang.index'),
                ],
                [
                    'label' => 'Transfer Penyesuain Stock',
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
            'keterangan' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.jumlah_masuk' => 'required|numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();

            $tanggalTransfer = $request->tanggal_transfer;
            $ubsId = $request->ubs_id;

            // Generate nomor transfer unik untuk Penyesuaian
            $nomorTransfer = 'TRF-P-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

            // Buat header Transfer Stock (Penyesuaian)
            $transfer = TransferStock::create([
                'nomor_transfer' => $nomorTransfer,
                'tanggal_transfer' => $tanggalTransfer,
                'dari_stock_type' => 'UBS',
                'dari_ubs_id' => $ubsId,
                'ke_stock_type' => 'HUB',
                'ke_ubs_id' => null,
                'keterangan' => $request->keterangan,
                'created_by' => Auth::id(),
            ]);

            $transferId = $transfer->id;

            // Ambil data UBS untuk notifikasi
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

                // 2. Ambil Stock UBS & Kurangi (UBS -> HUB)
                $stockUbs = StockGudang::where('barang_id', $barangId)
                    ->where('stock_type', 'UBS')
                    ->where('ubs_id', $ubsId)
                    ->lockForUpdate()
                    ->first();

                if (!$stockUbs || $stockUbs->jumlah_stock < $jumlahBaseTransfer) {
                    throw new \Exception("Stok UBS ({$namaUbs}) untuk barang '{$namaBarang}' tidak mencukupi.");
                }

                $ubsOld = (float) $stockUbs->jumlah_stock;
                $stockUbs->decrement('jumlah_stock', $jumlahBaseTransfer);
                $ubsNew = $ubsOld - $jumlahBaseTransfer;

                // 3. Ambil Stock HUB & Tambah
                $stockHub = StockGudang::firstOrCreate(
                    [
                        'barang_id' => $barangId,
                        'stock_type' => 'HUB',
                        'ubs_id' => null
                    ],
                    ['jumlah_stock' => 0, 'minimal_stock' => 0]
                );

                $hubOld = (float) $stockHub->jumlah_stock;
                $stockHub->increment('jumlah_stock', $jumlahBaseTransfer);
                $hubNew = $hubOld + $jumlahBaseTransfer;

                // 4. Catat detail Transfer Stock
                $transfer->details()->create([
                    'barang_id' => $barangId,
                    'qty' => $jumlahTransfer,
                    'satuan_id' => $satuanId,
                    'qty_base' => $jumlahBaseTransfer,
                    'nama_barang_snapshot' => $namaBarang,
                ]);

                // 5. Catat Ledger UBS (Keluar)
                StockLedger::create([
                    'tanggal' => $tanggalTransfer,
                    'barang_id' => $barangId,
                    'stock_type' => 'UBS',
                    'ubs_id' => $ubsId,
                    'tipe' => 'Keluar',
                    'ref_type' => 'TransferStock',
                    'ref_id' => $transferId,
                    'qty_masuk' => 0,
                    'qty_keluar' => $jumlahBaseTransfer,
                    'harga_satuan' => 0,
                    'created_by' => Auth::id(),
                    'keterangan' => "Penyesuaian ke HUB"
                ]);

                // 6. Catat Ledger HUB (Masuk)
                StockLedger::create([
                    'tanggal' => $tanggalTransfer,
                    'barang_id' => $barangId,
                    'stock_type' => 'HUB',
                    'ubs_id' => null,
                    'tipe' => 'Masuk',
                    'ref_type' => 'TransferStock',
                    'ref_id' => $transferId,
                    'qty_masuk' => $jumlahBaseTransfer,
                    'qty_keluar' => 0,
                    'harga_satuan' => 0,
                    'created_by' => Auth::id(),
                    'keterangan' => "Penyesuaian dari UBS " . ($kodeUbs ?? $namaUbs)
                ]);

                // 7. Info Satuan Default untuk Notifikasi
                $defaultSatuanKonv = BarangSatuanKonversi::with('satuan')
                    ->where('barang_id', $barangId)
                    ->where('is_default', 1)
                    ->first();

                $namaSatuanDefaultArray = $defaultSatuanKonv->satuan->nama ?? $namaSatuanBase;
                $konvDefault = $defaultSatuanKonv->konversi_ke_base ?? 1;

                // Hitung angka tampilan
                $txtHubOld = (float) ($hubOld / $konvDefault);
                $txtHubNew = (float) ($hubNew / $konvDefault);
                $txtUbsOld = (float) ($ubsOld / $konvDefault);
                $txtUbsNew = (float) ($ubsNew / $konvDefault);

                // 8. Bangun teks detail
                $itemsDetailText .= "{$no}. {$namaBarang}\n";
                $itemsDetailText .= "   " . (float) $jumlahTransfer . " {$namaSatuanInput}\n";
                $itemsDetailText .= "   " . ($kodeUbs ?? $namaUbs) . ": {$txtUbsOld} ➝ {$txtUbsNew} ({$namaSatuanDefaultArray})\n";
                $itemsDetailText .= "   HUB: {$txtHubOld} ➝ {$txtHubNew} ({$namaSatuanDefaultArray})\n\n";

                $no++;
            }

            $groupId = env('FONNTE_ID_GROUP_GUDANG_ABM');
            $tanggalFormat = date('d M Y', strtotime($tanggalTransfer));
            $messageGroup =
                "📦 *TRANSFER PENYESUAIAN STOCK*\n\n" .
                "No: {$nomorTransfer}\n" .
                "Tanggal: {$tanggalFormat}\n\n" .
                "Dari: UBS " . ($kodeUbs ?? $namaUbs) . "\n" .
                "Ke: HUB (Pusat)\n\n" .
                "Total: {$totalBarang} barang\n\n" .
                "Detail:\n" .
                $itemsDetailText .
                "Keterangan: {$request->keterangan}\n\n" .
                "Status: ✅ Berhasil";

            if ($groupId) {
                $this->notificationGroup->send($groupId, $messageGroup);
            }

            DB::commit();

            return redirect()->route('gudang.transferStockBarang.create')
                ->with('success', 'Transfer penyesuaian stok berhasil dilakukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal simpan transfer: ' . $e->getMessage()])->withInput();
        }
    }

    public function getStokBarangUbsHub($barangId, $ubsId)
    {
        $barang = MasterBarang::with('baseUnit')->findOrFail($barangId);
        $namaSatuanBase = $barang->baseUnit->nama ?? '-';

        $satuans = BarangSatuanKonversi::with('satuan')
            ->where('barang_id', $barangId)
            ->orderByDesc('is_default')
            ->get();

        // Ambil Stock HUB (Base)
        $stockHubBase = StockGudang::where('barang_id', $barangId)
            ->where('stock_type', 'HUB')
            ->whereNull('ubs_id')
            ->value('jumlah_stock') ?? 0;

        // Ambil Stock UBS (Base)
        $stockUbsBase = StockGudang::where('barang_id', $barangId)
            ->where('stock_type', 'UBS')
            ->where('ubs_id', $ubsId)
            ->value('jumlah_stock') ?? 0;

        $result = $satuans->map(function($sat) use ($stockHubBase, $stockUbsBase) {
            $konversi = (float)$sat->konversi_ke_base;

            // Konversi stok ke satuan terpilih
            $stokHubConvert = $stockHubBase / $konversi;
            $stokUbsConvert = $stockUbsBase / $konversi;

            return [
                'id' => $sat->satuan_id,
                'nama' => $sat->satuan->nama ?? '-',
                'is_default' => $sat->is_default,
                'konversi_ke_base' => $konversi,
                'stock_hub_saat_ini' => floor($stokHubConvert) == $stokHubConvert
                    ? $stokHubConvert
                    : number_format($stokHubConvert, 2, ',', ''),
                'stock_ubs_saat_ini' => floor($stokUbsConvert) == $stokUbsConvert
                    ? $stokUbsConvert
                    : number_format($stokUbsConvert, 2, ',', ''),
            ];
        });

        return response()->json($result);
    }
}
