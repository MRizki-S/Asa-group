<?php

namespace App\Http\Controllers\Gudang;

use App\Models\Ubs;
use App\Models\StockBarang;
use App\Models\MasterBarang;
use Illuminate\Http\Request;
use App\Models\StockGudangHub;
use App\Models\StockGudangUbs;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
            'stockHub:id,barang_id,minimal_stock',
            'stockUbs:id,barang_id,minimal_stock',
        ])->select([
            'id',
            'kode_barang',
            'nama_barang',
            'satuan',
            'created_by',
            'created_at',
        ])
            ->with('creator:id,username,nama_lengkap')
            ->orderByDesc('created_at')
            ->get();

        return view('gudang.master-barang.index', [
            'masterBarang' => $masterBarang,
            'breadcrumbs'  => [
                [
                    'label' => 'Master Barang',
                    'url'   => route('gudang.masterBarang.index'),
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
            $lastId  = intval(str_replace('BRG-', '', $lastBarang->kode_barang));
            $newKode = 'BRG-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newKode = 'BRG-0001';
        }

        return view('gudang.master-barang.create', [
            'newKodeBarang' => $newKode,
            'breadcrumbs'   => [
                [
                    'label' => 'Master Barang',
                    'url'   => route('gudang.masterBarang.index'),
                ],
                [
                    'label' => 'Tambah Master Barang',
                    'url'   => route('gudang.masterBarang.create'),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|unique:master_barang,kode_barang',
            'nama_barang' => 'required|string|max:255|unique:master_barang,nama_barang',
            'satuan'      => 'required|string|max:100',
            'minimal_stock_hub' => 'required|numeric',
            'minimal_stock_ubs' => 'required|numeric',
        ]);

        // dd($request->all());

        DB::transaction(function () use ($validated) {

            // INSERT MASTER BARANG
            $barang = MasterBarang::create([
                'kode_barang' => $validated['kode_barang'],
                'nama_barang' => $validated['nama_barang'],
                'satuan'      => $validated['satuan'],
                'created_by'  => Auth::id(),
            ]);

            // INSERT STOCK GUDANG HUB
            StockGudangHub::create([
                'barang_id'     => $barang->id,
                'jumlah_stock'  => 0,
                'minimal_stock' => $validated['minimal_stock_hub'],
            ]);

            // INSERT STOCK GUDANG UBS (FOREACH SEMUA UBS)
            $allUbs = Ubs::select('id')->get();

            $stockUbsData = [];

            foreach ($allUbs as $ubs) {
                $stockUbsData[] = [
                    'barang_id'     => $barang->id,
                    'ubs_id'        => $ubs->id,
                    'jumlah_stock'  => 0,
                    'minimal_stock' => $validated['minimal_stock_ubs'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            // Bulk insert ke stock_gudang_ubs
            if (!empty($stockUbsData)) {
                StockGudangUbs::insert($stockUbsData);
            }
        });

        return redirect()
            ->route('gudang.masterBarang.index')
            ->with('success', 'Master Barang & Stock berhasil ditambahkan.');
    }

    // view edit
    public function edit($kode_barang)
    {
        $barangEdit = MasterBarang::with([
            'stockHub:id,barang_id,minimal_stock',
            'stockUbs:id,barang_id,minimal_stock',
        ])
            ->where('kode_barang', $kode_barang)
            ->firstOrFail();
        // dd($barangEdit);
        return view('gudang.master-barang.edit', [
            'barangEdit'        => $barangEdit,
            'minimal_stock_hub' => optional($barangEdit->stockHub)->minimal_stock,
            'minimal_stock_ubs' => optional($barangEdit->stockUbs->first())->minimal_stock,
            'breadcrumbs'       => [
                [
                    'label' => 'Master Barang',
                    'url'   => route('gudang.masterBarang.index'),
                ],
                [
                    'label' => 'Edit Master Barang',
                    'url'   => route('gudang.masterBarang.edit', $kode_barang),
                ],
            ],
        ]);
    }


    public function update(Request $request, $kode_barang)
    {
        $validated = $request->validate([
            'nama_barang'        => 'required|string|max:255',
            'satuan'             => 'required|string|max:50',
            'minimal_stock_hub'  => 'required|numeric|min:0',
            'minimal_stock_ubs'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $kode_barang) {

            // Master Barang
            $masterBarang = MasterBarang::where('kode_barang', $kode_barang)->firstOrFail();

            $masterBarang->update([
                'nama_barang' => $validated['nama_barang'],
                'satuan'      => $validated['satuan'],
            ]);

            // Stock HUB
            StockGudangHub::where('barang_id', $masterBarang->id)
                ->update([
                    'minimal_stock' => $validated['minimal_stock_hub'],
                    'updated_at'    => now(),
                ]);

            // Stock UBS
            StockGudangUbs::where('barang_id', $masterBarang->id)
                ->update([
                    'minimal_stock' => $validated['minimal_stock_ubs'],
                    'updated_at'    => now(),
                ]);
        });

        return redirect()
            ->route('gudang.masterBarang.index')
            ->with('success', 'Master Barang & minimal stock berhasil diperbarui.');
    }

    // aksi delete
    public function destroy($kode_barang)
    {
        $masterBarang = MasterBarang::where('kode_barang', $kode_barang)->firstOrFail();
        $masterBarang->delete();

        return redirect()->route('gudang.masterBarang.index')
            ->with('success', 'Master Barang berhasil dihapus.');
    }
}
