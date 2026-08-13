<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\BarangRakitan;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KomposisiRakitanController extends Controller
{
    public function index()
    {
        $barangRakitans = BarangRakitan::with([
            'barangHasil:id,kode_barang,nama_barang',
            'satuanHasil:id,nama',
            'creator:id,username',
            'details:id,barang_rakitan_id,barang_bahan_id,satuan_id,qty,qty_base',
            'details.barangBahan:id,kode_barang,nama_barang',
            'details.satuan:id,nama',
        ])
            ->select([
                'id',
                'barang_hasil_id',
                'satuan_hasil_id',
                'qty_hasil',
                'qty_hasil_base',
                'status',
                'keterangan',
                'created_by',
                'created_at',
            ])
            ->withCount('details')
            ->latest()
            ->get();

        return view('gudang.barang-rakitan.komposisi-rakitan.index', [
            'barangRakitans' => $barangRakitans,
            'breadcrumbs' => [
                [
                    'label' => 'Komposisi Rakitan',
                    'url' => route('gudang.komposisiRakitan.index'),
                ],
            ],
        ]);
    }

    // create komposisi view
    public function create()
    {
        return view('gudang.barang-rakitan.komposisi-rakitan.create', [
            'masterBarangs' => $this->getMasterBarangOptions(),
            'breadcrumbs' => [
                [
                    'label' => 'Komposisi Rakitan',
                    'url' => route('gudang.komposisiRakitan.index'),
                ],
                [
                    'label' => 'Tambah Komposisi Rakitan',
                    'url' => route('gudang.komposisiRakitan.create'),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateKomposisiRakitan($request);

        try {
            DB::transaction(function () use ($validated) {
                $qtyHasilBase = $validated['qty_hasil'] * $this->resolveKonversiKeBase(
                    (int) $validated['barang_hasil_id'],
                    (int) $validated['satuan_hasil_id']
                );

                $barangRakitan = BarangRakitan::create([
                    'barang_hasil_id' => $validated['barang_hasil_id'],
                    'satuan_hasil_id' => $validated['satuan_hasil_id'],
                    'qty_hasil' => $validated['qty_hasil'],
                    'qty_hasil_base' => $qtyHasilBase,
                    'status' => $validated['status'] ?? 'inactive',
                    'keterangan' => $validated['keterangan'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $qtyBase = $item['qty'] * $this->resolveKonversiKeBase(
                        (int) $item['barang_bahan_id'],
                        (int) $item['satuan_id']
                    );

                    $barangRakitan->details()->create([
                        'barang_bahan_id' => $item['barang_bahan_id'],
                        'satuan_id' => $item['satuan_id'],
                        'qty' => $item['qty'],
                        'qty_base' => $qtyBase,
                    ]);
                }
            });

            return redirect()
                ->route('gudang.komposisiRakitan.index')
                ->with('success', 'Komposisi rakitan berhasil disimpan.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan komposisi rakitan: ' . $e->getMessage());
        }
    }

    public function edit(BarangRakitan $barang_rakitan)
    {
        $barang_rakitan->load([
            'details:id,barang_rakitan_id,barang_bahan_id,satuan_id,qty,qty_base',
        ]);

        return view('gudang.barang-rakitan.komposisi-rakitan.edit', [
            'mode' => 'edit',
            'formAction' => route('gudang.komposisiRakitan.update', $barang_rakitan),
            'formMethod' => 'PUT',
            'submitLabel' => 'Update',
            'masterBarangs' => $this->getMasterBarangOptions(),
            'initialData' => $this->buildInitialData($barang_rakitan),
            'breadcrumbs' => [
                [
                    'label' => 'Komposisi Rakitan',
                    'url' => route('gudang.komposisiRakitan.index'),
                ],
                [
                    'label' => 'Edit Komposisi Rakitan',
                    'url' => route('gudang.komposisiRakitan.edit', $barang_rakitan),
                ],
            ],
        ]);
    }

    public function update(Request $request, BarangRakitan $barang_rakitan)
    {
        $validated = $this->validateKomposisiRakitan($request);

        try {
            DB::transaction(function () use ($validated, $barang_rakitan) {
                $qtyHasilBase = $validated['qty_hasil'] * $this->resolveKonversiKeBase(
                    (int) $validated['barang_hasil_id'],
                    (int) $validated['satuan_hasil_id']
                );

                $barang_rakitan->update([
                    'barang_hasil_id' => $validated['barang_hasil_id'],
                    'satuan_hasil_id' => $validated['satuan_hasil_id'],
                    'qty_hasil' => $validated['qty_hasil'],
                    'qty_hasil_base' => $qtyHasilBase,
                    'status' => $validated['status'] ?? 'inactive',
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);

                $barang_rakitan->details()->delete();

                foreach ($validated['items'] as $item) {
                    $qtyBase = $item['qty'] * $this->resolveKonversiKeBase(
                        (int) $item['barang_bahan_id'],
                        (int) $item['satuan_id']
                    );

                    $barang_rakitan->details()->create([
                        'barang_bahan_id' => $item['barang_bahan_id'],
                        'satuan_id' => $item['satuan_id'],
                        'qty' => $item['qty'],
                        'qty_base' => $qtyBase,
                    ]);
                }
            });

            return redirect()
                ->route('gudang.komposisiRakitan.index')
                ->with('success', 'Komposisi rakitan berhasil diperbarui.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui komposisi rakitan: ' . $e->getMessage());
        }
    }

    public function destroy(BarangRakitan $barang_rakitan)
    {
        try {
            $barang_rakitan->delete();

            return redirect()
                ->route('gudang.komposisiRakitan.index')
                ->with('success', 'Komposisi rakitan berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()
                ->with('error', 'Gagal menghapus komposisi rakitan: ' . $e->getMessage());
        }
    }

    // validasi request untuk store dan update komposisi rakitan
    private function validateKomposisiRakitan(Request $request): array
    {
        $validated = $request->validate([
            'barang_hasil_id' => 'required|exists:master_barang,id',
            'satuan_hasil_id' => 'required|exists:master_satuan,id',
            'qty_hasil' => 'required|numeric|min:0.0001',
            'status' => 'nullable|in:active',
            'keterangan' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.barang_bahan_id' => 'required|exists:master_barang,id',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.qty' => 'required|numeric|min:0.0001',
        ]);

        foreach ($validated['items'] as $index => $item) {
            if ((int) $item['barang_bahan_id'] === (int) $validated['barang_hasil_id']) {
                throw ValidationException::withMessages([
                    "items.{$index}.barang_bahan_id" => 'Barang bahan tidak boleh sama dengan barang hasil rakitan.',
                ]);
            }
        }

        return $validated;
    }

    // ambil data master barang beserta satuan konversinya untuk dropdown di form komposisi rakitan
    private function getMasterBarangOptions()
    {
        return MasterBarang::with([
            'baseUnit:id,nama',
            'satuanKonversi:id,barang_id,satuan_id,konversi_ke_base,is_default',
            'satuanKonversi.satuan:id,nama',
        ])
            ->select([
                'id',
                'kode_barang',
                'nama_barang',
                'base_unit_id',
                'is_stock',
            ])
            ->orderBy('kode_barang')
            ->get()
            ->map(function (MasterBarang $barang) {
                $satuans = $barang->satuanKonversi
                    ->sortByDesc('is_default')
                    ->map(function ($konversi) {
                        return [
                            'id' => $konversi->satuan_id,
                            'nama' => $konversi->satuan?->nama ?? '-',
                            'konversi_ke_base' => (float) $konversi->konversi_ke_base,
                            'is_default' => (bool) $konversi->is_default,
                        ];
                    })
                    ->values();

                if ($satuans->isEmpty() && $barang->baseUnit) {
                    $satuans->push([
                        'id' => $barang->baseUnit->id,
                        'nama' => $barang->baseUnit->nama,
                        'konversi_ke_base' => 1,
                        'is_default' => true,
                    ]);
                }

                return [
                    'id' => $barang->id,
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'base_unit_id' => $barang->base_unit_id,
                    'base_unit_name' => $barang->baseUnit?->nama ?? '-',
                    'satuans' => $satuans,
                ];
            });
    }

    // fungsi untuk membangun data awal form edit komposisi rakitan
    private function buildInitialData(BarangRakitan $barangRakitan): array
    {
        return [
            'barang_hasil_id' => $barangRakitan->barang_hasil_id,
            'satuan_hasil_id' => $barangRakitan->satuan_hasil_id,
            'qty_hasil' => (float) $barangRakitan->qty_hasil,
            'status' => $barangRakitan->status,
            'keterangan' => $barangRakitan->keterangan,
            'items' => $barangRakitan->details
                ->map(function ($detail) {
                    return [
                        'barang_bahan_id' => $detail->barang_bahan_id,
                        'satuan_id' => $detail->satuan_id,
                        'qty' => (float) $detail->qty,
                    ];
                })
                ->values(),
        ];
    }

    // fungsi untuk mencari konversi ke base unit, jika tidak ditemukan maka akan melempar error validasi
    private function resolveKonversiKeBase(int $barangId, int $satuanId): float
    {
        $konversi = BarangSatuanKonversi::where('barang_id', $barangId)
            ->where('satuan_id', $satuanId)
            ->value('konversi_ke_base');

        if ($konversi !== null) {
            return (float) $konversi;
        }

        $barang = MasterBarang::select(['id', 'nama_barang', 'base_unit_id'])->findOrFail($barangId);

        if ((int) $barang->base_unit_id === $satuanId) {
            return 1;
        }

        throw ValidationException::withMessages([
            'satuan_id' => "Konversi satuan untuk {$barang->nama_barang} tidak ditemukan.",
        ]);
    }
}
