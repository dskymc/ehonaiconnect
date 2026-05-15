<?php
// Coded by DskyMC

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nomor_tiket' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'prioritas' => [
                'type'       => 'ENUM',
                'constraint' => ['Low', 'Medium', 'High'],
                'default'    => 'Medium',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Baru', 'Diproses', 'Tertunda', 'Selesai', 'Ditutup'],
                'default'    => 'Baru',
            ],
            'judul_laporan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'bukti_foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'teknisi_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('nomor_tiket', false, true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('teknisi_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('tickets');
    }

    public function down(): void
    {
        $this->forge->dropTable('tickets', true);
    }
}
