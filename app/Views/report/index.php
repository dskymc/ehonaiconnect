<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Laporan Bulanan<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper(['form', 'url']);
$printQuery = http_build_query([
    'bulan'   => $bulan,
    'tahun'   => $tahun,
    'kategori'=> $kategori,
]);
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="h4 fw-semibold text-secondary mb-1">Laporan Bulanan</h1>
        <p class="small text-muted mb-0">Rekap tiket berdasarkan bulan, tahun, dan kategori (tampilan cetak web).</p>
    </div>
    <a href="<?= esc(site_url('report/print?' . $printQuery), 'attr') ?>" target="_blank" rel="noopener noreferrer"
       class="btn btn-primary">
        <i class="bi bi-window me-1"></i> Cetak Web
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h2 class="h6 fw-semibold text-dark mb-3">Filter</h2>
        <form method="get" action="<?= esc(site_url('report'), 'attr') ?>" class="row g-3 align-items-end">
            <div class="col-6 col-md-3">
                <label for="bulan" class="form-label small fw-semibold">Bulan</label>
                <select name="bulan" id="bulan" class="form-select" required>
                    <?php
                    $bulanLabels = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                    ];
                    for ($b = 1; $b <= 12; $b++) :
                        ?>
                        <option value="<?= $b ?>" <?= (int) $bulan === $b ? 'selected' : '' ?>><?= esc($bulanLabels[$b]) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="tahun" class="form-label small fw-semibold">Tahun</label>
                <select name="tahun" id="tahun" class="form-select" required>
                    <?php
                    $yNow = (int) date('Y');
                    for ($y = $yNow + 1; $y >= $yNow - 10; $y--) :
                        ?>
                        <option value="<?= $y ?>" <?= (int) $tahun === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="kategori" class="form-label small fw-semibold">Kategori</label>
                <select name="kategori" id="kategori" class="form-select">
                    <option value="">— Semua kategori —</option>
                    <?php foreach ($kategoriList as $kat) : ?>
                        <option value="<?= esc($kat, 'attr') ?>" <?= $kategori === $kat ? 'selected' : '' ?>><?= esc($kat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Terapkan
                </button>
            </div>
        </form>
        <p class="small text-muted mb-0 mt-3">Periode aktif: <strong><?= esc($periodeLabel) ?></strong></p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h2 class="h6 mb-0 fw-semibold text-dark">Data tiket</h2>
        <p class="small text-muted mb-0 mt-1"><?= count($tickets) ?> entri</p>
    </div>
    <div class="card-body p-0">
        <?php if ($tickets === []) : ?>
            <p class="text-muted small mb-0 p-4">Tidak ada tiket pada filter ini.</p>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 small">
                    <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:2.5rem;">No</th>
                        <th>No. Tiket</th>
                        <th>Tanggal</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Pelapor</th>
                        <th>Instansi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $no = 1;
                    foreach ($tickets as $t) : ?>
                        <tr>
                            <td class="text-center text-secondary"><?= $no++ ?></td>
                            <td><code class="small"><?= esc($t->nomor_tiket) ?></code></td>
                            <td class="text-nowrap"><?= esc($t->created_at ?? '—') ?></td>
                            <td><?= esc($t->judul_laporan) ?></td>
                            <td><?= esc($t->kategori) ?></td>
                            <td><?= esc($t->prioritas) ?></td>
                            <td><?= esc($t->status) ?></td>
                            <td><?= esc($t->nama_pelapor ?? '—') ?></td>
                            <td><?= esc($t->instansi_pelapor ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
