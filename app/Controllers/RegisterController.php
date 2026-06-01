<?php
// Coded by DskyMC

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class RegisterController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index(): string
    {
        return view('auth/register');
    }

    public function process(): RedirectResponse
    {
        $rules = [
            'nama_lengkap' => 'required|max_length[150]',
            'instansi_opd' => 'required|max_length[150]',
            'no_hp'        => 'required|max_length[20]',
            'email'        => 'permit_empty|max_length[100]|valid_email',
            'username'     => 'required|max_length[100]|is_unique[users.username]',
            'password'     => 'required|min_length[8]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with(
                'error',
                implode(' ', $this->validator->getErrors())
            );
        }

        $listOpd      = config('Opd')->listOpd;
        $instansiPost = (string) $this->request->getPost('instansi_opd');
        if (! in_array($instansiPost, $listOpd, true)) {
            return redirect()->back()->withInput()->with(
                'error',
                'Silakan pilih Instansi OPD yang valid dari daftar.'
            );
        }

        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);

        $emailRaw = trim((string) $this->request->getPost('email'));

        $inserted = $userModel->insert([
            'username'     => (string) $this->request->getPost('username'),
            'password'     => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'nama_lengkap' => (string) $this->request->getPost('nama_lengkap'),
            'no_hp'        => (string) $this->request->getPost('no_hp'),
            'email'        => $emailRaw === '' ? null : $emailRaw,
            'instansi_opd' => $instansiPost,
            'role'         => 'opd',
            'is_active'    => 0,
        ]);

        if ($inserted === false) {
            return redirect()->back()->withInput()->with('error', implode(' ', $userModel->errors()));
        }

        helper(['email', 'whatsapp']);
        $noHpReg  = (string) $this->request->getPost('no_hp');
        $emailReg = $emailRaw === '' ? '-' : $emailRaw;
        $namaReg  = (string) $this->request->getPost('nama_lengkap');
        $userReg  = (string) $this->request->getPost('username');
        $bodyAdmin = 'OPD baru mendaftar melalui formulir registrasi e-Honai Connect.' . "\n\n"
            . 'Nama lengkap: ' . $namaReg . "\n"
            . 'Username: ' . $userReg . "\n"
            . 'Instansi OPD: ' . $instansiPost . "\n"
            . 'Nomor WhatsApp: ' . $noHpReg . "\n"
            . 'Email: ' . $emailReg . "\n\n"
            . 'Silakan lakukan verifikasi akun di menu Manajemen OPD (Admin).';

        foreach (ehonai_admin_notify_recipients() as $adminEmail) {
            sendNotification(
                $adminEmail,
                '[e-Honai Connect] Registrasi OPD baru menunggu verifikasi',
                $bodyAdmin
            );
        }

        ehonai_send_whatsapp_to_admins(ehonai_whatsapp_opd_registration_admin_message(
            $namaReg,
            $userReg,
            $instansiPost,
            $noHpReg,
            $emailReg
        ));

        return redirect()->to('/login')->with(
            'success',
            'Registrasi berhasil! Akun Anda sedang menunggu verifikasi dari Admin.'
        );
    }
}
