<?php
// Coded by DskyMC

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'username'     => 'admin',
            'password'     => password_hash('noprofile', PASSWORD_DEFAULT),
            'nama_lengkap' => 'Samuel DskyMC',
            'instansi_opd' => 'Diskominfosatik',
            'role'         => 'admin',
            'is_active'    => 1,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        $this->db->table('users')->insert($data);
    }
}
