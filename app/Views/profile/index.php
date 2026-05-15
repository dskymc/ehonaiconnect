<?php // Coded by DskyMC ?>
<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Profil Pengguna<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php helper(['form', 'url']); ?>
<div class="mb-4">
    <h1 class="h4 fw-semibold text-secondary mb-1">Profil Pengguna</h1>
    <p class="small text-muted mb-0">Perbarui nama tampilan dan kata sandi akun Anda.</p>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Informasi akun</h2>
                <dl class="row small mb-0">
                    <dt class="col-sm-4 text-muted">Username</dt>
                    <dd class="col-sm-8 mb-2"><code class="small"><?= esc($user->username) ?></code></dd>
                    <dt class="col-sm-4 text-muted">Role</dt>
                    <dd class="col-sm-8 mb-2"><span class="badge text-bg-secondary"><?= esc(strtoupper((string) $user->role)) ?></span></dd>
                    <dt class="col-sm-4 text-muted">Instansi</dt>
                    <dd class="col-sm-8 mb-2"><?= esc($user->instansi_opd ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted">Nama</dt>
                    <dd class="col-sm-8 mb-2 fw-semibold"><?= esc($user->nama_lengkap) ?></dd>
                    <dt class="col-sm-4 text-muted">No. HP</dt>
                    <dd class="col-sm-8 mb-2"><?= trim((string) ($user->no_hp ?? '')) !== '' ? esc((string) $user->no_hp) : '—' ?></dd>
                    <dt class="col-sm-4 text-muted">Email</dt>
                    <dd class="col-sm-8 mb-0"><?= ! empty($user->email) ? esc((string) $user->email) : '—' ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Ubah profil</h2>
                <?= form_open('profile/update', ['class' => 'needs-validation']) ?>
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required maxlength="150"
                           value="<?= esc(old('nama_lengkap', $user->nama_lengkap)) ?>">
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="no_hp" class="form-label fw-semibold small">Nomor WhatsApp (Aktif) <span class="text-danger">*</span></label>
                        <input type="text" name="no_hp" id="no_hp" class="form-control" required maxlength="20" inputmode="tel"
                               value="<?= esc(old('no_hp', (string) ($user->no_hp ?? ''))) ?>" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold small">Alamat Email</label>
                        <input type="email" name="email" id="email" class="form-control" maxlength="100" autocomplete="email"
                               value="<?= esc(old('email', (string) ($user->email ?? ''))) ?>" placeholder="opsional">
                        <div class="form-text small">Email boleh dikosongkan.</div>
                    </div>
                </div>
                <hr class="my-4">
                <p class="small text-muted mb-3">Kosongkan kedua kolom di bawah jika Anda tidak ingin mengganti password.</p>
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold small">Password baru</label>
                    <input type="password" name="password" id="password" class="form-control" minlength="8" maxlength="255" autocomplete="new-password">
                    <div class="form-text">Minimal 8 karakter jika diisi.</div>
                </div>
                <div class="mb-4">
                    <label for="password_confirm" class="form-label fw-semibold small">Konfirmasi password baru</label>
                    <input type="password" name="password_confirm" id="password_confirm" class="form-control" minlength="8" maxlength="255" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Simpan perubahan
                </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
