<?php

namespace App\Console\Commands;

use App\Models\PemesananUnitCicilan;
use App\Models\User;
use App\Services\NotificationPribadiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendNotifikasiCicilanSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketing:notifikasi-cicilan-sales 
                            {--days=3 : Jumlah hari ke depan dari hari ini menuju jatuh tempo (default 3 hari: H-3 murni, contoh tgl 10 -> target tgl 13)}
                            {--to= : Override nomor WhatsApp penerima (khusus testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi WhatsApp pengingat cicilan H-3 ke nomor Sales penanggung jawab';

    /**
     * Execute the console command.
     */
    public function handle(NotificationPribadiService $notifService)
    {
        $days = (int) $this->option('days');
        $overridePhone = $this->option('to');
        $targetDate = Carbon::today()->addDays($days)->toDateString();

        $this->info("Menjalankan pengecekan cicilan jatuh tempo untuk tanggal: {$targetDate} (+{$days} hari dari hari ini)");

        // 📝 Log mulai eksekusi
        Log::info("CRON H-3 CICILAN: Memulai pengecekan", [
            'target_date' => $targetDate,
            'days_ahead'  => $days,
            'is_testing'  => !empty($overridePhone),
            'run_at'      => now()->toDateTimeString(),
        ]);

        if ($overridePhone) {
            $this->warn("⚠️  MODE TESTING AKTIF: Semua notifikasi akan dialihkan ke nomor: {$overridePhone}");
        }

        try {
            // 🔹 Query cicilan yang aktif, belum lunas, dan tepat jatuh tempo pada targetDate
            $cicilans = PemesananUnitCicilan::with([
                    'pemesananUnit.sales',
                    'pemesananUnit.unit',
                    'pemesananUnit.perumahaan',
                    'pemesananUnit.dataDiri',
                ])
                ->where('is_active', true)
                ->where('status_bayar', '!=', 'lunas')
                ->whereDate('tanggal_jatuh_tempo', $targetDate)
                ->get();

            if ($cicilans->isEmpty()) {
                $this->info("Tidak ada tagihan cicilan yang jatuh tempo pada tanggal {$targetDate}.");
                Log::info("CRON H-3 CICILAN: Selesai. Tidak ada tagihan cicilan jatuh tempo pada tanggal {$targetDate}.");
                return 0;
            }

            $this->info("Ditemukan {$cicilans->count()} tagihan cicilan jatuh tempo pada tanggal {$targetDate}. Memproses notifikasi...");
            Log::info("CRON H-3 CICILAN: Ditemukan {$cicilans->count()} tagihan cicilan jatuh tempo pada tanggal {$targetDate}. Memproses pengiriman WA...");

            $successCount = 0;
            $failedCount = 0;

            foreach ($cicilans as $cicilan) {
                $pemesanan = $cicilan->pemesananUnit;
                if (!$pemesanan) {
                    continue;
                }

                // Dapatkan sales penanggung jawab (sales_id atau input_by)
                $sales = $pemesanan->sales ?? ($pemesanan->input_by ? User::find($pemesanan->input_by) : null);
                $noHpSales = $sales?->no_hp;
                $namaSales = $sales->nama_lengkap ?? $sales->username ?? 'Sales';

                $namaCustomer = $pemesanan->dataDiri?->nama_pribadi ?? $pemesanan->customer?->nama_lengkap ?? '-';
                $namaPerumahaan = $pemesanan->perumahaan?->nama_perumahaan ?? '-';
                $namaUnit = $pemesanan->unit?->nama_unit ?? '-';
                $nominal = number_format($cicilan->nominal, 0, ',', '.');
                $tglJatuhTempo = Carbon::parse($cicilan->tanggal_jatuh_tempo)->translatedFormat('d F Y');
                $tipeCicilan = strtoupper($cicilan->tipe_cicilan ?? 'DP');

                // Jika ada override untuk testing, gunakan nomor override
                $targetPhone = $overridePhone ?: $noHpSales;

                if (empty($targetPhone) || !$this->isValidPhoneNumber($targetPhone)) {
                    $this->warn("Cicilan ID {$cicilan->id} dilewati: Nomor HP Sales {$namaSales} ('{$noHpSales}') tidak valid / tidak ditemukan.");
                    Log::warning("CRON H-3 CICILAN: Dilewati, nomor HP sales tidak valid", [
                        'cicilan_id'  => $cicilan->id,
                        'sales'       => $namaSales,
                        'sales_phone' => $noHpSales,
                        'unit'        => $namaUnit,
                    ]);
                    $failedCount++;
                    continue;
                }

                $message = "Halo *{$namaSales}*,\n\n" .
                    "⚠️ *PENGINGAT CICILAN (H-3)*\n\n" .
                    "• *Unit*        : {$namaUnit} ({$namaPerumahaan})\n" .
                    "• *Customer*    : {$namaCustomer}\n" .
                    "• *Angsuran*    : Ke-{$cicilan->pembayaran_ke} ({$tipeCicilan})\n" .
                    "• *Nominal*     : Rp {$nominal}\n" .
                    "• *Jatuh Tempo* : {$tglJatuhTempo}\n\n" .
                    "Mohon segera difollow-up pembayarannya ya. Terima kasih! 🙏";

                $isSent = $notifService->sendWhatsApp($targetPhone, $message);

                if ($isSent) {
                    $this->info("✅ Notifikasi berhasil dikirim ke {$targetPhone} (Sales: {$namaSales}) untuk unit {$namaUnit}.");
                    Log::info("CRON H-3 CICILAN: Notifikasi WhatsApp berhasil terkirim", [
                        'cicilan_id'   => $cicilan->id,
                        'target_phone' => $targetPhone,
                        'sales'        => $namaSales,
                        'unit'         => $namaUnit,
                        'jatuh_tempo'  => $targetDate,
                        'nominal'      => $nominal,
                    ]);
                    $successCount++;
                } else {
                    $this->error("❌ Gagal mengirim notifikasi ke {$targetPhone} (Sales: {$namaSales}) untuk unit {$namaUnit}.");
                    Log::error("CRON H-3 CICILAN: Gagal mengirim WhatsApp via Fonnte", [
                        'cicilan_id'   => $cicilan->id,
                        'target_phone' => $targetPhone,
                        'sales'        => $namaSales,
                        'unit'         => $namaUnit,
                    ]);
                    $failedCount++;
                }
            }

            $this->info("Selesai. Berhasil terkirim: {$successCount}, Gagal: {$failedCount}");
            Log::info("CRON H-3 CICILAN: Eksekusi selesai", [
                'target_date' => $targetDate,
                'total_found' => $cicilans->count(),
                'success'     => $successCount,
                'failed'      => $failedCount,
            ]);

            return 0;
        } catch (\Throwable $e) {
            $this->error("Terjadi error pada cron H-3 cicilan: " . $e->getMessage());
            Log::error("CRON H-3 CICILAN: Terjadi error/exception", [
                'error_message' => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Validasi nomor WhatsApp (minimal 10 digit angka)
     */
    protected function isValidPhoneNumber(?string $phone): bool
    {
        if (empty($phone)) {
            return false;
        }
        $digits = preg_replace('/[^0-9]/', '', $phone);
        return strlen($digits) >= 10;
    }
}

