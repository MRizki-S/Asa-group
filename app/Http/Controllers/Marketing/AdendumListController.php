<?php

namespace App\Http\Controllers\Marketing;

use App\Models\Adendum;
use App\Models\Perumahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PemesananUnitCicilan;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationGroupService;

class AdendumListController extends Controller
{

    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    // list pengajuan adendum
    public function index()
    {
        $user = Auth::user();
        $perumahaanId = $this->currentPerumahaanId();

        $namaPerumahaan = 'Global';

        $query = Adendum::with([
            'pemesananUnit:id,unit_id,customer_id,sales_id,perumahaan_id',
            'pemesananUnit.customer:id,username,nama_lengkap',
            'pemesananUnit.sales:id,username,nama_lengkap',
            'pemesananUnit.unit:id,nama_unit',
        ])
            // ->where('status', 'pending')
            ->orderByDesc('created_at');

        // GLOBAL
        if ($user->is_global) {
            $listAdendum = $query->get()->groupBy(function ($item) {
                return $item->pemesananUnit->perumahaan_id;
            });
        } else {
            // NON-GLOBAL: filter perumahaan
            $listAdendum = $query
                ->whereHas('pemesananUnit', function ($q) use ($perumahaanId) {
                    $q->where('perumahaan_id', $perumahaanId);
                })
                ->get();

            // Tentukan nama perumahaan
            $perumahaan = Perumahaan::find($perumahaanId);
            if ($perumahaan) {
                $namaPerumahaan = $perumahaan->nama_perumahaan;
            }
        }

        // dd($listAdendum);
        return view('marketing.adendum.list-adendum.index', [
            'breadcrumbs' => [
                [
                    'label' => 'List Adendum ' .
                        ($user->is_global ? '(Global)' : ' - ' . $namaPerumahaan),
                    'url' => '',
                ],
            ],
            'isGlobal' => $user->is_global,
            'listAdendum' => $listAdendum,
            'namaPerumahaan' => $namaPerumahaan,
        ]);
    }

    // function untuk melihat dari adendum itu
    public function show($id)
    {
        // 🔹 Ambil adendum dulu (tanpa relasi)
        $adendum = Adendum::findOrFail($id);

        // 🔹 Query dasar
        $query = Adendum::with([
            'pemesananUnit',
            'pemesananUnit.customer:id,nama_lengkap',
            'pemesananUnit.unit',
            'pemesananUnit.unit.type:id,nama_type',
            'pemesananUnit.tahap:id,nama_tahap',
            'pemesananUnit.perumahaan:id,nama_perumahaan',
        ]);

        // 🔹 Jika jenis adalah cara_bayar → load sub adendum-nya
        if ($adendum->jenis === 'cara_bayar') {
            $query->with('subCaraBayar');
        }

        // 🔹 Ambil ulang adendum dgn relasi lengkap
        $dataAdendum = $query->findOrFail($id);

        $pemesanan = $dataAdendum->pemesananUnit;

        // 🔹 Siapkan detail sub adendum jika cara bayar
        $detailSubCaraBayar = null;

        if ($dataAdendum->jenis === 'cara_bayar' && $dataAdendum->subCaraBayar) {
            $detailSubCaraBayar = [
                'lama' => $dataAdendum->subCaraBayar->data_lama_json,
                'baru' => $dataAdendum->subCaraBayar->data_baru_json,
            ];
        }
        // dd($dataAdendum);
        // dd($detailSubCaraBayar);
        return view('marketing.adendum.list-adendum.show', [
            'dataAdendum' => $dataAdendum,
            'detailSubCaraBayar' => $detailSubCaraBayar,
            'breadcrumbs' => [
                [
                    'label' => 'Adendum',
                    'url' => route('marketing.adendum.index'),
                ],
                [
                    'label' => 'Adendum Unit - ' . ($pemesanan->unit->nama_unit ?? '-'),
                    'url' => '',
                ],
            ],
        ]);
    }

    // function untuk tolak adendum
    public function reject($id)
    {
        $adendum = Adendum::findOrFail($id);
        // dd($adendum);
        $adendum->status = 'tolak';
        $adendum->disetujui_oleh = Auth::user()->id;
        $adendum->save();

        return redirect()
            ->route('marketing.adendum.list')
            ->with('success', 'Pengajuan adendum ditolak, data dihapus, notifikasi WA dikirim.');
    }

