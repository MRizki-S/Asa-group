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

        $namaPerumahaan     = 'Global';
        $pengajuanPemesanan = collect();

        // 🔹 Query dasar
        $query = PemesananUnit::with(['perumahaan', 'unit', 'customer', 'sales'])
            ->where('status_pengajuan', 'pending')
            ->orderByDesc('created_at');

        // 🔸 Filter tambahan jika login adalah Marketing
        if ($user->hasAnyRole(roles: ['Marketing'])) {
            $query->where('sales_id', $user->id);
        }

        if ($user->is_global) {
            // 🌐 Jika user global → ambil semua & group by perumahaan
            $pengajuanPemesanan = $query->get()->groupBy('perumahaan_id');
        } else {
            // 🏠 Jika bukan global → filter hanya perumahaan miliknya
            $pengajuanPemesanan = $query->where('perumahaan_id', $perumahaanId)->get();

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
            'bonusCash',
            'bonusKpr',
        ])->findOrFail($id);
        // dd($pengajuan);
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

    public function approve($id)
    {
        DB::transaction(function () use ($id) {
            $pemesanan = PemesananUnit::with(['customer', 'sales', 'unit', 'cicilan', 'dataDiri', 'perumahaan'])->findOrFail($id);
            // dd($pemesanan);
            // Update status pengajuan jadi "acc"
            $pemesanan->update([
                'status_pengajuan' => 'acc',
            ]);

            // 💡 Update harga_jual di tabel unit
            $unit = $pemesanan->unit;

            if ($pemesanan->cara_bayar === 'cash') {
                $cash = $pemesanan->cash;
                if ($cash) {
                    $unit->update([
                        'harga_jual'  => $cash->harga_jadi,
                        'status_unit' => 'sold', // 🚀 Ubah status jadi sold
                    ]);
                }
            } elseif ($pemesanan->cara_bayar === 'kpr') {
                $kpr = $pemesanan->kpr;
                if ($kpr) {
                    $unit->update([
                        'harga_jual'  => $kpr->harga_total,
                        'status_unit' => 'sold', // 🚀 Ubah status jadi sold
                    ]);
                }
            } else {
                // Jika cara bayar tidak dikenali, tetap ubah jadi sold
                $unit->update(['status_unit' => 'sold']);
            }

            // Ambil data customer & sales
            $customerName   = $pemesanan->dataDiri->nama_pribadi ?? $pemesanan->customer->username ?? '-';
            $salesName      = $pemesanan->sales->username ?? '-';
            $unitName       = $pemesanan->unit->nama_unit ?? '-';
            $totalTagihan   = number_format($pemesanan->total_tagihan, 0, ',', '.');
            $sisaTagihan    = number_format($pemesanan->sisa_tagihan, 0, ',', '.');
            $namaPerumahaan = $pemesanan->perumahaan->nama_perumahaan ?? '-';

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
                "Silakan hubungi admin Kpr terkait jika ada pertanyaan.";

            // 🔹 Pesan untuk Customer (lebih hangat dan pendek)
            $messageCustomer =
                "🎉 Halo Bapak/Ibu {$customerName},\n\n" .
                "Kabar baik! Pemesanan unit *{$unitName}* di *{$namaPerumahaan}* telah berhasil dikonfirmasi oleh tim kami 🏡✨\n" .
                "Tim kami siap mendampingi Anda hingga proses *serah terima rumah* selesai dengan lancar😊.\n" .
                "Terima kasih atas kepercayaannya 🙏. ";

            // Kirim WA
            if ($pemesanan->sales->no_hp) {
                $this->notification->sendWhatsApp($pemesanan->sales->no_hp, $messageSales);
            }

            // if ($pemesanan->customer->no_hp) {
            //     $this->notification->sendWhatsApp($pemesanan->customer->no_hp, $messageCustomer);
            // }
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
            "Pengajuan pemesanan unit *{$unitName}* atas nama *{$customerName}* telah ditolak oleh Admin KPR.\n" .
            "Data pemesanan tersebut telah dihapus secara otomatis dari sistem.\n\n" .
            "Silakan hubungi Admin terkait untuk mengetahui alasan penolakan.";

        // 4️⃣ Kirim WA ke sales
        if ($sales && $sales->no_hp) {
            $this->notification->sendWhatsApp($sales->no_hp, $message);
        }

        return redirect()
            ->route('marketing.pengajuanPemesanan.index')
            ->with('success', 'Pengajuan ditolak, data dihapus, unit kembali ke booked, dan notifikasi WA dikirim.');
    }

}
