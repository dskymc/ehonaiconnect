<?php
// Coded by DskyMC

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'user_id',
        'ticket_id',
        'message',
        'is_read',
    ];

    /**
     * @return list<object>
     */
    public function getUnreadForUser(int $userId, int $limit = 15): array
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->orderBy('created_at', 'DESC')
            ->findAll($limit);
    }

    public function markReadForTicketAndUser(int $ticketId, int $userId): void
    {
        $this->db->table($this->table)
            ->where('ticket_id', $ticketId)
            ->where('user_id', $userId)
            ->update(['is_read' => 1]);
    }
}
