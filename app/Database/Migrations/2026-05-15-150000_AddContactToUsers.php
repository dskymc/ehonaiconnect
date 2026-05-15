<?php
// Coded by DskyMC

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContactToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => '',
                'after'      => 'nama_lengkap',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'no_hp',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', ['no_hp', 'email']);
    }
}
