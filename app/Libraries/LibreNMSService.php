<?php
// Coded by DskyMC

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use Config\LibreNMS as LibreNMSConfig;

class LibreNMSService
{
    private LibreNMSConfig $config;

    private CURLRequest $client;

    public function __construct(?LibreNMSConfig $config = null, ?CURLRequest $client = null)
    {
        $this->config = $config ?? config(LibreNMSConfig::class);
        $this->client = $client ?? \Config\Services::curlrequest([
            'timeout'         => $this->config->httpTimeout,
            'connect_timeout' => 10,
        ]);
    }

    public function isConfigured(): bool
    {
        return $this->config->enabled
            && $this->config->baseUrl !== ''
            && $this->config->apiToken !== '';
    }

    public function getBaseUrl(): string
    {
        return $this->config->baseUrl;
    }

    public function deviceUrl(string $hostname): string
    {
        return $this->config->baseUrl . '/device/device=' . rawurlencode($hostname);
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    public function ping(): array
    {
        $data = $this->request('GET', '/ping');

        if ($data === null) {
            return ['ok' => false, 'message' => 'LibreNMS tidak terjangkau'];
        }

        $message = isset($data['message']) ? (string) $data['message'] : '';

        return ['ok' => $message === 'pong' || ($data['status'] ?? '') === 'ok', 'message' => $message];
    }

    /**
     * Ringkasan dashboard (di-cache).
     *
     * @return array{
     *   available: bool,
     *   message?: string,
     *   total_active: int,
     *   total_down: int,
     *   active_alerts: int,
     *   devices_down: list<array<string, mixed>>
     * }
     */
    public function getDashboardSummary(): array
    {
        $empty = [
            'available'     => false,
            'message'       => 'LibreNMS belum dikonfigurasi',
            'total_active'  => 0,
            'total_down'    => 0,
            'active_alerts' => 0,
            'devices_down'  => [],
        ];

        if (! $this->isConfigured()) {
            return $empty;
        }

        $cache = \Config\Services::cache();
        $key   = 'librenms_dashboard_summary';
        $cached = $cache->get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $ping = $this->ping();
        if (! $ping['ok']) {
            $result = $empty;
            $result['message'] = $ping['message'] ?? 'LibreNMS tidak merespons';

            return $result;
        }

        $activeData = $this->request('GET', '/devices', ['type' => 'active']);
        $downData   = $this->request('GET', '/devices', ['type' => 'down', 'order' => 'hostname ASC']);
        $alertData  = $this->request('GET', '/alerts', ['state' => '1']);

        $devicesDown = [];
        if (is_array($downData['devices'] ?? null)) {
            foreach ($downData['devices'] as $device) {
                if (! is_array($device)) {
                    continue;
                }
                $hostname = (string) ($device['hostname'] ?? $device['sysName'] ?? '');
                if ($hostname === '') {
                    continue;
                }
                $devicesDown[] = [
                    'device_id' => (int) ($device['device_id'] ?? 0),
                    'hostname'  => $hostname,
                    'display'   => (string) ($device['display'] ?? $hostname),
                    'location'  => (string) ($device['location'] ?? ''),
                    'os'        => (string) ($device['os'] ?? ''),
                    'status'    => (int) ($device['status'] ?? 0),
                    'url'       => $this->deviceUrl($hostname),
                ];
                if (count($devicesDown) >= 10) {
                    break;
                }
            }
        }

        $result = [
            'available'     => true,
            'total_active'  => (int) ($activeData['count'] ?? 0),
            'total_down'    => (int) ($downData['count'] ?? count($devicesDown)),
            'active_alerts' => is_array($alertData['alerts'] ?? null) ? count($alertData['alerts']) : (int) ($alertData['count'] ?? 0),
            'devices_down'  => $devicesDown,
        ];

        $cache->save($key, $result, $this->config->cacheTtl);

        return $result;
    }

    /**
     * @return array{status: string, count: int, devices: list<array<string, mixed>>}
     */
    public function listDevices(string $type = 'active', ?string $query = null): array
    {
        $params = ['type' => $type, 'order' => 'hostname ASC'];
        if ($query !== null && $query !== '') {
            $params['type']  = 'hostname';
            $params['query'] = $query;
        }

        $data = $this->request('GET', '/devices', $params);

        if ($data === null) {
            return ['status' => 'error', 'count' => 0, 'devices' => []];
        }

        $devices = [];
        foreach ($data['devices'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $hostname = (string) ($row['hostname'] ?? '');
            $devices[] = [
                'device_id' => (int) ($row['device_id'] ?? 0),
                'hostname'  => $hostname,
                'display'   => (string) ($row['display'] ?? $hostname),
                'ip'        => (string) ($row['ip'] ?? ''),
                'location'  => (string) ($row['location'] ?? ''),
                'os'        => (string) ($row['os'] ?? ''),
                'hardware'  => (string) ($row['hardware'] ?? ''),
                'status'    => (int) ($row['status'] ?? 0),
                'url'       => $hostname !== '' ? $this->deviceUrl($hostname) : '',
            ];
        }

        return [
            'status'  => (string) ($data['status'] ?? 'ok'),
            'count'   => (int) ($data['count'] ?? count($devices)),
            'devices' => $devices,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDevice(string $hostname): ?array
    {
        $data = $this->request('GET', '/devices/' . rawurlencode($hostname));

        if ($data === null) {
            return null;
        }

        $devices = $data['devices'] ?? null;
        if (is_array($devices) && isset($devices[0]) && is_array($devices[0])) {
            return $devices[0];
        }

        if (isset($data['hostname'])) {
            return $data;
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listAlerts(int $state = 1, int $limit = 50): array
    {
        $data = $this->request('GET', '/alerts', [
            'state' => (string) $state,
            'order' => 'timestamp DESC',
        ]);

        if ($data === null || ! is_array($data['alerts'] ?? null)) {
            return [];
        }

        $out = [];
        foreach ($data['alerts'] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = $row;
            if (count($out) >= $limit) {
                break;
            }
        }

        return $out;
    }

    /**
     * @param array<string, string|int> $query
     * @return array<string, mixed>|null
     */
    private function request(string $method, string $path, array $query = []): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $url = $this->config->baseUrl . '/api/v0' . $path;
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        try {
            $response = $this->client->request($method, $url, [
                'headers'     => [
                    'X-Auth-Token' => $this->config->apiToken,
                    'Accept'       => 'application/json',
                ],
                'http_errors' => false,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'LibreNMS API error: ' . $e->getMessage());

            return null;
        }

        $code = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($code < 200 || $code >= 300) {
            log_message('error', 'LibreNMS HTTP ' . $code . ' ' . $path . ': ' . mb_substr($body, 0, 500));

            return null;
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : null;
    }
}
