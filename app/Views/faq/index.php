<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Pusat Bantuan (FAQ)<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
/** @var list<object> $faqs */
/** @var bool $isAdmin */
$faqUpdateBase = rtrim(site_url('faq/update'), '/');
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="h4 fw-semibold text-secondary mb-1">Pusat Bantuan (FAQ)</h1>
        <p class="small text-muted mb-0">Jawaban umum sebelum membuka tiket baru.</p>
    </div>
    <?php if ($isAdmin) : ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahFaq">
            <i class="bi bi-plus-lg me-1"></i> Tambah FAQ
        </button>
    <?php endif; ?>
</div>

<?php if ($faqs === []) : ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center text-muted">
            <i class="bi bi-inboxes fs-1 d-block mb-3 opacity-50"></i>
            <p class="mb-0">Belum ada FAQ.<?php if ($isAdmin) : ?> Klik <strong>Tambah FAQ</strong> untuk menambahkan entri pertama.<?php endif; ?></p>
        </div>
    </div>
<?php else : ?>
    <div class="accordion shadow-sm border-0 rounded overflow-hidden" id="faqAccordion">
        <?php foreach ($faqs as $f) : ?>
            <?php
            $fid = (int) $f->id;
            $payload = json_encode([
                'id'          => $fid,
                'pertanyaan'  => (string) $f->pertanyaan,
                'jawaban'     => (string) $f->jawaban,
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
            ?>
            <div class="accordion-item border-0 border-bottom">
                <h2 class="accordion-header" id="faqHeading<?= $fid ?>">
                    <button class="accordion-button collapsed fw-semibold text-start" type="button"
                            data-bs-toggle="collapse" data-bs-target="#faqCollapse<?= $fid ?>"
                            aria-expanded="false" aria-controls="faqCollapse<?= $fid ?>">
                        <?= esc($f->pertanyaan) ?>
                    </button>
                </h2>
                <div id="faqCollapse<?= $fid ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"
                     aria-labelledby="faqHeading<?= $fid ?>">
                    <div class="accordion-body bg-light bg-opacity-50">
                        <div class="text-dark small mb-3" style="white-space: pre-wrap;"><?= esc((string) $f->jawaban) ?></div>
                        <?php if ($isAdmin) : ?>
                            <div class="d-flex flex-wrap gap-2 pt-2 border-top border-light-subtle">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-faq"
                                        data-bs-toggle="modal" data-bs-target="#modalEditFaq"
                                        data-faq-item="<?= esc($payload, 'attr') ?>">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <?= form_open('faq/delete/' . $fid, [
                                    'class'      => 'd-inline',
                                    'onsubmit'   => "return confirm('Hapus FAQ ini secara permanen?');",
                                ]) ?>
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </button>
                                <?= form_close() ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($isAdmin) : ?>
    <div class="modal fade" id="modalTambahFaq" tabindex="-1" aria-labelledby="modalTambahFaqLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <?= form_open('faq/store') ?>
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title h5 fw-semibold" id="modalTambahFaqLabel">Tambah FAQ</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label for="tambah_pertanyaan" class="form-label fw-semibold small">Pertanyaan</label>
                        <input type="text" name="pertanyaan" id="tambah_pertanyaan" class="form-control" required maxlength="255"
                               value="<?= esc(old('pertanyaan', '')) ?>" placeholder="Ringkas dan jelas">
                    </div>
                    <div class="mb-0">
                        <label for="tambah_jawaban" class="form-label fw-semibold small">Jawaban</label>
                        <textarea name="jawaban" id="tambah_jawaban" class="form-control" rows="6" required
                                  placeholder="Penjelasan lengkap untuk pengguna"><?= esc(old('jawaban', '')) ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditFaq" tabindex="-1" aria-labelledby="modalEditFaqLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <?= form_open('faq/update/0', ['id' => 'formEditFaq']) ?>
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title h5 fw-semibold" id="modalEditFaqLabel">Edit FAQ</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label for="edit_pertanyaan" class="form-label fw-semibold small">Pertanyaan</label>
                        <input type="text" name="pertanyaan" id="edit_pertanyaan" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-0">
                        <label for="edit_jawaban" class="form-label fw-semibold small">Jawaban</label>
                        <textarea name="jawaban" id="edit_jawaban" class="form-control" rows="6" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>

<?php if ($isAdmin) : ?>
<?= $this->section('scripts') ?>
<script>
(function () {
    const updateBase = <?= json_encode($faqUpdateBase) ?>;
    const formEdit = document.getElementById('formEditFaq');
    const inpP = document.getElementById('edit_pertanyaan');
    const inpJ = document.getElementById('edit_jawaban');
    if (!formEdit || !inpP || !inpJ) return;

    document.querySelectorAll('.btn-edit-faq').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const raw = btn.getAttribute('data-faq-item');
            if (!raw) return;
            let d;
            try {
                d = JSON.parse(raw);
            } catch (e) {
                return;
            }
            formEdit.action = updateBase + '/' + encodeURIComponent(String(d.id));
            inpP.value = d.pertanyaan || '';
            inpJ.value = d.jawaban || '';
        });
    });
})();
</script>
<?= $this->endSection() ?>
<?php endif; ?>
