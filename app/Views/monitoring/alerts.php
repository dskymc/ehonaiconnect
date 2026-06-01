<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Alert Monitoring<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h1 class="h4 fw-semibold text-secondary mb-1">Riwayat Alert</h1>
        <p class="text-muted small mb-0">Event webhook e-Honai &amp; alert aktif LibreNMS</p>
    </div>
    <a href="<?= site_url('monitoring') ?>" class="btn btn-outline-secondary btn-sm">Daftar Perangkat</a>
</div>

<?php if ($liveAlerts !== []) : ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Alert Aktif (LibreNMS)</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>ID</th><th>Hostname</th><th>Rule</th><th>Severity</th><th>Waktu</th></tr>
            </thead>
            <tbody>
            <?php foreach ($liveAlerts as $a) : ?>
                <tr>
                    <td><?= esc((string) ($a['id'] ?? '')) ?></td>
                    <td><?= esc((string) ($a['hostname'] ?? $a['sysName'] ?? '-')) ?></td>
                    <td><?= esc((string) ($a['name'] ?? $a['rule'] ?? '-')) ?></td>
                    <td><?= esc((string) ($a['severity'] ?? '-')) ?></td>
                    <td><?= esc((string) ($a['timestamp'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Event Webhook (e-Honai)</div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Waktu</th>
                    <th>Hostname</th>
                    <th>State</th>
                    <th>Severity</th>
                    <th>Judul</th>
                    <th>Tiket</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($events === []) : ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada event webhook.</td></tr>
            <?php else : ?>
                <?php foreach ($events as $ev) : ?>
                    <tr>
                        <td class="text-nowrap small"><?= esc($ev->created_at ?? '') ?></td>
                        <td><?= esc($ev->hostname ?? '-') ?></td>
                        <td><span class="badge <?= ($ev->state ?? '') === 'ok' ? 'bg-success' : 'bg-danger' ?>"><?= esc($ev->state ?? '-') ?></span></td>
                        <td><?= esc($ev->severity ?? '-') ?></td>
                        <td class="small"><?= esc($ev->title ?? '-') ?></td>
                        <td>
                            <?php if ((int) ($ev->ticket_id ?? 0) > 0) : ?>
                                <a href="<?= site_url('ticket/show/' . (int) $ev->ticket_id) ?>">#<?= (int) $ev->ticket_id ?></a>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
