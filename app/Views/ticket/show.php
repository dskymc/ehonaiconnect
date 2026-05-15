<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Detail Tiket<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$replies = $replies ?? [];
?>
<style>
    .diskusi-bubble-opd {
        max-width: min(85%, 28rem);
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }
    .diskusi-bubble-staff {
        max-width: min(85%, 28rem);
        background: linear-gradient(135deg, #0d4a7a, #0d6e6e);
        color: #fff;
    }
    .diskusi-bubble-staff .text-muted-staff {
        color: rgba(255, 255, 255, 0.85) !important;
    }
    .diskusi-thread {
        max-height: 420px;
        overflow-y: auto;
    }
</style>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="h4 fw-semibold text-secondary mb-1">Detail Tiket</h1>
        <p class="mb-0"><code><?= esc($ticket->nomor_tiket) ?></code></p>
    </div>
    <a href="<?= base_url('ticket') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke daftar</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Isi Laporan</h2>
                <p class="fw-semibold mb-2"><?= esc($ticket->judul_laporan) ?></p>
                <dl class="row small mb-0">
                    <dt class="col-sm-3">Kategori</dt>
                    <dd class="col-sm-9"><?= esc($ticket->kategori) ?></dd>
                    <dt class="col-sm-3">Prioritas</dt>
                    <dd class="col-sm-9"><?= view('partials/prioritas_badge', ['prioritas' => $ticket->prioritas ?? '']) ?></dd>
                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9"><?= esc($ticket->status) ?></dd>
                </dl>
                <hr>
                <h3 class="h6">Deskripsi</h3>
                <p class="mb-0 text-break"><?= nl2br(esc($ticket->deskripsi)) ?></p>
                <?php if (! empty($ticket->bukti_foto)) : ?>
                    <hr>
                    <h3 class="h6">Lampiran</h3>
                    <a href="<?= base_url('uploads/tickets/' . rawurlencode($ticket->bukti_foto)) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                        Unduh / lihat lampiran
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Data Pelapor</h2>
                <dl class="row small mb-0">
                    <dt class="col-5">Nama</dt>
                    <dd class="col-7"><?= esc($ticket->pelapor_nama_lengkap ?? '—') ?></dd>
                    <dt class="col-5">Username</dt>
                    <dd class="col-7"><?= esc($ticket->pelapor_username ?? '—') ?></dd>
                    <dt class="col-5">Instansi</dt>
                    <dd class="col-7"><?= esc($ticket->pelapor_instansi_opd ?? '—') ?></dd>
                </dl>
            </div>
        </div>

        <?php if (session()->get('role') === 'admin') : ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Ubah status (Admin)</h2>
                    <?= form_open('ticket/update-status/' . (int) $ticket->id) ?>
                    <div class="mb-3">
                        <label for="status" class="form-label small fw-semibold">Status</label>
                        <select name="status" id="status" class="form-select form-select-sm" required>
                            <?php foreach (['Baru', 'Diproses', 'Tertunda', 'Selesai', 'Ditutup'] as $st) : ?>
                                <option value="<?= esc($st, 'attr') ?>" <?= $ticket->status === $st ? 'selected' : '' ?>><?= esc($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Simpan status</button>
                    <?= form_close() ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm mt-2">
    <div class="card-header bg-white border-bottom py-3">
        <h2 class="h6 mb-0 fw-semibold text-dark"><i class="bi bi-chat-dots me-2 text-primary"></i>History / Diskusi Laporan</h2>
        <p class="small text-muted mb-0 mt-1">Percakapan terkait tiket ini.</p>
    </div>
    <div class="card-body">
        <div class="diskusi-thread d-flex flex-column gap-3 mb-4 pe-1">
            <?php if ($replies === []) : ?>
                <p class="text-muted small mb-0 text-center py-3">Belum ada balasan.</p>
            <?php else : ?>
                <?php foreach ($replies as $r) :
                    $pRole = (string) ($r->pengirim_role ?? '');
                    $isStaff = in_array($pRole, ['admin', 'teknisi'], true);
                    ?>
                    <div class="d-flex <?= $isStaff ? 'justify-content-end' : 'justify-content-start' ?>">
                        <div class="rounded-4 p-3 shadow-sm <?= $isStaff ? 'diskusi-bubble-staff' : 'diskusi-bubble-opd' ?>">
                            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-baseline mb-2">
                                <span class="small fw-semibold"><?= esc($r->pengirim_nama_lengkap ?? 'Pengguna') ?></span>
                                <span class="small <?= $isStaff ? 'text-muted-staff' : 'text-muted' ?>"><?= esc(strtoupper($pRole)) ?></span>
                            </div>
                            <p class="mb-2 small text-break"><?= nl2br(esc($r->pesan)) ?></p>
                            <?php if (! empty($r->lampiran)) : ?>
                                <div class="mt-2">
                                    <a href="<?= base_url('uploads/replies/' . rawurlencode($r->lampiran)) ?>" target="_blank" rel="noopener noreferrer" class="d-inline-block">
                                        <img src="<?= base_url('uploads/replies/' . rawurlencode($r->lampiran)) ?>"
                                             alt="Lampiran balasan" class="img-fluid rounded-3 border shadow-sm" style="max-height: 220px; max-width: 100%;">
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="small <?= $isStaff ? 'text-muted-staff' : 'text-muted' ?> mt-2 mb-0"><?= esc($r->created_at ?? '') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <hr class="my-3">

        <h3 class="h6 fw-semibold mb-3">Kirim balasan</h3>
        <?= form_open_multipart('ticket/reply', ['class' => 'diskusi-form']) ?>
        <input type="hidden" name="ticket_id" value="<?= (int) $ticket->id ?>">
        <div class="mb-3">
            <label for="pesan" class="form-label small fw-semibold">Pesan</label>
            <textarea name="pesan" id="pesan" class="form-control" rows="3" required placeholder="Tulis balasan..."><?= esc(old('pesan', '')) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="lampiran" class="form-label small fw-semibold">Lampiran gambar (opsional)</label>
            <input type="file" name="lampiran" id="lampiran" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif">
            <div class="form-text">JPG, PNG, WEBP, atau GIF. Maks. 2 MB.</div>
        </div>
        <button type="submit" class="btn btn-primary">Kirim balasan</button>
        <?= form_close() ?>
    </div>
</div>
<?= $this->endSection() ?>
