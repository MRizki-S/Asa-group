<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\NotaBarangMasuk;
use App\Models\StockGudang;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DaftarNotaMasukController extends Controller
{
    // daftar nota barang masuk
    public function index(Request $request)
    {
        $query = NotaBarangMasuk::with('details.barang')
            ->where('status', 'posted')
            ->orderBy('created_at', 'desc');

        // Jika ada request tanggal → pakai filter
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_nota', $request->tanggal);
        }
        // Jika tidak ada request → default hari ini
        else {
            $query->whereDate('tanggal_nota', now()->toDateString());
        }

        $notas = $query->get();

        return view('gudang.daftar-nota-masuk.index', [
            'notas' => $notas,
            'breadcrumbs' => [
                [
                    'label' => 'Daftar Nota Barang Masuk',
                    'url' => route('gudang.daftarNotaMasuk.index'),
                ],
            ],
        ]);
    }

    //show detail dari daftar nota barang mausk
    public function show($nomorNota)
    {
        $nota = NotaBarangMasuk::with(['details.barang', 'details.satuan'])
            ->where('status', 'posted')
            ->where('nomor_nota', $nomorNota)
            ->firstOrFail();

        return view('gudang.daftar-nota-masuk.show', [
            'nota' => $nota,
            'breadcrumbs' => [
                [
                    'label' => 'Daftar Nota Barang Masuk',
                    'url' => route('gudang.daftarNotaMasuk.index'),
                ],
                [
                    'label' => 'Detail Nota Barang Masuk - ' . $nota->nomor_nota,
                    'url' => route('gudang.daftarNotaMasuk.show', $nota->nomor_nota),
                ],
            ],
        ]);
    }

    // delete nota barang masuk
    public function destroy($nomorNota)
    {
        $nota = NotaBarangMasuk::with('details.barang')
            ->where('nomor_nota', $nomorNota)
            ->firstOrFail();

        try {
            DB::transaction(function () use ($nota) {
                // Hanya kurangi stok jika statusnya BUKAN Draft 
                // (karena Draft belum masuk ke tabel stock_gudang)
                if ($nota->status !== 'Draft') {
                    foreach ($nota->details as $detail) {
                        if ($detail->barang && $detail->barang->is_stock) {

                            $stock = StockGudang::where('barang_id', $detail->barang_id)
                                ->where('stock_type', 'HUB')
                                ->lockForUpdate()
                                ->first();

                            if ($stock) {
                                $stock->decrement('jumlah_stock', $detail->jumlah_sisa);
                            }

                            // Catat di Ledger sebagai koreksi
                            StockLedger::create([
                                'tanggal' => now(),
                                'barang_id' => $detail->barang_id,
                                'stock_type' => 'HUB',
                                'ubs_id' => null,
                                'tipe' => 'koreksi',
                                'ref_type' => 'NotaBarangMasuk_Delete',
                                'ref_id' => $nota->id,
                                'qty_masuk' => 0,
                                'qty_keluar' => $detail->jumlah_sisa,
                                'harga_satuan' => $detail->harga_satuan,
                                'created_by' => Auth::id(),
                            ]);
                        }
                    }
                }

                // Hapus detail dan header
                $nota->details()->delete();
                $nota->delete();
            });

            return redirect()
                ->route('gudang.daftarNotaMasuk.index')
                ->with('success', 'Nota barang masuk berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus nota: ' . $e->getMessage());
        }
    }
}
