<?php
namespace App\Http\Controllers\Marketing;

use App\Models\MasterBank;
use Illuminate\Http\Request;
use App\Models\PemesananUnit;
use App\Models\MasterKprDokumen;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PemesananUnitKprDokumen;
use App\Services\NotificationPribadiService;

class KelengkapanBerkasKprController extends Controller
{

    protected NotificationPribadiService $notification;

    public function __construct(NotificationPribadiService $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Tampilkan halaman checklist kelengkapan berkas KPR.
     */
    public function editKpr($id)
    {
        // 🔹 Ambil data pemesanan + relasi
        $pemesanan = PemesananUnit::with([
            'kpr.bank',
            'dataDiri',
            'customer',
            'sales',
            'unit',
            'perumahaan',
        ])->findOrFail($id);

        $kpr = $pemesanan->kpr;

        // 🔸 Jika belum punya data KPR sama sekali
        if (! $kpr) {
            return back()->with('error', 'Data KPR belum dibuat untuk pemesanan ini.');
        }

        // Ambil semua daftar bank terbaru dulu (newest first)
        $bankList = MasterBank::orderBy('created_at', 'desc')->get();

        // 🔹 Cek apakah bank_id sudah ada
        if (is_null($kpr->bank_id)) {
            // Ambil semua daftar bank untuk dipilih
            $bankList = MasterBank::orderBy('nama_bank')->get();

            return view('marketing.manage-pemesanan.kelengkapan-berkas.kpr-pilih-bank', [
                'pemesanan'   => $pemesanan,
                'kpr'         => $kpr,
                'bankList'    => $bankList,
                'breadcrumbs' => [
                    ['label' => 'Manage Pemesanan', 'url' => route('marketing.managePemesanan.index')],
                    ['label' => 'Pilih Bank KPR', 'url' => ''],
                ],
            ]);
        }
        // dd('dia sudah punya bank');

        // 🔹 Jika sudah punya bank, ambil daftar dokumen
        $dokumenList = $kpr->dokumen()
            ->with(['masterDokumen:id,nama_dokumen,kategori', 'updatedBy:id,username'])
            ->get()
            ->groupBy(fn($item) => $item->masterDokumen->kategori ?? 'lainnya');
        // dd($dokumenList);
        // dd($pemesanan);

        // 🔹 Daftar status KPR
        $statusList = [
            'proses'    => 'Proses',
            'acc'       => 'Acc',
            'realisasi' => 'Realisasi',
        ];

        return view('marketing.manage-pemesanan.kelengkapan-berkas.berkas-kpr', [
            'pemesanan'   => $pemesanan,
            'dokumenList' => $dokumenList,
            'bankList'    => $bankList,
            'statusList'  => $statusList,
            'breadcrumbs' => [
                ['label' => 'Manage Pemesanan', 'url' => route('marketing.managePemesanan.index')],
                ['label' => 'Kelengkapan Berkas KPR', 'url' => ''],
            ],
        ]);
    }
    /**
     * Proses update status dokumen KPR.
     */
    public function updateKpr(Request $request, $id)
    {
        $request->validate([
            'bank_id'    => 'required|exists:master_bank,id',
            'status_kpr' => 'required|string',
            'dokumen'    => 'nullable|array',
        ]);

        DB::transaction(function () use ($request, $id) {
            $pemesanan = PemesananUnit::with('kpr')->findOrFail($id);
            $kpr       = $pemesanan->kpr;

            if (! $kpr) {
                throw new \Exception('Data KPR tidak ditemukan.');
            }

            // Simpan status lama untuk deteksi perubahan
            $oldStatus = $kpr->status_kpr;

            // 🧩 Cek apakah bank berubah
            $bankBerubah = $request->bank_id != $kpr->bank_id;

            if ($bankBerubah) {
                // 🚮 Hapus semua dokumen lama
                PemesananUnitKprDokumen::where('pemesanan_unit_kpr_id', $kpr->id)->delete();

                // 🔁 Reset status_kpr jadi "proses" dan update bank_id baru
                $kpr->update([
                    'bank_id'    => $request->bank_id,
                    'status_kpr' => 'proses',
                ]);

                // 🔍 Ambil master dokumen bank baru
                $masterDocs = MasterKprDokumen::where('bank_id', $request->bank_id)->get();

                $insertData = [];
                $now        = now();

                foreach ($masterDocs as $doc) {
                    $insertData[] = [
                        'pemesanan_unit_kpr_id' => $kpr->id,
                        'master_kpr_dokumen_id' => $doc->id,
                        'status'                => 0,
                        'created_at'            => $now,
                    ];
                }

                if (! empty($insertData)) {
                    PemesananUnitKprDokumen::insert($insertData);
                }
            } else {
                // 🔸 Kalau bank tidak berubah, update status_kpr dari form
                $kpr->update([
                    'status_kpr' => $request->status_kpr,
                ]);

                // 🔹 Update status dokumen
                $dokumenIds = array_keys($request->input('dokumen', []));
                $allDokumen = PemesananUnitKprDokumen::where('pemesanan_unit_kpr_id', $kpr->id)->get();

                foreach ($allDokumen as $dok) {
                    $isChecked = in_array($dok->id, $dokumenIds);
                    $newStatus = $isChecked ? 1 : 0;

                    if ($dok->status != $newStatus) {
                        $dok->update([
                            'status'         => $newStatus,
                            'tanggal_update' => now(),
                            'updated_by'     => auth()->id(),
                        ]);
                    }
                }

                // 🔔 Jika status berubah dari bukan "acc" menjadi "acc", kirim pesan ke WA customer
                if ($oldStatus !== 'acc' && $request->status_kpr === 'acc') {
                    $customer = $pemesanan->customer;
                    $bank     = $pemesanan->kpr->bank;

                    if ($customer && $customer->no_hp) {
                        $namaCustomer = $customer->nama_lengkap ?? 'Customer';
                        $namaBank     = $bank->nama_bank ?? '-';
                        $kodeBank     = $bank->kode_bank ?? '-';

                        // Pesan WhatsApp
                        $messageCustomer = "Bapak/Ibu {$namaCustomer},\n\n"
                            . "Selamat! Pengajuan KPR Anda pada bank *{$namaBank}* ({$kodeBank}) telah *di-ACC*.\n"
                            . "Terima kasih telah mempercayakan proses pembelian rumah Anda kepada kami. 😊";

                        // Kirim pesan
                        $this->notification->sendWhatsApp($customer->no_hp, $messageCustomer);
                    }
                }
            }
        });

        return redirect()
            ->route('marketing.kelengkapanBerkasKpr.editKpr', $id)
            ->with('success', 'Perubahan KPR berhasil disimpan.');
    }

    public function setBank(Request $request, $id)
    {
        $request->validate([
            'bank_id' => 'required|exists:master_bank,id',
        ]);
        // dd($request->all());

        $pemesanan = PemesananUnit::with('kpr')->findOrFail($id);
        $kpr       = $pemesanan->kpr;
        // dd($kpr);

        if (! $kpr) {
            return back()->with('error', 'Data KPR belum ditemukan.');
        }

        // ✅ Update bank_id
        $kpr->update(['bank_id' => $request->bank_id]);

        // 🔍 Ambil daftar dokumen master untuk bank ini
        $masterDokumen = MasterKprDokumen::where('bank_id', $request->bank_id)->get();

        // 🚫 Jika belum ada, beri peringatan
        if ($masterDokumen->isEmpty()) {
            return redirect()
                ->route('marketing.kelengkapanBerkasKpr.editKpr', $id)
                ->with('warning', 'Bank diset, namun belum ada Master Dokumen pada bank ini.');
        }

        // 🧩 Cek dokumen KPR yang sudah ada agar tidak dobel
        $existingDokumenIds = PemesananUnitKprDokumen::where('pemesanan_unit_kpr_id', $kpr->id)
            ->pluck('master_kpr_dokumen_id')
            ->toArray();

        // 🏗️ Insert dokumen baru berdasarkan master dokumen bank
        $insertData = [];
        $now        = now();
        foreach ($masterDokumen as $doc) {
            if (! in_array($doc->id, $existingDokumenIds)) {
                $insertData[] = [
                    'pemesanan_unit_kpr_id' => $kpr->id,
                    'master_kpr_dokumen_id' => $doc->id,
                    'status'                => 0,
                    'created_at'            => $now,
                ];
            }
        }

        if (! empty($insertData)) {
            PemesananUnitKprDokumen::insert($insertData);
        }

        return redirect()
            ->route('marketing.kelengkapanBerkasKpr.editKpr', $id)
            ->with('success', 'Bank berhasil diset dan dokumen KPR berhasil diinisialisasi.');
    }

}
