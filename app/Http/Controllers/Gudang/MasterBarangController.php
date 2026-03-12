<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\StockGudang;
use App\Models\BarangSatuanKonversi;
use App\Models\Ubs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterBarangController extends Controller
{
    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $masterBarang = MasterBarang::with([
            'baseUnit:id,nama'
        ])
            ->select([
                'id',
                'kode_barang',
                'nama_barang',
                'base_unit_id',
                'is_stock'
            ])
            ->withCount('satuanKonversi')
            ->orderBy('kode_barang')
            ->get();

        return view('gudang.master-barang.index', [
            'masterBarang' => $masterBarang,
            'breadcrumbs' => [
                [
                    'label' => 'Master Barang',
                    'url' => route('gudang.masterBarang.index'),
                ],
            ],
        ]);
    }

    public function create()
    {
        // Ambil kode barang terakhir
        $lastBarang = MasterBarang::latest('id')->first();

        // Generate kode baru
        if ($lastBarang) {
            $lastId = intval(str_replace('BRG-', '', $lastBarang->kode_barang));
            $newKode = 'BRG-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newKode = 'BRG-0001';
        }

        $satuan = MasterSatuan::all();

        return view('gudang.master-barang.create', [
            'newKodeBarang' => $newKode,
            'satuan' => $satuan,
            'breadcrumbs' => [
                [
                    'label' => 'Master Barang',
                    'url' => route('gudang.masterBarang.index'),
                ],
                [
                    'label' => 'Tambah Master Barang',
                    'url' => route('gudang.masterBarang.create'),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|unique:master_barang,kode_barang',
            'nama_barang' => 'required|string|max:255|unique:master_barang,nama_barang',
            'satuan_id' => 'required|exists:master_satuan,id',
            'is_stock' => 'required|in:0,1',
            // validasi konversi dari array items
            'items' => 'required|array',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.konversi_ke_base' => 'required|numeric|min:0.01',
            'default_row' => 'required|numeric',
            // validasi stok
            'minimal_stock_hub' => 'nullable|numeric|min:0',
            'minimal_stock_ubs' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $request) {

            // INSERT MASTER BARANG
            $barang = MasterBarang::create([
                'kode_barang' => $validated['kode_barang'],
                'nama_barang' => $validated['nama_barang'],
                'base_unit_id' => $validated['satuan_id'],
                'is_stock' => $validated['is_stock'] == '1',
            ]);

            // INSERT SATUAN KONVERSI
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $index => $item) {
                    if (!empty($item['satuan_id']) && !empty($item['konversi_ke_base'])) {
                        // Jika default_row match dengan index saat ini, jadikan true
                        $is_default = ($request->default_row != null && $request->default_row == $index) ? true : false;

                        BarangSatuanKonversi::create([
                            'barang_id' => $barang->id,
                            'satuan_id' => $item['satuan_id'],
                            'konversi_ke_base' => $item['konversi_ke_base'],
                            'is_default' => $is_default,
                        ]);
                    }
                }
            }

            // INSERT STOCK GUDANG JIKA is_stock = 1 (Stock)
            if ($validated['is_stock'] == '1') {

                // INSERT STOCK GUDANG HUB
                StockGudang::create([
                    'barang_id' => $barang->id,
                    'stock_type' => 'HUB',
                    'ubs_id' => null,
                    'jumlah_stock' => 0,
                    'minimal_stock' => $validated['minimal_stock_hub'] ?? 0,
                ]);

                // INSERT STOCK GUDANG UBS
                $allUbs = Ubs::select('id')->get();
                $stockUbsData = [];

                foreach ($allUbs as $ubs) {
                    $stockUbsData[] = [
                        'barang_id' => $barang->id,
                        'stock_type' => 'UBS',
                        'ubs_id' => $ubs->id,
                        'jumlah_stock' => 0,
                        'minimal_stock' => $validated['minimal_stock_ubs'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($stockUbsData)) {
                    StockGudang::insert($stockUbsData);
                }
            }
        });

        return redirect()
            ->route('gudang.masterBarang.index')
            ->with('success', 'Master Barang dan satuan konversi berhasil ditambahkan.');
    }

    // view edit
    public function edit($kode_barang)
    {
        $barangEdit = MasterBarang::with([
            'stock:id,barang_id,minimal_stock,stock_type,ubs_id',
            'satuanKonversi'
        ])
            ->where('kode_barang', $kode_barang)
            ->firstOrFail();

        $stockHub = $barangEdit->stock->where('stock_type', 'HUB')->first();
        $stockUbs = $barangEdit->stock->where('stock_type', 'UBS')->first();

        $satuan = MasterSatuan::all();

        return view('gudang.master-barang.edit', [
            'barangEdit' => $barangEdit,
            'satuan' => $satuan,
            'minimal_stock_hub' => optional($stockHub)->minimal_stock,
            'minimal_stock_ubs' => optional($stockUbs)->minimal_stock,
            'breadcrumbs' => [
                [
                    'label' => 'Master Barang',
                    'url' => route('gudang.masterBarang.index'),
                ],
                [
                    'label' => 'Edit Master Barang',
                    'url' => route('gudang.masterBarang.edit', $kode_barang),
                ],
            ],
        ]);
    }


    public function update(Request $request, $kode_barang)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan_id' => 'required|exists:master_satuan,id',
            'is_stock' => 'required|in:0,1',
            // validasi konversi dari array items
            'items' => 'required|array',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.konversi_ke_base' => 'required|numeric|min:0.01',
            'default_row' => 'required|numeric',
            // validasi stok
            'minimal_stock_hub' => 'nullable|numeric|min:0',
            'minimal_stock_ubs' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $request, $kode_barang) {

            // Master Barang
            $masterBarang = MasterBarang::where('kode_barang', $kode_barang)->firstOrFail();

            $masterBarang->update([
                'nama_barang' => $validated['nama_barang'],
                'base_unit_id' => $validated['satuan_id'],
                'is_stock' => $validated['is_stock'] == '1',
            ]);

            // UPDATE SATUAN KONVERSI (Hapus semua yang lama terlebih dahulu lalu buat baru)
            $masterBarang->satuanKonversi()->delete();

            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $index => $item) {
                    if (!empty($item['satuan_id']) && !empty($item['konversi_ke_base'])) {
                        // Jika default_row match dengan index saat ini, jadikan true
                        $is_default = ($request->default_row != null && $request->default_row == $index) ? true : false;

                        BarangSatuanKonversi::create([
                            'barang_id' => $masterBarang->id,
                            'satuan_id' => $item['satuan_id'],
                            'konversi_ke_base' => $item['konversi_ke_base'],
                            'is_default' => $is_default,
                        ]);
                    }
                }
            }

            // Jika barang menggunakan stock
            if ($masterBarang->is_stock) {
                // Stock HUB
                $stockHub = StockGudang::where('barang_id', $masterBarang->id)
                    ->where('stock_type', 'HUB')
                    ->first();

                if ($stockHub) {
                    $stockHub->update([
                        'minimal_stock' => $validated['minimal_stock_hub'] ?? 0,
                    ]);
                } else {
                    StockGudang::create([
                        'barang_id' => $masterBarang->id,
                        'stock_type' => 'HUB',
                        'jumlah_stock' => 0,
                        'minimal_stock' => $validated['minimal_stock_hub'] ?? 0,
                    ]);
                }

                // Stock UBS
                $allUbs = Ubs::select('id')->get();
                foreach ($allUbs as $ubs) {
                    $stockUbs = StockGudang::where('barang_id', $masterBarang->id)
                        ->where('ubs_id', $ubs->id)
                        ->where('stock_type', 'UBS')
                        ->first();

                    if ($stockUbs) {
                        $stockUbs->update([
                            'minimal_stock' => $validated['minimal_stock_ubs'] ?? 0,
                        ]);
                    } else {
                        StockGudang::create([
                            'barang_id' => $masterBarang->id,
                            'ubs_id' => $ubs->id,
                            'stock_type' => 'UBS',
                            'jumlah_stock' => 0,
                            'minimal_stock' => $validated['minimal_stock_ubs'] ?? 0,
                        ]);
                    }
                }
            } else {
                // Jika stock dirubah menjadi direct, kita hapus jumlah minimal_stock nya saja menjadi 0
                // (tidak didelete seluruh barisnya untuk menjaga agar history 'jumlah_stock' sisa fisik tidak hilang jika ada, meskipun direct).
                $masterBarang->stock()->update([
                    'minimal_stock' => 0
                ]);
            }
        });

        return redirect()
            ->route('gudang.masterBarang.index')
            ->with('success', 'Master Barang beserta konfigurasi stock dan konversi berhasil diperbarui.');
    }

    public function destroy($kode_barang)
    {
        $masterBarang = MasterBarang::where('kode_barang', $kode_barang)->firstOrFail();

        // Hapus relasi terlebih dahulu jika onDelete('cascade') tidak dikonfigurasi di database
        $masterBarang->satuanKonversi()->delete();
        $masterBarang->stock()->delete();

        // Baru hapus master
        $masterBarang->delete();

        return redirect()->route('gudang.masterBarang.index')
            ->with('success', 'Master Barang beserta stok & konversi berhasil dihapus.');
    }
}