    // approve pengajuan adendum 
    public function approve($id)
    {
        $adendum = Adendum::with(['pemesananUnit', 'pengaju', 'penyetuju', 'subCaraBayar'])
            ->findOrFail($id);

        DB::beginTransaction();
        try {

            switch ($adendum->jenis) {
                case 'cara_bayar':
                    $this->approveCaraBayar($adendum);
                    break;

                case 'ganti_unit':
                    throw new \Exception("Approve adendum ganti unit belum dibuat.");

                default:
                    throw new \Exception("Jenis adendum tidak dikenali.");
            }

            DB::commit();

            return redirect()
                ->route('marketing.adendum.detail', $adendum->id)
                ->with('success', 'Adendum berhasil disetujui.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }


    /**
     * APPROVE ADENDUM CARA BAYAR
     */
    private function approveCaraBayar(Adendum $adendum)
    {
        // Update status adendum
        $adendum->update([
            'status' => 'acc',
            'disetujui_oleh' => Auth::id(),
        ]);

        // Ambil sub adendum
        $sub = $adendum->subCaraBayar;
        if (!$sub) {
            throw new \Exception('Sub adendum cara bayar tidak ditemukan.');
        }

        // Ambil pemesanan unit
        $pemesanan = $adendum->pemesananUnit;

        $caraBayarLama = $sub->cara_bayar_lama;
        $caraBayarBaru = $sub->cara_bayar_baru;

        $dataBaru = $sub->data_baru_json;
        $cicilanBaru = $dataBaru['cicilan'] ?? [];

        // ============================
        // UPDATE / GANTI KPR / CASH
        // ============================
        if ($caraBayarLama === $caraBayarBaru) {

            if ($caraBayarBaru === 'kpr') {
                $pemesanan->kpr()->update([
                    'dp_rumah_induk' => $dataBaru['dp_rumah_induk'] ?? null,
                    'dp_dibayarkan_pembeli' => $dataBaru['dp_dibayarkan_pembeli'] ?? null,
                    'sbum_dari_pemerintah' => $dataBaru['sbum_dari_pemerintah'] ?? 0,
                    'luas_kelebihan' => $dataBaru['luas_kelebihan'] ?? null,
                    'nominal_kelebihan' => $dataBaru['nominal_kelebihan'] ?? null,
                    'total_dp' => $dataBaru['total_dp'] ?? null,
                    'harga_kpr' => $dataBaru['harga_kpr'] ?? null,
                    'harga_total' => $dataBaru['harga_total'] ?? null,
                ]);

            } else { // CASH
                $pemesanan->cash()->update([
                    'harga_rumah' => $dataBaru['harga_rumah'] ?? null,
                    'luas_kelebihan' => $dataBaru['luas_kelebihan'] ?? null,
                    'nominal_kelebihan' => $dataBaru['nominal_kelebihan'] ?? null,
                    'harga_jadi' => $dataBaru['harga_jadi'] ?? null,
                ]);
            }

        } else {
            // Cara bayar berubah → delete lama
            $caraBayarLama === 'kpr'
                ? $pemesanan->kpr()->delete()
                : $pemesanan->cash()->delete();

            // Insert baru
            $caraBayarBaru === 'kpr'
                ? $pemesanan->kpr()->create([
                    'dp_rumah_induk' => $dataBaru['dp_rumah_induk'] ?? null,
                    'dp_dibayarkan_pembeli' => $dataBaru['dp_dibayarkan_pembeli'] ?? null,
                    'sbum_dari_pemerintah' => $dataBaru['sbum_dari_pemerintah'] ?? 0,
                    'luas_kelebihan' => $dataBaru['luas_kelebihan'] ?? null,
                    'nominal_kelebihan' => $dataBaru['nominal_kelebihan'] ?? null,
                    'total_dp' => $dataBaru['total_dp'] ?? null,
                    'harga_kpr' => $dataBaru['harga_kpr'] ?? null,
                    'harga_total' => $dataBaru['harga_total'] ?? null,
                ])
                : $pemesanan->cash()->create([
                    'harga_rumah' => $dataBaru['harga_rumah'] ?? null,
                    'luas_kelebihan' => $dataBaru['luas_kelebihan'] ?? null,
                    'nominal_kelebihan' => $dataBaru['nominal_kelebihan'] ?? null,
                    'harga_jadi' => $dataBaru['harga_jadi'] ?? null,
                ]);
        }

        // Update cara bayar
        $pemesanan->update([
            'cara_bayar' => $caraBayarBaru,
            'total_tagihan' => $dataBaru['harga_total']
                ?? $dataBaru['harga_jadi']
                ?? $pemesanan->total_tagihan
        ]);

        // ============================
        // NONAKTIFKAN CICILAN LAMA
        // ============================
        PemesananUnitCicilan::where('pemesanan_unit_id', $pemesanan->id)
            ->where('status_bayar', '!=', 'lunas')
            ->update(['is_active' => 0]);

        // ============================
        // INPUT CICILAN BARU
        // ============================
        foreach ($cicilanBaru as $c) {
            if ($c['status_bayar'] !== 'lunas') {
                PemesananUnitCicilan::create([
                    'pemesanan_unit_id' => $pemesanan->id,
                    'adendum_id' => $adendum->id,
                    'pembayaran_ke' => $c['pembayaran_ke'],
                    'status_bayar' => $c['status_bayar'],
                    'tanggal_jatuh_tempo' => $c['tanggal_jatuh_tempo'],
                    'nominal' => $c['nominal'],
                    'is_active' => 1,
                ]);
            }
        }
    }





}
