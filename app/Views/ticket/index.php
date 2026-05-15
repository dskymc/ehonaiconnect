<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Tiket Laporan<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h4 fw-semibold text-secondary mb-0">Tiket Laporan</h1>
    <?php if (session()->get('role') === 'admin' || session()->get('role') === 'opd') : ?>
        <a href="<?= base_url('ticket/create') ?>" class="btn btn-primary btn-sm">Buat tiket</a>
    <?php endif; ?>
</div>

<?php if ($tickets === []) : ?>
    <p class="text-muted mb-0">Belum ada data tiket.</p>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>No. Tiket</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th>Pelapor</th>
                <th class="text-end text-nowrap">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tickets as $t) : ?>
                <tr>
                    <td><code class="small"><?= esc($t->nomor_tiket) ?></code></td>
                    <td><?= esc($t->judul_laporan) ?></td>
                    <td><?= esc($t->kategori) ?></td>
                    <td><?= view('partials/prioritas_badge', ['prioritas' => $t->prioritas ?? '']) ?></td>
                    <td><span class="badge text-bg-secondary"><?= esc($t->status) ?></span></td>
                    <td><?= esc($t->nama_pelapor ?? '—') ?></td>
                    <td class="text-end">
                        <a href="<?= base_url('ticket/show/' . (int) $t->id) ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
