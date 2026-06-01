<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Perangkat — <?= esc($hostname) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
/** @var \App\Libraries\LibreNMSService $librenms */
$isUp = $cached !== null ? (int) $cached->status === 1 : ((int) ($detail['status'] ?? 0) === 1);
?>
<div class="mb-4">
    <a href="<?= site_url('monitoring') ?>" class="btn btn-link btn-sm text-decoration-none px-0 mb-2">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke daftar
    </a>
    <h1 class="h4 fw-semibold text-secondary mb-1"><?= esc($hostname) ?></h1>
    <span class="badge <?= $isUp ? 'bg-success' : 'bg-danger' ?>"><?= $isUp ? 'Up' : 'Down' ?></span>
    <?php if ($openTicket !== null) : ?>
        <a href="<?= site_url('ticket/show/' . (int) $openTicket->id) ?>" class="badge bg-warning text-dark text-decoration-none ms-1">
            Tiket terbuka: <?= esc($openTicket->nomor_tiket) ?>
        </a>
    <?php endif; ?>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Informasi Cache</div>
            <div class="card-body small">
                <?php if ($cached === null) : ?>
                    <p class="text-muted mb-0">Belum ada di cache lokal. Jalankan <code>php spark monitoring:sync</code>.</p>
                <?php else : ?>
                    <dl class="row mb-0">
                        <dt class="col-4">Display</dt><dd class="col-8"><?= esc($cached->display_name ?? '-') ?></dd>
                        <dt class="col-4">IP</dt><dd class="col-8"><?= esc($cached->ip ?? '-') ?></dd>
                        <dt class="col-4">Lokasi</dt><dd class="col-8"><?= esc($cached->location ?? '-') ?></dd>
                        <dt class="col-4">OS</dt><dd class="col-8"><?= esc($cached->os ?? '-') ?></dd>
                        <dt class="col-4">Hardware</dt><dd class="col-8"><?= esc($cached->hardware ?? '-') ?></dd>
                        <dt class="col-4">Sync</dt><dd class="col-8"><?= esc($cached->synced_at ?? '-') ?></dd>
                    </dl>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Data LibreNMS (API)</div>
            <div class="card-body small">
                <?php if ($detail === null) : ?>
                    <p class="text-muted mb-0">Detail API tidak tersedia.</p>
                <?php else : ?>
                    <dl class="row mb-0">
                        <dt class="col-4">SysName</dt><dd class="col-8"><?= esc($detail['sysName'] ?? '-') ?></dd>
                        <dt class="col-4">Version</dt><dd class="col-8"><?= esc($detail['version'] ?? '-') ?></dd>
                        <dt class="col-4">Hardware</dt><dd class="col-8"><?= esc($detail['hardware'] ?? '-') ?></dd>
                        <dt class="col-4">Uptime</dt><dd class="col-8"><?= esc($detail['uptime'] ?? '-') ?></dd>
                    </dl>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($librenms->isConfigured()) : ?>
    <div class="mt-3">
        <a href="<?= esc($librenms->deviceUrl($hostname)) ?>" class="btn btn-primary" target="_blank" rel="noopener">
            <i class="bi bi-box-arrow-up-right me-1"></i>Buka di LibreNMS
        </a>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
