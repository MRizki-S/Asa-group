<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\NotaBarangMasuk;
use App\Models\NotaBarangMasukDetail;
use App\Models\StockGudang;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotaBarangMasukController extends Controller
{
    public function getSatuan($id)
    {
        $satuans = DB::table('barang_satuan_konversi as bsk')
            ->join('master_satuan as ms', 'ms.id', '=', 'bsk.satuan_id')
            ->where('bsk.barang_id', $id)
            ->select(
                'ms.id',
                'ms.nama',
                'bsk.is_default'
            )
            ->orderByDesc('bsk.is_default')
            ->get();

        return response()->json($satuans);
    }


    // create barang masuk pada halaman nota barang masuk
    public function create()
    {
        // Ambil nota terakhir
        $lastNota = NotaBarangMasuk::latest('id')->first();

        // Generate kode baru
        if ($lastNota) {
            $lastId = intval(str_replace('NOTA-', '', $lastNota->nomor_nota));
            $nowNomorNota = 'NOTA-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nowNomorNota = 'NOTA-0001';
        }
        // Ambil master barang
        $masterBarangs = MasterBarang::select('id', 'kode_barang', 'nama_barang')
            ->get();
        
        return view('gudang.nota-barang-masuk.create', [
            'newNomorNota' => $nowNomorNota,
            'masterBarangs' => $masterBarangs,
            'breadcrumbs' => [
                [
                    'label' => 'Tambah Nota Barang Masuk',
                    'url' => route('gudang.notaBarangMasuk.create'),
                ],
            ],
        ]);
    }

    // aksi store
    public function store(Request $request)
    {
        // validasi input
        $validated = $request->validate([
            'nomor_nota' => 'required|string|unique:nota_barang_masuk,nomor_nota',
            'tanggal_nota' => 'required|date',
            'supplier' => 'required|string|max:255',
            'cara_bayar' => 'required|in:cash,hutang',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.merk' => 'nullable|string|max:255',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.jumlah_masuk' => 'required|numeric|min:0.001',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.harga_total' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {

                // 1. INSERT HEADER (NOTA)
                $nota = NotaBarangMasuk::create([
                    'nomor_nota' => $validated['nomor_nota'],
                    'tanggal_nota' => $validated['tanggal_nota'],
                    'supplier' => $validated['supplier'],
                    'cara_bayar' => $validated['cara_bayar'],
                    'status' => 'Draft', // Default status Draft
                    'created_by' => Auth::id(),
                ]);

                // 2. LOOP ITEMS & INSERT DETAIL
                foreach ($validated['items'] as $item) {

                    // Ambil konversi satuan
                    $konversi = BarangSatuanKonversi::where('barang_id', $item['barang_id'])
                        ->where('satuan_id', $item['satuan_id'])
                        ->first();

                    if (!$konversi) {
                        throw new \Exception("Konversi satuan tidak ditemukan.");
                    }

                    // Hitung jumlah dalam base_unit (pcs, m, dll)
                    $jumlahBase = $item['jumlah_masuk'] * $konversi->konversi_ke_base;

                    // A. Insert ke detail nota
                    NotaBarangMasukDetail::create([
                        'nota_id' => $nota->id,
                        'barang_id' => $item['barang_id'],
                        'merk' => $item['merk'] ?? null,
                        'jumlah_input' => $item['jumlah_masuk'],
                        'satuan_id' => $item['satuan_id'],
                        'jumlah_base' => $jumlahBase,
                        'harga_satuan' => $item['harga_satuan'],
                        'harga_total' => $item['harga_total'],
                        'jumlah_sisa' => $jumlahBase, // Awalnya sisa = base
                    ]);
                }
            });

            return redirect()
                ->route('gudang.notaBarangMasuk.create')
                ->with('success', 'Nota barang masuk berhasil disimpan sebagai Draft.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    
}
