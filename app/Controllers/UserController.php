<?php
// Coded by DskyMC

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class UserController extends BaseController
{
    protected $helpers = ['form', 'url'];

    protected function ensureAdmin(): ?RedirectResponse
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with(
                'error',
                'Anda tidak memiliki akses ke halaman ini.'
            );
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function listOpdConfig(): array
    {
        return config('Opd')->listOpd;
    }

    protected function instansiInternalDefault(): string
    {
        return (string) config('Opd')->instansiInternal;
    }

    public function index(): RedirectResponse|string
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);
        $users     = $userModel->orderBy('id', 'ASC')->findAll();

        return view('user/index', ['users' => $users]);
    }

    public function store(): RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $role = (string) $this->request->getPost('role');

        $rules = [
            'nama_lengkap' => 'required|max_length[150]',
            'username'     => 'required|max_length[100]|is_unique[users.username]',
            'password'     => 'required|min_length[8]|max_length[255]',
            'role'         => 'required|in_list[opd,teknisi,admin]',
        ];

        if ($role === 'opd') {
            $rules['instansi_opd'] = 'required|max_length[150]';
        }

        if (! $this->validate($rules)) {
            return redirect()->to('/user')->withInput()->with(
                'error',
                implode(' ', $this->validator->getErrors())
            )->with('modal', 'tambah');
        }

        $listOpd = $this->listOpdConfig();

        if ($role === 'opd') {
            $instansi = (string) $this->request->getPost('instansi_opd');
            if (! in_array($instansi, $listOpd, true)) {
                return redirect()->to('/user')->withInput()->with(
                    'error',
                    'Instansi OPD tidak valid. Pilih entri dari daftar resmi.'
                )->with('modal', 'tambah');
            }
        } else {
            $instansi = $this->instansiInternalDefault();
        }

        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);

        $inserted = $userModel->insert([
            'username'     => (string) $this->request->getPost('username'),
            'password'     => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'nama_lengkap' => (string) $this->request->getPost('nama_lengkap'),
            'instansi_opd' => $instansi,
            'role'         => $role,
            'is_active'    => 1,
        ]);

        if ($inserted === false) {
            return redirect()->to('/user')->withInput()->with(
                'error',
                implode(' ', $userModel->errors())
            )->with('modal', 'tambah');
        }

        return redirect()->to('/user')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function updatePassword(int $id): RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $rules = [
            'password'         => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/user')->with(
                'error',
                implode(' ', $this->validator->getErrors())
            );
        }

        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);
        $user      = $userModel->find($id);

        if ($user === null) {
            return redirect()->to('/user')->with('error', 'Pengguna tidak ditemukan.');
        }

        $userModel->update($id, [
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/user')->with('success', 'Password pengguna berhasil direset.');
    }

    public function delete(int $id): RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $sessionId = (int) session()->get('id');
        if ($id === $sessionId) {
            return redirect()->to('/user')->with('error', 'Tidak dapat menghapus akun yang sedang digunakan.');
        }

        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);
        $user      = $userModel->find($id);

        if ($user === null) {
            return redirect()->to('/user')->with('error', 'Pengguna tidak ditemukan.');
        }

        if ((string) $user->role === 'admin') {
            $adminCount = model(UserModel::class)->where('role', 'admin')->countAllResults();
            if ($adminCount <= 1) {
                return redirect()->to('/user')->with('error', 'Tidak dapat menghapus satu-satunya akun admin.');
            }
        }

        $userModel->delete($id);

        return redirect()->to('/user')->with('success', 'Pengguna berhasil dihapus secara permanen.');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);
        $user      = $userModel->find($id);

        if ($user === null || $user->role !== 'opd') {
            return redirect()->to('/user')->with('error', 'Data pengguna OPD tidak ditemukan.');
        }

        $current = (int) ($user->is_active ?? 0);
        $new     = $current === 1 ? 0 : 1;

        $userModel->update($id, ['is_active' => $new]);

        return redirect()->to('/user')->with('success', 'Status akun pengguna berhasil diperbarui.');
    }
}
