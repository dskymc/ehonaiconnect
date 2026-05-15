<?php
// Coded by DskyMC

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContactToTickets extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('tickets', [
            'no_hp_pelapor' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => '',
                'after'      => 'pelapor_instansi',
            ],
            'email_pelapor' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'no_hp_pelapor',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('tickets', ['no_hp_pelapor', 'email_pelapor']);
    }
}
