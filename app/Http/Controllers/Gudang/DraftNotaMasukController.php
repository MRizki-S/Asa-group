<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotaBarangMasuk;
use App\Models\MasterBarang;
use App\Models\StockGudang;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DraftNotaMasukController extends Controller
{
    // menampilkan list draft nota masuk (status = 'Draft')
    public function index()
    {
        $notas = NotaBarangMasuk::with('details.barang')
            ->where('status', 'Draft')
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

    // edit draft nota masuk 
    public function edit($nomorNota)
    {
        $nota = NotaBarangMasuk::with(['details.barang', 'details.satuan'])
            ->where('nomor_nota', $nomorNota)
            ->where('status', 'Draft')
            ->firstOrFail();

        $masterBarangs = MasterBarang::select('id', 'kode_barang', 'nama_barang')->get();

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
                    'label' => 'Edit Draft - ' . $nota->nomor_nota,
                    'url' => route('gudang.draftNotaMasuk.edit', $nota->nomor_nota),
                ],
            ],
        ]);
    }

    // update perubahaan pada draft nota masuk dengan status tetap draft 
    public function update(Request $request, $nomorNota)
    {
        $request->validate([
            'tanggal_nota' => 'required|date',
            'supplier' => 'required|string',
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

            $nota = NotaBarangMasuk::where('nomor_nota', $nomorNota)
                ->where('status', 'Draft')
                ->firstOrFail();

            // 1. Update Header Nota
            $nota->update([
                'tanggal_nota' => $request->tanggal_nota,
                'supplier' => $request->supplier,
                'cara_bayar' => $request->cara_bayar,
            ]);

            // 2. Hapus Detail Lama
            $nota->details()->delete();

            // 3. Masukkan Detail Baru (Logic sama dengan Store)
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
                    'merk' => $item['merk'],
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
                ->with('success', 'Draft nota ' . $nomorNota . ' berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui draft: ' . $e->getMessage()])->withInput();
        }
    }

    // Posting draft nota masuk ke stok gudang HUB
    public function post(Request $request, $nomorNota)
    {
        $request->validate([
            'tanggal_nota' => 'required|date',
            'supplier' => 'required|string',
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

            $nota = NotaBarangMasuk::where('nomor_nota', $nomorNota)
                ->where('status', 'Draft')
                ->lockForUpdate()
                ->firstOrFail();

            // 1. Update Header Nota & Status jadi Posted
            $nota->update([
                'tanggal_nota' => $request->tanggal_nota,
                'supplier' => $request->supplier,
                'cara_bayar' => $request->cara_bayar,
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            // 2. Hapus Detail Lama (sebagai antisipasi jika ada perubahan saat posting)
            $nota->details()->delete();

            // 3. Masukkan Detail Baru & Update Stok & Ledger
            foreach ($request->items as $item) {
                // Ambil konversi ke base unit
                $konversi = DB::table('barang_satuan_konversi')
                    ->where('barang_id', $item['barang_id'])
                    ->where('satuan_id', $item['satuan_id'])
                    ->value('konversi_ke_base') ?? 1;

                $jumlahBase = $item['jumlah_masuk'] * $konversi;
                $hargaSatuanBase = $item['harga_satuan'] / $konversi;

                // Simpan Detail
                $detail = $nota->details()->create([
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

                // 4. Update Stok Gudang (HUB)
                $stock = StockGudang::firstOrCreate(
                    [
                        'barang_id'  => $item['barang_id'],
                        'stock_type' => 'HUB',
                        'ubs_id'     => null
                    ],
                    ['jumlah_stock' => 0, 'minimal_stock' => 0]
                );
                
                $stock->increment('jumlah_stock', $jumlahBase);

                // 5. Catat ke Stock Ledger
                StockLedger::create([
                    'tanggal' => $request->tanggal_nota,
                    'barang_id' => $item['barang_id'],
                    'stock_type' => 'HUB',
                    'ubs_id' => null, // HUB tidak memiliki UBS
                    'tipe' => 'Masuk',
                    'ref_type' => 'NotaBarangMasuk',
                    'ref_id' => $nota->id,
                    'qty_masuk' => $jumlahBase,
                    'qty_keluar' => 0,
                    'harga_satuan' => $hargaSatuanBase,
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('gudang.daftarNotaMasuk.index')
                ->with('success', 'Nota ' . $nomorNota . ' berhasil diposting ke stok gudang HUB.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal posting nota: ' . $e->getMessage()])->withInput();
        }
    }
    // Menghapus draft nota secara permanen
    public function destroy($nomorNota)
    {
        try {
            DB::beginTransaction();

            $nota = NotaBarangMasuk::where('nomor_nota', $nomorNota)
                ->where('status', 'Draft')
                ->firstOrFail();

            // 1. Hapus Details
            $nota->details()->delete();

            // 2. Hapus Header
            $nota->delete();

            DB::commit();

            return redirect()->route('gudang.gudang.daftarNotaMasuk.destroy')
                ->with('success', 'Draft nota ' . $nomorNota . ' berhasil dihapus permanen.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus draft: ' . $e->getMessage()]);
        }
    }
}
