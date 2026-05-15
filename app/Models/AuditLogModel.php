<?php
// Coded by DskyMC

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'user_id',
        'aksi',
        'deskripsi',
        'ip_address',
        'created_at',
    ];

    /** @var list<string> */
    protected $beforeInsert = ['setCreatedAt'];

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function setCreatedAt(array $data): array
    {
        if (! isset($data['data']['created_at'])) {
            $data['data']['created_at'] = date('Y-m-d H:i:s');
        }

        return $data;
    }

    /**
     * @return list<object>
     */
    public function getLogsWithUser(int $limit = 500): array
    {
        return $this->select('audit_logs.*, users.nama_lengkap AS nama_user')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->orderBy('audit_logs.created_at', 'DESC')
            ->orderBy('audit_logs.id', 'DESC')
            ->findAll($limit);
    }
}
