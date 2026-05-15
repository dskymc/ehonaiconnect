<?php
// Coded by DskyMC

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index(): string
    {
        return view('auth/login');
    }

    public function process(): RedirectResponse
    {
        $rules = [
            'username' => 'required|max_length[100]',
            'password' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with(
                'error',
                implode(' ', $this->validator->getErrors())
            );
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);
        $user      = $userModel->where('username', $username)->first();

        if ($user === null || ! password_verify($password, $user->password)) {
            return redirect()->to('/login')->withInput()->with(
                'error',
                'Username atau password tidak sesuai.'
            );
        }

        $isActive = (int) ($user->is_active ?? 0);

        if ($isActive === 0) {
            session()->destroy();

            return redirect()->to('/login')->withInput()->with(
                'error',
                'Akun Anda belum diaktifkan. Silakan hubungi Admin Diskominfosatik.'
            );
        }

        if ($isActive === 1) {
            session()->set([
                'id'           => $user->id,
                'username'     => $user->username,
                'nama_lengkap' => $user->nama_lengkap,
                'role'         => $user->role,
                'instansi_opd' => $user->instansi_opd,
                'isLoggedIn'   => true,
            ]);

            return redirect()->to('/dashboard');
        }

        session()->destroy();

        return redirect()->to('/login')->withInput()->with(
            'error',
            'Akun Anda belum diaktifkan. Silakan hubungi Admin Diskominfosatik.'
        );
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
