<?php
// Coded by DskyMC

namespace App\Commands;

use App\Models\MonitoredDeviceModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class MonitoringSync extends BaseCommand
{
    protected $group       = 'Monitoring';
    protected $name        = 'monitoring:sync';
    protected $description = 'Sinkron daftar perangkat dari LibreNMS ke cache lokal (monitored_devices).';
    protected $usage       = 'monitoring:sync';

    public function run(array $params): void
    {
        $librenms = Services::librenms();
        if (! $librenms->isConfigured()) {
            CLI::error('LibreNMS belum dikonfigurasi (LIBRENMS_URL / LIBRENMS_API_TOKEN).');

            return;
        }

        $result = $librenms->listDevices('all');
        if (($result['status'] ?? '') === 'error' || $result['devices'] === []) {
            $result = $librenms->listDevices('active');
        }

        /** @var MonitoredDeviceModel $model */
        $model  = model(MonitoredDeviceModel::class);
        $synced = 0;
        $now    = date('Y-m-d H:i:s');

        foreach ($result['devices'] as $device) {
            $deviceId = (int) ($device['device_id'] ?? 0);
            $hostname = (string) ($device['hostname'] ?? '');
            if ($deviceId <= 0 || $hostname === '') {
                continue;
            }

            $row = [
                'librenms_device_id' => $deviceId,
                'hostname'           => $hostname,
                'display_name'       => (string) ($device['display'] ?? $hostname),
                'ip'                 => (string) ($device['ip'] ?? ''),
                'os'                 => (string) ($device['os'] ?? ''),
                'location'           => (string) ($device['location'] ?? ''),
                'hardware'           => (string) ($device['hardware'] ?? ''),
                'status'             => (int) ($device['status'] ?? 0) === 1 ? 1 : 0,
                'synced_at'          => $now,
            ];

            $existing = $model->findByLibrenmsDeviceId($deviceId);
            if ($existing === null) {
                $model->insert($row);
            } else {
                $model->update((int) $existing->id, $row);
            }
            $synced++;
        }

        \Config\Services::cache()->delete('librenms_dashboard_summary');

        CLI::write('Sinkron selesai: ' . $synced . ' perangkat.', 'green');
    }
}
