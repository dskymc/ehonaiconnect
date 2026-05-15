<?php
// Coded by DskyMC

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'comment'    => '0=Belum Verifikasi, 1=Aktif',
            ],
        ]);

        $this->db->table('users')->update(['is_active' => 1]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'is_active');
    }
}
