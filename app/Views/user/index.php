<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Manajemen Pengguna<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper(['form', 'url']);
$showModalTambah = session()->getFlashdata('modal') === 'tambah';
$currentUserId   = (int) session()->get('id');
$listOpd         = config('Opd')->listOpd;
$roleOldTambah   = old('role', 'opd');
$isOpdOldTambah  = $roleOldTambah === 'opd';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 fw-semibold text-secondary mb-1">Manajemen Pengguna</h1>
        <p class="small text-muted mb-0">Kelola akun OPD, teknisi, dan admin.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna Baru
    </button>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
        <h2 class="h5 mb-0 fw-semibold text-dark">Daftar pengguna</h2>
        <p class="small text-muted mb-0 mt-2">Status verifikasi hanya berlaku untuk peran OPD.</p>
    </div>
    <div class="card-body p-0">
        <?php if ($users === []) : ?>
            <div class="p-4 p-md-5 text-center text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                <p class="mb-0">Belum ada pengguna terdaftar.</p>
            </div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 user-opd-table">
                    <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-center text-nowrap" style="width: 3.5rem;">No</th>
                        <th scope="col">Nama Lengkap</th>
                        <th scope="col">Instansi OPD</th>
                        <th scope="col">Username</th>
                        <th scope="col" class="text-center text-nowrap" style="width: 7rem;">Role</th>
                        <th scope="col" class="text-center text-nowrap" style="width: 11rem;">Status</th>
                        <th scope="col" class="text-center text-nowrap" style="width: 14rem;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $no = 1;
                    foreach ($users as $u) :
                        $aktif = (int) ($u->is_active ?? 0) === 1;
                        $role  = (string) ($u->role ?? 'opd');
                        $roleClass = match ($role) {
                            'admin'   => 'text-bg-danger',
                            'teknisi' => 'text-bg-info',
                            default   => 'text-bg-primary',
                        };
                        ?>
                        <tr>
                            <td class="text-center text-secondary"><?= esc((string) $no++) ?></td>
                            <td class="fw-medium"><?= esc($u->nama_lengkap) ?></td>
                            <td><?= esc($u->instansi_opd ?? '—') ?></td>
                            <td><code class="small bg-light px-2 py-1 rounded"><?= esc($u->username) ?></code></td>
                            <td class="text-center">
                                <span class="badge rounded-pill <?= esc($roleClass, 'attr') ?>"><?= esc(strtoupper($role)) ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($role === 'opd') : ?>
                                    <?php if ($aktif) : ?>
                                        <span class="badge rounded-pill text-bg-success px-3 py-2">Aktif</span>
                                    <?php else : ?>
                                        <span class="badge rounded-pill text-bg-warning text-dark px-3 py-2">Menunggu Verifikasi</span>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span class="badge rounded-pill text-bg-secondary px-3 py-2"><?= $aktif ? 'Aktif' : 'Nonaktif' ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary btn-reset-pw"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalResetPassword"
                                        data-user-id="<?= (int) $u->id ?>"
                                        data-user-name="<?= esc($u->nama_lengkap, 'attr') ?>">
                                    <i class="bi bi-key"></i> Edit/Reset Password
                                </button>
                                <?php if ($role === 'opd') : ?>
                                    <?php if ($aktif) : ?>
                                        <a href="<?= base_url('user/toggle-status/' . (int) $u->id) ?>"
                                           class="btn btn-sm btn-danger">Nonaktifkan</a>
                                    <?php else : ?>
                                        <a href="<?= base_url('user/toggle-status/' . (int) $u->id) ?>"
                                           class="btn btn-sm btn-success">Aktifkan</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ((int) $u->id !== $currentUserId) : ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-hapus-user"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalHapusUser"
                                            data-user-id="<?= (int) $u->id ?>"
                                            data-user-name="<?= esc($u->nama_lengkap, 'attr') ?>"
                                            data-username="<?= esc($u->username, 'attr') ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Tambah Pengguna -->
<div class="modal fade" id="modalTambahPengguna" tabindex="-1" aria-labelledby="modalTambahPenggunaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h2 class="modal-title h5 fw-semibold" id="modalTambahPenggunaLabel">Tambah Pengguna Baru</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <?= form_open('user/store', ['id' => 'formTambahPengguna']) ?>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nama_lengkap" class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required maxlength="150"
                               value="<?= esc(old('nama_lengkap', '')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="username" class="form-label fw-semibold small">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" class="form-control" required maxlength="100" autocomplete="off"
                               value="<?= esc(old('username', '')) ?>">
                    </div>
                    <div class="col-md-6" id="wrapInstansiTambah">
                        <label for="instansi_opd_select" class="form-label fw-semibold small">Instansi OPD <span class="text-danger">*</span></label>
                        <select name="instansi_opd" id="instansi_opd_select" class="form-select"
                            <?= $isOpdOldTambah ? 'required' : 'disabled' ?>>
                            <option value="">--- Pilih OPD ---</option>
                            <?php foreach ($listOpd as $opd) : ?>
                                <option value="<?= esc($opd, 'attr') ?>" <?= old('instansi_opd') === $opd ? 'selected' : '' ?>><?= esc($opd) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="role_tambah" class="form-label fw-semibold small">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role_tambah" class="form-select" required>
                            <?php foreach (['opd' => 'OPD', 'teknisi' => 'Teknisi', 'admin' => 'Admin'] as $val => $lbl) : ?>
                                <option value="<?= esc($val, 'attr') ?>" <?= old('role', 'opd') === $val ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <p class="form-text small mb-0 text-primary <?= $isOpdOldTambah ? 'd-none' : '' ?>" id="hintInstansiInternal">
                            <i class="bi bi-info-circle me-1"></i>Admin &amp; Teknisi: instansi otomatis <strong><?= esc(config('Opd')->instansiInternal) ?></strong>.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label for="password_tambah" class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password_tambah" class="form-control" required minlength="8" maxlength="255" autocomplete="new-password">
                        <div class="form-text">Minimal 8 karakter.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Modal: Reset Password -->
