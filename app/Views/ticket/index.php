<?php // Coded by DskyMC ?>
<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Tiket Laporan<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    @keyframes blink {
        50% {
            opacity: 0;
        }
    }

    .blink {
        animation: blink 1s linear infinite;
    }
</style>
<?= $this->endSection() ?>

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
                <th class="text-nowrap">No. HP</th>
                <th>Email</th>
                <th class="text-end text-nowrap">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tickets as $t) : ?>
                <?php
                $createdTs = strtotime((string) ($t->created_at ?? ''));
                if ($createdTs === false) {
                    $createdTs = time();
                }
                $hoursSinceCreated = (time() - $createdTs) / 3600;
                $isSlaOverdueBaru  = ($t->status === 'Baru' && $hoursSinceCreated > 24);
                $hpPel = trim((string) ($t->no_hp_pelapor ?? ''));
                if ($hpPel === '') {
                    $hpPel = trim((string) ($t->pelapor_user_no_hp ?? ''));
                }
                $emPel = trim((string) ($t->email_pelapor ?? ''));
                if ($emPel === '') {
                    $emPel = trim((string) ($t->pelapor_user_email ?? ''));
                }
                $waDigits = preg_replace('/\D/', '', $hpPel);
                if ($waDigits !== '' && str_starts_with($waDigits, '0')) {
                    $waDigits = '62' . substr($waDigits, 1);
                }
                ?>
                <tr>
                    <td><code class="small"><?= esc($t->nomor_tiket) ?></code></td>
                    <td><?= esc($t->judul_laporan) ?></td>
                    <td><?= esc($t->kategori) ?></td>
                    <td><?= view('partials/prioritas_badge', ['prioritas' => $t->prioritas ?? '']) ?></td>
                    <td class="text-nowrap">
                        <span class="badge text-bg-secondary"><?= esc($t->status) ?></span>
                        <?php if ($isSlaOverdueBaru) : ?>
                            <span class="badge bg-danger blink ms-1">OVERDUE</span>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($t->nama_pelapor ?? '—') ?></td>
                    <td class="small text-nowrap">
                        <?php if ($hpPel !== '') : ?>
                            <span><?= esc($hpPel) ?></span>
                            <?php if ($waDigits !== '') : ?>
                                <a href="https://wa.me/<?= esc($waDigits, 'attr') ?>" target="_blank" rel="noopener noreferrer"
                                   class="btn btn-sm btn-success ms-1 py-0 px-2 align-baseline" title="Chat WhatsApp" aria-label="WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            <?php endif; ?>
                        <?php else : ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td class="small text-break"><?= $emPel !== '' ? esc($emPel) : '—' ?></td>
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
