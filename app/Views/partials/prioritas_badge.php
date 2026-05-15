<?php
$p = (string) ($prioritas ?? '');
$class = match ($p) {
    'High'   => 'text-bg-danger',
    'Medium' => 'text-bg-warning text-dark',
    'Low'    => 'text-bg-success',
    default  => 'text-bg-secondary',
};
?>
<span class="badge rounded-pill fw-semibold px-3 py-2 <?= esc($class, 'attr') ?>"><?= esc($p !== '' ? $p : '—') ?></span>
