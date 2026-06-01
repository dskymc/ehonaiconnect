<?php
// Coded by DskyMC

namespace Config;

use CodeIgniter\Config\BaseConfig;

class WhatsApp extends BaseConfig
{
    public string $apiUrl = 'https://api.fonnte.com/send';

    /** Token API Fonnte (Authorization header). */
    public string $token = '';

    /**
     * Daftar nomor admin penerima notifikasi (dipisah koma/semicolon/spasi).
     * Contoh .env: whatsapp.adminNotify = 628123456789,628987654321
     */
    public string $adminNotify = '';

    /** Nonaktifkan pengiriman tanpa menghapus kode (mis. saat development). */
    public bool $enabled = true;

    public function __construct()
    {
        parent::__construct();

        $this->token       = (string) env('FONNTE_TOKEN', $this->token);
        $this->adminNotify = (string) env('whatsapp.adminNotify', $this->adminNotify);
        $this->enabled       = filter_var(env('whatsapp.enabled', $this->enabled), FILTER_VALIDATE_BOOLEAN);
        $this->apiUrl        = (string) env('whatsapp.apiUrl', $this->apiUrl);
    }
}
