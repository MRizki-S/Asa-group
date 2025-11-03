<?php
namespace App\Http\Controllers\Etalase;

use App\Http\Controllers\Controller;
use App\Models\Perumahaan;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $tipeUnits  = Type::with('perumahaan')->latest()->paginate(3);
    //     $perumahaan = Perumahaan::all();
    //     return view("Etalase.tipe-unit.index", compact('tipeUnits', 'perumahaan'));
    // }
    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    // public function index(Request $request)
    // {
    //     $query = Type::with('perumahaan');

    //     if ($request->has('search') && $request->search != '') {
    //         $query->where('nama_type', 'like', "%{$request->search}%")
    //             ->orWhereHas('perumahaan', function ($q) use ($request) {
    //                 $q->where('nama_perumahaan', 'like', "%{$request->search}%");
    //             });
    //     }

    //     $tipeUnits  = $query->latest()->paginate(5)->withQueryString();
    //     $perumahaan = Perumahaan::all();

    //     // kalau request ajax, render partial table saja
    //     if ($request->ajax()) {
    //         return view('Etalase.tipe-unit.partials.table', [
    //             'tipeUnits'   => $tipeUnits,
    //             'breadcrumbs' => [
    //                 ['label' => 'Tipe Unit', 'url' => route('tipe-unit.index')],
    //             ],
    //         ])->render();
    //     }

    //     return view("Etalase.tipe-unit.index",
    //         [
    //             'tipeUnits'   => $tipeUnits,
    //             'perumahaan'  => $perumahaan,
    //             'breadcrumbs' => [
    //                 ['label' => 'Tipe Unit', 'url' => route('tipe-unit.index')],
    //             ],
    //         ]);
    // }

    /**
     * Show the form for creating a new resource.
     */

    public function index(Request $request)
    {
        $perumahaanId = $this->currentPerumahaanId();

        $query = Type::with('perumahaan');

        // 🔹 Filter berdasarkan perumahaan_id (kecuali global tanpa session)
        if ($perumahaanId) {
            $query->where('perumahaan_id', $perumahaanId);
        }

        // 🔹 Pencarian nama tipe atau nama perumahaan
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nama_type', 'like', "%{$search}%")
                    ->orWhereHas('perumahaan', function ($p) use ($search) {
                        $p->where('nama_perumahaan', 'like', "%{$search}%");
                    });
            });
        }

        $tipeUnits  = $query->latest()->paginate(5)->withQueryString();
        $perumahaan = Perumahaan::all();

        // 🔹 Kalau request AJAX, kirim partial table aja
        if ($request->ajax()) {
            return view('Etalase.tipe-unit.partials.table', [
                'tipeUnits'   => $tipeUnits,
                'breadcrumbs' => [
                    ['label' => 'Tipe Unit', 'url' => route('tipe-unit.index')],
                ],
            ])->render();
        }

        // 🔹 Ambil nama perumahaan untuk breadcrumb
        $namaPerumahaan = null;
        if ($perumahaanId) {
            $namaPerumahaan = Perumahaan::where('id', $perumahaanId)->value('nama_perumahaan');
        }

        return view('Etalase.tipe-unit.index', [
            'tipeUnits'   => $tipeUnits,
            'perumahaan'  => $perumahaan,
            'breadcrumbs' => [
                [
                    'label' => 'Tipe Unit - ' . ($namaPerumahaan ?? '-'),
                    'url'   => route('tipe-unit.index'),
                ],
            ],
        ]);
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // Validasi input
        $validated = $request->validate([
            'perumahaan_id' => 'required|exists:perumahaan,id',
            'nama_type'     => 'required|string|max:255',
            'luas_bangunan' => 'required|numeric',
            'luas_tanah'    => 'required|numeric',
            'harga_dasar'   => 'required|numeric',
        ]);

        // Tambahkan field manual dari server
        $validated['status_pengajuan'] = 'acc';

        try {
            // Simpan ke database
            Type::create($validated);

            // Flash message sukses
            return redirect()
                ->back()
                ->with('success', 'Tipe Unit baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            // Jika terjadi error saat simpan
            return redirect()
                ->back()
                ->withInput() // agar input lama tetap muncul
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        // Ambil data, jika tidak ketemu otomatis 404
        $type = Type::findOrFail($id);
        dd($type);
        // Kirim response JSON agar bisa diisi ke input modal
        return response()->json([
            'success' => true,
            'data'    => $type,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'perumahaan_id' => 'required|exists:perumahaan,id',
            'nama_type'     => 'required|string|max:255',
            'luas_bangunan' => 'required|numeric',
            'luas_tanah'    => 'required|numeric',
        ]);

        $type = Type::findOrFail($id);
        $type->update($validated);
        return redirect()
            ->back()
            ->with('success', 'Tipe Unit berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($slug)
    {
        $type = Type::where('slug', $slug)->firstOrFail();

        try {
            $type->delete();

            return redirect()
                ->back()
                ->with('success', 'Tipe Unit berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }

    }
}
