<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationPribadiService
{
    protected string $apiKey = '';
    protected string $sendUrl = '';

    public function __construct()
    {
        // Ambil dari .env dengan default kosong
        $this->apiKey  = env('FONNTE_API_KEY', '');
        $this->sendUrl = env('FONNTE_SEND_API_URL', '');

        // Validasi
        if (empty($this->apiKey) || empty($this->sendUrl)) {
            Log::warning('Fonnte API Key atau URL tidak ditemukan di .env. Pengiriman pesan mungkin gagal.');
        }
    }

    /**
     * Kirim WhatsApp
     */
    public function sendWhatsApp(string $noHp, string $message): bool
    {
        if (empty($this->apiKey) || empty($this->sendUrl)) {
            Log::error('API Key atau URL kosong, pesan tidak terkirim.');
            return false;
        }   

        try {
            $response = Http::withHeaders([
                'Authorization' => "{$this->apiKey}",
            ])->post($this->sendUrl, [
                'target'  => $noHp,
                'message' => $message,
                'countryCode' => '62', // opsional, kalau perlu
            ]);

            Log::info('Fonnte response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response->successful() && $response->json('status') === true;
        } catch (\Throwable $e) {
            Log::error('Exception saat kirim WA: ' . $e->getMessage());
            return false;
        }
    }
}
