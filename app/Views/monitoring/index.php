<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Monitoring Perangkat<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
/** @var \App\Libraries\LibreNMSService $librenms */
$librenms = $librenms ?? \Config\Services::librenms();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h1 class="h4 fw-semibold text-secondary mb-1">Monitoring Perangkat</h1>
        <p class="text-muted small mb-0">Data dari LibreNMS<?= $useApi ? ' (langsung API)' : ' (cache lokal)' ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= site_url('monitoring/alerts') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-bell me-1"></i>Riwayat Alert
        </a>
        <?php if ($librenms->isConfigured()) : ?>
            <a href="<?= esc($librenms->getBaseUrl()) ?>" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">
                <i class="bi bi-box-arrow-up-right me-1"></i>LibreNMS
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (! $librenms->isConfigured()) : ?>
    <div class="alert alert-warning">LibreNMS belum dikonfigurasi. Isi <code>LIBRENMS_URL</code> dan <code>LIBRENMS_API_TOKEN</code> di file <code>.env</code>.</div>
<?php endif; ?>

<form method="get" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="search" name="q" class="form-control form-control-sm" placeholder="Cari hostname, IP, lokasi…" value="<?= esc($search) ?>">
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Semua</option>
            <option value="up" <?= $filter === 'up' ? 'selected' : '' ?>>Up</option>
            <option value="down" <?= $filter === 'down' ? 'selected' : '' ?>>Down</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Hostname</th>
                <th>IP</th>
                <th>Lokasi</th>
                <th>OS</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $rows = $useApi ? $apiDevices : array_map(static function ($d) use ($librenms) {
            return [
                'hostname'  => $d->hostname,
                'display'   => $d->display_name ?? $d->hostname,
                'ip'        => $d->ip ?? '',
                'location'  => $d->location ?? '',
                'os'        => $d->os ?? '',
                'status'    => (int) $d->status,
                'url'       => $librenms->deviceUrl((string) $d->hostname),
            ];
        }, $devices);
        ?>
        <?php if ($rows === []) : ?>
            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada perangkat. Jalankan <code>php spark monitoring:sync</code> atau periksa koneksi API.</td></tr>
        <?php else : ?>
            <?php foreach ($rows as $row) : ?>
                <?php
                $hostname = (string) ($row['hostname'] ?? '');
                $isUp     = (int) ($row['status'] ?? 0) === 1;
                ?>
                <tr>
                    <td class="fw-semibold"><?= esc($row['display'] ?? $hostname) ?></td>
                    <td><?= esc($row['ip'] ?? '-') ?></td>
                    <td><?= esc($row['location'] ?? '-') ?></td>
                    <td><?= esc($row['os'] ?? '-') ?></td>
                    <td>
                        <span class="badge <?= $isUp ? 'bg-success' : 'bg-danger' ?>"><?= $isUp ? 'Up' : 'Down' ?></span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="<?= site_url('monitoring/device/' . rawurlencode($hostname)) ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                        <?php if (! empty($row['url'])) : ?>
                            <a href="<?= esc($row['url']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">LibreNMS</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
