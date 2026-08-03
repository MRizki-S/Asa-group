<?php

namespace App\Services;

use App\Models\MasterTukang;
use App\Models\PembangunanKawasan;
use App\Models\PembangunanProyek;
use App\Models\PembangunanUnit;
use App\Models\UpahHarianTukang;
use App\Models\UpahHarianTukangAlokasi;
use App\Models\UpahHarianTukangDetail;
use App\Models\UpahHarianTukangRekap;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengajuanUpahHarianTukangService
{
    /**
     * Mengambil daftar master tukang yang aktif berdasarkan jenis referensi.
     */
    public function masterTukangForJenis(string $jenisReferensi)
    {
        return MasterTukang::where('status', true)
            ->where('jenis_referensi', $jenisReferensi)
            ->orderBy('kode')
            ->get();
    }

    /**
     * Mengambil daftar referensi unit pembangunan beserta nama perumahannya.
     */
    public function pembangunanUnits()
    {
        return PembangunanUnit::with(['unit', 'perumahaan'])->get()->map(function ($pembangunanUnit) {
            $perumahanName = $pembangunanUnit->perumahaan->nama_perumahaan ?? '';
            $prefix = $this->perumahanPrefix($perumahanName);

            return [
                'id'    => $pembangunanUnit->id,
                'label' => $prefix . ($pembangunanUnit->unit->nama_unit ?? '-'),
            ];
        });
    }

    /**
     * Mengambil daftar referensi kawasan pembangunan beserta nama perumahannya.
     */
    public function pembangunanKawasans()
    {
        return PembangunanKawasan::with('perumahan')->get()->map(function ($pembangunanKawasan) {
            $perumahanName = $pembangunanKawasan->perumahan->nama_perumahaan ?? '';
            $prefix = $this->perumahanPrefix($perumahanName);

            return [
                'id'    => $pembangunanKawasan->id,
                'label' => $prefix . ($pembangunanKawasan->nama ?? 'Kawasan #' . $pembangunanKawasan->id),
            ];
        });
    }

    /**
     * Mengambil daftar referensi proyek pembangunan untuk Mangoon.
     */
    public function pembangunanProyeks()
    {
        return PembangunanProyek::orderBy('nama')->get()->map(function ($proyek) {
            return [
                'id'    => $proyek->id,
                'label' => $proyek->nama ?? 'Proyek #' . $proyek->id,
            ];
        });
    }

    /**
     * Menghasilkan singkatan/prefix nama perumahan untuk label referensi.
     */
    private function perumahanPrefix(?string $perumahanName): string
    {
        if (!$perumahanName) {
            return '';
        }

        $lowerName = strtolower($perumahanName);
        if ($lowerName === 'asa dreamland') {
            return 'ADL - ';
        }

        if ($lowerName === 'lembah hijau residence') {
            return 'LHR - ';
        }

        $abbr = '';
        foreach (explode(' ', $perumahanName) as $word) {
            $abbr .= strtoupper(substr($word, 0, 1));
        }

        return $abbr ? $abbr . ' - ' : '';
    }

    /**
     * Membuat nomor transaksi pengajuan upah harian tukang secara otomatis.
     */
    public function generateNomorUpah(): string
    {
        $today = now()->format('ymd');
        $prefix = 'UHT-' . $today . '-';
        $lastRecord = UpahHarianTukang::where('nomor_upah_harian', 'like', $prefix . '%')
            ->orderBy('nomor_upah_harian', 'desc')
            ->first();

        $nextSeq = '0001';
        if ($lastRecord) {
            $lastSeq = (int) substr($lastRecord->nomor_upah_harian, -4);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . $nextSeq;
    }

    /**
     * Memproses validasi payload JSON request dari form pengajuan upah.
     */
    public function validateAndDecodePayload(string $payloadJson, string $jenisReferensi): array
    {
        $data = json_decode($payloadJson, true);

        if (!is_array($data)) {
            throw ValidationException::withMessages([
                'payload' => 'Payload data tidak valid.',
            ]);
        }

        $this->ensurePayloadMatchesJenis($data, $jenisReferensi);

        return $data;
    }

    /**
     * Memastikan jenis referensi tukang pada payload cocok dengan jenis pengajuan.
     */
    private function ensurePayloadMatchesJenis(array $data, string $jenisReferensi): void
    {
        if (empty($data['tukang_details']) || !is_array($data['tukang_details'])) {
            throw ValidationException::withMessages([
                'payload' => 'Pilih minimal satu tukang.',
            ]);
        }

        $tukangIds = collect($data['tukang_details'])
            ->pluck('tukang_id')
            ->filter()
            ->unique()
            ->values();

        if ($tukangIds->isEmpty()) {
            throw ValidationException::withMessages([
                'payload' => 'Payload tukang tidak valid.',
            ]);
        }

        $tukangs = MasterTukang::whereIn('id', $tukangIds)->pluck('jenis_referensi', 'id');

        if ($tukangs->count() !== $tukangIds->count()) {
            throw ValidationException::withMessages([
                'payload' => 'Ada data tukang yang tidak ditemukan.',
            ]);
        }

        $invalid = $tukangs->filter(fn ($jenis) => $jenis !== $jenisReferensi);

        if ($invalid->isNotEmpty()) {
            $label = $jenisReferensi === 'mangoon' ? 'Mangoon' : 'ABM';

            throw ValidationException::withMessages([
                'payload' => "Ada tukang yang tidak sesuai dengan pengajuan {$label}.",
            ]);
        }
    }

    /**
     * Memproses validasi dan penyimpanan transaksi pengajuan upah baru ke database.
     */
    public function storeNewPengajuan(array $data, string $jenisReferensi): void
    {
        DB::transaction(function () use ($data, $jenisReferensi) {
            $pengajuan = $this->saveHeader($data, $jenisReferensi, 'draft');
            $this->saveDetails($pengajuan, $data);
        });
    }

    /**
     * Memproses pembaruan data draft pengajuan upah yang sudah ada di database.
     */
    public function updateExistingDraft(UpahHarianTukang $pengajuan, array $data, string $jenisReferensi): void
    {
        abort_if($pengajuan->jenis_referensi !== $jenisReferensi, 404);
        abort_if($pengajuan->status !== 'draft', 403, 'Hanya pengajuan berstatus draft yang dapat diubah.');

        DB::transaction(function () use ($data, $pengajuan) {
            $this->updateHeader($pengajuan, $data, 'draft');
            $this->deleteOldDetail($pengajuan);
            $this->saveDetails($pengajuan, $data);
        });
    }

    /**
     * Memproses pembaruan data draft dan langsung mengajukannya (status 'diajukan' dan generate rekap).
     */
    public function submitPengajuan(UpahHarianTukang $pengajuan, array $data, string $jenisReferensi): void
    {
        abort_if($pengajuan->jenis_referensi !== $jenisReferensi, 404);
        abort_if($pengajuan->status !== 'draft', 403, 'Hanya pengajuan berstatus draft yang dapat diajukan.');

        DB::transaction(function () use ($data, $pengajuan) {
            $this->updateHeader($pengajuan, $data, 'diajukan');
            $this->deleteOldDetail($pengajuan);
            $this->saveDetails($pengajuan, $data);
            $this->generateRekap($pengajuan);
        });
    }

    /**
     * Menyimpan data header (informasi utama) pengajuan upah harian tukang.
     */
    private function saveHeader(array $data, string $jenisReferensi, string $status): UpahHarianTukang
    {
        return UpahHarianTukang::create([
            'nomor_upah_harian' => $data['nomor_upah_harian'],
            'jenis_referensi'   => $jenisReferensi,
            'tanggal_mulai'     => $data['tanggal_mulai'],
            'tanggal_selesai'   => $data['tanggal_selesai'],
            'status'            => $status,
            'created_by'        => Auth::id(),
        ]);
    }

    /**
     * Memperbarui periode tanggal dan status pada header pengajuan upah harian tukang.
     */
    private function updateHeader(UpahHarianTukang $pengajuan, array $data, string $status): void
    {
        $pengajuan->update([
            'tanggal_mulai'   => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'status'          => $status,
        ]);
    }

    /**
     * Menghapus detail, alokasi, dan rekap lama saat melakukan update draft.
     */
    private function deleteOldDetail(UpahHarianTukang $pengajuan): void
    {
        $pengajuan->details()->delete();
        UpahHarianTukangRekap::where('upah_harian_tukang_id', $pengajuan->id)->delete();
    }

    /**
     * Menyimpan rincian kehadiran dan nominal harian tukang dari payload.
     */
    private function saveDetails(UpahHarianTukang $pengajuan, array $data): void
    {
        foreach ($data['tukang_details'] as $tukangPayload) {
            $tukangId = $tukangPayload['tukang_id'];
            $gajiSnap = $tukangPayload['gaji_harian_default_snapshot'] ?? 0;
            $jamSnap  = $tukangPayload['jam_default_snapshot'] ?? 0;

            foreach ($tukangPayload['details'] as $dayPayload) {
                $detail = UpahHarianTukangDetail::create([
                    'upah_harian_tukang_id'        => $pengajuan->id,
                    'tukang_id'                    => $tukangId,
                    'tanggal'                      => $dayPayload['tanggal'],
                    'status_kehadiran'             => (bool) $dayPayload['status_kehadiran'],
                    'gaji_harian_default_snapshot' => $gajiSnap,
                    'jam_default_snapshot'         => $jamSnap,
                    'nominal_harian_final'         => $dayPayload['nominal_harian_final'] ?? 0,
                    'jam_kerja'                    => $dayPayload['jam_kerja'] ?? 0,
                    'keterangan'                   => $dayPayload['keterangan'] ?? null,
                ]);

                $this->saveAllocation($detail, $dayPayload);
            }
        }
    }

    /**
     * Menyimpan alokasi kerja (normal dan lembur) untuk detail kehadiran tukang.
     */
    private function saveAllocation(UpahHarianTukangDetail $detail, array $dayPayload): void
    {
        if (!$dayPayload['status_kehadiran']) {
            return;
        }

        foreach ($dayPayload['alokasi_normal'] ?? [] as $allocation) {
            UpahHarianTukangAlokasi::create([
                'upah_harian_tukang_detail_id' => $detail->id,
                'referensi_jenis'              => $allocation['referensi_jenis'],
                'referensi_id'                 => $allocation['referensi_id'],
                'jenis'                        => 'normal',
                'jam_kerja'                    => $allocation['jam'],
                'tarif_per_jam'                => 0,
                'subtotal'                     => 0,
                'keterangan'                   => null,
            ]);
        }

        foreach ($dayPayload['alokasi_lembur'] ?? [] as $allocation) {
            $tarif = $allocation['tarif'] ?? 0;
            $jam   = $allocation['jam'] ?? 0;

            UpahHarianTukangAlokasi::create([
                'upah_harian_tukang_detail_id' => $detail->id,
                'referensi_jenis'              => $allocation['referensi_jenis'],
                'referensi_id'                 => $allocation['referensi_id'],
                'jenis'                        => 'lembur',
                'jam_kerja'                    => $jam,
                'tarif_per_jam'                => $tarif,
                'subtotal'                     => $jam * $tarif,
                'keterangan'                   => null,
            ]);
        }
    }

    /**
     * Menghitung total upah normal, lembur, dan membuat rekapitulasi upah per tukang.
     */
    private function generateRekap(UpahHarianTukang $pengajuan): void
    {
        $detailsByTukang = UpahHarianTukangDetail::where('upah_harian_tukang_id', $pengajuan->id)
            ->with('alokasi')
            ->get()
            ->groupBy('tukang_id');

        foreach ($detailsByTukang as $tukangId => $details) {
            $totalNormal = $details->where('status_kehadiran', true)->sum('nominal_harian_final');
            $totalLembur = $details->sum(fn ($detail) => $detail->alokasi->where('jenis', 'lembur')->sum('subtotal'));
            $totalUpah   = $totalNormal + $totalLembur;
            $bon         = 0;

            UpahHarianTukangRekap::create([
                'upah_harian_tukang_id' => $pengajuan->id,
                'tukang_id'             => $tukangId,
                'total_upah_normal'     => $totalNormal,
                'total_upah_lembur'     => $totalLembur,
                'total_upah'            => $totalUpah,
                'bon'                   => $bon,
                'total_diterima'        => $totalUpah - $bon,
            ]);
        }
    }

    /**
     * Menyusun data pengajuan yang ada ke dalam format array untuk Alpine.js.
     */
    public function buildExistingData(UpahHarianTukang $pengajuan): array
    {
        $result = [];

        foreach ($pengajuan->details->groupBy('tukang_id') as $details) {
            $first  = $details->first();
            $tukang = $first->tukang;
            if (!$tukang) {
                continue;
            }

            $result[] = [
                'id'                  => $tukang->id,
                'kode'                => $tukang->kode,
                'nama_tukang'         => $tukang->nama_tukang,
                'gaji_harian_default' => (float) $first->gaji_harian_default_snapshot,
                'jam_kerja_default'   => (int) $first->jam_default_snapshot,
                'details'             => $details->sortBy('tanggal')->values()->map(function ($detail) {
                    $normal = $detail->alokasi->where('jenis', 'normal');
                    $lembur = $detail->alokasi->where('jenis', 'lembur');

                    return [
                        'tanggal'        => $detail->tanggal->format('Y-m-d'),
                        'hadir'          => (bool) $detail->status_kehadiran,
                        'nominal'        => (float) $detail->nominal_harian_final,
                        'jam_normal'     => (int) $detail->jam_kerja,
                        'alokasi_normal' => $normal->values()->map(fn ($allocation) => [
                            'referensi_jenis' => $allocation->referensi_jenis,
                            'referensi_id'    => (string) $allocation->referensi_id,
                            'jam'             => (int) $allocation->jam_kerja,
                        ])->toArray(),
                        'alokasi_lembur' => $lembur->values()->map(fn ($allocation) => [
                            'referensi_jenis' => $allocation->referensi_jenis,
                            'referensi_id'    => (string) $allocation->referensi_id,
                            'jam'             => (int) $allocation->jam_kerja,
                            'tarif'           => (float) $allocation->tarif_per_jam,
                        ])->toArray(),
                    ];
                })->toArray(),
            ];
        }

        return $result;
    }
}
