<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PemesananUnit;
use App\Models\Perumahaan;
use App\Services\NotificationPribadiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengajuanPemesananController extends Controller
{

    protected NotificationPribadiService $notification;

    public function __construct(NotificationPribadiService $notification)
    {
        $this->notification = $notification;
    }

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
        $user         = Auth::user();

        // Default nilai awal
        $namaPerumahaan     = 'Global';
        $pengajuanPemesanan = collect();

        if ($user->is_global) {
            // 🔹 Jika user global → ambil semua pending & group by perumahaan
            $pengajuanPemesanan = PemesananUnit::with(['perumahaan', 'unit', 'customer', 'sales'])
                ->where('status_pengajuan', 'pending')
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('perumahaan_id'); // kelompokkan per perumahaan

        } else {
            // 🔹 Jika bukan global → ambil hanya perumahaan user
            $pengajuanPemesanan = PemesananUnit::with(['perumahaan', 'unit', 'customer', 'sales'])
                ->where('status_pengajuan', 'pending')
                ->where('perumahaan_id', $perumahaanId)
                ->orderByDesc('created_at')
                ->get();

            // Ambil nama perumahaan
            $perumahaan = Perumahaan::find($perumahaanId);
            if ($perumahaan) {
                $namaPerumahaan = $perumahaan->nama_perumahaan;
            }
        }

        return view('marketing.pengajuan-pemesanan.index', [
            'breadcrumbs'        => [
                [
                    'label' => 'Pengajuan Pemesanan Unit' .
                    ($user->is_global ? ' (Global)' : ' - ' . $namaPerumahaan),
                    'url'   => route('marketing.pengajuanPemesanan.index'),
                ],
            ],
            'isGlobal'           => $user->is_global,
            'pengajuanPemesanan' => $pengajuanPemesanan,
            'namaPerumahaan'     => $namaPerumahaan,
        ]);
    }

    // show detail pemesanan unit yang diajukan
    public function show($id)
    {
        $pengajuan = PemesananUnit::with([
            'customer',
            'sales',
            'perumahaan',
            'tahap',
            'unit',
            'dataDiri',
            'cash',
            'kpr',
            'caraBayar',
            'cicilan',
            'promo',         // ambil semua promo
            'keterlambatan', // snapshot keterlambatan
            'pembatalan',    // snapshot pembatalan
        ])->findOrFail($id);

        return view('marketing.pengajuan-pemesanan.show', [
            'pengajuan'     => $pengajuan,
            'breadcrumbs'   => [
                ['label' => 'Pengajuan Pemesanan Unit', 'url' => route('marketing.pengajuanPemesanan.index')],
                ['label' => 'Unit ' . ($pengajuan->unit->nama_unit ?? '-'), 'url' => ''],
            ],
            'keterlambatan' => $pengajuan->keterlambatan,
            'pembatalan'    => $pengajuan->pembatalan,
            'promo'         => $pengajuan->promo, // langsung relasi promo
        ]);
    }

    // function untuk approve pengajuan pemesanan unit
    // public function approve($id)
    // {
    //     // dd('masuk approve');
    //     $pemesanan = PemesananUnit::findOrFail($id);

    //     // Update status pengajuan jadi "acc"
    //     $pemesanan->update([
    //         'status_pengajuan' => 'acc',
    //     ]);

    //     return redirect()
    //         ->route('marketing.pengajuanPemesanan.index')
    //         ->with('success', 'Pengajuan berhasil disetujui (ACC).');
    // }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {
            $pemesanan = PemesananUnit::with(['customer', 'sales', 'unit', 'cicilan', 'dataDiri'])->findOrFail($id);
            // dd($pemesanan);
            // Update status pengajuan jadi "acc"
            $pemesanan->update([
                'status_pengajuan' => 'acc',
            ]);

            // Ambil data customer & sales
            $customerName = $pemesanan->dataDiri->nama_pribadi ?? $pemesanan->customer->username ?? '-';
            $salesName    = $pemesanan->sales->username ?? '-';
            $unitName     = $pemesanan->unit->nama_unit ?? '-';
            $totalTagihan = number_format($pemesanan->total_tagihan, 0, ',', '.');
            $sisaTagihan  = number_format($pemesanan->sisa_tagihan, 0, ',', '.');

            // Cicilan detail
            $cicilanText = '';
            foreach ($pemesanan->cicilan as $c) {
                $cicilanText .= "- Pembayaran ke-{$c->pembayaran_ke}: Rp "
                . number_format($c->nominal, 0, ',', '.')
                . " (Jatuh tempo: " . $c->tanggal_jatuh_tempo->format('d/m/Y') . ")\n";
            }
            if (! $cicilanText) {
                $cicilanText = "- Tidak ada cicilan.\n";
            }

            // Pesan untuk Sales
            $messageSales = "Halo {$salesName},\n\n" .
                "Pemesanan unit '{$unitName}' oleh {$customerName} telah di-ACC oleh admin KPR.\n\n" .
                "Rincian tagihan:\n" .
                "Total: Rp {$totalTagihan}\n" .
                "Sisa: Rp {$sisaTagihan}\n" .
                "Cicilan:\n{$cicilanText}\n" .
                "Silakan cek sistem untuk detail lebih lanjut.";

            // Pesan untuk Customer
            $messageCustomer = "Halo {$customerName},\n\n" .
                "Info pemesanan unit '{$unitName}':\n" .
                "Total tagihan: Rp {$totalTagihan}\n" .
                "Sisa tagihan: Rp {$sisaTagihan}\n" .
                "Cicilan:\n{$cicilanText}\n" .
                "Silakan cek sistem untuk informasi lebih lengkap.";

            // Kirim WA
            if ($pemesanan->sales->no_hp) {
                $this->notification->sendWhatsApp($pemesanan->sales->no_hp, $messageSales);
            }

            if ($pemesanan->customer->no_hp) {
                $this->notification->sendWhatsApp($pemesanan->customer->no_hp, $messageCustomer);
            }
        });

        return redirect()
            ->route('marketing.pengajuanPemesanan.index')
            ->with('success', 'Pengajuan berhasil disetujui (ACC) dan notifikasi terkirim.');
    }

    public function reject($id)
    {
        $pemesanan = PemesananUnit::with(['sales', 'unit', 'customer'])->findOrFail($id);

        $sales        = $pemesanan->sales;
        $unit         = $pemesanan->unit;
        $unitName     = $unit->nama_unit ?? '-';
        $customerName = $pemesanan->customer->username ?? '-';

        // 1️⃣ Kembalikan status unit ke booked
        if ($unit) {
            $unit->update(['status_unit' => 'booked']);
        }

        // 2️⃣ Hapus pemesanan unit
        $pemesanan->delete();

        // 3️⃣ Buat pesan WA fleksibel
        $message = "Halo {$sales->username},\n\n" .
            "Pemesanan unit '{$unitName}' oleh {$customerName} telah ditolak oleh admin KPR.\n" .
            "Data pemesanan ini otomatis dihapus.\n\n" .
            "Silakan cek sistem jika perlu konfirmasi lebih lanjut.";

        // 4️⃣ Kirim WA ke sales
        if ($sales && $sales->no_hp) {
            $this->notification->sendWhatsApp($sales->no_hp, $message);
        }

        return redirect()
            ->route('marketing.pengajuanPemesanan.index')
            ->with('success', 'Pengajuan ditolak, data dihapus, unit kembali ke booked, dan notifikasi WA dikirim.');
    }

}
