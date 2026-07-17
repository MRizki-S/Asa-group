<?php

namespace App\Http\Controllers\Gudang\PermintaanBarang;

use App\Http\Controllers\Controller;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\NotaBarangMasukDetail;
use App\Models\PembangunanKawasanBahan;
use App\Models\PembangunanKawasanBarangOrder;
use App\Models\PembangunanKawasanBarangFifoUsage;
use App\Models\PembangunanProyekBahan;
use App\Models\PembangunanProyekBarangOrder;
use App\Models\PembangunanProyekBarangFifoUsage;
use App\Models\PembangunanUnitBahan;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\StockGudang;
use App\Models\StockLedger;
use App\Models\Ubs;
use App\Services\NotificationPribadiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermintaanBarangController extends Controller
{
    protected NotificationPribadiService $notification;

    public function __construct(NotificationPribadiService $notification)
    {
        $this->notification = $notification;
    }

    private function getOrderConfig($jenisOrder)
    {
        $configs = [
            'pembangunan_unit' => [
                'model' => PembangunanUnitBarangOrder::class,
                'with' => [
                    'details.barang.baseUnit',
                    'details.rapBahan',
                    'user',
                    'qc',
                    'pembangunanUnit.unit.blok',
                    'pembangunanUnit.unit.type',
                    'pembangunanUnit.tahap.perumahaan',
                    'pembangunanUnit.pengawas',
                ],
                'title' => 'Permintaan Barang Unit',
                'bahanModel' => PembangunanUnitBahan::class,
                'parent_id_field' => 'pembangunan_unit_id',
                'qc_id_field' => 'pembangunan_unit_qc_id',
            ],
            'pembangunan_kawasan' => [
                'model' => PembangunanKawasanBarangOrder::class,
                'with' => [
                    'details.barang.baseUnit',
                    'pembuat',
                    'kawasan.perumahan',
                ],
                'title' => 'Permintaan Barang Kawasan',
                'bahanModel' => PembangunanKawasanBahan::class,
                'parent_id_field' => 'pembangunan_kawasan_id',
                'qc_id_field' => null,
            ],
            'pembangunan_proyek_mangoon' => [
                'model' => PembangunanProyekBarangOrder::class,
                'with' => [
                    'details.barang.baseUnit',
                    'pembuat',
                    'proyek',
                ],
                'title' => 'Permintaan Barang Proyek Mangoon',
                'bahanModel' => PembangunanProyekBahan::class,
                'parent_id_field' => 'pembangunan_proyek_id',
                'qc_id_field' => null,
            ],
        ];

        return $configs[$jenisOrder] ?? $configs['pembangunan_unit'];
    }

    private function orderQuery($category)
    {
        $config = $this->getOrderConfig($category);

        return $config['model']::with($config['with'])
            ->withCount('details')
            ->latest('tanggal_diajukan');
    }

    private function statusOptions(bool $includeMenunggu = true): array
    {
        $options = [
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
        ];

        return $includeMenunggu
            ? ['diproses' => 'Menunggu'] + $options
            : $options;
    }

    // view daftar permintaan barang yang masih diproses (status menunggu)
    public function index(Request $request)
    {
        $status = $request->get('status', 'diproses');
        $rawJenisOrder = $request->get('jenis_order', 'pembangunan_unit');

        // Categories from sidebar
        $categories = ['pembangunan_unit', 'pembangunan_kawasan', 'pembangunan_proyek_mangoon'];

        // Resolve category and filter
        if (in_array($rawJenisOrder, $categories)) {
            $category = $rawJenisOrder;
            $filterJenis = 'all';
        } else {
            $category = $request->get('category', 'pembangunan_unit');
            $filterJenis = in_array($rawJenisOrder, ['stock', 'direct']) ? $rawJenisOrder : 'all';
        }

        $config = $this->getOrderConfig($category);
        $query = $this->orderQuery($category);

        if ($status !== 'all') {
            $query->where('status_order', $status);
        }

        if ($filterJenis !== 'all') {
            $query->where('jenis_order', $filterJenis);
        }

        $orders = $query->get();

        return view('gudang.permintaan-barang.index', [
            'orders' => $orders,
            'status' => $status,
            'category' => $category,
            'jenisOrder' => $filterJenis,
            'statusOptions' => $this->statusOptions(),
            'isHistory' => false,
            'titlePage' => $config['title'],
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index', ['jenis_order' => $category]),
                ],
            ],
        ]);
    }

    // view history dari permintaan barang
    public function history(Request $request)
    {
        $status = $request->get('status', 'all');
        $rawJenisOrder = $request->get('jenis_order', 'pembangunan_unit');

        $categories = ['pembangunan_unit', 'pembangunan_kawasan', 'pembangunan_proyek_mangoon'];

        if (in_array($rawJenisOrder, $categories)) {
            $category = $rawJenisOrder;
            $filterJenis = 'all';
        } else {
            $category = $request->get('category', 'pembangunan_unit');
            $filterJenis = in_array($rawJenisOrder, ['stock', 'direct']) ? $rawJenisOrder : 'all';
        }

        $config = $this->getOrderConfig($category);
        $query = $this->orderQuery($category);

        if ($status === 'all') {
            $query->where('status_order', '!=', 'diproses');
        } else {
            $query->where('status_order', $status);
        }

        if ($filterJenis !== 'all') {
            $query->where('jenis_order', $filterJenis);
        }

        $orders = $query->get();

        return view('gudang.permintaan-barang.index', [
            'orders' => $orders,
            'status' => $status,
            'category' => $category,
            'jenisOrder' => $filterJenis,
            'statusOptions' => $this->statusOptions(false),
            'isHistory' => true,
            'titlePage' => 'Riwayat ' . $config['title'],
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index', ['jenis_order' => $category]),
                ],
                [
                    'label' => 'Riwayat',
                    'url' => route('gudang.permintaanBarang.history', ['jenis_order' => $category]),
                ],
            ],
        ]);
    }

    // function untuk melihat detail order barang sebelum dilakukan acc oleh gudang
    public function show(Request $request, $id)
    {
        $category = $request->get('jenis_order', 'pembangunan_unit');
        $config = $this->getOrderConfig($category);

        $order = $config['model']::with($config['with'])->findOrFail($id);

        $ubsId = $order->ubs_id;
        if (!$ubsId) {
            if ($category === 'pembangunan_unit') {
                $ubsId = $order->pembangunanUnit?->perumahaan_id;
            } elseif ($category === 'pembangunan_kawasan') {
                $ubsId = $order->kawasan?->perumahaan_id ?? $order->kawasan?->perumahan_id;
            }
        }

        $stocks = [];
        $ubsCode = null;
        if ($ubsId) {
            $ubsCode = Ubs::where('id', $ubsId)->value('kode_ubs');
            $stocks = StockGudang::where('stock_type', 'UBS')
                ->where('ubs_id', $ubsId)
                ->whereIn('barang_id', $order->details->pluck('barang_id'))
                ->get()
                ->pluck('jumlah_stock', 'barang_id')
                ->toArray();
        }

        $conversions = [];
        foreach ($order->details as $detail) {
            $factor = BarangSatuanKonversi::where('barang_id', $detail->barang_id)
                ->where('satuan_id', $detail->satuan_id)
                ->value('konversi_ke_base');
            $conversions[$detail->id] = (float) ($factor ?? 1.0);
        }

        return view('gudang.permintaan-barang.show', [
            'order' => $order,
            'category' => $category,
            'stocks' => $stocks,
            'conversions' => $conversions,
            'ubsCode' => $ubsCode,
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index', ['jenis_order' => $category]),
                ],
                [
                    'label' => 'Detail Permintaan - REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'url' => route('gudang.permintaanBarang.show', ['id' => $order->id, 'jenis_order' => $category]),
                ],
            ],
        ]);
    }

    public function acc(Request $request, $id)
    {
        $category = $request->get('jenis_order', 'pembangunan_kawasan');
        
        if (!in_array($category, ['pembangunan_kawasan', 'pembangunan_proyek_mangoon'])) {
            return back()->with('error', 'Kategori permintaan barang tidak valid.');
        }

        $config = $this->getOrderConfig($category);
        $order = $config['model']::with($config['with'])->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return back()->with('error', 'Permintaan barang ini sudah tidak dalam status menunggu.');
        }

        try {
            DB::transaction(function () use ($order, $request, $category) {
                $this->processAcc($order, $request, $category);
            });

            // Kirim notifikasi WA setelah transaksi berhasil commit
            $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Admin Gudang';
            $nomorOrder = $order->nomor_order ?? ('REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT));
            $message = "✅ Orderan barang dengan No. Order *{$nomorOrder}* telah dikonfirmasi oleh Pihak Gudang (*{$adminName}*).";
            $targetGroup = env('FONNTE_ID_ORDER_BARANG_ABM');
            if (!empty($targetGroup)) {
                $this->notification->sendWhatsApp($targetGroup, $message);
            }
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal ACC permintaan barang: ' . $e->getMessage());
        }

        return redirect()
            ->route('gudang.permintaanBarang.history', ['jenis_order' => $category])
            ->with('success', 'Permintaan barang berhasil di-ACC.');
    }

    private function processAcc($order, Request $request, string $category): void
    {
        $ubsId = $order->ubs_id;
        
        if (!$ubsId) {
            if ($category === 'pembangunan_kawasan') {
                $ubsId = $order->kawasan?->perumahaan_id ?? $order->kawasan?->perumahan_id;
            }
        }

        if (!$ubsId) {
            throw new \Exception('Data UBS/perumahan tujuan stock tidak ditemukan.');
        }

        foreach ($order->details as $detail) {
            $this->assertDetailMatchesOrderType($order, $detail);

            if ($detail->konfirmasi) {
                continue;
            }

            $jumlahBase = $this->resolveJumlahBase($detail);
            $hargaTotal = 0.0;
            $hargaSatuanBase = 0.0;

            if ($detail->barang?->is_stock) {
                $stock = StockGudang::where('barang_id', $detail->barang_id)
                    ->where('stock_type', 'UBS')
                    ->where('ubs_id', $ubsId)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || (float) $stock->jumlah_stock < $jumlahBase) {
                    $namaBarang = $detail->nama_barang ?? $detail->barang?->nama_barang ?? 'Barang';
                    throw new \Exception("Stok UBS untuk {$namaBarang} tidak mencukupi.");
                }

                $fifoResult = $this->consumeNotaFifo($detail->barang_id, $jumlahBase);
                $hargaTotal = $fifoResult['harga_total'];
                $hargaSatuanBase = $jumlahBase > 0 ? $hargaTotal / $jumlahBase : 0;

                // Simpan setiap layer FIFO yang dipakai ke tabel fifo_usage
                foreach ($fifoResult['layers'] as $layer) {
                    if ($category === 'pembangunan_kawasan') {
                        PembangunanKawasanBarangFifoUsage::create([
                            'order_detail_id' => $detail->id,
                            'nota_barang_masuk_detail_id' => $layer['nota_barang_masuk_detail_id'],
                            'jumlah_base' => $layer['jumlah_base'],
                            'jumlah_return_base' => 0,
                            'harga_satuan_snapshot' => $layer['harga_satuan_snapshot'],
                            'harga_total_snapshot' => $layer['harga_total_snapshot'],
                        ]);
                    } else {
                        PembangunanProyekBarangFifoUsage::create([
                            'order_detail_id' => $detail->id,
                            'nota_barang_masuk_detail_id' => $layer['nota_barang_masuk_detail_id'],
                            'jumlah_base' => $layer['jumlah_base'],
                            'jumlah_return_base' => 0,
                            'harga_satuan_snapshot' => $layer['harga_satuan_snapshot'],
                            'harga_total_snapshot' => $layer['harga_total_snapshot'],
                        ]);
                    }
                }

                $stock->decrement('jumlah_stock', $jumlahBase);

                StockLedger::create([
                    'tanggal' => now(),
                    'barang_id' => $detail->barang_id,
                    'stock_type' => 'UBS',
                    'ubs_id' => $ubsId,
                    'tipe' => 'keluar',
                    'ref_type' => get_class($order),
                    'ref_id' => $order->id,
                    'qty_masuk' => 0,
                    'qty_keluar' => $jumlahBase,
                    'harga_satuan' => $hargaSatuanBase,
                    'created_by' => Auth::id(),
                ]);
            } else {
                $hargaTotal = $this->resolveDirectHargaTotal($request, $detail);
                $hargaSatuanBase = $jumlahBase > 0 ? $hargaTotal / $jumlahBase : 0;
            }

            $detail->update([
                'konfirmasi' => true,
                'jumlah_base' => $jumlahBase,
                'harga_satuan_snapshot' => $hargaSatuanBase,
                'harga_total_snapshot' => $hargaTotal,
            ]);

            $this->upsertBahan($order, $detail, $hargaTotal, $category);
        }

        $order->update([
            'status_order' => 'selesai',
            'tanggal_selesai' => now(),
        ]);
    }

    private function consumeNotaFifo(int $barangId, float $jumlahBase): array
    {
        $remaining = $jumlahBase;
        $hargaTotal = 0.0;
        $usedLayers = [];

        $layers = NotaBarangMasukDetail::query()
            ->select('nota_barang_masuk_detail.*')
            ->join('nota_barang_masuk', 'nota_barang_masuk.id', '=', 'nota_barang_masuk_detail.nota_id')
            ->where('nota_barang_masuk_detail.barang_id', $barangId)
            ->where('nota_barang_masuk_detail.jumlah_sisa', '>', 0)
            ->where('nota_barang_masuk.status', 'posted')
            ->orderBy('nota_barang_masuk.tanggal_nota')
            ->orderBy('nota_barang_masuk_detail.id')
            ->lockForUpdate()
            ->get();

        $available = (float) $layers->sum('jumlah_sisa');
        if ($available + 0.000001 < $jumlahBase) {
            $namaBarang = MasterBarang::where('id', $barangId)->value('nama_barang') ?? 'barang ini';
            throw new \Exception("Sisa nota barang masuk untuk {$namaBarang} tidak mencukupi.");
        }

        foreach ($layers as $layer) {
            if ($remaining <= 0.000001) {
                break;
            }

            $takeQty = min((float) $layer->jumlah_sisa, $remaining);
            $hargaSatuanBase = (float) ($layer->harga_satuan_base ?: 0);

            if ($hargaSatuanBase <= 0 && (float) $layer->jumlah_base > 0) {
                $hargaSatuanBase = (float) $layer->harga_total / (float) $layer->jumlah_base;
            }

            $layerHargaTotal = round($takeQty * $hargaSatuanBase, 2);
            $hargaTotal += $layerHargaTotal;

            $layer->update([
                'jumlah_sisa' => (float) $layer->jumlah_sisa - $takeQty,
            ]);

            $usedLayers[] = [
                'nota_barang_masuk_detail_id' => $layer->id,
                'jumlah_base' => $takeQty,
                'harga_satuan_snapshot' => $hargaSatuanBase,
                'harga_total_snapshot' => $layerHargaTotal,
            ];

            $remaining -= $takeQty;
        }

        return [
            'harga_total' => round($hargaTotal, 2),
            'layers' => $usedLayers,
        ];
    }

    private function resolveJumlahBase($detail): float
    {
        $faktorKonversi = BarangSatuanKonversi::where('barang_id', $detail->barang_id)
            ->where('satuan_id', $detail->satuan_id)
            ->value('konversi_ke_base');

        if ($faktorKonversi === null) {
            return (float) $detail->jumlah_base;
        }

        return round((float) $detail->jumlah_input * (float) $faktorKonversi, 3);
    }

    private function resolveDirectHargaTotal(Request $request, $detail): float
    {
        $hargaTotal = $request->input("harga_total.{$detail->id}");

        if ($hargaTotal === null || $hargaTotal === '') {
            $namaBarang = $detail->nama_barang ?? $detail->barang?->nama_barang ?? 'Barang';
            throw new \Exception("Harga total untuk {$namaBarang} wajib diisi.");
        }

        $hargaTotal = (float) $hargaTotal;

        if ($hargaTotal < 0) {
            $namaBarang = $detail->nama_barang ?? $detail->barang?->nama_barang ?? 'Barang';
            throw new \Exception("Harga total untuk {$namaBarang} tidak boleh minus.");
        }

        return round($hargaTotal, 2);
    }

    private function assertDetailMatchesOrderType($order, $detail): void
    {
        $barang = $detail->barang;

        if (!$barang) {
            throw new \Exception("Data master barang untuk {$detail->nama_barang} tidak ditemukan.");
        }

        $expectedStock = $order->jenis_order === 'stock';

        if ((bool) $barang->is_stock !== $expectedStock) {
            $jenis = $expectedStock ? 'stock' : 'direct';
            throw new \Exception("Barang {$detail->nama_barang} tidak sesuai dengan jenis order {$jenis}.");
        }
    }

    private function upsertBahan($order, $detail, float $hargaTotal, string $category): void
    {
        if ($category === 'pembangunan_kawasan') {
            $bahan = PembangunanKawasanBahan::where('pembangunan_kawasan_id', $order->pembangunan_kawasan_id)
                ->where('barang_id', $detail->barang_id)
                ->first();

            if ($bahan) {
                $bahan->update([
                    'jumlah_pakai' => (float) $bahan->jumlah_pakai + (float) $detail->jumlah_input,
                    'harga_total_snapshot' => (float) $bahan->harga_total_snapshot + $hargaTotal,
                ]);
                return;
            }

            PembangunanKawasanBahan::create([
                'pembangunan_kawasan_id' => $order->pembangunan_kawasan_id,
                'barang_id' => $detail->barang_id,
                'nama_barang' => $detail->nama_barang ?? $detail->barang?->nama_barang ?? '-',
                'satuan' => $detail->satuan ?? $detail->barang?->baseUnit?->nama ?? '-',
                'jumlah_pakai' => (float) $detail->jumlah_input,
                'harga_total_snapshot' => $hargaTotal,
            ]);
        } else {
            $bahan = PembangunanProyekBahan::where('pembangunan_proyek_id', $order->pembangunan_proyek_id)
                ->where('barang_id', $detail->barang_id)
                ->first();

            if ($bahan) {
                $bahan->update([
                    'jumlah_pakai' => (float) $bahan->jumlah_pakai + (float) $detail->jumlah_input,
                    'harga_total_snapshot' => (float) $bahan->harga_total_snapshot + $hargaTotal,
                ]);
                return;
            }

            PembangunanProyekBahan::create([
                'pembangunan_proyek_id' => $order->pembangunan_proyek_id,
                'barang_id' => $detail->barang_id,
                'nama_barang' => $detail->nama_barang ?? $detail->barang?->nama_barang ?? '-',
                'satuan' => $detail->satuan ?? $detail->barang?->baseUnit?->nama ?? '-',
                'jumlah_pakai' => (float) $detail->jumlah_input,
                'harga_total_snapshot' => $hargaTotal,
            ]);
        }
    }
}
