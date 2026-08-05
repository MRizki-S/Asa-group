<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\MasterSupplier;
use Illuminate\Http\Request;

class MasterSupplierController extends Controller
{
    // Tampilkan daftar supplier
    public function index()
    {
        $masterSupplier = MasterSupplier::all();
        
        return view('gudang.master-supplier.index', [
            'masterSupplier' => $masterSupplier,
            'breadcrumbs' => [
                [
                    'label' => 'Master Supplier',
                    'url' => route('gudang.masterSupplier.index'),
                ],
            ],
        ]);
    }

    // Tampilkan halaman tambah supplier
    public function create()
    {
        // Generate preview kode berikutnya
        $last = MasterSupplier::orderByDesc('id')->value('kode_supplier');
        $nextNum = 1;
        if ($last && preg_match('/SPL-(\d+)/', $last, $m)) {
            $nextNum = (int) $m[1] + 1;
        }
        $kodePreview = 'SPL-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        return view('gudang.master-supplier.create', [
            'kodePreview' => $kodePreview,
            'breadcrumbs' => [
                [
                    'label' => 'Master Supplier',
                    'url' => route('gudang.masterSupplier.index'),
                ],
                [
                    'label' => 'Tambah Supplier',
                    'url' => route('gudang.masterSupplier.create'),
                ],
            ],
        ]);
    }

    // Simpan supplier baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'kategori_supplier' => 'nullable|string|max:100',
            'status' => 'required|integer|in:0,1',
            'npwp' => 'nullable|string|max:50',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'rekening_bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:100',
        ]);

        // Generate kode supplier otomatis (SPL-0001, SPL-0002, ...)
        $last = MasterSupplier::orderByDesc('id')->lockForUpdate()->value('kode_supplier');
        $nextNum = 1;
        if ($last && preg_match('/SPL-(\d+)/', $last, $m)) {
            $nextNum = (int) $m[1] + 1;
        }
        $kodeSupplier = 'SPL-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        MasterSupplier::create(array_merge($request->except('kode_supplier'), [
            'kode_supplier' => $kodeSupplier,
        ]));

        return redirect()->route('gudang.masterSupplier.index')
            ->with('success', 'Supplier ' . $kodeSupplier . ' berhasil ditambahkan.');
    }

    // Tampilkan halaman edit supplier
    public function edit($id)
    {
        $supplier = MasterSupplier::findOrFail($id);

        return view('gudang.master-supplier.edit', [
            'supplier' => $supplier,
            'breadcrumbs' => [
                [
                    'label' => 'Master Supplier',
                    'url' => route('gudang.masterSupplier.index'),
                ],
                [
                    'label' => 'Edit Supplier',
                    'url' => route('gudang.masterSupplier.edit', $id),
                ],
            ],
        ]);
    }

    // Update supplier
    public function update(Request $request, $id)
    {
        $supplier = MasterSupplier::findOrFail($id);

        $request->validate([
            'kode_supplier' => 'required|string|max:50|unique:master_supplier,kode_supplier,' . $id,
            'nama_supplier' => 'required|string|max:255',
            'kategori_supplier' => 'nullable|string|max:100',
            'status' => 'required|integer|in:0,1',
            'npwp' => 'nullable|string|max:50',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'rekening_bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:100',
        ]);

        $supplier->update($request->all());

        return redirect()->route('gudang.masterSupplier.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    // Hapus supplier
    public function destroy($id)
    {
        $supplier = MasterSupplier::findOrFail($id);

        // Validasi apakah supplier sudah digunakan di nota barang masuk
        if ($supplier->notas()->exists()) {
            return redirect()->route('gudang.masterSupplier.index')
                ->withErrors(['error' => 'Supplier tidak dapat dihapus karena sudah memiliki transaksi nota barang masuk.']);
        }

        $supplier->delete();

        return redirect()->route('gudang.masterSupplier.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}
