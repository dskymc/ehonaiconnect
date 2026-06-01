<?php
// Coded by DskyMC

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use Config\WhatsApp as WhatsAppConfig;

class WhatsAppService
{
    private WhatsAppConfig $config;

    private CURLRequest $client;

    public function __construct(?WhatsAppConfig $config = null, ?CURLRequest $client = null)
    {
        $this->config = $config ?? config(WhatsAppConfig::class);
        $this->client = $client ?? \Config\Services::curlrequest([
            'timeout'         => 15,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Kirim pesan teks ke nomor WhatsApp via Fonnte.
     */
    public function send(string $target, string $message): bool
    {
        if (! $this->config->enabled) {
            log_message('notice', 'WhatsApp notifikasi dinonaktifkan (whatsapp.enabled=false); pesan dilewati.');

            return false;
        }

        $token = trim($this->config->token);
        if ($token === '') {
            log_message('notice', 'FONNTE_TOKEN belum dikonfigurasi; notifikasi WhatsApp dilewati.');

            return false;
        }

        $normalizedTarget = $this->normalizePhoneNumber($target);
        if ($normalizedTarget === null) {
            log_message('notice', 'Nomor WhatsApp tujuan tidak valid: ' . $target);

            return false;
        }

        $body = trim($message);
        if ($body === '') {
            return false;
        }

        try {
            $response = $this->client->post($this->config->apiUrl, [
                'headers' => [
                    'Authorization' => $token,
                ],
                'form_params' => [
                    'target'  => $normalizedTarget,
                    'message' => $body,
                ],
                'http_errors' => false,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menghubungi API Fonnte: ' . $e->getMessage());

            return false;
        }

        $statusCode = $response->getStatusCode();
        $rawBody    = (string) $response->getBody();

        if ($statusCode < 200 || $statusCode >= 300) {
            log_message('error', 'Fonnte HTTP ' . $statusCode . ': ' . $rawBody);

            return false;
        }

        $decoded = json_decode($rawBody, true);
        if (is_array($decoded) && array_key_exists('status', $decoded) && $decoded['status'] === false) {
            $reason = isset($decoded['reason']) ? (string) $decoded['reason'] : $rawBody;
            log_message('error', 'Fonnte menolak pengiriman: ' . $reason);

            return false;
        }

        return true;
    }

    /**
     * Normalisasi nomor Indonesia ke format internasional (62xxxxxxxxxx).
     */
    public function normalizePhoneNumber(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', trim($phone));
        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        if (! str_starts_with($digits, '62') || strlen($digits) < 10 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }
}
