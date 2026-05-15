<?php
// Coded by DskyMC

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPelaporManualToTickets extends Migration
{
    public function up(): void
    {
        $db = $this->db;

        $fk = $db->query(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? '
            . 'AND REFERENCED_TABLE_NAME IS NOT NULL',
            ['tickets', 'user_id']
        )->getRow();

        if ($fk !== null && isset($fk->CONSTRAINT_NAME)) {
            $db->query('ALTER TABLE `tickets` DROP FOREIGN KEY `' . $fk->CONSTRAINT_NAME . '`');
        }

        $db->query('ALTER TABLE `tickets` MODIFY `user_id` INT UNSIGNED NULL');

        $this->forge->addColumn('tickets', [
            'pelapor_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'pelapor_instansi' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
        ]);

        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
    }

    public function down(): void
    {
        $db = $this->db;

        $fk = $db->query(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? '
            . 'AND REFERENCED_TABLE_NAME IS NOT NULL',
            ['tickets', 'user_id']
        )->getRow();

        if ($fk !== null && isset($fk->CONSTRAINT_NAME)) {
            $db->query('ALTER TABLE `tickets` DROP FOREIGN KEY `' . $fk->CONSTRAINT_NAME . '`');
        }

        $this->forge->dropColumn('tickets', ['pelapor_nama', 'pelapor_instansi']);

        $db->query('DELETE FROM `tickets` WHERE `user_id` IS NULL');
        $db->query('ALTER TABLE `tickets` MODIFY `user_id` INT UNSIGNED NOT NULL');

        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
    }
}
