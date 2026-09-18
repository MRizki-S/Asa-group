<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\NotaBarangMasuk;
use App\Models\NotaBarangMasukDetail;
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
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);
        $tanggal = $request->get('tanggal');

        $query = NotaBarangMasuk::with(['details.barang', 'details.satuan', 'supplier', 'ubs'])
            ->where('status', 'posted')
            ->orderBy('posted_at', 'desc');

        // Jika ada request tanggal → pakai filter hari (override bulan/tahun)
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_nota', $tanggal);
        }
        // Jika tidak ada request tanggal → filter by bulan & tahun
        else {
            $query->whereMonth('tanggal_nota', $bulan)
                  ->whereYear('tanggal_nota', $tahun);
        }

        $notas = $query->get();

        // Bagi data nota ke kategori supplier dan internal
        $notasSupplier = $notas->filter(fn($n) => $n->jenis_nota === 'supplier');
        $notasSupplierHutang = $notasSupplier->filter(fn($n) => $n->cara_bayar === 'hutang');
        $notasSupplierLunas = $notasSupplier->filter(fn($n) => $n->cara_bayar !== 'hutang'); // selain hutang dianggap lunas (cash)

        $notasInternal = $notas->filter(fn($n) => $n->jenis_nota !== 'supplier');

        // Hitung total hutang dan total cash untuk nota supplier yang sudah acc/posted
        $totalHutang = (float) $notasSupplierHutang->sum(fn($n) => (float) $n->details->sum('harga_total'));
        $totalCash   = (float) $notasSupplierLunas->sum(fn($n) => (float) $n->details->sum('harga_total'));
        $totalSemua  = $totalHutang + $totalCash;

        $periodeLabel = $request->filled('tanggal')
            ? 'Tanggal: ' . \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y')
            : 'Bulan: ' . \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') . ' ' . $tahun;

        return view('gudang.daftar-nota-masuk.index', [
            'notasSupplier' => $notasSupplier,
            'notasSupplierHutang' => $notasSupplierHutang,
            'notasSupplierLunas' => $notasSupplierLunas,
            'notasInternal' => $notasInternal,
            'totalHutang' => $totalHutang,
            'totalCash' => $totalCash,
            'totalSemua' => $totalSemua,
            'periodeLabel' => $periodeLabel,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal,
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
        $nota = NotaBarangMasuk::with(['details.barang', 'details.satuan', 'supplier', 'ubs'])
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
                if ($nota->status !== 'draft') {
                    foreach ($nota->details as $detail) {
                        if ($detail->barang && $detail->barang->is_stock) {

                            $stock = StockGudang::where('barang_id', $detail->barang_id)
                                ->where('stock_type', $nota->stock_type)
                                ->where('ubs_id', $nota->ubs_id)
                                ->lockForUpdate()
                                ->first();

                            if ($stock) {
                                $stock->decrement('jumlah_stock', $detail->jumlah_sisa);
                            }

                            // Catat di Ledger sebagai koreksi
                            StockLedger::create([
                                'tanggal' => now(),
                                'barang_id' => $detail->barang_id,
                                'stock_type' => $nota->stock_type,
                                'ubs_id' => $nota->ubs_id,
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

    // Update tanggal nota barang masuk (Khusus Superadmin)
    public function updateTanggal(Request $request, $nomorNota)
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole(['Superadmin', 'superadmin'])) {
            abort(403, 'Hanya Superadmin yang memiliki akses untuk mengubah tanggal nota.');
        }

        $request->validate([
            'tanggal_nota' => 'required|date',
        ]);

        $nota = NotaBarangMasuk::where('nomor_nota', $nomorNota)->firstOrFail();

        try {
            DB::transaction(function () use ($nota, $request) {
                $nota->update([
                    'tanggal_nota' => $request->tanggal_nota,
                ]);

                // Sinkronkan juga tanggal transaksi di StockLedger
                StockLedger::where('ref_type', 'NotaBarangMasuk')
                    ->where('ref_id', $nota->id)
                    ->update([
                        'tanggal' => $request->tanggal_nota,
                    ]);
            });

            return back()->with('success', 'Tanggal nota ' . $nota->nomor_nota . ' berhasil diubah menjadi ' . \Carbon\Carbon::parse($request->tanggal_nota)->format('d-m-Y') . '.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui tanggal nota: ' . $e->getMessage());
        }
    }
}
