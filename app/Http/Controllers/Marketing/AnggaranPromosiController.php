<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Perumahaan;
use App\Models\MarketingAnggaranPromosi;
use App\Http\Requests\Marketing\StoreAnggaranPromosiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnggaranPromosiController extends Controller
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
        $user = Auth::user();
        $perumahaanId = $this->currentPerumahaanId();
        $perumahaan = Perumahaan::find($perumahaanId);

        $anggarans = MarketingAnggaranPromosi::with(['updater'])
            ->where('perumahaan_id', $perumahaanId)
            ->orderBy('tahun', 'desc')
            ->orderBy('quarter', 'desc')
            ->get();

        return view('marketing.target-marketing.anggaran-promosi.index', [
            'perumahaan' => $perumahaan,
            'anggarans' => $anggarans,
            'breadcrumbs' => [
                [
                    'label' => 'Anggaran Promosi - ' . ($perumahaan->nama_perumahaan ?? '-'),
                    'url' => route('marketing.anggaran-promosi.index'),
                ],
            ],
        ]);
    }

    public function store(StoreAnggaranPromosiRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $quarterName = 'Q' . $validated['quarter'];

            $anggaran = MarketingAnggaranPromosi::updateOrCreate(
                [
                    'perumahaan_id' => $validated['perumahaan_id'],
                    'tahun' => $validated['tahun'],
                    'quarter' => $quarterName,
                ],
                [
                    'target_anggaran' => $validated['target_anggaran'],
                    'realisasi_anggaran' => $validated['realisasi_anggaran'],
                    'catatan' => $validated['catatan'],
                    'updated_by' => Auth::id(),
                ]
            );

            if ($anggaran->wasRecentlyCreated) {
                $anggaran->update(['created_by' => Auth::id()]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Anggaran promosi berhasil disimpan.',
                'data' => $anggaran->load(['updater'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan anggaran: ' . $e->getMessage()
            ], 500);
        }
    }
}