<div class="modal fade" id="modalResetPassword" tabindex="-1" aria-labelledby="modalResetPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h2 class="modal-title h5 fw-semibold" id="modalResetPasswordLabel">Edit / Reset Password</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="post" action="" id="formResetPassword" autocomplete="off">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Pengguna: <strong id="resetPwUserLabel">—</strong></p>
                    <div class="mb-3">
                        <label for="password_reset" class="form-label fw-semibold small">Password baru <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password_reset" class="form-control" required minlength="8" maxlength="255" autocomplete="new-password">
                    </div>
                    <div class="mb-0">
                        <label for="password_confirm" class="form-label fw-semibold small">Konfirmasi password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control" required minlength="8" maxlength="255" autocomplete="new-password">
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Hapus -->
<div class="modal fade" id="modalHapusUser" tabindex="-1" aria-labelledby="modalHapusUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-danger text-white">
                <h2 class="modal-title h5 fw-semibold" id="modalHapusUserLabel">Hapus pengguna</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="post" action="" id="formHapusUser">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p class="mb-0">Yakin ingin menghapus permanen akun berikut?</p>
                    <p class="fw-semibold mb-0 mt-2"><span id="hapusUserNama">—</span> (<code id="hapusUserUsername">—</code>)</p>
                    <p class="small text-danger mb-0 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus permanen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .user-opd-table thead th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 600;
        color: #495057;
        vertical-align: middle;
    }
    .user-opd-table tbody td {
        vertical-align: middle;
        padding-top: 0.65rem;
        padding-bottom: 0.65rem;
    }
    .user-opd-table tbody tr:hover {
        background-color: rgba(13, 110, 110, 0.04);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const baseReset = <?= json_encode(site_url('user/update-password')) ?>;
    const baseHapus = <?= json_encode(site_url('user/delete')) ?>;
    const wrapInstansi = document.getElementById('wrapInstansiTambah');
    const roleTambah = document.getElementById('role_tambah');
    const instansiSelect = document.getElementById('instansi_opd_select');
    const hintInternal = document.getElementById('hintInstansiInternal');

    function syncInstansiVisibility() {
        if (!wrapInstansi || !roleTambah || !instansiSelect) return;
        const isOpd = roleTambah.value === 'opd';
        wrapInstansi.classList.toggle('d-none', !isOpd);
        if (hintInternal) {
            hintInternal.classList.toggle('d-none', isOpd);
        }
        instansiSelect.disabled = !isOpd;
        instansiSelect.required = isOpd;
        if (!isOpd) {
            instansiSelect.value = '';
        }
    }

    if (roleTambah) {
        roleTambah.addEventListener('change', syncInstansiVisibility);
        syncInstansiVisibility();
    }

    const modalReset = document.getElementById('modalResetPassword');
    if (modalReset) {
        modalReset.addEventListener('show.bs.modal', function (ev) {
            const btn = ev.relatedTarget;
            if (!btn || !btn.getAttribute) return;
            const id = btn.getAttribute('data-user-id');
            const nama = btn.getAttribute('data-user-name') || '—';
            const form = document.getElementById('formResetPassword');
            const label = document.getElementById('resetPwUserLabel');
            if (form) form.setAttribute('action', baseReset + '/' + encodeURIComponent(id));
            if (label) label.textContent = nama;
        });
        modalReset.addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('formResetPassword');
            if (form) form.reset();
        });
    }

    const modalHapus = document.getElementById('modalHapusUser');
    if (modalHapus) {
        modalHapus.addEventListener('show.bs.modal', function (ev) {
            const btn = ev.relatedTarget;
            if (!btn || !btn.getAttribute) return;
            const id = btn.getAttribute('data-user-id');
            const nama = btn.getAttribute('data-user-name') || '—';
            const uname = btn.getAttribute('data-username') || '—';
            const form = document.getElementById('formHapusUser');
            const elNama = document.getElementById('hapusUserNama');
            const elUser = document.getElementById('hapusUserUsername');
            if (form) form.setAttribute('action', baseHapus + '/' + encodeURIComponent(id));
            if (elNama) elNama.textContent = nama;
            if (elUser) elUser.textContent = uname;
        });
    }

    <?php if (! empty($showModalTambah)) : ?>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('modalTambahPengguna');
        if (el && typeof bootstrap !== 'undefined') {
            const m = bootstrap.Modal.getOrCreateInstance(el);
            m.show();
            syncInstansiVisibility();
        }
    });
    <?php endif; ?>
})();
</script>
<?= $this->endSection() ?>
