<?php
namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\NotaBarangMasuk;
use App\Models\NotaBarangMasukDetail;
use App\Models\StockBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotaBarangMasukController extends Controller
{
    // daftar nota barang masuk
    public function index(Request $request)
    {
        $query = NotaBarangMasuk::with('details.barang')
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
            'notas'       => $notas,
            'breadcrumbs' => [
                [
                    'label' => 'Daftar Nota Barang Masuk',
                    'url'   => route('gudang.notaBarangMasuk.index'),
                ],
            ],
        ]);
    }

    //show detail dari daftar nota barang mausk
    public function show($nomorNota)
    {
        $nota = NotaBarangMasuk::with('details.barang')
            ->where('nomor_nota', $nomorNota)
            ->firstOrFail();

        return view('gudang.daftar-nota-masuk.show', [
            'nota'        => $nota,
            'breadcrumbs' => [
                [
                    'label' => 'Daftar Nota Barang Masuk',
                    'url'   => route('gudang.notaBarangMasuk.index'),
                ],
                [
                    'label' => 'Detail Nota Barang Masuk - ' . $nota->nomor_nota,
                    'url'   => route('gudang.notaBarangMasuk.show', $nota->nomor_nota),
                ],
            ],
        ]);

    }

    // create barang masuk pada halaman nota barang masuk
    public function create()
    {
        // Ambil nota terakhir
        $lastNota = NotaBarangMasuk::latest('id')->first();

        // Generate kode baru
        if ($lastNota) {
            $lastId       = intval(str_replace('NOTA-', '', $lastNota->nomor_nota));
            $nowNomorNota = 'NOTA-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nowNomorNota = 'NOTA-0001';
        }
        // Ambil master barang
        $masterBarangs = MasterBarang::select('id', 'kode_barang', 'nama_barang')
            ->get();
        // dd($masterBarangs);
        return view('gudang.nota-barang-masuk.create', [
            'newNomorNota'  => $nowNomorNota,
            'masterBarangs' => $masterBarangs,
            'breadcrumbs'   => [
                [
                    'label' => 'Tambah Nota Barang Masuk',
                    'url'   => route('gudang.notaBarangMasuk.create'),
                ],
            ],
        ]);
    }

    // aksi store
    public function store(Request $request)
    {

        // dd($request->all());
        // validasi input
        $validated = $request->validate([
            'nomor_nota'           => 'required|string|unique:nota_barang_masuk,nomor_nota',
            'tanggal_nota'         => 'required|date',
            'nama_barang'          => 'required|string|max:255',
            'cara_bayar'           => 'required|string',
            'items'                => 'required|array|min:1',
            'items.*.barang_id'    => 'required|exists:master_barang,id',
            'items.*.merk'         => 'nullable|string|max:255',
            'items.*.jumlah_masuk' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.harga_total'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {

            //  INSERT HEADER (NOTA)
            $nota = NotaBarangMasuk::create([
                'nomor_nota'   => $validated['nomor_nota'],
                'tanggal_nota' => $validated['tanggal_nota'],
                'supplier'     => $validated['nama_barang'],
                'cara_bayar'   => $validated['cara_bayar'],
                'created_by'   => Auth::id(),
            ]);

            // INSERT DETAIL BARANG
            foreach ($validated['items'] as $item) {

                // 1Insert detail nota
                NotaBarangMasukDetail::create([
                    'nota_id'      => $nota->id,
                    'barang_id'    => $item['barang_id'],
                    'merk'         => $item['merk'] ?? null,
                    'jumlah_masuk' => $item['jumlah_masuk'],
                    'harga_satuan' => $item['harga_satuan'],
                    'harga_total'  => $item['harga_total'],
                    'jumlah_sisa'  => $item['jumlah_masuk'], // awal = masuk
                ]);

                //  Update stock barang
                $stock = StockBarang::where('barang_id', $item['barang_id'])
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->increment('jumlah_stock', $item['jumlah_masuk']);
                } else {
                    StockBarang::create([
                        'barang_id'    => $item['barang_id'],
                        'jumlah_stock' => $item['jumlah_masuk'],
                    ]);
                }

            }
        });

        return redirect()
            ->route('gudang.notaBarangMasuk.create')
            ->with('success', 'Nota barang masuk berhasil disimpan dan stock barang bertambah.');
    }

    // delete nota barang masuk
    public function destroy($nomorNota)
    {
        $nota = NotaBarangMasuk::with('details')
            ->where('nomor_nota', $nomorNota)
            ->firstOrFail();

        DB::transaction(function () use ($nota) {

            foreach ($nota->details as $detail) {

                // Kurangi stok berdasarkan SISA barang (bukan jumlah masuk)
                $stock = StockBarang::where('barang_id', $detail->barang_id)->lockForUpdate()->first();

                if ($stock) {
                    $stock->decrement('jumlah_stock', $detail->jumlah_sisa);
                }
            }

            // Hapus detail nota
            $nota->details()->delete();

            // Hapus header nota
            $nota->delete();
        });

        return redirect()
            ->route('gudang.notaBarangMasuk.index')
            ->with('success', 'Nota barang masuk berhasil dihapus dan stok diperbarui sesuai sisa barang.');
    }
}
