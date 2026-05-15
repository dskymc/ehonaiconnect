<?php
// Coded by DskyMC

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class ProfileController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index(): RedirectResponse|string
    {
        $uid = (int) session()->get('id');
        if ($uid < 1) {
            return redirect()->to('/login');
        }

        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);
        $user      = $userModel->find($uid);

        if ($user === null) {
            return redirect()->to('/dashboard')->with('error', 'Data pengguna tidak ditemukan.');
        }

        return view('profile/index', ['user' => $user]);
    }

    public function update(): RedirectResponse
    {
        $uid = (int) session()->get('id');
        if ($uid < 1) {
            return redirect()->to('/login');
        }

        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);
        $user      = $userModel->find($uid);

        if ($user === null) {
            return redirect()->to('/dashboard')->with('error', 'Data pengguna tidak ditemukan.');
        }

        $rules = [
            'nama_lengkap' => 'required|max_length[150]',
            'no_hp'        => 'required|max_length[20]',
            'email'        => 'permit_empty|max_length[100]|valid_email',
        ];

        $passwordBaru = trim((string) $this->request->getPost('password'));
        if ($passwordBaru !== '') {
            $rules['password']         = 'required|min_length[8]|max_length[255]';
            $rules['password_confirm'] = 'required|matches[password]';
        }

        if (! $this->validate($rules)) {
            return redirect()->to('/profile')->withInput()->with(
                'error',
                implode(' ', $this->validator->getErrors())
            );
        }

        $emailRaw = trim((string) $this->request->getPost('email'));

        $data = [
            'nama_lengkap' => (string) $this->request->getPost('nama_lengkap'),
            'no_hp'        => (string) $this->request->getPost('no_hp'),
            'email'        => $emailRaw === '' ? null : $emailRaw,
        ];

        if ($passwordBaru !== '') {
            $plain = (string) $this->request->getPost('password');
            $data['password'] = password_hash($plain, PASSWORD_DEFAULT);
        }

        if (! $userModel->update($uid, $data)) {
            return redirect()->to('/profile')->withInput()->with(
                'error',
                implode(' ', $userModel->errors())
            );
        }

        $fresh = $userModel->find($uid);
        if ($fresh !== null) {
            session()->set([
                'username'     => $fresh->username,
                'nama_lengkap' => $fresh->nama_lengkap,
                'role'         => $fresh->role,
                'instansi_opd' => $fresh->instansi_opd,
                'no_hp'        => (string) ($fresh->no_hp ?? ''),
                'email'        => $fresh->email !== null && $fresh->email !== '' ? (string) $fresh->email : '',
            ]);
        }

        return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui.');
    }
}
