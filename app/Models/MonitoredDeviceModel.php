<?php
// Coded by DskyMC

namespace App\Models;

use CodeIgniter\Model;

class MonitoredDeviceModel extends Model
{
    protected $table         = 'monitored_devices';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'librenms_device_id',
        'hostname',
        'display_name',
        'ip',
        'os',
        'location',
        'hardware',
        'status',
        'synced_at',
    ];

    /**
     * @return list<object>
     */
    public function getAllOrdered(string $statusFilter = 'all', ?string $search = null): array
    {
        $builder = $this->builder();
        if ($statusFilter === 'up') {
            $builder->where('status', 1);
        } elseif ($statusFilter === 'down') {
            $builder->where('status', 0);
        }
        if ($search !== null && $search !== '') {
            $builder->groupStart()
                ->like('hostname', $search)
                ->orLike('display_name', $search)
                ->orLike('ip', $search)
                ->orLike('location', $search)
                ->groupEnd();
        }

        return $builder->orderBy('hostname', 'ASC')->get()->getResult();
    }

    public function findByLibrenmsDeviceId(int $deviceId): ?object
    {
        return $this->where('librenms_device_id', $deviceId)->first();
    }

    public function findByHostname(string $hostname): ?object
    {
        return $this->where('hostname', $hostname)->first();
    }
}
