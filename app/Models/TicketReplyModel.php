<?php
// Coded by DskyMC

namespace App\Models;

use CodeIgniter\Model;

class TicketReplyModel extends Model
{
    protected $table         = 'ticket_replies';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'ticket_id',
        'user_id',
        'pesan',
        'lampiran',
    ];

    /**
     * @return list<object>
     */
    public function getRepliesByTicket(int $ticket_id): array
    {
        $builder = $this->builder();
        $builder->select(
            $this->table . '.*, users.nama_lengkap AS pengirim_nama_lengkap, users.role AS pengirim_role'
        );
        $builder->join('users', 'users.id = ' . $this->table . '.user_id', 'inner');
        $builder->where($this->table . '.ticket_id', $ticket_id);
        $builder->orderBy($this->table . '.created_at', 'ASC');

        return $builder->get()->getResult();
    }
}
