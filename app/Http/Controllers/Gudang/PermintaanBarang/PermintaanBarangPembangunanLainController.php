<?php

namespace App\Http\Controllers\Gudang\PermintaanBarang;

use App\Http\Controllers\Controller;
use App\Models\BarangRusak;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\NotaBarangMasukDetail;
use App\Models\PembangunanLain;
use App\Models\PembangunanLainBahan;
use App\Models\PembangunanLainBarangFifoUsage;
use App\Models\PembangunanLainBarangOrder;
use App\Models\PembangunanLainBarangOrderDetail;
use App\Models\PembangunanLainBarangReturn;
use App\Models\PembangunanLainBarangReturnDetail;
use App\Models\PembangunanLainBarangReturnFifo;
use App\Models\PembangunanLainSub;
use App\Models\StockGudang;
use App\Models\StockLedger;
use App\Models\Ubs;
use App\Services\NotificationPribadiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermintaanBarangPembangunanLainController extends Controller
{
    protected NotificationPribadiService $notification;

    public function __construct(NotificationPribadiService $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Simpan Pengajuan Order Barang Keluar Lain-lain
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Superadmin') && !$user->can('gudang.permintaan-barang.pemb-lain.create')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk membuat permintaan barang ini.'], 403);
        }

        $request->validate([
            'pembangunan_lain_id'     => 'required|exists:pembangunan_lain,id',
            'pembangunan_lain_sub_id' => 'required|exists:pembangunan_lain_sub,id',
            'jenis_order'             => 'required|in:stock,direct,cash',
            'ubs_id'                  => 'nullable|required_if:jenis_order,stock|exists:ubs,id',
            'pengawas_id'             => 'nullable|exists:users,id',
            'tanggal_order'           => 'required|date',
            'waktu_order'             => 'nullable|string',
            'catatan'                 => 'nullable|string',
            'items'                   => 'required|array|min:1',
            'items.*.barang_id'       => 'required|exists:master_barang,id',
            'items.*.satuan_id'       => 'nullable|exists:master_satuan,id',
            'items.*.jumlah_input'    => 'required|numeric|min:0.001',
            'items.*.harga_satuan'    => 'nullable|numeric|min:0',
            'items.*.harga_total'     => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate nomor_order: ORD-BLL-YYYYMMDD-XXXX
            $tglOrder = $request->tanggal_order;
            $dateClean = date('Ymd', strtotime($tglOrder));
            $prefix = "ORD-BLL-{$dateClean}-";

            $lastOrder = PembangunanLainBarangOrder::where('nomor_order', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($lastOrder && preg_match('/-(\d+)$/', $lastOrder->nomor_order, $matches)) {
                $nextNumber = ((int) $matches[1]) + 1;
            }
            $nomorOrder = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Waktu & tanggal diajukan
            $waktu = $request->waktu_order ?: date('H:i:s');
            $tanggalDiajukan = date('Y-m-d H:i:s', strtotime("{$tglOrder} {$waktu}"));

            $order = PembangunanLainBarangOrder::create([
                'nomor_order'             => $nomorOrder,
                'pembangunan_lain_id'     => $request->pembangunan_lain_id,
                'pembangunan_lain_sub_id' => $request->pembangunan_lain_sub_id,
                'pengawas_id'             => $request->pengawas_id ?: Auth::id(),
                'ubs_id'                  => $request->jenis_order === 'stock' ? $request->ubs_id : null,
                'jenis_order'             => $request->jenis_order,
                'status_order'            => 'diproses',
                'tanggal_diajukan'        => $tanggalDiajukan,
                'created_by'              => Auth::id(),
                'catatan'                 => $request->catatan,
            ]);

            foreach ($request->items as $item) {
                $barang = MasterBarang::find($item['barang_id']);
                $jumlahInput = (float) $item['jumlah_input'];

                // Hitung jumlah_base berdasarkan konversi satuan
                $faktorKonversi = 1.0;
                if (!empty($item['satuan_id'])) {
                    $konversi = BarangSatuanKonversi::where('barang_id', $item['barang_id'])
                        ->where('satuan_id', $item['satuan_id'])
                        ->first();
                    if ($konversi && (float) $konversi->konversi_ke_base > 0) {
                        $faktorKonversi = (float) $konversi->konversi_ke_base;
                    }
                }
                $jumlahBase = round($jumlahInput * $faktorKonversi, 3);

                $hargaSatuan = !empty($item['harga_satuan']) ? (float) $item['harga_satuan'] : 0;
                $hargaTotal = !empty($item['harga_total']) ? (float) $item['harga_total'] : ($hargaSatuan * $jumlahInput);

                PembangunanLainBarangOrderDetail::create([
                    'order_id'              => $order->id,
                    'barang_id'             => $item['barang_id'],
                    'satuan_id'             => $item['satuan_id'] ?? null,
                    'nama_barang'           => $barang?->nama_barang ?? ($item['nama_barang'] ?? '-'),
                    'satuan'                => $item['satuan'] ?? ($barang?->baseUnit?->nama ?? '-'),
                    'jumlah_input'          => $jumlahInput,
                    'jumlah_base'           => $jumlahBase,
                    'konfirmasi'            => false,
                    'harga_satuan_snapshot' => $hargaSatuan,
                    'harga_total_snapshot'  => $hargaTotal,
                ]);
            }

            DB::commit();

            // Kirim notifikasi WA Pengajuan Order Pembangunan Lain
            $targetGroup = env('FONNTE_ID_GROUP_ORDER_BARANG_UNIT', env('FONNTE_ID_ORDER_BARANG_ABM'));
            if (!empty($targetGroup)) {
                try {
                    $order->loadMissing(['proyek', 'sub', 'ubs', 'pengawas', 'pembuat', 'details.barang']);
                    $pengaju = Auth::user()->nama_lengkap ?? Auth::user()->name;
                    $pengawasNama = $order->pengawas?->nama_lengkap ?? $order->pengawas?->name ?? '-';

                    $message = view('notifications.whatsapp.pembangunan_lain.order_barang', [
                        'order'        => $order,
                        'namaProyek'   => $order->proyek?->nama_proyek ?? '-',
                        'namaSub'      => $order->sub?->nama_sub ?? '-',
                        'pengawas'     => $pengawasNama,
                        'pengaju'      => $pengaju,
                        'tanggalNbk'   => $tglOrder,
                        'waktuOrder'   => $waktu,
                        'gudangAsal'   => $order->ubs ? "{$order->ubs->nama_ubs} ({$order->ubs->kode_ubs})" : null,
                    ])->render();

                    $this->notification->sendWhatsApp($targetGroup, $message);
                } catch (\Throwable $e) {
                    Log::error('Gagal kirim notif WA order pembangunan lain: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Order barang '{$order->nomor_order}' berhasil diajukan.",
                'order'   => $order,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat order barang: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ACC Pengeluaran Barang Lain-lain (Potong Stok FIFO & Catat Ledger)
     */
    public function accBarangOrder(Request $request, $id)
    {
        $order = PembangunanLainBarangOrder::with([
            'details.barang.baseUnit',
            'proyek',
            'sub',
            'ubs',
        ])->findOrFail($id);

        $user = Auth::user();
        $isStock = ($order->jenis_order ?? 'stock') === 'stock';
        $isDirect = ($order->jenis_order ?? '') === 'direct';
        $isCash = ($order->jenis_order ?? '') === 'cash';
        $canAcc = $user->hasRole('Superadmin')
            || ($isStock && ($user->can('gudang.permintaan-barang.pemb-lain.aksi-stock') || $user->can('gudang.permintaan-barang.aksi-stock')))
            || ($isDirect && ($user->can('gudang.permintaan-barang.pemb-lain.aksi-direct') || $user->can('gudang.permintaan-barang.aksi-direct')))
            || ($isCash && ($user->can('gudang.permintaan-barang.pemb-lain.aksi-cash') || $user->can('gudang.permintaan-barang.aksi-cash')));

        if (!$canAcc) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk ACC permintaan barang ' . ($order->jenis_order ?? 'ini') . '.');
        }

        if (!in_array($order->status_order, ['diproses', 'menunggu_spv'])) {
            return back()->with('error', 'Permintaan barang lain-lain ini sudah tidak dalam status menunggu proses.');
        }

        try {
            DB::transaction(function () use ($order, $request) {
                // Generate nomor NBK resmi: NBK-BLL-YYYYMMDD-XXXX
                if (empty($order->nomor_nbk)) {
                    $datePrefix = "NBK-BLL-" . now()->format('Ymd') . '-';

                    $lastNbk = PembangunanLainBarangOrder::where('nomor_nbk', 'like', $datePrefix . '%')
                        ->orderBy('nomor_nbk', 'desc')
                        ->lockForUpdate()
                        ->first();

                    $nextSeq = 1;
                    if ($lastNbk) {
                        $lastSeq = (int) substr($lastNbk->nomor_nbk, strlen($datePrefix));
                        $nextSeq = $lastSeq + 1;
                    }
                    $order->nomor_nbk = $datePrefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
                }

                $this->processAccOrder($order, $request);

                $order->update([
                    'status_order'    => 'selesai',
                    'tanggal_selesai' => now(),
                    'acc_by'          => Auth::id(),
                    'spv_by'          => Auth::id(),
                    'tanggal_spv'     => now(),
                    'nomor_nbk'       => $order->nomor_nbk,
                ]);
            });

            // Kirim notifikasi WA ACC Pembangunan Lain
            $targetGroup = env('FONNTE_ID_ORDER_BARANG_ABM', env('FONNTE_ID_GROUP_ACC_ORDER_BARANG_UNIT', env('FONNTE_ID_GROUP_ORDER_BARANG_UNIT')));
            if (!empty($targetGroup)) {
                try {
                    $order->loadMissing(['proyek', 'sub', 'ubs', 'pengawas', 'accBy', 'details.barang']);
                    $approver = Auth::user();
                    $adminName = $approver->nama_lengkap ?? $approver->name ?? 'Petugas';
                    $roleName = $approver->getRoleNames()->first() ?? 'Petugas Gudang';

                    $message = view('notifications.whatsapp.pembangunan_lain.acc_order_barang', [
                        'order'      => $order,
                        'namaProyek' => $order->proyek?->nama_proyek ?? '-',
                        'namaSub'    => $order->sub?->nama_sub ?? '-',
                        'gudangAsal' => $order->ubs ? "{$order->ubs->nama_ubs} ({$order->ubs->kode_ubs})" : null,
                        'spvName'    => $adminName,
                        'spvRole'    => $roleName,
                        'tanggalAcc' => now()->format('d/m/Y H:i') . ' WIB',
                    ])->render();

                    $this->notification->sendWhatsApp($targetGroup, $message);
                } catch (\Throwable $e) {
                    Log::error('Gagal kirim notif WA acc pembangunan lain: ' . $e->getMessage());
                }
            }

            $isCash = $order->jenis_order === 'cash';
            $successMsg = $isCash
                ? "Permintaan barang lain-lain (Cash) berhasil di-ACC. Realisasi biaya telah dicatat tanpa memotong stok gudang."
                : "Permintaan barang lain-lain berhasil di-ACC. Stok gudang telah dipotong FIFO dan dicatat ke ledger.";

            return redirect()
                ->route('gudang.permintaanBarang.show', ['id' => $order->id, 'jenis_order' => 'pembangunan_lain'])
                ->with('success', $successMsg);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses ACC barang lain-lain: ' . $e->getMessage());
        }
    }

    /**
     * Logika Pemotongan Stok FIFO & Pembuatan Ledger untuk Barang Lain-lain
     */
    private function processAccOrder(PembangunanLainBarangOrder $order, Request $request): void
    {
        $isStock = $order->jenis_order === 'stock';
        $ubsId = $order->ubs_id;

        if ($isStock && empty($ubsId)) {
            throw new \Exception('Gudang asal wajib dipilih untuk order bertipe Stock.');
        }

        foreach ($order->details as $detail) {
            if ($detail->konfirmasi) {
                continue;
            }

            $jumlahBase = (float) $detail->jumlah_base;
            $hargaTotal = 0.0;
            $hargaSatuanBase = 0.0;

            if ($order->jenis_order === 'cash') {
                $cashPrices = $this->resolveCashPricesOrder($request, $detail);
                $hargaTotal = $cashPrices['harga_total'];
                $hargaSatuanBase = $cashPrices['harga_satuan'];

                $detail->update([
                    'konfirmasi'            => true,
                    'harga_satuan_snapshot' => $hargaSatuanBase,
                    'harga_total_snapshot'  => $hargaTotal,
                ]);
            } elseif ($order->jenis_order === 'direct') {
                $hargaTotal = $this->resolveDirectHargaTotalOrder($request, $detail);
                $hargaSatuanBase = $jumlahBase > 0 ? round($hargaTotal / $jumlahBase, 2) : 0;

                $detail->update([
                    'konfirmasi'            => true,
                    'harga_satuan_snapshot' => $hargaSatuanBase,
                    'harga_total_snapshot'  => $hargaTotal,
                ]);
            } else {
                // Tipe STOCK: Potong FIFO dari NotaBarangMasukDetail gudang terpilih
                $fifoResult = $this->applyFifoOrder($order, $detail, $jumlahBase, $ubsId);
                $hargaTotal = $fifoResult['harga_total'];
                $hargaSatuanBase = $jumlahBase > 0 ? round($hargaTotal / $jumlahBase, 2) : 0;

                foreach ($fifoResult['layers'] as $layer) {
                    PembangunanLainBarangFifoUsage::create([
                        'order_detail_id'             => $detail->id,
                        'nota_barang_masuk_detail_id' => $layer['nota_barang_masuk_detail_id'],
                        'jumlah_base'                 => $layer['jumlah_base'],
                        'harga_satuan_snapshot'       => $layer['harga_satuan_snapshot'],
                        'harga_total_snapshot'        => $layer['harga_total_snapshot'],
                    ]);
                }

                // Potong stok gudang (StockGudang)
                $stockGudang = StockGudang::firstOrCreate([
                    'stock_type' => 'UBS',
                    'ubs_id'     => $ubsId,
                    'barang_id'  => $detail->barang_id,
                ], [
                    'jumlah_stock' => 0,
                ]);

                if ((float) $stockGudang->jumlah_stock < $jumlahBase) {
                    throw new \Exception("Stok gudang tidak mencukupi untuk barang '{$detail->nama_barang}'. Tersedia: {$stockGudang->jumlah_stock}, diminta: {$jumlahBase}");
                }

                $saldoAkhir = (float) $stockGudang->jumlah_stock - $jumlahBase;
                $stockGudang->update([
                    'jumlah_stock' => $saldoAkhir,
                ]);

                // Catat mutasi keluar di StockLedger
                StockLedger::create([
                    'tanggal'      => now(),
                    'barang_id'    => $detail->barang_id,
                    'stock_type'   => 'UBS',
                    'ubs_id'       => $ubsId,
                    'tipe'         => 'keluar',
                    'ref_type'     => 'PembangunanLainBarangOrder',
                    'ref_id'       => $order->id,
                    'qty_masuk'    => 0,
                    'qty_keluar'   => $jumlahBase,
                    'harga_satuan' => $hargaSatuanBase,
                    'created_by'   => Auth::id(),
                ]);

                $detail->update([
                    'konfirmasi'            => true,
                    'harga_satuan_snapshot' => $hargaSatuanBase,
                    'harga_total_snapshot'  => $hargaTotal,
                ]);
            }

            // Catat pemakaian bahan di PembangunanLainBahan
            $this->upsertPembangunanLainBahan($order, $detail, $hargaTotal);
        }
    }

    /**
     * Alokasi FIFO layer
     */
    private function applyFifoOrder(PembangunanLainBarangOrder $order, $detail, float $neededQty, int $ubsId): array
    {
        $layers = NotaBarangMasukDetail::query()
            ->select('nota_barang_masuk_detail.*')
            ->join('nota_barang_masuk', 'nota_barang_masuk.id', '=', 'nota_barang_masuk_detail.nota_id')
            ->where('nota_barang_masuk_detail.barang_id', $detail->barang_id)
            ->where('nota_barang_masuk_detail.jumlah_sisa', '>', 0)
            ->where('nota_barang_masuk.status', 'posted')
            ->where('nota_barang_masuk.stock_type', 'UBS')
            ->where('nota_barang_masuk.ubs_id', $ubsId)
            ->orderBy('nota_barang_masuk.tanggal_nota', 'asc')
            ->orderBy('nota_barang_masuk_detail.id', 'asc')
            ->lockForUpdate()
            ->get();

        $totalAvailable = (float) $layers->sum('jumlah_sisa');
        if ($totalAvailable < $neededQty) {
            $namaBarang = $detail->nama_barang ?: 'Barang';
            throw new \Exception("Stok FIFO di gudang terpilih tidak mencukupi untuk {$namaBarang}. Dibutuhkan {$neededQty}, hanya tersedia {$totalAvailable}.");
        }

        $remaining = $neededQty;
        $hargaTotal = 0.0;
        $usedLayers = [];

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
                'jumlah_base'                 => $takeQty,
                'harga_satuan_snapshot'       => $hargaSatuanBase,
                'harga_total_snapshot'        => $layerHargaTotal,
            ];

            $remaining -= $takeQty;
        }

        return [
            'harga_total' => round($hargaTotal, 2),
            'layers'      => $usedLayers,
        ];
    }

    private function resolveDirectHargaTotalOrder(Request $request, $detail): float
    {
        $hargaTotal = $request->input("harga_total.{$detail->id}");

        if ($hargaTotal === null || $hargaTotal === '') {
            if (!is_null($detail->harga_total_snapshot) && (float) $detail->harga_total_snapshot > 0) {
                return (float) $detail->harga_total_snapshot;
            }
            $namaBarang = $detail->nama_barang ?: 'Barang';
            throw new \Exception("Harga total untuk {$namaBarang} wajib diisi.");
        }

        $hargaTotal = (float) $hargaTotal;
        if ($hargaTotal < 0) {
            throw new \Exception("Harga total tidak boleh minus.");
        }

        return round($hargaTotal, 2);
    }

    private function resolveCashPricesOrder(Request $request, $detail): array
    {
        $hargaTotalInput = $request->input("harga_total.{$detail->id}");
        $hargaSatuanInput = $request->input("harga_satuan.{$detail->id}");
        $qtyInput = (float) ($detail->jumlah_input ?? 0);

        $hargaTotal = null;
        $hargaSatuan = null;

        if ($hargaTotalInput !== null && $hargaTotalInput !== '') {
            $hargaTotal = (float) $hargaTotalInput;
        }
        if ($hargaSatuanInput !== null && $hargaSatuanInput !== '') {
            $hargaSatuan = (float) $hargaSatuanInput;
        }

        if ($hargaTotal === null && $hargaSatuan !== null && $qtyInput > 0) {
            $hargaTotal = round($hargaSatuan * $qtyInput, 2);
        } elseif ($hargaSatuan === null && $hargaTotal !== null && $qtyInput > 0) {
            $hargaSatuan = round($hargaTotal / $qtyInput, 2);
        }

        if ($hargaTotal === null) {
            if (!is_null($detail->harga_total_snapshot) && (float) $detail->harga_total_snapshot > 0) {
                $hargaTotal = (float) $detail->harga_total_snapshot;
            } elseif (!is_null($detail->harga_satuan_snapshot) && $qtyInput > 0) {
                $hargaTotal = round((float) $detail->harga_satuan_snapshot * $qtyInput, 2);
            }
        }

        if ($hargaSatuan === null) {
            if (!is_null($detail->harga_satuan_snapshot) && (float) $detail->harga_satuan_snapshot > 0) {
                $hargaSatuan = (float) $detail->harga_satuan_snapshot;
            } elseif ($hargaTotal !== null && $qtyInput > 0) {
                $hargaSatuan = round($hargaTotal / $qtyInput, 2);
            }
        }

        return [
            'harga_total'  => round($hargaTotal ?: 0, 2),
            'harga_satuan' => round($hargaSatuan ?: 0, 2),
        ];
    }

    private function upsertPembangunanLainBahan(PembangunanLainBarangOrder $order, $detail, float $hargaTotal): void
    {
        $baseUnitName = $detail->barang?->baseUnit?->nama ?? ($detail->satuan ?: '-');

        $bahan = PembangunanLainBahan::where('pembangunan_lain_id', $order->pembangunan_lain_id)
            ->where('pembangunan_lain_sub_id', $order->pembangunan_lain_sub_id)
            ->where('barang_id', $detail->barang_id)
            ->first();

        if ($bahan) {
            $bahan->update([
                'jumlah_pakai'         => (float) $bahan->jumlah_pakai + (float) $detail->jumlah_base,
                'harga_total_snapshot' => (float) $bahan->harga_total_snapshot + $hargaTotal,
            ]);
            return;
        }

        PembangunanLainBahan::create([
            'pembangunan_lain_id'     => $order->pembangunan_lain_id,
            'pembangunan_lain_sub_id' => $order->pembangunan_lain_sub_id,
            'barang_id'               => $detail->barang_id,
            'nama_barang'             => $detail->nama_barang ?? ($detail->barang?->nama_barang ?? '-'),
            'satuan'                  => $baseUnitName,
            'jumlah_pakai'            => (float) $detail->jumlah_base,
            'harga_total_snapshot'    => $hargaTotal,
        ]);
    }

    /**
     * Tolak Permintaan Barang Lain-lain
     */
    public function tolakBarangOrder(Request $request, $id)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|max:1000',
        ]);

        $order = PembangunanLainBarangOrder::findOrFail($id);

        $user = Auth::user();
        $isStock = ($order->jenis_order ?? 'stock') === 'stock';
        $isDirect = ($order->jenis_order ?? '') === 'direct';
        $isCash = ($order->jenis_order ?? '') === 'cash';
        $canAksi = $user->hasRole('Superadmin')
            || ($isStock && ($user->can('gudang.permintaan-barang.pemb-lain.aksi-stock') || $user->can('gudang.permintaan-barang.aksi-stock')))
            || ($isDirect && ($user->can('gudang.permintaan-barang.pemb-lain.aksi-direct') || $user->can('gudang.permintaan-barang.aksi-direct')))
            || ($isCash && ($user->can('gudang.permintaan-barang.pemb-lain.aksi-cash') || $user->can('gudang.permintaan-barang.aksi-cash')));

        if (!$canAksi) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk menolak permintaan barang ' . ($order->jenis_order ?? 'ini') . '.');
        }

        if (!in_array($order->status_order, ['diproses', 'menunggu_spv'])) {
            return back()->with('error', 'Permintaan barang ini sudah tidak dalam status menunggu.');
        }

        $order->update([
            'status_order' => 'ditolak',
            'alasan_tolak' => $request->alasan_tolak,
        ]);

        // Kirim notifikasi WA Tolak Pembangunan Lain
        $targetGroup = env('FONNTE_ID_GROUP_TOLAK_ORDER_BARANG_UNIT', env('FONNTE_ID_GROUP_ORDER_BARANG_UNIT', env('FONNTE_ID_ORDER_BARANG_ABM')));
        if (!empty($targetGroup)) {
            try {
                $order->loadMissing(['proyek', 'sub']);
                $user = Auth::user();
                $adminGudang = $user->nama_lengkap ?? $user->name ?? 'Petugas';
                $roleName = $user->getRoleNames()->first() ?? 'Petugas Gudang';

                $message = view('notifications.whatsapp.pembangunan_lain.tolak_order_barang', [
                    'order'       => $order,
                    'namaProyek'  => $order->proyek?->nama_proyek ?? '-',
                    'namaSub'     => $order->sub?->nama_sub ?? '-',
                    'adminGudang' => $adminGudang,
                    'penolakRole' => $roleName,
                    'tanggal'     => now()->format('d/m/Y H:i') . ' WIB',
                    'alasanTolak' => $request->alasan_tolak,
                ])->render();

                $this->notification->sendWhatsApp($targetGroup, $message);
            } catch (\Throwable $e) {
                Log::error('Gagal kirim notif WA tolak pembangunan lain: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Permintaan barang lain-lain berhasil ditolak.');
    }

    /**
     * Ajukan Ulang Permintaan Barang Lain-lain yang Ditolak
     */
    public function resubmitBarangOrder(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasRole('Superadmin') && !$user->can('gudang.permintaan-barang.pemb-lain.ajukan-kembali')) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk mengajukan kembali permintaan barang ini.');
        }

        $request->validate([
            'catatan' => 'nullable|string|max:1000',
        ]);

        $order = PembangunanLainBarangOrder::findOrFail($id);

        if ($order->status_order !== 'ditolak') {
            return back()->with('error', 'Hanya permintaan yang ditolak yang dapat diajukan kembali.');
        }

        $order->update([
            'status_order'     => 'diproses',
            'catatan'          => $request->catatan,
            'tanggal_diajukan' => now(),
        ]);

        return back()->with('success', 'Permintaan barang lain-lain berhasil diajukan kembali.');
    }

    /**
     * Cetak Nota Keluar (NBK) PDF
     */
    public function notaPdf($id)
    {
        $order = PembangunanLainBarangOrder::with([
            'details.barang.baseUnit',
            'pembuat',
            'pengawas',
            'proyek',
            'sub',
            'ubs',
            'accBy',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('gudang.permintaan-barang.nota-keluar-lain-pdf', compact('order'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('NBK-' . ($order->nomor_nbk ?? $order->nomor_order) . '.pdf');
    }

    /**
     * Daftar Retur Barang Pembangunan Lain-lain (Menunggu Konfirmasi)
     */
    public function indexReturn(Request $request)
    {
        $status = $request->get('status', 'diproses');

        $query = PembangunanLainBarangReturn::with([
            'proyek',
            'sub',
            'createdBy',
            'details',
        ]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $returns = $query->latest('tanggal_return')->get();

        return view('gudang.return-barang.index', [
            'category'      => 'pembangunan_lain',
            'titlePage'     => 'Konfirmasi Retur Barang Pemb. Lain-lain',
            'returns'       => $returns,
            'status'        => $status,
            'statusOptions' => [
                'diproses' => 'Menunggu',
                'selesai'  => 'Selesai',
                'ditolak'  => 'Ditolak',
            ],
            'isHistory'     => false,
        ]);
    }

    /**
     * Riwayat Retur Barang Pembangunan Lain-lain
     */
    public function historyReturn(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = PembangunanLainBarangReturn::with([
            'proyek',
            'sub',
            'createdBy',
            'details',
        ])->where('status', '!=', 'diproses');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $returns = $query->latest('tanggal_return')->get();

        return view('gudang.return-barang.index', [
            'category'      => 'pembangunan_lain',
            'titlePage'     => 'Riwayat Retur Barang Pemb. Lain-lain',
            'returns'       => $returns,
            'status'        => $status,
            'statusOptions' => [
                'selesai' => 'Selesai',
                'ditolak' => 'Ditolak',
            ],
            'isHistory'     => true,
        ]);
    }

    /**
     * Detail Retur Barang Pembangunan Lain-lain
     */
    public function showReturn($id)
    {
        $return = PembangunanLainBarangReturn::with([
            'proyek',
            'sub',
            'ubs',
            'createdBy',
            'accBy',
            'details.barang.baseUnit',
        ])->findOrFail($id);

        foreach ($return->details as $det) {
            $mb = $det->barang;
            $satuanOptions = [];
            if ($mb?->baseUnit) {
                $satuanOptions[] = [
                    'id'     => $mb->base_unit_id,
                    'nama'   => $mb->baseUnit->nama,
                    'faktor' => 1.0,
                ];
            }
            $konversiList = BarangSatuanKonversi::with('satuan')->where('barang_id', $det->barang_id)->get();
            foreach ($konversiList as $k) {
                if ($k->satuan_id != $mb?->base_unit_id && $k->satuan) {
                    $satuanOptions[] = [
                        'id'     => $k->satuan_id,
                        'nama'   => $k->satuan->nama,
                        'faktor' => (float) $k->konversi_ke_base,
                    ];
                }
            }
            $det->satuan_options = $satuanOptions;
        }

        return view('gudang.return-barang.show', [
            'category'  => 'pembangunan_lain',
            'return'    => $return,
            'titlePage' => 'Detail Retur Barang Pemb. Lain-lain',
        ]);
    }

    /**
     * Summary Barang yang dapat diretur untuk Sub-Pekerjaan tertentu
     */
    public function returnSummary(Request $request, $subId)
    {
        $details = PembangunanLainBarangOrderDetail::query()
            ->join('pembangunan_lain_barang_order as o', 'o.id', '=', 'pembangunan_lain_barang_order_detail.order_id')
            ->where('o.pembangunan_lain_sub_id', $subId)
            ->where('o.status_order', 'selesai')
            ->where('pembangunan_lain_barang_order_detail.konfirmasi', true)
            ->select([
                'pembangunan_lain_barang_order_detail.barang_id',
                DB::raw('MAX(pembangunan_lain_barang_order_detail.nama_barang) as nama_barang'),
                DB::raw('SUM(pembangunan_lain_barang_order_detail.jumlah_base) as total_diterima_base'),
            ])
            ->groupBy('pembangunan_lain_barang_order_detail.barang_id')
            ->get();

        $returnedBase = PembangunanLainBarangReturnDetail::query()
            ->join('pembangunan_lain_barang_returns as r', 'r.id', '=', 'pembangunan_lain_barang_return_details.return_id')
            ->where('r.pembangunan_lain_sub_id', $subId)
            ->whereIn('r.status', ['diproses', 'selesai'])
            ->select([
                'pembangunan_lain_barang_return_details.barang_id',
                DB::raw('SUM(pembangunan_lain_barang_return_details.jumlah_base) as total_returned_base'),
            ])
            ->groupBy('pembangunan_lain_barang_return_details.barang_id')
            ->pluck('total_returned_base', 'barang_id')
            ->toArray();

        $items = $details->map(function ($d) use ($returnedBase) {
            $totalDiterimaBase = (float) $d->total_diterima_base;
            $sudahReturBase = (float) ($returnedBase[$d->barang_id] ?? 0);
            $sisaBase = max(0, $totalDiterimaBase - $sudahReturBase);

            $masterBarang = MasterBarang::with(['baseUnit', 'satuanKonversi.satuan'])->find($d->barang_id);
            $baseSatuanNama = $masterBarang?->baseUnit?->nama ?? 'Unit';
            $baseSatuanId = $masterBarang?->base_unit_id;

            $satuanOptions = [];
            if ($baseSatuanId) {
                $satuanOptions[] = [
                    'satuan_id'         => $baseSatuanId,
                    'nama_satuan'       => $baseSatuanNama,
                    'konversi_ke_base'  => 1.0,
                    'is_base'           => true,
                ];
            }
            if ($masterBarang?->satuanKonversi) {
                foreach ($masterBarang->satuanKonversi as $konv) {
                    if ($konv->satuan_id != $baseSatuanId && $konv->satuan) {
                        $satuanOptions[] = [
                            'satuan_id'         => $konv->satuan_id,
                            'nama_satuan'       => $konv->satuan->nama,
                            'konversi_ke_base'  => (float) $konv->konversi_ke_base,
                            'is_base'           => false,
                        ];
                    }
                }
            }

            return [
                'barang_id'           => $d->barang_id,
                'nama_barang'         => $masterBarang->nama_barang ?? $d->nama_barang,
                'kode_barang'         => $masterBarang?->kode_barang ?? '-',
                'base_satuan_id'      => $baseSatuanId,
                'base_satuan_nama'    => $baseSatuanNama,
                'total_diterima_base' => $totalDiterimaBase,
                'sudah_retur_base'    => $sudahReturBase,
                'sisa_retur_base'     => $sisaBase,
                'satuan_options'      => $satuanOptions,
            ];
        });

        return response()->json(['items' => $items]);
    }

    /**
     * Persetujuan (ACC) Retur Barang Pembangunan Lain-lain
     */
    public function accBarangReturn(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasRole('Superadmin') && !$user->can('gudang.return-barang.return-lain.aksi')) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk ACC retur barang ini.');
        }

        $return = PembangunanLainBarangReturn::with(['proyek', 'sub', 'details.barang.baseUnit'])->findOrFail($id);

        if ($return->status !== 'diproses') {
            return back()->with('error', 'Status pengajuan retur barang pembangunan lain ini sudah tidak dapat di-ACC.');
        }

        try {
            DB::transaction(function () use ($request, $return) {
                $ubsId = $return->ubs_id ?? 1;
                $notaRtnId = null;

                foreach ($return->details as $detail) {
                    $itemInput = collect($request->input('items', []))->firstWhere('id', $detail->id);

                    $layakInput = isset($itemInput['jumlah_layak_input'])
                        ? (float) $itemInput['jumlah_layak_input']
                        : (float) $detail->jumlah_base;

                    $rusakInput = isset($itemInput['jumlah_rusak_input'])
                        ? (float) $itemInput['jumlah_rusak_input']
                        : 0.0;

                    if ($layakInput < 0 || $rusakInput < 0) {
                        throw new \Exception("Jumlah barang layak dan rusak untuk {$detail->nama_barang} tidak boleh bernilai negatif.");
                    }

                    $satuanInputId = isset($itemInput['satuan_id']) ? (int) $itemInput['satuan_id'] : $detail->satuan_id;

                    $faktor = 1.0;
                    if ($detail->barang && $detail->barang->base_unit_id == $satuanInputId) {
                        $faktor = 1.0;
                    } else {
                        $faktor = BarangSatuanKonversi::where('barang_id', $detail->barang_id)
                            ->where('satuan_id', $satuanInputId)
                            ->value('konversi_ke_base') ?? 1.0;
                    }

                    $jumlahLayakBase = round($layakInput * $faktor, 4);
                    $jumlahRusakBase = round($rusakInput * $faktor, 4);

                    if (($jumlahLayakBase + $jumlahRusakBase) > ((float) $detail->jumlah_base + 0.001)) {
                        throw new \Exception("Jumlah barang layak + rusak untuk {$detail->nama_barang} tidak boleh melebihi jumlah base (" . (float) $detail->jumlah_base . ").");
                    }

                    if (abs(($jumlahLayakBase + $jumlahRusakBase) - (float) $detail->jumlah_base) > 0.001) {
                        throw new \Exception("Jumlah layak + rusak untuk barang {$detail->nama_barang} harus persis sama dengan total return (" . (float) $detail->jumlah_base . ").");
                    }

                    // 1. Ambil catatan pemakaian FIFO (jika berasal dari order stock)
                    $fifoUsages = PembangunanLainBarangFifoUsage::query()
                        ->join('pembangunan_lain_barang_order_detail as od', 'od.id', '=', 'pembangunan_lain_barang_fifo_usage.order_detail_id')
                        ->join('pembangunan_lain_barang_order as o', 'o.id', '=', 'od.order_id')
                        ->where('o.pembangunan_lain_sub_id', $return->pembangunan_lain_sub_id)
                        ->where('o.status_order', 'selesai')
                        ->where('od.barang_id', $detail->barang_id)
                        ->whereRaw('pembangunan_lain_barang_fifo_usage.jumlah_base > pembangunan_lain_barang_fifo_usage.jumlah_return_base')
                        ->orderBy('o.tanggal_diajukan', 'asc')
                        ->orderBy('pembangunan_lain_barang_fifo_usage.id', 'asc')
                        ->select('pembangunan_lain_barang_fifo_usage.*', 'od.id as order_detail_id')
                        ->lockForUpdate()
                        ->get();

                    $remainingReturn = (float) $detail->jumlah_base;
                    $remainingLayak = $jumlahLayakBase;
                    $remainingRusak = $jumlahRusakBase;
                    $totalHargaReturnDetail = 0.0;

                    foreach ($fifoUsages as $fifoUsage) {
                        if ($remainingReturn <= 0.0001) break;

                        $sisaInUsage = (float) $fifoUsage->jumlah_base - (float) $fifoUsage->jumlah_return_base;
                        $takeQty = min($sisaInUsage, $remainingReturn);

                        $takeLayak = min($takeQty, $remainingLayak);
                        $takeRusak = $takeQty - $takeLayak;

                        $hargaSatuanSnapshot = (float) $fifoUsage->harga_satuan_snapshot;
                        $hargaTotalSnapshot = round($takeQty * $hargaSatuanSnapshot, 2);
                        $totalHargaReturnDetail += $hargaTotalSnapshot;

                        PembangunanLainBarangReturnFifo::create([
                            'return_detail_id'      => $detail->id,
                            'fifo_usage_id'         => $fifoUsage->id,
                            'jumlah_base'           => $takeQty,
                            'jumlah_return_base'    => $takeQty,
                            'jumlah_layak_base'     => $takeLayak,
                            'jumlah_rusak_base'     => $takeRusak,
                            'harga_satuan_snapshot' => $hargaSatuanSnapshot,
                            'harga_total_snapshot'  => $hargaTotalSnapshot,
                        ]);

                        $fifoUsage->increment('jumlah_return_base', $takeQty);

                        $orderDetail = PembangunanLainBarangOrderDetail::find($fifoUsage->order_detail_id);
                        if ($orderDetail) {
                            $orderDetail->increment('jumlah_return_base', $takeQty);
                            $orderDetail->increment('jumlah_return', round($takeQty / $faktor, 3));
                        }

                        $remainingReturn -= $takeQty;
                        $remainingLayak -= $takeLayak;
                        $remainingRusak -= $takeRusak;
                    }

                    // 2. Jika masih ada sisa return (misalnya dari pembelian langsung / cash)
                    if ($remainingReturn > 0.0001) {
                        $cashOrderDetails = PembangunanLainBarangOrderDetail::query()
                            ->join('pembangunan_lain_barang_order as o', 'o.id', '=', 'pembangunan_lain_barang_order_detail.order_id')
                            ->where('o.pembangunan_lain_sub_id', $return->pembangunan_lain_sub_id)
                            ->where('o.status_order', 'selesai')
                            ->where('pembangunan_lain_barang_order_detail.barang_id', $detail->barang_id)
                            ->whereRaw('pembangunan_lain_barang_order_detail.jumlah_base > pembangunan_lain_barang_order_detail.jumlah_return_base')
                            ->orderBy('o.tanggal_diajukan', 'asc')
                            ->select('pembangunan_lain_barang_order_detail.*')
                            ->lockForUpdate()
                            ->get();

                        foreach ($cashOrderDetails as $cod) {
                            if ($remainingReturn <= 0.0001) break;

                            $sisaInCod = (float) $cod->jumlah_base - (float) $cod->jumlah_return_base;
                            $takeQty = min($sisaInCod, $remainingReturn);

                            $takeLayak = min($takeQty, $remainingLayak);
                            $takeRusak = $takeQty - $takeLayak;

                            $hargaSatuanSnapshot = (float) $cod->harga_satuan_snapshot;
                            $hargaTotalSnapshot = round($takeQty * $hargaSatuanSnapshot, 2);
                            $totalHargaReturnDetail += $hargaTotalSnapshot;

                            PembangunanLainBarangReturnFifo::create([
                                'return_detail_id'      => $detail->id,
                                'fifo_usage_id'         => null,
                                'jumlah_base'           => $takeQty,
                                'jumlah_return_base'    => $takeQty,
                                'jumlah_layak_base'     => $takeLayak,
                                'jumlah_rusak_base'     => $takeRusak,
                                'harga_satuan_snapshot' => $hargaSatuanSnapshot,
                                'harga_total_snapshot'  => $hargaTotalSnapshot,
                            ]);

                            $cod->increment('jumlah_return_base', $takeQty);
                            $cod->increment('jumlah_return', round($takeQty / $faktor, 3));

                            $remainingReturn -= $takeQty;
                            $remainingLayak -= $takeLayak;
                            $remainingRusak -= $takeRusak;
                        }
                    }

                    $avgHargaSatuan = (float) $detail->jumlah_base > 0 ? $totalHargaReturnDetail / (float) $detail->jumlah_base : 0;
                    $detail->update([
                        'jumlah_layak_base'     => $jumlahLayakBase,
                        'jumlah_rusak_base'     => $jumlahRusakBase,
                        'harga_satuan_snapshot' => $avgHargaSatuan,
                        'harga_total_snapshot'  => $totalHargaReturnDetail,
                    ]);

                    // 3. Kurangi snapshot biaya material sub-proyek di PembangunanLainBahan
                    $bahan = PembangunanLainBahan::where('pembangunan_lain_sub_id', $return->pembangunan_lain_sub_id)
                        ->where('barang_id', $detail->barang_id)
                        ->first();

                    if ($bahan) {
                        $newJumlahPakai = max(0, (float) $bahan->jumlah_pakai - (float) $detail->jumlah_base);
                        $newHargaTotal = max(0, (float) $bahan->harga_total_snapshot - $totalHargaReturnDetail);
                        $bahan->update([
                            'jumlah_pakai'         => $newJumlahPakai,
                            'harga_total_snapshot' => $newHargaTotal,
                        ]);
                    }

                    // 4. Barang Layak -> Masuk Stock Gudang UBS & Buat Nota Barang Masuk (jenis_nota = 'return_barang')
                    if ($jumlahLayakBase > 0) {
                        if (!$notaRtnId) {
                            $datePrefixNota = 'RTN-' . now()->format('Ymd') . '-';
                            $lastNota = DB::table('nota_barang_masuk')
                                ->where('nomor_nota', 'like', $datePrefixNota . '%')
                                ->orderBy('id', 'desc')
                                ->first();
                            $seq = $lastNota ? ((int) substr($lastNota->nomor_nota, strlen($datePrefixNota)) + 1) : 1;
                            $nomorNota = $datePrefixNota . str_pad($seq, 4, '0', STR_PAD_LEFT);

                            $notaRtnId = DB::table('nota_barang_masuk')->insertGetId([
                                'nomor_nota'   => $nomorNota,
                                'tanggal_nota' => now()->format('Y-m-d'),
                                'jenis_nota'   => 'return_barang',
                                'cara_bayar'   => 'cash',
                                'stock_type'   => 'UBS',
                                'ubs_id'       => $ubsId,
                                'status'       => 'posted',
                                'created_by'   => Auth::id(),
                                'posted_at'    => now(),
                                'created_at'   => now(),
                                'updated_at'   => now(),
                            ]);
                        }

                        $layakHargaTotal = round($jumlahLayakBase * $avgHargaSatuan, 2);

                        DB::table('nota_barang_masuk_detail')->insert([
                            'nota_id'           => $notaRtnId,
                            'barang_id'         => $detail->barang_id,
                            'jumlah_input'      => round($jumlahLayakBase / $faktor, 3),
                            'satuan_id'         => $satuanInputId,
                            'jumlah_base'       => $jumlahLayakBase,
                            'jumlah_sisa'       => $jumlahLayakBase,
                            'harga_satuan'      => round($avgHargaSatuan * $faktor, 2),
                            'harga_satuan_base' => $avgHargaSatuan,
                            'harga_total'       => $layakHargaTotal,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ]);

                        $stock = StockGudang::where('barang_id', $detail->barang_id)
                            ->where('stock_type', 'UBS')
                            ->where('ubs_id', $ubsId)
                            ->lockForUpdate()
                            ->first();

                        if ($stock) {
                            $stock->increment('jumlah_stock', $jumlahLayakBase);
                        } else {
                            StockGudang::create([
                                'barang_id'    => $detail->barang_id,
                                'stock_type'   => 'UBS',
                                'ubs_id'       => $ubsId,
                                'jumlah_stock' => $jumlahLayakBase,
                            ]);
                        }

                        StockLedger::create([
                            'tanggal'      => now(),
                            'barang_id'    => $detail->barang_id,
                            'stock_type'   => 'UBS',
                            'ubs_id'       => $ubsId,
                            'tipe'         => 'masuk',
                            'ref_type'     => 'PembangunanLainBarangReturn',
                            'ref_id'       => $return->id,
                            'qty_masuk'    => $jumlahLayakBase,
                            'qty_keluar'   => 0,
                            'harga_satuan' => $avgHargaSatuan,
                            'created_by'   => Auth::id(),
                        ]);
                    }

                    // 5. Barang Rusak -> Catat di tabel barang_rusak
                    if ($jumlahRusakBase > 0) {
                        $nomorBr = 'BR-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
                        BarangRusak::create([
                            'nomor_barang_rusak' => $nomorBr,
                            'tgl_rusak'          => now(),
                            'stock_type'         => 'UBS',
                            'ubs_id'             => $ubsId,
                            'barang_id'          => $detail->barang_id,
                            'satuan_id'          => $satuanInputId,
                            'qty_out'            => round($jumlahRusakBase / $faktor, 3),
                            'qty_base'           => $jumlahRusakBase,
                            'status'             => 'posted',
                            'keterangan'         => "Barang Rusak dari Retur Pemb. Lain #{$return->pembangunan_lain_sub_id} ({$return->nomor_return})",
                            'created_by'         => Auth::id(),
                            'posted_at'          => now(),
                        ]);
                    }
                }

                $return->update([
                    'status'        => 'selesai',
                    'status_return' => 'disetujui',
                    'acc_by'        => Auth::id(),
                    'acc_at'        => now(),
                ]);
            });

            // Kirim notifikasi WA
            $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Admin Gudang';
            $targetGroup = env('FONNTE_ID_GROUP_ACC_RETUR_BARANG_UNIT', env('FONNTE_ID_GROUP_RETUR_BARANG_UNIT', env('FONNTE_ID_ORDER_BARANG_ABM')));
            if (!empty($targetGroup)) {
                $message = view('notifications.whatsapp.pembangunan_lain.acc_retur_barang', [
                    'return'      => $return,
                    'namaProyek'  => $return->proyek?->nama_proyek ?? '-',
                    'namaSub'     => $return->sub?->nama_sub ?? '-',
                    'adminGudang' => $adminName,
                    'tanggalAcc'  => now()->format('d/m/Y H:i') . ' WIB',
                ])->render();
                $this->notification->sendWhatsApp($targetGroup, $message);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal ACC retur barang pembangunan lain: ' . $e->getMessage());
        }

        return back()->with('success', 'Pengajuan retur barang pembangunan lain berhasil di-ACC.');
    }

    /**
     * Penolakan Retur Barang Pembangunan Lain-lain
     */
    public function rejectBarangReturn(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasRole('Superadmin') && !$user->can('gudang.return-barang.return-lain.aksi')) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk menolak retur barang ini.');
        }

        $return = PembangunanLainBarangReturn::with(['proyek', 'sub'])->findOrFail($id);

        if ($return->status !== 'diproses') {
            return back()->with('error', 'Status pengajuan retur barang ini tidak dapat ditolak.');
        }

        $request->validate([
            'alasan_tolak' => 'required|string',
        ]);

        $return->update([
            'status'        => 'ditolak',
            'status_return' => 'ditolak',
            'alasan_tolak'  => $request->alasan_tolak,
            'acc_by'        => Auth::id(),
            'acc_at'        => now(),
        ]);

        // Kirim notifikasi WA
        $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Admin Gudang';
        $targetGroup = env('FONNTE_ID_GROUP_TOLAK_RETUR_BARANG_UNIT', env('FONNTE_ID_GROUP_RETUR_BARANG_UNIT', env('FONNTE_ID_ORDER_BARANG_ABM')));
        if (!empty($targetGroup)) {
            $message = view('notifications.whatsapp.pembangunan_lain.tolak_retur_barang', [
                'return'      => $return,
                'namaProyek'  => $return->proyek?->nama_proyek ?? '-',
                'namaSub'     => $return->sub?->nama_sub ?? '-',
                'adminGudang' => $adminName,
                'alasan'      => $request->alasan_tolak,
                'tanggal'     => now()->format('d/m/Y H:i') . ' WIB',
            ])->render();
            $this->notification->sendWhatsApp($targetGroup, $message);
        }

        return back()->with('success', 'Pengajuan retur barang pembangunan lain berhasil ditolak.');
    }
}
