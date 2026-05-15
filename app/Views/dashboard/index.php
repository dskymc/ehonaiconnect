<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .ehonai-ping-card {
        background: linear-gradient(135deg, #f8fafc 0%, #fff 48%, #f0f7ff 100%);
        border: 1px solid rgba(12, 53, 105, 0.08) !important;
    }

    .ehonai-ping-card .ehonai-ping-accent {
        width: 4px;
        background: linear-gradient(180deg, #0d6efd 0%, #0c3569 100%);
        border-radius: 4px;
        flex-shrink: 0;
    }

    #ping-value {
        letter-spacing: -0.03em;
        transition: color 0.35s ease, transform 0.25s ease;
    }

    #ping-value.ping-value-updated {
        transform: scale(1.02);
    }

    #ping-status {
        transition: background-color 0.4s ease, color 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
    }

    #ping-status.ping-status-pulse {
        animation: ehonaiPingPulse 0.6s ease;
    }

    @keyframes ehonaiPingPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.35);
        }
        100% {
            box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$namaUser    = session()->get('nama_lengkap') ?? 'Pengguna';
$ticketStats = $ticketStats ?? [
    'total_bulan_ini'   => 0,
    'menunggu'          => 0,
    'sedang_dikerjakan' => 0,
    'selesai_bulan_ini' => 0,
];
?>
<div class="mb-4">
    <h1 class="h4 fw-semibold text-secondary mb-1">Dashboard</h1>
    <p class="lead text-dark mb-0">
        Selamat Datang di e-Honai Connect, <span class="fw-semibold text-primary"><?= esc($namaUser) ?></span>
    </p>
</div>

<div class="row g-3 g-md-4 mb-4">
    <div class="col-12">
        <div class="card ehonai-ping-card border-0 shadow-sm h-100">
            <div class="card-body p-4 p-md-4">
                <div class="d-flex gap-3 align-items-stretch flex-column flex-md-row">
                    <div class="ehonai-ping-accent d-none d-md-block" aria-hidden="true"></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-inline-flex">
                                    <i class="bi bi-activity fs-4" aria-hidden="true"></i>
                                </span>
                                <h2 class="h5 fw-semibold text-secondary mb-0">Monitoring Konektivitas Server</h2>
                            </div>
                            <span class="small text-muted text-nowrap">
                                <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>Pembaruan tiap 10 detik
                            </span>
                        </div>
                        <div class="row align-items-center g-3">
                            <div class="col-auto">
                                <h3 id="ping-value" class="display-6 fw-bold text-primary mb-0">... ms</h3>
                                <p class="small text-muted mb-0 mt-1">Estimasi latensi ke titik uji (TCP)</p>
                            </div>
                            <div class="col">
                                <div id="ping-status" class="alert alert-secondary border-0 shadow-sm mb-0 py-2 px-3 d-inline-block"
                                     role="status">
                                    Memuat status jaringan…
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-md-4 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 text-white bg-primary">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="text-uppercase small fw-semibold opacity-90">Total Tiket Bulan Ini</span>
                    <span class="rounded-3 bg-white bg-opacity-25 p-2"><i class="bi bi-calendar3 fs-5"></i></span>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $ticketStats['total_bulan_ini'] ?></p>
                <p class="small opacity-75 mb-0 mt-2">Berdasarkan tanggal pembuatan tiket</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 text-white bg-danger">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="text-uppercase small fw-semibold opacity-90">Menunggu Penanganan</span>
                    <span class="rounded-3 bg-white bg-opacity-25 p-2"><i class="bi bi-exclamation-octagon fs-5"></i></span>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $ticketStats['menunggu'] ?></p>
                <p class="small opacity-75 mb-0 mt-2">Status Baru &amp; Tertunda</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 bg-warning">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="text-uppercase small fw-semibold text-dark opacity-75">Sedang Dikerjakan</span>
                    <span class="rounded-3 bg-dark bg-opacity-10 text-dark p-2"><i class="bi bi-gear-wide-connected fs-5"></i></span>
                </div>
                <p class="display-6 fw-bold text-dark mb-0"><?= (int) $ticketStats['sedang_dikerjakan'] ?></p>
                <p class="small text-dark opacity-75 mb-0 mt-2">Status Diproses</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 text-white bg-success">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="text-uppercase small fw-semibold opacity-90">Selesai Bulan Ini</span>
                    <span class="rounded-3 bg-white bg-opacity-25 p-2"><i class="bi bi-patch-check fs-5"></i></span>
                </div>
                <p class="display-6 fw-bold mb-0"><?= (int) $ticketStats['selesai_bulan_ini'] ?></p>
                <p class="small opacity-75 mb-0 mt-2">Selesai / Ditutup (pembaruan bulan ini)</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const pingUrl = <?= json_encode(site_url('dashboard/checkPing')) ?>;
    const valueEl = document.getElementById('ping-value');
    const statusEl = document.getElementById('ping-status');
    if (!pingUrl || !valueEl || !statusEl) return;

    const alertClasses = ['alert-success', 'alert-warning', 'alert-danger', 'alert-secondary', 'alert-info', 'alert-primary'];

    function stripAlertClasses(el) {
        alertClasses.forEach(function (c) {
            el.classList.remove(c);
        });
    }

    function applyPingUI(data) {
        const ping = typeof data.ping === 'number' ? data.ping : parseInt(data.ping, 10) || 0;
        const text = typeof data.status_text === 'string' ? data.status_text : 'Status tidak diketahui';
        let color = typeof data.color === 'string' ? data.color : 'secondary';
        if (['success', 'warning', 'danger'].indexOf(color) === -1) {
            color = 'secondary';
        }

        valueEl.textContent = ping + ' ms';
        valueEl.classList.remove('text-success', 'text-warning', 'text-danger', 'text-primary');
        if (color === 'success') valueEl.classList.add('text-success');
        else if (color === 'warning') valueEl.classList.add('text-warning');
        else if (color === 'danger') valueEl.classList.add('text-danger');
        else valueEl.classList.add('text-primary');

        valueEl.classList.add('ping-value-updated');
        window.setTimeout(function () {
            valueEl.classList.remove('ping-value-updated');
        }, 250);

        stripAlertClasses(statusEl);
        statusEl.classList.add('alert-' + color);
        statusEl.textContent = text;
        statusEl.classList.remove('ping-status-pulse');
        void statusEl.offsetWidth;
        statusEl.classList.add('ping-status-pulse');
    }

    function fetchPing() {
        fetch(pingUrl, {credentials: 'same-origin', headers: {'Accept': 'application/json'}})
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(applyPingUI)
            .catch(function () {
                valueEl.textContent = '— ms';
                stripAlertClasses(statusEl);
                statusEl.classList.add('alert-danger');
                statusEl.textContent = 'Gagal memuat data pemantauan';
            });
    }

    window.addEventListener('load', function () {
        fetchPing();
        window.setInterval(fetchPing, 10000);
    });
})();
</script>
<?= $this->endSection() ?>
