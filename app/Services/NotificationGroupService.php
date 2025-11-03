<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationGroupService
{
    protected string $apiKey;
    protected string $fonnteSendApiUrl;

    public function __construct()
    {
        $this->apiKey           = env('FONNTE_API_KEY', '');
        $this->fonnteSendApiUrl = env('FONNTE_SEND_API_URL', 'https://api.fonnte.com/send');

        if (empty($this->apiKey)) {
            Log::warning('Fonnte API Key tidak ditemukan di .env. Pengiriman pesan mungkin gagal.');
        }
    }

    /**
     * Kirim pesan ke grup WhatsApp.
     *
     * @param string $groupId ID grup WhatsApp
     * @param string $message Konten pesan
     * @return bool
     */
    public function send(string $groupId, string $message): bool
    {
        if (empty($this->apiKey) || empty($this->fonnteSendApiUrl) || empty($groupId)) {
            Log::error('Fonnte API, URL, atau Group ID kosong. Pesan tidak terkirim.', [
                'group_id' => $groupId,
            ]);
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->post($this->fonnteSendApiUrl, [
                'target'  => $groupId,
                'message' => $message,
            ]);

            if ($response->successful() && $response->json('status')) {
                Log::info('Pesan WhatsApp berhasil dikirim ke grup.', [
                    'group_id' => $groupId,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Gagal mengirim pesan WhatsApp ke grup via Fonnte.', [
                    'group_id'      => $groupId,
                    'status'        => $response->status(),
                    'response_body' => $response->body()
                ]);
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('Exception saat mengirim pesan WhatsApp via Fonnte: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
