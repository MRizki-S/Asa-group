<?php
namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\StockBarang;
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
        $masterBarang = MasterBarang::select([
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
        ]);

        DB::transaction(function () use ($validated) {

            // INSERT MASTER BARANG
            $barang = MasterBarang::create([
                'kode_barang' => $validated['kode_barang'],
                'nama_barang' => $validated['nama_barang'],
                'satuan'      => $validated['satuan'],
                'created_by'  => Auth::id(),
            ]);

            // CEK & INSERT STOCK BARANG
            $stockExists = StockBarang::where('barang_id', $barang->id)->exists();

            if (! $stockExists) {
                StockBarang::create([
                    'barang_id'    => $barang->id,
                    'jumlah_stock' => 0, // awal selalu 0
                ]);
            }
        });

        return redirect()
            ->route('gudang.masterBarang.index')
            ->with('success', 'Master Barang & Stock berhasil ditambahkan.');
    }

    // view edit
    public function edit($kode_barang)
    {
        $barangEdit = MasterBarang::where('kode_barang', $kode_barang)->firstOrFail();
        // dd($barangEdit);
        return view('gudang.master-barang.edit', [
            'barangEdit'  => $barangEdit,
            'breadcrumbs' => [
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

    // Update
    public function update(Request $request, $kode_barang)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan'      => 'required|string|max:50',
        ]);

        $masterBarang = MasterBarang::where('kode_barang', $kode_barang)->firstOrFail();

        $masterBarang->update([
            'nama_barang' => $request->nama_barang,
            'satuan'      => $request->satuan,
        ]);

        return redirect()->route('gudang.masterBarang.index')
            ->with('success', 'Master Barang berhasil diperbarui.');
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
