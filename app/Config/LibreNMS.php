<?php
// Coded by DskyMC

namespace Config;

use CodeIgniter\Config\BaseConfig;

class LibreNMS extends BaseConfig
{
    public bool $enabled = true;

    public string $baseUrl = '';

    public string $apiToken = '';

    public string $webhookSecret = '';

    /** Daftar IP LibreNMS yang diizinkan mengirim webhook (kosong = tidak dibatasi). */
    public string $webhookAllowIps = '';

    public int $cacheTtl = 120;

    public int $httpTimeout = 15;

    public function __construct()
    {
        parent::__construct();

        $this->enabled         = filter_var(env('librenms.enabled', $this->enabled), FILTER_VALIDATE_BOOLEAN);
        $this->baseUrl         = rtrim((string) env('LIBRENMS_URL', $this->baseUrl), '/');
        $this->apiToken        = (string) env('LIBRENMS_API_TOKEN', $this->apiToken);
        $this->webhookSecret   = (string) env('LIBRENMS_WEBHOOK_SECRET', $this->webhookSecret);
        $this->webhookAllowIps = (string) env('librenms.webhookAllowIps', $this->webhookAllowIps);
        $this->cacheTtl        = (int) env('librenms.cacheTtl', $this->cacheTtl);
        $this->httpTimeout     = (int) env('librenms.httpTimeout', $this->httpTimeout);
    }
}
