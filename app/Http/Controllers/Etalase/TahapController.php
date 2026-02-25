<?php
namespace App\Http\Controllers\Etalase;

use App\Http\Controllers\Controller;
use App\Models\KualifikasiBlok;
use App\Models\Perumahaan;
use App\Models\Tahap;
use App\Models\Type;
use Illuminate\Http\Request;

class TahapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create(Perumahaan $perumahaan)
    {
        // Ambil hanya id dan nama_type
        $types                  = Type::select('id', 'nama_type')->get();
        $kualifikasiPosisiBloks = KualifikasiBlok::select('id', 'nama_kualifikasi_blok')->get();
        return view('Etalase.tahap.create', [
            'perumahaan'             => $perumahaan,
            'types'                  => $types,
            'kualifikasiPosisiBloks' => $kualifikasiPosisiBloks,
            'breadcrumbs'            => [
                ['label' => 'Perumahaan', 'url' => route('perumahaan.index')],
                ['label' => $perumahaan->nama_perumahaan, 'url' => route('perumahaan.show', $perumahaan->slug)],
                ['label' => 'Tambah Tahap', 'url' => ''],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Perumahaan $perumahaan)
    {
        $validated = $request->validate([
            'nama_tahap' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('tahap')->where(function ($query) use ($request) {
                    return $query->where('perumahaan_id', $request->perumahaan_id);
                }),
            ],
        ], [
            'nama_tahap.unique' => 'Nama tahap untuk perumahaan ini sudah digunakan.',
        ]);

        $tahap = Tahap::create([
            'perumahaan_id' => $perumahaan->id,
            'nama_tahap'    => $validated['nama_tahap'],
        ]);

        return redirect()
            ->route('tahap.edit', [$perumahaan->slug, $tahap->slug])
            ->with('success', 'Tahap berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Perumahaan $perumahaan, Tahap $tahap)
    {
        // Pastikan tahap memang milik perumahaan
        abort_unless($tahap->perumahaan_id === $perumahaan->id, 404);

        $availableTypes = Type::where('perumahaan_id', $perumahaan->id)
            ->whereDoesntHave('tahaps', function ($query) use ($tahap) {
                $query->where('tahap_id', $tahap->id);
            })
            ->get();
        // dd(vars: $availableTypes);

        // Ambil semua nama_type dari relasi pivot
        $tahapType = $tahap->types()->latest()->get();
        // dd(vars: $tahapType);

        $availableKualifikasiBlok = KualifikasiBlok::whereDoesntHave('tahaps', function ($query) use ($tahap) {
            $query->where('tahap_id', $tahap->id);
        })->get();
        // dd(vars: $availableKualifikasiBlok);

        $tahapKualifikasi = $tahap->kualifikasiBlok()->latest()->get();
        // dd(vars: $tahapKualifikasi);

        return view('Etalase.tahap.edit', [
            'perumahaan'               => $perumahaan,
            'tahap'                    => $tahap,
            'tahapType'                => $tahapType,
            'tahapKualifikasi'         => $tahapKualifikasi,
            'availableTypes'           => $availableTypes,
            'availableKualifikasiBlok' => $availableKualifikasiBlok,
            'breadcrumbs'              => [
                ['label' => 'Perumahaan', 'url' => route('perumahaan.index')],
                ['label' => $perumahaan->nama_perumahaan, 'url' => route('perumahaan.show', $perumahaan->slug)],
                ['label' => 'Edit Tahap: ' . $tahap->nama_tahap, 'url' => ''],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Perumahaan $perumahaan, Tahap $tahap)
    {
        // Pastikan tahap memang milik perumahaan
        abort_unless($tahap->perumahaan_id === $perumahaan->id, 404);

        // Validasi input
        $validated = $request->validate([
            'nama_tahap' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('tahap')->where(function ($query) use ($request, $perumahaan, $tahap) {
                    return $query->where('perumahaan_id', $perumahaan->id)
                        ->where('id', '<>', $tahap->id); // exclude current record
                }),
            ],
        ], [
            'nama_tahap.required' => 'Nama tahap wajib diisi.',
            'nama_tahap.unique'   => 'Nama tahap untuk perumahaan ini sudah digunakan.',
        ]);

        // Update data
        $tahap->update([
            'nama_tahap' => $validated['nama_tahap'],
        ]);

        // Redirect ke halaman edit dengan slug baru
        return redirect()->route('tahap.edit', [
            'perumahaan' => $perumahaan->slug,
            'tahap'      => $tahap->slug,
        ])->with('success', 'Tahap berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Perumahaan $perumahaan, Tahap $tahap)
    {
        // Pastikan tahap memang milik perumahaan
        abort_unless($tahap->perumahaan_id === $perumahaan->id, 404);

        // Hapus tahap (pivot table tahap_type dan unit akan otomatis dihapus jika onDelete cascade)
        $tahap->delete();

        return redirect()->route('perumahaan.show', $perumahaan->slug)
            ->with('success', "Tahap '{$tahap->nama_tahap}' berhasil dihapus.");
    }
}
