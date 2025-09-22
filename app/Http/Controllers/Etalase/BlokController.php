<?php
namespace App\Http\Controllers\Etalase;

use App\Http\Controllers\Controller;
use App\Models\Blok;
use App\Models\Perumahaan;
use App\Models\Tahap;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlokController extends Controller
{

    public function index(Request $request)
    {
        // Mulai query dengan relasi perumahaan & tahap
        $query = Blok::with([
            'perumahaan:id,nama_perumahaan,slug',
            'tahap:id,perumahaan_id,nama_tahap,slug',
             'unit:id,blok_id,nama_unit'
        ])->latest('created_at');

        // ===== Filter Perumahaan (slug) =====
        if ($request->filled('perumahaanFil')) {
            $slugPerum = $request->input('perumahaanFil');
            $query->whereHas('perumahaan', function ($q) use ($slugPerum) {
                $q->where('slug', $slugPerum);
            });
        }

        // ===== Filter Tahap (slug) =====
        if ($request->filled('tahapFil')) {
            $slugTahap = $request->input('tahapFil');
            $query->whereHas('tahap', function ($q) use ($slugTahap) {
                $q->where('slug', $slugTahap);
            });
        }

        // Ambil data setelah filter (atau semua jika tanpa filter)
        $allBlok = $query->get();

        // Perumahaan untuk pilihan filter
        $allPerumahaan = Perumahaan::select('id', 'nama_perumahaan', 'slug')
            ->latest()->get();

        $perumahaanSlug = $request->query('perumahaanFil'); // nilai lama
        $tahapSlug      = $request->query('tahapFil');
        return view('Etalase.blok.index', [
            'allBlok'        => $allBlok,
            'allPerumahaan'  => $allPerumahaan,
            'perumahaanSlug' => $perumahaanSlug, // kirim ke Blade
            'tahapSlug'      => $tahapSlug,
            'breadcrumbs'    => [
                ['label' => 'Blok', 'url' => route('blok.index')],
            ],
        ]);
    }

    public function create()
    {
        $allPerumahaan = Perumahaan::select('id', 'nama_perumahaan', 'slug')->latest()->get();

        return view('Etalase.blok.create', [
            'allPerumahaan' => $allPerumahaan,
            'breadcrumbs'   => [
                ['label' => 'Blok', 'url' => route('blok.index')],
                ['label' => 'Tambah Blok', 'url' => route('blok.create')],
            ],
        ]);
    }

    // endpoint untuk ambil tahap sesuai perumahaan
    public function listByPerumahaan(Perumahaan $perumahaan)
    {
        // otomatis pakai slug karena getRouteKeyName di model Perumahaan
        return response()->json(
            $perumahaan->tahap()->select('id', 'nama_tahap', 'slug')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'perumahaan_id' => ['required', 'exists:perumahaan,id'],
            'tahap_id'      => ['required', 'exists:tahap,id'],
            'nama_blok'     => [
                'required', 'string', 'max:100',
                Rule::unique('blok', 'nama_blok')->where(function ($query) use ($request) {
                    return $query->where('perumahaan_id', $request->perumahaan_id)
                        ->where('tahap_id', $request->tahap_id);
                }),
            ],
        ], [
            'nama_blok.unique' => 'Nama Blok sudah ada pada perumahaan & tahap ini.',
        ]);

        // jika lolos validasi, simpan
        Blok::create($validated);

        return redirect()
            ->route('blok.index')
            ->with('success', 'Blok berhasil ditambahkan.');
    }

    public function edit(Blok $blok)
    {
        // dd($blok);
        $allPerumahaan = Perumahaan::select('id', 'nama_perumahaan', 'slug')->latest()->get();

        return view('Etalase.blok.edit', [
            'blok'          => $blok,
            'allPerumahaan' => $allPerumahaan,
            // 'tahapByPerumahaan'  => $tahapByPerumahaan,
            'breadcrumbs'   => [
                ['label' => 'Blok', 'url' => route(name: 'blok.index')],
                ['label' => 'Edit Blok', 'url' => route('blok.edit', $blok)],
            ],
        ]);
    }

    public function update(Request $request, Blok $blok)
    {
        $validated = $request->validate([
            'perumahaan_id' => ['required', 'exists:perumahaan,id'],
            'tahap_id'      => ['required', 'exists:tahap,id'],
            'nama_blok'     => [
                'required', 'string', 'max:100',
                Rule::unique('blok', 'nama_blok')->where(function ($query) use ($request) {
                    return $query->where('perumahaan_id', $request->perumahaan_id)
                        ->where('tahap_id', $request->tahap_id);
                })->ignore($blok->id),
            ],
        ], [
            'nama_blok.unique' => 'Nama Blok sudah ada pada perumahaan & tahap ini.',
        ]);

        // jika lolos validasi, update
        $blok->update($validated);

        return redirect()
            ->route('blok.index')
            ->with('success', 'Blok berhasil diupdate.');
    }

    public function destroy(Blok $blok)
    {
        $blok->delete();

        return redirect()
            ->route('blok.index')
            ->with('success', 'Blok berhasil dihapus.');
    }

}
