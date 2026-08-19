<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PemesananUnit;
use App\Models\Perumahaan;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GantiUnitController extends Controller
{
    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    public function index()
    {
        $perumahaanId = $this->currentPerumahaanId();

        // Ambil nama perumahaan aktif
        $namaPerumahaan = null;
        if ($perumahaanId) {
            $namaPerumahaan = Perumahaan::where('id', $perumahaanId)->value('nama_perumahaan');
        }

        // List Pemesanan Unit yang sudah di-ACC & belum dibatalkan
        $pemesananList = PemesananUnit::with([
            'unit.blok',
            'customer',
            'perumahaan',
            'tahap',
            'sales',
            'agent',
        ])
            ->where('status_pengajuan', 'acc')
            ->when($perumahaanId, function ($q) use ($perumahaanId) {
                $q->where('perumahaan_id', $perumahaanId);
            })
            ->whereDoesntHave('pengajuanPembatalan', function ($q) {
                $q->where('status_pengajuan', '!=', 'ditolak');
            })
            ->orderBy('tanggal_pemesanan', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // List Unit yang available
        $unitAvailable = Unit::with(['blok', 'tahap', 'type', 'perumahaan'])
            ->where('status_unit', 'available')
            ->when($perumahaanId, function ($q) use ($perumahaanId) {
                $q->where('perumahaan_id', $perumahaanId);
            })
            ->orderBy('nama_unit', 'asc')
            ->get();

        return view('marketing.ganti-unit.index', [
            'pemesananList'  => $pemesananList,
            'unitAvailable'  => $unitAvailable,
            'namaPerumahaan' => $namaPerumahaan,
            'breadcrumbs'    => [
                [
                    'label' => 'Ganti Unit' . ($namaPerumahaan ? ' - ' . $namaPerumahaan : ''),
                    'url'   => route('marketing.gantiUnit.index'),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pemesanan_unit_id' => 'required|exists:pemesanan_unit,id',
            'unit_baru_id'      => 'required|exists:unit,id',
        ], [
            'pemesanan_unit_id.required' => 'Pemesanan unit asal harus dipilih.',
            'pemesanan_unit_id.exists'   => 'Pemesanan unit tidak valid.',
            'unit_baru_id.required'      => 'Unit pengganti harus dipilih.',
            'unit_baru_id.exists'        => 'Unit pengganti tidak valid.',
        ]);

        DB::beginTransaction();
        try {
            $pemesanan = PemesananUnit::with(['unit', 'customer'])->findOrFail($request->pemesanan_unit_id);
            $unitLama = Unit::find($pemesanan->unit_id);
            $unitBaru = Unit::findOrFail($request->unit_baru_id);

            // Validasi apakah unit baru sama dengan unit lama
            if ($unitLama && $unitLama->id === $unitBaru->id) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['unit_baru_id' => 'Unit pengganti tidak boleh sama dengan unit awal yang sedang dipesan.']);
            }

            // Validasi ketersediaan unit baru
            if ($unitBaru->status_unit !== 'available') {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['unit_baru_id' => 'Unit ' . $unitBaru->nama_unit . ' sudah tidak tersedia (status: ' . $unitBaru->status_unit . ').']);
            }

            // 1. Ubah status unit lama menjadi available kembali
            if ($unitLama) {
                $unitLama->update([
                    'status_unit' => 'available',
                ]);
            }

            // 2. Ubah status unit pengganti menjadi sold
            $unitBaru->update([
                'status_unit' => 'sold',
            ]);

            // 3. Update data pemesanan unit
            $pemesanan->update([
                'unit_id'  => $unitBaru->id,
                'tahap_id' => $unitBaru->tahap_id ?? $pemesanan->tahap_id,
            ]);

            DB::commit();

            $namaUnitLama = $unitLama ? $unitLama->nama_unit : 'Unit Lama';
            $namaCustomer = $pemesanan->customer->nama_lengkap ?? $pemesanan->customer->username ?? 'Customer';

            return redirect()->route('marketing.gantiUnit.index')
                ->with('success', "Berhasil mengganti unit untuk {$namaCustomer} dari [{$namaUnitLama}] ke [{$unitBaru->nama_unit}]!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan saat mengganti unit: ' . $e->getMessage()]);
        }
    }
}
