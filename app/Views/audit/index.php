<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Log Sistem<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .text-monospace {
        font-family: var(--bs-font-monospace, ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="mb-4">
    <h1 class="h4 fw-semibold text-secondary mb-1">Log Sistem</h1>
    <p class="small text-muted mb-0">Riwayat aktivitas penting (audit trail) e-Honai Connect.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-nowrap px-3">Waktu</th>
                        <th scope="col" class="px-3">User</th>
                        <th scope="col" class="text-nowrap px-3">Aksi</th>
                        <th scope="col" class="px-3">Deskripsi</th>
                        <th scope="col" class="text-nowrap px-3">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs === []) : ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Belum ada catatan log.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($logs as $log) : ?>
                            <tr>
                                <td class="text-nowrap px-3"><?= esc($log->created_at ?? '') ?></td>
                                <td class="px-3"><?= esc($log->nama_user ?? '(tidak diketahui)') ?></td>
                                <td class="px-3"><span class="badge text-bg-secondary fw-normal"><?= esc($log->aksi ?? '') ?></span></td>
                                <td class="px-3"><?= esc($log->deskripsi ?? '') ?></td>
                                <td class="px-3 text-monospace text-nowrap"><?= esc($log->ip_address ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
