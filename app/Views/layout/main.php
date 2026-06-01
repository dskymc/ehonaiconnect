<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php
        $ehonaiLayoutTitle = trim($this->renderSection('title', true));
        echo esc($ehonaiLayoutTitle !== '' ? $ehonaiLayoutTitle . ' · e-Honai Connect' : 'e-Honai Connect');
        ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo-papua-pegunungan.png') ?>">
    <style>
        :root {
            --ehonai-nav: #0c3569;
            --ehonai-nav-deep: #082544;
            --ehonai-nav-hover: rgba(255, 255, 255, 0.12);
            --ehonai-sidebar-active: #0d6efd;
            --ehonai-sidebar-bg: #f8fafc;
        }

        body.ehonai-layout {
            font-family: "Source Sans 3", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #eef2f6;
            min-height: 100vh;
        }

        .navbar-ehonai {
            background: linear-gradient(90deg, var(--ehonai-nav-deep) 0%, var(--ehonai-nav) 45%, #0d4a7a 100%);
            box-shadow: 0 0.125rem 0.75rem rgba(8, 37, 68, 0.25);
            padding-top: 0.55rem;
            padding-bottom: 0.55rem;
            z-index: 1030;
        }

        .navbar-ehonai .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.02em;
            font-size: 1.15rem;
            color: #fff;
        }

        .navbar-ehonai .navbar-brand:focus-visible {
            outline: 2px solid rgba(255, 255, 255, 0.45);
            outline-offset: 2px;
        }

        .navbar-ehonai .brand-mark {
            width: 2rem;
            height: 2rem;
            object-fit: contain;
            border-radius: 0.35rem;
            background: rgba(255, 255, 255, 0.95);
            padding: 2px;
        }

        .navbar-ehonai .nav-user {
            font-size: 0.9rem;
            font-weight: 500;
            opacity: 0.95;
        }

        .navbar-ehonai .nav-user-link {
            color: inherit;
            text-decoration: none;
            border-radius: 0.35rem;
            padding: 0.15rem 0.35rem;
            margin: -0.15rem -0.35rem;
            transition: background-color 0.15s ease, opacity 0.15s ease;
        }

        .navbar-ehonai .nav-user-link:hover,
        .navbar-ehonai .nav-user-link:focus-visible {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            opacity: 1;
        }

        .navbar-ehonai .nav-notif-btn {
            color: #fff;
            text-decoration: none;
            border-radius: 0.35rem;
            padding: 0.35rem 0.45rem;
            line-height: 1;
            border: 0;
            opacity: 0.95;
            transition: background-color 0.15s ease, opacity 0.15s ease;
        }

        .navbar-ehonai .nav-notif-btn:hover,
        .navbar-ehonai .nav-notif-btn:focus-visible {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            opacity: 1;
        }

        .navbar-ehonai .nav-notif-btn .badge-notif {
            font-size: 0.65rem;
            min-width: 1.1rem;
            padding: 0.2em 0.4em;
        }

        .navbar-ehonai .dropdown-menu-notif .notif-item-msg {
            font-size: 0.8125rem;
            line-height: 1.35;
        }

        .navbar-ehonai .btn-logout {
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 0.4rem;
            padding: 0.35rem 0.85rem;
        }

        .layout-shell {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 3.25rem);
        }

        #sidebarMenu.offcanvas-md {
            --bs-offcanvas-width: min(280px, 88vw);
            background: var(--ehonai-sidebar-bg);
        }

        @media (min-width: 768px) {
            #sidebarMenu.offcanvas-md {
                --bs-offcanvas-width: min(280px, 88vw);
                position: relative;
                top: auto;
                transform: none !important;
                visibility: visible !important;
                flex: 0 0 min(280px, 88vw);
                width: min(280px, 88vw);
                max-width: 280px;
                align-self: stretch;
                min-height: calc(100vh - 3.25rem);
                max-height: none;
                overflow-y: auto;
                overflow-x: hidden;
                border-right: 1px solid rgba(12, 53, 105, 0.08);
                box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.6);
                z-index: 1;
            }
        }

        .sidebar-nav .nav-link {
            color: #334155;
            font-weight: 500;
            padding: 0.65rem 1.1rem;
            border-radius: 0.45rem;
            margin: 0.15rem 0.65rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .sidebar-nav .nav-link:hover {
            background: rgba(13, 110, 253, 0.08);
            color: var(--ehonai-sidebar-active);
        }

        .sidebar-nav .nav-link.active {
            background: rgba(13, 110, 253, 0.12);
            color: var(--ehonai-nav);
            font-weight: 600;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.1rem;
            opacity: 0.85;
        }

        .main-content {
            flex: 1;
            min-width: 0;
            padding: 1.35rem 1.25rem 2rem;
        }

        @media (min-width: 992px) {
            .main-content {
                padding: 1.75rem 2rem 2.5rem;
            }
        }

        .main-content-inner {
            background: #fff;
            border-radius: 0.65rem;
            box-shadow: 0 0.15rem 0.65rem rgba(12, 53, 105, 0.06);
            border: 1px solid rgba(12, 53, 105, 0.06);
            padding: 1.35rem 1.5rem;
            min-height: 280px;
        }

        @media (min-width: 768px) {
            .main-content-inner {
                padding: 1.5rem 1.75rem;
            }
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body class="ehonai-layout d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-md navbar-dark navbar-ehonai sticky-top">
    <div class="container-fluid px-3 px-md-4">
        <button class="btn btn-link text-white d-md-none me-2 p-1 border-0 shadow-none" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu"
                aria-label="Buka menu">
            <i class="bi bi-list fs-4"></i>
        </button>
        <a class="navbar-brand d-flex align-items-center gap-2 py-0" href="<?= base_url('dashboard') ?>">
            <img src="<?= base_url('assets/images/logo-papua-pegunungan.png') ?>" alt="" class="brand-mark" width="32" height="32" decoding="async">
            <span>e-Honai Connect</span>
        </a>
        <div class="ms-auto d-flex align-items-center gap-1 gap-md-2 flex-shrink-0">
            <?php
            $ehonaiNavNotifications = [];
            $ehonaiNavNotifCount    = 0;
            if (session()->get('isLoggedIn')) {
                $ehonaiNavNotifications = model(\App\Models\NotificationModel::class)->getUnreadForUser((int) session()->get('id'), 15);
                $ehonaiNavNotifCount     = count($ehonaiNavNotifications);
            }
            ?>
            <?php if (session()->get('isLoggedIn')) : ?>
                <div class="dropdown">
                    <button class="btn btn-link nav-notif-btn position-relative" type="button"
                            id="ehonaiNavNotifDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false" aria-label="Notifikasi perubahan status tiket"
                            title="Notifikasi status tiket">
                        <i class="bi bi-bell fs-5" aria-hidden="true"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger badge-notif <?= $ehonaiNavNotifCount === 0 ? 'd-none' : '' ?>"
                              id="ehonai-nav-notif-badge"><?= $ehonaiNavNotifCount ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-notif shadow border-0 py-0 mt-1"
                        id="ehonai-nav-notif-menu"
                        style="width: min(22rem, 92vw); max-height: 22rem; overflow-y: auto;"
                        aria-labelledby="ehonaiNavNotifDropdown">
                        <li class="px-3 py-2 border-bottom bg-light small fw-semibold text-secondary sticky-top">
                            Perubahan status laporan
                        </li>
                        <li id="ehonai-nav-notif-empty" class="<?= $ehonaiNavNotifCount > 0 ? 'd-none' : '' ?>">
                            <span class="dropdown-item-text text-muted text-center py-4 small d-block">Tidak ada notifikasi.</span>
                        </li>
                        <?php foreach ($ehonaiNavNotifications as $n) : ?>
                            <li class="notif-item border-bottom border-light" data-notif-id="<?= (int) $n->id ?>">
                                <div class="dropdown-item-text px-3 py-2">
                                    <p class="mb-1 notif-item-msg text-dark"><?= esc($n->message) ?></p>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <?php if (! empty($n->ticket_id)) : ?>
                                            <a href="<?= base_url('ticket/show/' . (int) $n->ticket_id) ?>" class="btn btn-sm btn-outline-primary py-0">Lihat tiket</a>
                                        <?php endif; ?>
                                        <span class="text-muted small"><?= esc($n->created_at ?? '') ?></span>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (session()->get('isLoggedIn')) : ?>
                <?php if (session()->get('role') === 'admin') : ?>
                    <div class="dropdown">
                        <button class="btn btn-link nav-user nav-user-link text-white text-truncate dropdown-toggle border-0 shadow-none text-decoration-none d-inline-flex align-items-center"
                                type="button" id="ehonaiProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                style="max-width: 14rem;" title="Menu profil Admin">
                            <i class="bi bi-person-circle me-sm-1"></i><span class="d-none d-sm-inline text-truncate"><?= esc(session()->get('nama_lengkap') ?? 'Admin') ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1 py-1" aria-labelledby="ehonaiProfileDropdown" style="min-width: 12rem;">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= base_url('profile') ?>">
                                    <i class="bi bi-person text-secondary" aria-hidden="true"></i> Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= base_url('backup') ?>">
                                    <i class="bi bi-database-down text-secondary" aria-hidden="true"></i> Backup Database
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else : ?>
                    <a href="<?= base_url('profile') ?>" class="nav-user nav-user-link text-white text-truncate d-none d-sm-inline" style="max-width: 14rem;" title="Profil pengguna">
                        <i class="bi bi-person-circle me-1"></i><?= esc(session()->get('nama_lengkap') ?? 'Pengguna') ?>
                    </a>
                    <a href="<?= base_url('profile') ?>" class="nav-user nav-user-link text-white text-truncate d-sm-none" style="max-width: 7rem;" title="<?= esc(session()->get('nama_lengkap') ?? 'Profil') ?>">
                        <i class="bi bi-person-circle"></i>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <a class="btn btn-light btn-sm btn-logout text-dark" href="<?= base_url('logout') ?>">Logout</a>
        </div>
    </div>
</nav>

<div class="layout-shell flex-grow-1">
    <aside class="offcanvas offcanvas-md offcanvas-start shadow-sm" tabindex="-1" id="sidebarMenu"
           aria-labelledby="sidebarMenuLabel">
        <div class="offcanvas-header border-bottom d-md-none py-2">
            <span class="offcanvas-title fw-semibold text-secondary" id="sidebarMenuLabel">Menu</span>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0 pt-md-3">
            <nav class="sidebar-nav nav flex-column flex-grow-1">
                <a class="nav-link <?= uri_string() === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link <?= str_starts_with(uri_string(), 'ticket') ? 'active' : '' ?>" href="<?= base_url('ticket') ?>">
                    <i class="bi bi-ticket-detailed"></i> Tiket Laporan
                </a>
                <a class="nav-link <?= uri_string() === 'faq' || str_starts_with(uri_string(), 'faq/') ? 'active' : '' ?>" href="<?= base_url('faq') ?>">
                    <i class="bi bi-question-circle"></i> Pusat Bantuan (FAQ)
                </a>
                <?php if (session()->get('role') === 'admin') : ?>
                    <a class="nav-link <?= uri_string() === 'monitoring' || str_starts_with(uri_string(), 'monitoring/') ? 'active' : '' ?>" href="<?= base_url('monitoring') ?>">
                        <i class="bi bi-hdd-network"></i> Monitoring Perangkat
                    </a>
                    <a class="nav-link <?= uri_string() === 'user' || str_starts_with(uri_string(), 'user/') ? 'active' : '' ?>" href="<?= base_url('user') ?>">
                        <i class="bi bi-building-gear"></i> Manajemen OPD
                    </a>
                    <a class="nav-link <?= uri_string() === 'report' || str_starts_with(uri_string(), 'report/') ? 'active' : '' ?>" href="<?= base_url('report') ?>">
                        <i class="bi bi-file-earmark-bar-graph"></i> Laporan Rekap
                    </a>
                    <a class="nav-link <?= uri_string() === 'audit' ? 'active' : '' ?>" href="<?= base_url('audit') ?>">
                        <i class="bi bi-journal-text"></i> Log Sistem
                    </a>
                <?php endif; ?>
            </nav>
            <div class="small text-muted px-4 py-3 d-none d-md-block border-top mt-auto opacity-75">
                Dinas Komunikasi, Informatika, Persandian dan Statistik
            </div>
        </div>
    </aside>

    <main class="main-content">
        <div class="main-content-inner">
            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('success')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>
            <?= $this->renderSection('content') ?>
        </div>
    </main>
</div>

<footer class="footer mt-auto py-3 bg-white border-top w-100 shadow-sm" style="border-color: rgba(12, 53, 105, 0.08) !important;">
    <div class="container-fluid px-3 px-md-4">
        <p class="small text-muted text-center mb-0">
            Copyright &copy; <?= date('Y') ?> Diskominfosatik Provinsi Papua Pegunungan. e-Honai Connect Developed by APTIKA TEAM, Team Lead by ORL.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<?php if (session()->get('isLoggedIn')) : ?>
<script>
(function () {
    const feedUrl = <?= json_encode(site_url('dashboard/notifications-feed')) ?>;
    const listEl = document.getElementById('ehonai-nav-notif-menu');
    const badgeEl = document.getElementById('ehonai-nav-notif-badge');
    const emptyEl = document.getElementById('ehonai-nav-notif-empty');
    if (!listEl || !feedUrl) return;

    function existingIds() {
        return new Set(
            Array.from(listEl.querySelectorAll('.notif-item[data-notif-id]')).map(function (el) {
                return el.getAttribute('data-notif-id');
            })
        );
    }

    function removeEmptyState() {
        if (emptyEl) emptyEl.classList.add('d-none');
    }

    function prependNotif(item) {
        removeEmptyState();
        const li = document.createElement('li');
        li.className = 'notif-item border-bottom border-light border-start border-primary border-3';
        li.setAttribute('data-notif-id', String(item.id));
        const ticketUrl = item.ticket_id
            ? <?= json_encode(rtrim(site_url('ticket/show'), '/')) ?> + '/' + item.ticket_id
            : '';
        li.innerHTML =
            '<div class="dropdown-item-text px-3 py-2">' +
            '<p class="mb-1 notif-item-msg text-dark">' + escapeHtml(item.message) + '</p>' +
            '<div class="d-flex flex-wrap align-items-center gap-2">' +
            (ticketUrl
                ? '<a href="' + ticketUrl + '" class="btn btn-sm btn-outline-primary py-0">Lihat tiket</a>'
                : '') +
            '<span class="text-muted small">' + escapeHtml(item.created_at || '') + '</span>' +
            '</div></div>';
        const header = listEl.querySelector('li:first-child');
        if (header && header.nextSibling) {
            listEl.insertBefore(li, header.nextSibling);
        } else {
            listEl.appendChild(li);
        }
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function refreshNotifs() {
        fetch(feedUrl, {credentials: 'same-origin', headers: {'Accept': 'application/json'}})
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !Array.isArray(data.items)) return;
                const have = existingIds();
                data.items.forEach(function (item) {
                    if (!have.has(String(item.id))) {
                        prependNotif(item);
                        have.add(String(item.id));
                    }
                });
                if (badgeEl) {
                    const n = listEl.querySelectorAll('.notif-item[data-notif-id]').length;
                    badgeEl.textContent = String(n);
                    badgeEl.classList.toggle('d-none', n === 0);
                }
            })
            .catch(function () {});
    }

    setInterval(refreshNotifs, 45000);
})();
</script>
<?php endif; ?>
<?= $this->renderSection('scripts') ?>
</body>
</html>
