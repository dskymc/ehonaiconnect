<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Buat Tiket<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$hasOpdUsers = session()->get('role') === 'admin' && $opdUsers !== [];
$oldMode     = old('pelapor_mode', $hasOpdUsers ? 'opd_terdaftar' : 'input_manual');
?>
<h1 class="h4 fw-semibold text-secondary mb-3">Buat Tiket Baru</h1>

<?= form_open_multipart('ticket/store', ['class' => 'needs-validation', 'id' => 'formBuatTiket']) ?>
<?php if (session()->get('role') === 'admin') : ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h6 fw-semibold text-dark mb-3">Pelapor</h2>
            <?php if ($hasOpdUsers) : ?>
                <div class="mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="pelapor_mode" id="pelapor_mode_opd" value="opd_terdaftar"
                               required <?= $oldMode !== 'input_manual' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="pelapor_mode_opd">Pengguna OPD terdaftar</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="pelapor_mode" id="pelapor_mode_manual" value="input_manual"
                               required <?= $oldMode === 'input_manual' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="pelapor_mode_manual">Input manual (nama &amp; instansi)</label>
                    </div>
                </div>
                <div id="wrapPelaporOpd" class="mb-0">
                    <label for="user_id" class="form-label small fw-semibold">Pilih akun OPD <span class="text-danger">*</span></label>
                    <select name="user_id" id="user_id" class="form-select">
                        <option value="">— Pilih pengguna OPD —</option>
                        <?php foreach ($opdUsers as $u) : ?>
                            <option value="<?= (int) $u->id ?>" <?= (string) old('user_id') === (string) $u->id ? 'selected' : '' ?>>
                                <?= esc($u->nama_lengkap) ?> (<?= esc($u->username) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="wrapPelaporManual" class="mb-0 d-none">
                    <div class="mb-3">
                        <label for="pelapor_nama" class="form-label small fw-semibold">Nama pelapor <span class="text-danger">*</span></label>
                        <input type="text" name="pelapor_nama" id="pelapor_nama" class="form-control" maxlength="150"
                               value="<?= esc(old('pelapor_nama', '')) ?>" placeholder="Nama lengkap pelapor">
                    </div>
                    <div class="mb-0">
                        <label for="pelapor_instansi" class="form-label small fw-semibold">Instansi / OPD <span class="text-danger">*</span></label>
                        <input type="text" name="pelapor_instansi" id="pelapor_instansi" class="form-control" maxlength="200"
                               value="<?= esc(old('pelapor_instansi', '')) ?>" placeholder="Nama instansi atau unit">
                    </div>
                </div>
            <?php else : ?>
                <p class="small text-muted mb-3">Belum ada pengguna OPD terdaftar. Isi data pelapor secara manual.</p>
                <input type="hidden" name="pelapor_mode" value="input_manual">
                <div class="mb-3">
                    <label for="pelapor_nama" class="form-label small fw-semibold">Nama pelapor <span class="text-danger">*</span></label>
                    <input type="text" name="pelapor_nama" id="pelapor_nama" class="form-control" required maxlength="150"
                           value="<?= esc(old('pelapor_nama', '')) ?>" placeholder="Nama lengkap pelapor">
                </div>
                <div class="mb-0">
                    <label for="pelapor_instansi" class="form-label small fw-semibold">Instansi / OPD <span class="text-danger">*</span></label>
                    <input type="text" name="pelapor_instansi" id="pelapor_instansi" class="form-control" required maxlength="200"
                           value="<?= esc(old('pelapor_instansi', '')) ?>" placeholder="Nama instansi atau unit">
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="mb-3">
    <label for="judul_laporan" class="form-label fw-semibold">Judul Laporan <span class="text-danger">*</span></label>
    <input type="text" name="judul_laporan" id="judul_laporan" class="form-control" required maxlength="255"
           value="<?= esc(old('judul_laporan', '')) ?>">
</div>
<div class="mb-3">
    <label for="kategori" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
    <input type="text" name="kategori" id="kategori" class="form-control" required maxlength="100"
           value="<?= esc(old('kategori', '')) ?>" placeholder="Contoh: Jaringan, Aplikasi, Perangkat">
</div>
<div class="mb-3">
    <label for="prioritas" class="form-label fw-semibold">Prioritas <span class="text-danger">*</span></label>
    <select name="prioritas" id="prioritas" class="form-select" required>
        <?php foreach (['Low' => 'Rendah', 'Medium' => 'Sedang', 'High' => 'Tinggi'] as $val => $lbl) : ?>
            <option value="<?= esc($val, 'attr') ?>" <?= old('prioritas', 'Medium') === $val ? 'selected' : '' ?>><?= esc($lbl) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="mb-3">
    <label for="deskripsi" class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5" required
              placeholder="Jelaskan permasalahan secara ringkas dan jelas."><?= esc(old('deskripsi', '')) ?></textarea>
</div>
<div class="mb-3">
    <label for="bukti_foto" class="form-label fw-semibold">Lampiran bukti (opsional)</label>
    <input type="file" name="bukti_foto" id="bukti_foto" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf">
    <div class="form-text">Maks. 2 MB. Format: JPG, PNG, WEBP, atau PDF.</div>
</div>
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">Simpan Tiket</button>
    <a href="<?= base_url('ticket') ?>" class="btn btn-outline-secondary">Batal</a>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>

<?php if (session()->get('role') === 'admin' && $hasOpdUsers) : ?>
<?= $this->section('scripts') ?>
<script>
(function () {
    const rOpd = document.getElementById('pelapor_mode_opd');
    const rMan = document.getElementById('pelapor_mode_manual');
    const wrapOpd = document.getElementById('wrapPelaporOpd');
    const wrapMan = document.getElementById('wrapPelaporManual');
    const selUser = document.getElementById('user_id');
    const inpNama = document.getElementById('pelapor_nama');
    const inpIns = document.getElementById('pelapor_instansi');

    function sync() {
        const manual = rMan && rMan.checked;
        if (!wrapOpd || !wrapMan) return;
        wrapOpd.classList.toggle('d-none', manual);
        wrapMan.classList.toggle('d-none', !manual);
        if (selUser) {
            selUser.disabled = manual;
            selUser.required = !manual;
            if (manual) selUser.value = '';
        }
        if (inpNama) {
            inpNama.disabled = !manual;
            inpNama.required = manual;
            if (!manual) inpNama.value = '';
        }
        if (inpIns) {
            inpIns.disabled = !manual;
            inpIns.required = manual;
            if (!manual) inpIns.value = '';
        }
    }

    if (rOpd) rOpd.addEventListener('change', sync);
    if (rMan) rMan.addEventListener('change', sync);
    sync();
})();
</script>
<?= $this->endSection() ?>
<?php endif; ?>
