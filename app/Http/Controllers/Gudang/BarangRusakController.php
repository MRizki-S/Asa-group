<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\BarangRusak;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\NotaBarangMasukDetail;
use App\Models\StockGudang;
use App\Models\StockLedger;
use App\Models\Ubs;
use App\Services\NotificationGroupService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BarangRusakController extends Controller
{

    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }


    // function index untuk menampilkan daftar barang rusak by default bulan ini
    public function index(Request $request)
    {
        $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'status' => ['nullable', 'in:posted,cancelled,all'],
        ]);

        $status = $request->get('status', 'posted');

        $tanggalAwal = $request->filled('tanggal_awal')
            ? Carbon::parse($request->tanggal_awal)->toDateString()
            : now()->startOfMonth()->toDateString();

        $tanggalAkhir = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->toDateString()
            : now()->endOfMonth()->toDateString();

        $barangRusaks = BarangRusak::query()
            ->select([
                'id',
                'nomor_barang_rusak',
                'tgl_rusak',
                'stock_type',
                'ubs_id',
                'barang_id',
                'satuan_id',
                'qty_out',
                'qty_base',
                'status',
                'keterangan',
                'created_by',
            ])
            ->with([
                'barang:id,kode_barang,nama_barang',
                'satuan:id,nama',
                'ubs:id,nama_ubs',
                'creator:id,nama_lengkap,username',
            ])
            ->whereBetween('tgl_rusak', [$tanggalAwal, $tanggalAkhir])
            ->when($status !== 'all', fn($query) => $query->where('status', $status))
            ->orderByDesc('tgl_rusak')
            ->orderByDesc('id')
            ->get();

        return view('gudang.barang-rusak.index', [
            'barangRusaks' => $barangRusaks,
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'status' => $status,
            'breadcrumbs' => [
                [
                    'label' => 'Daftar Barang Rusak',
                    'url' => route('gudang.barangRusak.index'),
                ],
            ],
        ]);
    }

    // function untuk create barang menjadi barang rusak
    public function create()
    {
        $masterBarangs = MasterBarang::query()
            ->where('is_stock', true)
            ->select('id', 'kode_barang', 'nama_barang')
            ->orderBy('nama_barang')
            ->get();

        $ubsList = Ubs::query()
            ->select('id', 'nama_ubs')
            ->orderBy('nama_ubs')
            ->get();

        return view('gudang.barang-rusak.create', [
            'newNomorBarangRusak' => old('nomor_barang_rusak', $this->generateNomorBarangRusak()),
            'masterBarangs' => $masterBarangs,
            'ubsList' => $ubsList,
            'createdByName' => Auth::user()?->nama_lengkap ?? Auth::user()?->username ?? '-',
            'breadcrumbs' => [
                [
                    'label' => 'Daftar Barang Rusak',
                    'url' => route('gudang.barangRusak.index'),
                ],
                [
                    'label' => 'Tambah Barang Rusak',
                    'url' => route('gudang.barangRusak.create'),
                ],
            ],
        ]);
    }

    // function aksi store ketika submit form create barang rusak, dengan validasi, pengecekan stock, konversi satuan, pencatatan FIFO, dan update stock serta ledger
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_barang_rusak' => ['required', 'string', 'max:100', 'unique:barang_rusak,nomor_barang_rusak'],
            'stock_type' => ['required', 'in:HUB,UBS'],
            'ubs_id' => ['nullable', 'required_if:stock_type,UBS', 'exists:ubs,id'],
            'tgl_rusak' => ['required', 'date'],
            'barang_id' => ['required', 'exists:master_barang,id'],
            'satuan_id' => ['required', 'exists:master_satuan,id'],
            'qty_out' => ['required', 'numeric', 'min:0.0001'],
            'keterangan' => ['nullable', 'string'],
        ]);

        if ($validated['stock_type'] === 'HUB') {
            $validated['ubs_id'] = null;
        }

        $messageGroup = null;

        try {
            DB::transaction(function () use ($validated, &$messageGroup) {
                $barang = MasterBarang::findOrFail($validated['barang_id']);
                if (!$barang->is_stock) {
                    throw new \Exception("Barang {$barang->nama_barang} bukan barang stock.");
                }

                $satuanNama = MasterSatuan::where('id', $validated['satuan_id'])->value('nama') ?? '-';

                $qtyBase = $this->resolveQtyBase(
                    (int) $validated['barang_id'],
                    (int) $validated['satuan_id'],
                    (float) $validated['qty_out'],
                    $barang
                );

                $stock = StockGudang::query()
                    ->where('barang_id', $validated['barang_id'])
                    ->where('stock_type', $validated['stock_type'])
                    ->when(
                        $validated['stock_type'] === 'UBS',
                        fn($query) => $query->where('ubs_id', $validated['ubs_id']),
                        fn($query) => $query->whereNull('ubs_id')
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$stock || (float) $stock->jumlah_stock < $qtyBase) {
                    throw new \Exception("Stock {$validated['stock_type']} untuk {$barang->nama_barang} tidak mencukupi.");
                }

                $stockLamaBase = (float) $stock->jumlah_stock;

                $barangRusak = BarangRusak::create([
                    'nomor_barang_rusak' => $validated['nomor_barang_rusak'],
                    'tgl_rusak' => $validated['tgl_rusak'],
                    'stock_type' => $validated['stock_type'],
                    'ubs_id' => $validated['ubs_id'],
                    'barang_id' => $validated['barang_id'],
                    'satuan_id' => $validated['satuan_id'],
                    'qty_out' => $validated['qty_out'],
                    'qty_base' => $qtyBase,
                    'status' => 'posted',
                    'keterangan' => $validated['keterangan'] ?? null,
                    'created_by' => Auth::id(),
                    'posted_at' => now(),
                ]);

                $fifoResult = $this->consumeNotaFifo((int) $validated['barang_id'], $qtyBase);

                foreach ($fifoResult['layers'] as $layer) {
                    $barangRusak->fifoDetails()->create([
                        'nota_barang_masuk_detail_id' => $layer['nota_barang_masuk_detail_id'],
                        'qty_base_diambil' => $layer['qty_base_diambil'],
                        'harga_satuan_base' => $layer['harga_satuan_base'],
                        'harga_total' => $layer['harga_total'],
                    ]);
                }

                $stock->decrement('jumlah_stock', $qtyBase);
                $stockSisaBase = $stockLamaBase - $qtyBase;

                StockLedger::create([
                    'tanggal' => $validated['tgl_rusak'],
                    'barang_id' => $validated['barang_id'],
                    'stock_type' => $validated['stock_type'],
                    'ubs_id' => $validated['ubs_id'],
                    'tipe' => 'keluar',
                    'ref_type' => 'BarangRusak',
                    'ref_id' => $barangRusak->id,
                    'qty_masuk' => 0,
                    'qty_keluar' => $qtyBase,
                    'harga_satuan' => $qtyBase > 0 ? $fifoResult['harga_total'] / $qtyBase : 0,
                    'created_by' => Auth::id(),
                ]);

                $konversiTampilan = (float) $validated['qty_out'] > 0
                    ? $qtyBase / (float) $validated['qty_out']
                    : 1;

                if ($konversiTampilan <= 0) {
                    $konversiTampilan = 1;
                }

                $stockLamaDisplay = $this->formatQtyForNotification($stockLamaBase / $konversiTampilan);
                $qtyRusakDisplay = $this->formatQtyForNotification((float) $validated['qty_out']);
                $stockSisaDisplay = $this->formatQtyForNotification($stockSisaBase / $konversiTampilan);

                $sourceGudang = $validated['stock_type'] === 'UBS'
                    ? 'UBS ' . (Ubs::where('id', $validated['ubs_id'])->value('nama_ubs') ?? '-')
                    : 'HUB';

                $tanggalFormat = date('d M Y', strtotime($validated['tgl_rusak']));
                $messageGroup =
                    "📦 *BARANG RUSAK - PENGURANGAN STOCK*\n\n" .
                    "No: {$barangRusak->nomor_barang_rusak}\n" .
                    "Tanggal: {$tanggalFormat}\n\n" .
                    "Gudang: {$sourceGudang}\n\n" .
                    "Barang: {$barang->kode_barang} - {$barang->nama_barang}\n" .
                    "Jumlah Rusak: {$qtyRusakDisplay} {$satuanNama}\n\n" .
                    "Stock: {$stockLamaDisplay} {$satuanNama} - {$qtyRusakDisplay} {$satuanNama} = {$stockSisaDisplay} {$satuanNama}\n" .
                    "Keterangan: " . ($validated['keterangan'] ?? '-') . "\n\n" .
                    "Status: ✅ Berhasil";
            });

            $groupId = env('FONNTE_ID_GROUP_GUDANG_STOCK');
            if ($groupId && $messageGroup) {
                try {
                    $this->notificationGroup->send($groupId, $messageGroup);
                } catch (\Throwable $notificationError) {
                    Log::warning('Gagal mengirim notifikasi barang rusak ke grup gudang.', [
                        'error' => $notificationError->getMessage(),
                        'nomor_barang_rusak' => $validated['nomor_barang_rusak'],
                    ]);
                }
            }

            return redirect()
                ->route('gudang.barangRusak.index')
                ->with('success', 'Barang rusak berhasil dicatat dan stock berhasil dikurangi.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan barang rusak: ' . $e->getMessage());
        }
    }

    // cari satuan by stock yang dippilih (HUB/UBS) dan stok saat ini untuk barang yang dipilih
    public function getSatuanDanStok(Request $request, int $barangId)
    {
        $request->validate([
            'stock_type' => ['required', 'in:HUB,UBS'],
            'ubs_id' => ['nullable', 'required_if:stock_type,UBS', 'exists:ubs,id'],
        ]);

        $stockBase = StockGudang::query()
            ->where('barang_id', $barangId)
            ->where('stock_type', $request->stock_type)
            ->when(
                $request->stock_type === 'UBS',
                fn($query) => $query->where('ubs_id', $request->ubs_id),
                fn($query) => $query->whereNull('ubs_id')
            )
            ->value('jumlah_stock') ?? 0;

        $satuans = BarangSatuanKonversi::with('satuan:id,nama')
            ->where('barang_id', $barangId)
            ->orderByDesc('is_default')
            ->get();

        return response()->json($satuans->map(function ($satuan) use ($stockBase) {
            $konversi = (float) $satuan->konversi_ke_base;
            $stockSatuan = $konversi > 0 ? (float) $stockBase / $konversi : 0;

            return [
                'id' => $satuan->satuan_id,
                'nama' => $satuan->satuan?->nama ?? '-',
                'is_default' => $satuan->is_default,
                'konversi_ke_base' => $konversi,
                'stock_saat_ini' => round($stockSatuan, 3),
            ];
        }));
    }

    // fungsi untuk mengkonversi qty_out ke qty_base menggunakan konversi satuan, dengan pengecekan jika satuan yang dipilih adalah satuan dasar
    private function resolveQtyBase(int $barangId, int $satuanId, float $qtyOut, MasterBarang $barang): float
    {
        $konversi = BarangSatuanKonversi::where('barang_id', $barangId)
            ->where('satuan_id', $satuanId)
            ->value('konversi_ke_base');

        if (!$konversi && (int) $barang->base_unit_id === $satuanId) {
            $konversi = 1;
        }

        if (!$konversi || (float) $konversi <= 0) {
            throw new \Exception("Konversi satuan untuk {$barang->nama_barang} tidak ditemukan.");
        }

        return round($qtyOut * (float) $konversi, 3);
    }

    // fungsi untuk mengkonsumsi nota barang masuk secara FIFO dan mengembalikan total harga serta detail layer yang digunakan
    private function consumeNotaFifo(int $barangId, float $qtyBase): array
    {
        $remaining = $qtyBase;
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

        if ((float) $layers->sum('jumlah_sisa') + 0.000001 < $qtyBase) {
            $namaBarang = MasterBarang::where('id', $barangId)->value('nama_barang') ?? 'barang ini';
            throw new \Exception("Sisa nota barang masuk untuk {$namaBarang} tidak mencukupi.");
        }

        foreach ($layers as $layer) {
            if ($remaining <= 0) {
                break;
            }

            $takeQty = min((float) $layer->jumlah_sisa, $remaining);
            $hargaSatuanBase = (float) ($layer->harga_satuan_base ?: 0);

            if ($hargaSatuanBase <= 0 && (float) $layer->jumlah_base > 0) {
                $hargaSatuanBase = (float) $layer->harga_total / (float) $layer->jumlah_base;
            }

            $layerHargaTotal = $takeQty * $hargaSatuanBase;
            $hargaTotal += $layerHargaTotal;

            $layer->update([
                'jumlah_sisa' => (float) $layer->jumlah_sisa - $takeQty,
            ]);

            $usedLayers[] = [
                'nota_barang_masuk_detail_id' => $layer->id,
                'qty_base_diambil' => round($takeQty, 3),
                'harga_satuan_base' => round($hargaSatuanBase, 2),
                'harga_total' => round($layerHargaTotal, 2),
            ];

            $remaining -= $takeQty;
        }

        return [
            'harga_total' => round($hargaTotal, 2),
            'layers' => $usedLayers,
        ];
    }

    private function formatQtyForNotification(float $value): string
    {
        $formatted = number_format($value, 3, ',', '.');
        $formatted = rtrim($formatted, '0');

        return rtrim($formatted, ',');
    }

    // fungsi untuk menggenerate nomor barang rusak dengan format BR-YYYYMMDD-XXXXX, dimana XXXXX adalah string acak 5 karakter, dan memastikan nomor unik
    private function generateNomorBarangRusak(): string
    {
        do {
            $nomor = 'BR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (BarangRusak::where('nomor_barang_rusak', $nomor)->exists());

        return $nomor;
    }

    // function untuk menampilkan detail barang rusak, termasuk informasi barang, satuan, ubs (jika ada), user pembuat, user pembatal (jika ada), dan detail FIFO yang menunjukkan nota masuk mana saja yang diambil untuk barang rusak ini
    public function show(string $nomorBarangRusak)
    {
        $barangRusak = BarangRusak::query()
            ->with([
                'barang:id,kode_barang,nama_barang',
                'satuan:id,nama',
                'ubs:id,nama_ubs',
                'creator:id,nama_lengkap,username',
                'canceller:id,nama_lengkap,username',
                'fifoDetails.notaDetail.nota:id,nomor_nota,tanggal_nota,supplier_id',
                'fifoDetails.notaDetail.nota.supplier:id,nama_supplier',
            ])
            ->where('nomor_barang_rusak', $nomorBarangRusak)
            ->firstOrFail();

        return view('gudang.barang-rusak.show', [
            'barangRusak' => $barangRusak,
            'breadcrumbs' => [
                [
                    'label' => 'Daftar Barang Rusak',
                    'url' => route('gudang.barangRusak.index'),
                ],
                [
                    'label' => 'Detail Barang Rusak - ' . $barangRusak->nomor_barang_rusak,
                    'url' => route('gudang.barangRusak.show', $barangRusak->nomor_barang_rusak),
                ],
            ],
        ]);
    }

    // function untuk aksi cancel/kembalikan ke stock dari barang yang sudah rusak
    public function cancel(Request $request, string $nomorBarangRusak)
    {
        $validated = $request->validate([
            'cancel_reason' => ['nullable', 'string'],
        ]);

        $messageGroup = null;

        try {
            DB::transaction(function () use ($nomorBarangRusak, $validated, &$messageGroup) {
                $barangRusak = BarangRusak::query()
                    ->where('nomor_barang_rusak', $nomorBarangRusak)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($barangRusak->status !== 'posted') {
                    throw new \Exception('Barang rusak ini sudah dibatalkan sebelumnya.');
                }

                $fifoDetails = $barangRusak->fifoDetails()
                    ->with('notaDetail')
                    ->lockForUpdate()
                    ->get();

                if ($fifoDetails->isEmpty()) {
                    throw new \Exception('Data FIFO barang rusak tidak ditemukan.');
                }

                $barang = MasterBarang::findOrFail($barangRusak->barang_id);
                $satuanNama = MasterSatuan::where('id', $barangRusak->satuan_id)->value('nama') ?? '-';
                $konversiTampilan = (float) $barangRusak->qty_out > 0
                    ? (float) $barangRusak->qty_base / (float) $barangRusak->qty_out
                    : 1;

                if ($konversiTampilan <= 0) {
                    $konversiTampilan = 1;
                }

                $stock = StockGudang::query()
                    ->where('barang_id', $barangRusak->barang_id)
                    ->where('stock_type', $barangRusak->stock_type)
                    ->when(
                        $barangRusak->stock_type === 'UBS',
                        fn($query) => $query->where('ubs_id', $barangRusak->ubs_id),
                        fn($query) => $query->whereNull('ubs_id')
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    $stock = StockGudang::create([
                        'barang_id' => $barangRusak->barang_id,
                        'stock_type' => $barangRusak->stock_type,
                        'ubs_id' => $barangRusak->stock_type === 'UBS' ? $barangRusak->ubs_id : null,
                        'jumlah_stock' => 0,
                        'minimal_stock' => 0,
                    ]);
                }

                $stockLamaBase = (float) $stock->jumlah_stock;
                $notaRestoreText = '';

                foreach ($fifoDetails as $fifo) {
                    $notaDetail = NotaBarangMasukDetail::query()
                        ->with('nota:id,nomor_nota')
                        ->whereKey($fifo->nota_barang_masuk_detail_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$notaDetail) {
                        throw new \Exception('Detail nota barang masuk untuk rollback FIFO tidak ditemukan.');
                    }

                    $notaDetail->increment('jumlah_sisa', (float) $fifo->qty_base_diambil);
                    $notaRestoreText .= "- " . ($notaDetail->nota?->nomor_nota ?? 'Nota #' . $notaDetail->nota_id) .
                        ": +" . $this->formatQtyForNotification((float) $fifo->qty_base_diambil / $konversiTampilan) . " {$satuanNama}\n";
                }

                $stock->increment('jumlah_stock', (float) $barangRusak->qty_base);
                $stockSisaBase = $stockLamaBase + (float) $barangRusak->qty_base;

                StockLedger::create([
                    'tanggal' => now(),
                    'barang_id' => $barangRusak->barang_id,
                    'stock_type' => $barangRusak->stock_type,
                    'ubs_id' => $barangRusak->ubs_id,
                    'tipe' => 'return',
                    'ref_type' => 'BarangRusak_Cancel',
                    'ref_id' => $barangRusak->id,
                    'qty_masuk' => $barangRusak->qty_base,
                    'qty_keluar' => 0,
                    'harga_satuan' => (float) $barangRusak->qty_base > 0
                        ? (float) $fifoDetails->sum('harga_total') / (float) $barangRusak->qty_base
                        : 0,
                    'created_by' => Auth::id(),
                ]);

                $barangRusak->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => Auth::id(),
                    'cancel_reason' => $validated['cancel_reason'] ?? null,
                ]);

                $stockLamaDisplay = $this->formatQtyForNotification($stockLamaBase / $konversiTampilan);
                $qtyKembaliDisplay = $this->formatQtyForNotification((float) $barangRusak->qty_out);
                $stockSisaDisplay = $this->formatQtyForNotification($stockSisaBase / $konversiTampilan);

                $sourceGudang = $barangRusak->stock_type === 'UBS'
                    ? 'UBS ' . (Ubs::where('id', $barangRusak->ubs_id)->value('nama_ubs') ?? '-')
                    : 'HUB';

                $tanggalFormat = now()->format('d M Y H:i');
                $messageGroup =
                    "♻️ *BARANG RUSAK DIKEMBALIKAN KE STOCK*\n\n" .
                    "No: {$barangRusak->nomor_barang_rusak}\n" .
                    "Tanggal Cancel: {$tanggalFormat}\n\n" .
                    "Gudang: {$sourceGudang}\n\n" .
                    "Barang: {$barang->kode_barang} - {$barang->nama_barang}\n" .
                    "Jumlah Dikembalikan: {$qtyKembaliDisplay} {$satuanNama}\n\n" .
                    "Stock: {$stockLamaDisplay} {$satuanNama} + {$qtyKembaliDisplay} {$satuanNama} = {$stockSisaDisplay} {$satuanNama}\n\n" .
                    "Nota FIFO Dikembalikan:\n" .
                    ($notaRestoreText ?: "-\n") .
                    "Alasan: " . ($validated['cancel_reason'] ?? '-') . "\n\n" .
                    "Status: ✅ Stock berhasil dikembalikan";
            });

            $groupId = env('FONNTE_ID_GROUP_GUDANG_STOCK');
            if ($groupId && $messageGroup) {
                try {
                    $this->notificationGroup->send($groupId, $messageGroup);
                } catch (\Throwable $notificationError) {
                    Log::warning('Gagal mengirim notifikasi cancel barang rusak ke grup gudang.', [
                        'error' => $notificationError->getMessage(),
                        'nomor_barang_rusak' => $nomorBarangRusak,
                    ]);
                }
            }

            return redirect()
                ->route('gudang.barangRusak.show', $nomorBarangRusak)
                ->with('success', 'Barang rusak berhasil dibatalkan dan stock sudah dikembalikan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan barang rusak: ' . $e->getMessage());
        }
    }
}
