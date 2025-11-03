<?php
namespace App\Http\Controllers\Etalase;

use App\Http\Controllers\Controller;
use App\Models\Blok;
use App\Models\Perumahaan;
use App\Models\Tahap;
use App\Models\TahapKualifikasi;
use App\Models\Type;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexGlobal()
    {
        $perumahaan = Perumahaan::all();
        return view('Etalase.unit.selectPerumahaan', [
            'perumahaan'  => $perumahaan,
            'breadcrumbs' => [
                ['label' => 'Pilih Perumahaan Terlebih dahulu!', 'url' => route('unit.indexGlobal')],
            ],

        ]);
    }

    // public function index($slug)
    // {
    //     // Ambil perumahaan (hanya untuk info nama, breadcrumb, dsb)
    //     $perumahaan = Perumahaan::where('slug', $slug)->firstOrFail();

    //     // Langsung ambil semua unit yang perumahaan_id-nya sesuai
    //     $units = Unit::with([
    //         'tahap:id,nama_tahap', // kalau mau tampilkan nama tahap
    //         'blok:id,nama_blok',   // kalau mau tampilkan nama blok
    //         'type:id,nama_type',   // kalau mau tampilkan tipe
    //     ])
    //         ->where('perumahaan_id', $perumahaan->id)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     // dd($units);

    //     return view('Etalase.unit.listUnit', [
    //         'perumahaan'  => $perumahaan,
    //         'units'       => $units,
    //         'breadcrumbs' => [
    //             ['label' => $perumahaan->nama_perumahaan, 'url' => route('unit.indexGlobal')],
    //             ['label' => 'List Unit', 'url' => route('unit.index', $perumahaan->slug)],
    //         ],
    //     ]);
    // }

    /**
     * Show the form for creating a new resource.
     */

    public function index($perumahaanSlug, Request $request)
    {
        $perumahaan = Perumahaan::where('slug', $perumahaanSlug)->firstOrFail();

        $query = Unit::with(['tahap:id,nama_tahap,slug', 'blok:id,nama_blok,slug', 'type:id,nama_type,harga_dasar'])
            ->where('perumahaan_id', $perumahaan->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('tahapFil')) {
            $slugTahap = $request->input('tahapFil');
            $query->whereHas('tahap', fn($q) => $q->where('slug', $slugTahap));
        }
        if ($request->filled('blokFil')) {
            $slugBlok = $request->input('blokFil');
            $query->whereHas('blok', fn($q) => $q->where('slug', $slugBlok));
        }
        if ($request->filled('typeFil')) {
            $query->where('type_id', $request->input('typeFil'));
        }

        $units = $query->get();

        $tahapAll = Tahap::where('perumahaan_id', $perumahaan->id)->get();
        $blokAll  = Blok::where('perumahaan_id', $perumahaan->id)->get();
        $typeAll  = Type::all();

        return view('Etalase.unit.listUnit', compact(
            'perumahaan', 'units', 'tahapAll', 'blokAll', 'typeAll'
        ))->with([
            'tahapSlug'   => $request->query('tahapFil'),
            'blokSlug'    => $request->query('blokFil'),
            'typeId'      => $request->query('typeFil'),
            'breadcrumbs' => [
                ['label' => $perumahaan->nama_perumahaan, 'url' => route('unit.indexGlobal')],
                ['label' => 'List Unit', 'url' => route('unit.index', $perumahaan->slug)],
            ],
        ]);

    }

    public function create($slug)
    {
        // Ambil perumahaan berdasar slug
        $perumahaan = Perumahaan::where('slug', $slug)->firstOrFail();

        // Tahap milik perumahaan ini saja
        $tahapPerumahaan = Tahap::with(['types', 'kualifikasiBlok' => function ($q) {
            $q->withPivot('id', 'nominal_tambahan');
        }])
            ->where('perumahaan_id', $perumahaan->id)
            ->get();
        // dd($tahapPerumahaan);

        // blok milik perumahaan ini saja
        $blokPerumahaan = Blok::where('perumahaan_id', $perumahaan->id)
            ->get();
        // dd($blokPerumahaan);

        return view('Etalase.unit.createUnit', [
            'perumahaan'      => $perumahaan,
            'tahapPerumahaan' => $tahapPerumahaan,
            'blokPerumahaan'  => $blokPerumahaan,
            'breadcrumbs'     => [
                ['label' => $perumahaan->nama_perumahaan, 'url' => route('unit.indexGlobal')],
                ['label' => 'List Unit', 'url' => route('unit.index', $perumahaan->slug)],
                ['label' => 'Tambah Unit', 'url' => '#'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $perumahaanSlug)
    {
        $request->validate([
            'perumahaan_id'        => 'required|exists:perumahaan,id',
            'tahap_id'             => 'required|exists:tahap,id',
            'blok_id'              => 'required|exists:blok,id',
            'type_id'              => 'required|exists:type,id',
            'nama_unit'            => 'required|string|max:255|unique:unit,nama_unit',
            'kualifikasi_dasar'    => 'required|in:standar,kelebihan_tanah',
            'tahap_kualifikasi_id' => 'required|exists:tahap_kualifikasi,id',
            'luas_kelebihan'       => 'required_if:kualifikasi_dasar,kelebihan_tanah|string|max:255',
            'nominal_kelebihan'    => 'required_if:kualifikasi_dasar,kelebihan_tanah|numeric|min:0',
        ]);

        // Ambil harga dasar dari type
        $type      = Type::findOrFail($request->type_id);
        $hargaType = $type->harga_dasar;

        // Ambil nominal tambahan dari tahap_kualifikasi
        $tahapKualifikasi    = TahapKualifikasi::findOrFail($request->tahap_kualifikasi_id);
        $nominalTambahanBlok = $tahapKualifikasi->nominal_tambahan;

        // Ambil SBUM dari .env (default ke 4.000.000 kalau belum diatur)
        $sbumPemerintah = (int) env('SBUM_PEMERINTAH', 4000000);

        // Hitung nominal dasar (kelebihan tanah kalau ada)
        $nominalKualifikasiDasar = $request->kualifikasi_dasar === 'kelebihan_tanah'
            ? $request->nominal_kelebihan
            : 0;

        // Hitung harga final
        $hargaFinal = $hargaType + $nominalKualifikasiDasar + $nominalTambahanBlok + $sbumPemerintah;

        // Simpan unit
        $unit = Unit::create([
            'perumahaan_id'        => $request->perumahaan_id,
            'tahap_id'             => $request->tahap_id,
            'blok_id'              => $request->blok_id,
            'type_id'              => $request->type_id,
            'nama_unit'            => $request->nama_unit,
            'kualifikasi_dasar'    => $request->kualifikasi_dasar,
            'luas_kelebihan'       => $request->kualifikasi_dasar === 'kelebihan_tanah' ? $request->luas_kelebihan : null,
            'nominal_kelebihan'    => $request->kualifikasi_dasar === 'kelebihan_tanah' ? $request->nominal_kelebihan : null,
            'tahap_kualifikasi_id' => $request->tahap_kualifikasi_id,
            'status_unit'          => 'available',
            'harga_final'          => $hargaFinal,
        ]);

        return redirect()
            ->route('unit.index', $perumahaanSlug)
            ->with('success', 'Unit berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show($perumahaanSlug, $unitId)
    {
        // Ambil perumahaan berdasarkan slug
        $perumahaan = Perumahaan::where('slug', $perumahaanSlug)->firstOrFail();

        // Ambil unit beserta relasi pentingnya
        $unit = Unit::with([
            'perumahaan',
            'tahap.kualifikasiBlok',
            'blok',
            'type',
            'tahapKualifikasi.kualifikasiBlok',
        ])->findOrFail($unitId);
// dd($unit);  
        return view('Etalase.unit.showUnit', [
            'perumahaan'  => $perumahaan,
            'unit'        => $unit,
            'breadcrumbs' => [
                ['label' => $perumahaan->nama_perumahaan, 'url' => route('unit.indexGlobal')],
                ['label' => 'List Unit', 'url' => route('unit.index', $perumahaan->slug)],
                ['label' => 'Detail Unit', 'url' => '#'],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($perumahaanSlug, $unitId)
    {
        // Ambil perumahaan berdasar slug
        $perumahaan = Perumahaan::where('slug', $perumahaanSlug)->firstOrFail();

        // Ambil unit beserta relasinya
        $unit = Unit::with([
            'perumahaan',
            'tahap.kualifikasiBlok',
            'blok',
            'type',
            'tahapKualifikasi',
        ])->findOrFail($unitId);
        // dd($unit);

        // Ambil semua tahap milik perumahaan unit ini
        $tahapPerumahaan = Tahap::with([
            'types',
            'kualifikasiBlok' => function ($q) {
                $q->withPivot('id', 'nominal_tambahan');
            },
        ])->where('perumahaan_id', $perumahaan->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil semua blok milik perumahaan
        $blokPerumahaan = Blok::where('perumahaan_id', $perumahaan->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Etalase.unit.editUnit', [
            'perumahaan'      => $perumahaan, // kirim perumahaan juga
            'unit'            => $unit,
            'tahapPerumahaan' => $tahapPerumahaan,
            'blokPerumahaan'  => $blokPerumahaan,
            'breadcrumbs'     => [
                ['label' => $perumahaan->nama_perumahaan, 'url' => route('unit.indexGlobal')],
                ['label' => 'List Unit', 'url' => route('unit.index', $perumahaan->slug)],
                ['label' => 'Edit Unit', 'url' => '#'],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $perumahaanSlug, $unitId)
    {
        $unit = Unit::findOrFail($unitId);

        $request->validate([
            'perumahaan_id'        => 'required|exists:perumahaan,id',
            'tahap_id'             => 'required|exists:tahap,id',
            'blok_id'              => 'required|exists:blok,id',
            'type_id'              => 'required|exists:type,id',
            'nama_unit'            => [
                'required',
                'string',
                'max:255',
                Rule::unique('unit', 'nama_unit')->ignore($unit->id),
            ],
            'kualifikasi_dasar'    => 'required|in:standar,kelebihan_tanah',
            'tahap_kualifikasi_id' => 'required|exists:tahap_kualifikasi,id',
            'luas_kelebihan'       => 'required_if:kualifikasi_dasar,kelebihan_tanah|string|max:255',
            'nominal_kelebihan'    => 'required_if:kualifikasi_dasar,kelebihan_tanah|numeric|min:0',
        ]);

        // Ambil harga dasar dari type
        $type      = Type::findOrFail($request->type_id);
        $hargaType = $type->harga_dasar;

        // Ambil nominal tambahan dari tahap_kualifikasi
        $tahapKualifikasi    = TahapKualifikasi::findOrFail($request->tahap_kualifikasi_id);
        $nominalTambahanBlok = $tahapKualifikasi->nominal_tambahan;

        // Ambil SBUM dari .env (default 4.000.000)
        $sbumPemerintah = (int) env('SBUM_PEMERINTAH', 4000000);

        // Hitung nominal kelebihan tanah (jika ada)
        $nominalKualifikasiDasar = $request->kualifikasi_dasar === 'kelebihan_tanah'
            ? $request->nominal_kelebihan
            : 0;

        // Hitung harga final
        $hargaFinal = $hargaType + $nominalKualifikasiDasar + $nominalTambahanBlok + $sbumPemerintah;

        // Update data unit
        $unit->update([
            'perumahaan_id'        => $request->perumahaan_id,
            'tahap_id'             => $request->tahap_id,
            'blok_id'              => $request->blok_id,
            'type_id'              => $request->type_id,
            'nama_unit'            => $request->nama_unit,
            'kualifikasi_dasar'    => $request->kualifikasi_dasar,
            'luas_kelebihan'       => $request->kualifikasi_dasar === 'kelebihan_tanah' ? $request->luas_kelebihan : null,
            'nominal_kelebihan'    => $request->kualifikasi_dasar === 'kelebihan_tanah' ? $request->nominal_kelebihan : null,
            'tahap_kualifikasi_id' => $request->tahap_kualifikasi_id,
            'harga_final'          => $hargaFinal,
        ]);

        return redirect()
            ->route('unit.edit', [$perumahaanSlug, $unit->id])
            ->with('success', 'Unit berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($perumahaanSlug, $unitId)
    {
        // Cari unit berdasarkan ID
        $unit = Unit::findOrFail($unitId);

        // Hapus unit
        $unit->delete();

        // Redirect kembali ke list unit perumahaan
        return redirect()
            ->route('unit.index', $perumahaanSlug)
            ->with('success', 'Unit berhasil dihapus!');
    }
}
