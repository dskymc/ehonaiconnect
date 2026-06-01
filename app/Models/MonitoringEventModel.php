<?php
// Coded by DskyMC

namespace App\Models;

use CodeIgniter\Model;

class MonitoringEventModel extends Model
{
    protected $table         = 'monitoring_events';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'librenms_device_id',
        'librenms_alert_id',
        'hostname',
        'severity',
        'state',
        'title',
        'message',
        'dedupe_key',
        'payload',
        'ticket_id',
        'created_at',
    ];

    /**
     * @return list<object>
     */
    public function getRecent(int $limit = 50): array
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function existsByDedupeKey(string $dedupeKey): bool
    {
        if ($dedupeKey === '') {
            return false;
        }

        return $this->where('dedupe_key', $dedupeKey)->countAllResults() > 0;
    }
}
