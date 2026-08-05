<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotaBarangMasuk;
use App\Models\MasterBarang;
use App\Models\StockGudang;
use App\Models\StockLedger;
use App\Models\MasterSupplier;
use App\Models\Ubs;
use App\Services\NotificationGroupService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DraftNotaMasukController extends Controller
{
    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }
    // menampilkan list draft nota masuk (status = 'draft')
    public function index()
    {
        $notas = NotaBarangMasuk::with(['details.barang', 'supplier'])
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('gudang.draft-nota-masuk.index', [
            'notas' => $notas,
            'breadcrumbs' => [
                [
                    'label' => 'Daftar Nota Barang Masuk',
                    'url' => route('gudang.daftarNotaMasuk.index'),
                ],
                [
                    'label' => 'Draft Nota Barang Masuk',
                    'url' => route('gudang.draftNotaMasuk.index'),
                ],
            ],
        ]);
    }

    // edit draft nota masuk — pakai $id (primary key)
    public function edit($id)
    {
        $nota = NotaBarangMasuk::with(['details.barang', 'details.satuan', 'supplier', 'ubs'])
            ->where('id', $id)
            ->where('status', 'draft')
            ->firstOrFail();

        $masterBarangs = MasterBarang::where('is_stock', 1)->select('id', 'kode_barang', 'nama_barang')->get();

        // Ambil master supplier yang aktif
        $masterSuppliers = MasterSupplier::where('status', 1)
            ->orderBy('nama_supplier')
            ->get();

        // Ambil unit bisnis / gudang ubs
        $ubs = Ubs::orderBy('nama_ubs')
            ->get();

        // Siapkan data item untuk Alpine.js
        $existingItems = $nota->details->map(function ($detail) {
            return [
                'barang_id' => $detail->barang_id,
                'merk' => $detail->merk ?? '',
                'satuan_id' => $detail->satuan_id,
                'jumlah' => (float)$detail->jumlah_input,
                'harga_satuan' => (float)$detail->harga_satuan,
                'harga_satuan_display' => number_format($detail->harga_satuan, 0, ',', '.'),
                'harga_total' => (float)$detail->harga_total,
                'harga_total_display' => number_format($detail->harga_total, 0, ',', '.'),
                // Ambil daftar satuan untuk barang ini agar dropdown satuan langsung terisi
                'satuanList' => DB::table('barang_satuan_konversi as bsk')
                    ->join('master_satuan as ms', 'ms.id', '=', 'bsk.satuan_id')
                    ->where('bsk.barang_id', $detail->barang_id)
                    ->select('ms.id', 'ms.nama', 'bsk.is_default')
                    ->get()
            ];
        });

        return view('gudang.draft-nota-masuk.edit', [
            'nota' => $nota,
            'masterBarangs' => $masterBarangs,
            'masterSuppliers' => $masterSuppliers,
            'ubs' => $ubs,
            'existingItems' => $existingItems,
            'breadcrumbs' => [
                [
                    'label' => 'Daftar Nota Barang Masuk',
                    'url' => route('gudang.daftarNotaMasuk.index'),
                ],
                [
                    'label' => 'Draft Nota Barang Masuk',
                    'url' => route('gudang.draftNotaMasuk.index'),
                ],
                [
                    'label' => 'Edit Draft #' . $nota->id,
                    'url' => route('gudang.draftNotaMasuk.edit', $nota->id),
                ],
            ],
        ]);
    }

    // update perubahan pada draft nota masuk — status tetap draft
    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_nota' => 'required|date',
            'supplier_id' => 'required|exists:master_supplier,id',
            'ubs_id' => 'required|exists:ubs,id',
            'cara_bayar' => 'required|in:cash,hutang',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.jumlah_masuk' => 'required|numeric|min:0.001',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.harga_total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $nota = NotaBarangMasuk::where('id', $id)
                ->where('status', 'draft')
                ->firstOrFail();

            // 1. Update Header Nota (stok_gudang tidak disentuh karena masih draft)
            $nota->update([
                'tanggal_nota' => $request->tanggal_nota,
                'supplier_id' => $request->supplier_id,
                'cara_bayar' => $request->cara_bayar,
                'stock_type' => 'UBS', // paksa stock_type ke UBS
                'ubs_id' => $request->ubs_id,
            ]);

            // 2. Hapus Detail Lama
            $nota->details()->delete();

            // 3. Masukkan Detail Baru
            foreach ($request->items as $item) {
                // Ambil konversi ke base unit
                $konversi = DB::table('barang_satuan_konversi')
                    ->where('barang_id', $item['barang_id'])
                    ->where('satuan_id', $item['satuan_id'])
                    ->value('konversi_ke_base') ?? 1;

                $jumlahBase = $item['jumlah_masuk'] * $konversi;
                $hargaSatuanBase = $item['harga_satuan'] / $konversi;

                $nota->details()->create([
                    'barang_id' => $item['barang_id'],
                    'merk' => $item['merk'] ?? '',
                    'satuan_id' => $item['satuan_id'],
                    'jumlah_input' => $item['jumlah_masuk'],
                    'jumlah_base' => $jumlahBase,
                    'jumlah_sisa' => $jumlahBase,
                    'harga_satuan' => $item['harga_satuan'],
                    'harga_satuan_base' => $hargaSatuanBase,
                    'harga_total' => $item['harga_total'],
                ]);
            }

            DB::commit();

            return redirect()->route('gudang.draftNotaMasuk.index')
                ->with('success', 'Draft #' . $id . ' berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui draft: ' . $e->getMessage()])->withInput();
        }
    }

    // Posting draft nota masuk ke stok gudang HUB / UBS
    public function post(Request $request, $id)
    {
        $request->validate([
            'tanggal_nota' => 'required|date',
            'supplier_id' => 'required|exists:master_supplier,id',
            'ubs_id' => 'required|exists:ubs,id',
            'cara_bayar' => 'required|in:cash,hutang',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.jumlah_masuk' => 'required|numeric|min:0.001',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.harga_total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $nota = NotaBarangMasuk::where('id', $id)
                ->where('status', 'draft')
                ->lockForUpdate()
                ->firstOrFail();

            // 1. Generate nomor nota berdasarkan tanggal posting (bukan tanggal nota)
            $prefix = 'NOTA-' . now()->format('Ymd') . '-';
            $lastNomor = NotaBarangMasuk::where('nomor_nota', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('nomor_nota')
                ->value('nomor_nota');

            $nextSeq = $lastNomor ? (intval(substr($lastNomor, -4)) + 1) : 1;
            $nomorNota = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            // 2. Update Header Nota & Status jadi posted
            $nota->update([
                'nomor_nota' => $nomorNota,
                'tanggal_nota' => $request->tanggal_nota,
                'supplier_id' => $request->supplier_id,
                'cara_bayar' => $request->cara_bayar,
                'stock_type' => 'UBS', // default/paksa stock_type ke UBS
                'ubs_id' => $request->ubs_id,
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            // 3. Hapus Detail Lama (antisipasi jika ada perubahan saat posting)
            $nota->details()->delete();

            // 4. Masukkan Detail Baru & Update Stok & Ledger
            $itemsDetailText = '';
            $noItem = 1;
            $totalBarang = count($request->items);
            foreach ($request->items as $item) {
                // Ambil konversi ke base unit
                $konversi = DB::table('barang_satuan_konversi')
                    ->where('barang_id', $item['barang_id'])
                    ->where('satuan_id', $item['satuan_id'])
                    ->value('konversi_ke_base') ?? 1;

                $jumlahBase = $item['jumlah_masuk'] * $konversi;
                $hargaSatuanBase = $item['harga_satuan'] / $konversi;

                // Simpan Detail
                $nota->details()->create([
                    'barang_id' => $item['barang_id'],
                    'merk' => $item['merk'] ?? '',
                    'satuan_id' => $item['satuan_id'],
                    'jumlah_input' => $item['jumlah_masuk'],
                    'jumlah_base' => $jumlahBase,
                    'jumlah_sisa' => $jumlahBase,
                    'harga_satuan' => $item['harga_satuan'],
                    'harga_satuan_base' => $hargaSatuanBase,
                    'harga_total' => $item['harga_total'],
                ]);

                // 5. Update Stok Gudang sesuai dengan stock_type dan ubs_id pada header nota
                $stock = StockGudang::firstOrCreate(
                    [
                        'barang_id'  => $item['barang_id'],
                        'stock_type' => $nota->stock_type,
                        'ubs_id'     => $nota->ubs_id
                    ],
                    ['jumlah_stock' => 0, 'minimal_stock' => 0]
                );

                $stockSebelum = (float) $stock->jumlah_stock;
                $stock->increment('jumlah_stock', $jumlahBase);
                $stockSesudah = $stockSebelum + $jumlahBase;

                // 6. Catat ke Stock Ledger sesuai dengan stock_type dan ubs_id pada header nota
                StockLedger::create([
                    'tanggal' => $request->tanggal_nota,
                    'barang_id' => $item['barang_id'],
                    'stock_type' => $nota->stock_type,
                    'ubs_id' => $nota->ubs_id,
                    'tipe' => 'Masuk',
                    'ref_type' => 'NotaBarangMasuk',
                    'ref_id' => $nota->id,
                    'qty_masuk' => $jumlahBase,
                    'qty_keluar' => 0,
                    'harga_satuan' => $hargaSatuanBase,
                    'created_by' => Auth::id(),
                ]);

                // 7. Kumpulkan teks detail barang untuk notifikasi WA
                $kodeBarang = DB::table('master_barang')->where('id', $item['barang_id'])->value('kode_barang') ?? '';
                $namaBarang = DB::table('master_barang')->where('id', $item['barang_id'])->value('nama_barang') ?? '-';
                $namaSatuan = DB::table('master_satuan')->where('id', $item['satuan_id'])->value('nama') ?? '';

                $itemsDetailText .= "Barang: {$kodeBarang} - {$namaBarang}\n";
                if (!empty($item['merk'])) {
                    $itemsDetailText .= "Merk: {$item['merk']}\n";
                }
                $itemsDetailText .= "Jumlah Masuk: " . (float)$item['jumlah_masuk'] . " {$namaSatuan}\n";
                $itemsDetailText .= "Stock: {$stockSebelum} {$namaSatuan} + " . (float)$item['jumlah_masuk'] . " {$namaSatuan} = {$stockSesudah} {$namaSatuan}\n\n";
                $noItem++;
            }

            DB::commit();

            // 8. Kirim notifikasi WA ke grup gudang
            $groupId = env('FONNTE_ID_GROUP_GUDANG_STOCK');
            if ($groupId) {
                $namaSupplier = DB::table('master_supplier')->where('id', $request->supplier_id)->value('nama_supplier') ?? '-';
                $namaGudang   = DB::table('ubs')->where('id', $request->ubs_id)->value('nama_ubs') ?? '-';
                $tanggalFormat = \Carbon\Carbon::parse($request->tanggal_nota)->isoFormat('D MMM Y');

                $message =
                    "📦 *BARANG MASUK - PENAMBAHAN STOCK*\n\n" .
                    "No: {$nomorNota}\n" .
                    "Tanggal: {$tanggalFormat}\n\n" .
                    "Supplier: {$namaSupplier}\n" .
                    "Gudang: {$namaGudang}\n" .
                    "Cara Bayar: " . ucfirst($request->cara_bayar) . "\n\n" .
                    $itemsDetailText .
                    "Status: ✅ Berhasil";

                $this->notificationGroup->send($groupId, $message);
            }

            return redirect()->route('gudang.daftarNotaMasuk.index')
                ->with('success', 'Berhasil diposting sebagai ' . $nomorNota . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal posting nota: ' . $e->getMessage()])->withInput();
        }
    }

    // Menghapus draft nota secara permanen
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $nota = NotaBarangMasuk::where('id', $id)
                ->where('status', 'draft')
                ->firstOrFail();

            // 1. Hapus Details
            $nota->details()->delete();

            // 2. Hapus Header
            $nota->delete();

            DB::commit();

            return redirect()->route('gudang.draftNotaMasuk.index')
                ->with('success', 'Draft #' . $id . ' berhasil dihapus permanen.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus draft: ' . $e->getMessage()]);
        }
    }
}
