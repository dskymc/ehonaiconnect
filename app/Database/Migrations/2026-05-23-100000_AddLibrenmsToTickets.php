<?php
// Coded by DskyMC

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLibrenmsToTickets extends Migration
{
    public function up(): void
    {
        $fields = [
            'librenms_device_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'teknisi_id',
            ],
            'librenms_alert_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'librenms_device_id',
            ],
            'librenms_hostname' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'librenms_alert_id',
            ],
            'source' => [
                'type'       => 'ENUM',
                'constraint' => ['manual', 'librenms'],
                'default'    => 'manual',
                'after'      => 'librenms_hostname',
            ],
        ];

        $this->forge->addColumn('tickets', $fields);
        $this->forge->addKey('librenms_device_id');
    }

    public function down(): void
    {
        $this->forge->dropColumn('tickets', ['librenms_device_id', 'librenms_alert_id', 'librenms_hostname', 'source']);
    }
}
