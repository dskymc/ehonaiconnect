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

        $inserted = $userModel->insert([
            'username'     => (string) $this->request->getPost('username'),
            'password'     => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'nama_lengkap' => (string) $this->request->getPost('nama_lengkap'),
            'instansi_opd' => $instansiPost,
            'role'         => 'opd',
            'is_active'    => 0,
        ]);

        if ($inserted === false) {
            return redirect()->back()->withInput()->with('error', implode(' ', $userModel->errors()));
        }

        return redirect()->to('/login')->with(
            'success',
            'Registrasi berhasil! Akun Anda sedang menunggu verifikasi dari Admin.'
        );
    }
}
