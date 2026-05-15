<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Tiket · e-Honai Connect</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 1.25rem 1.5rem 2rem;
            background: #fff;
        }
        .kop {
            text-align: center;
            margin-bottom: 0;
        }
        .kop h3 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .kop h4 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0 0 0.35rem;
            text-transform: uppercase;
        }
        .kop .alamat {
            margin: 0.25rem 0 0;
            font-size: 10.5pt;
            line-height: 1.45;
        }
        .judul-dokumen {
            text-align: center;
            margin: 1.1rem 0 0.75rem;
        }
        .judul-dokumen h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
            text-decoration: underline;
            text-transform: none;
        }
        .doc-meta {
            font-size: 10pt;
            margin-bottom: 0.85rem;
            text-align: center;
        }
        table.rekap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.35rem;
            font-size: 9.5pt;
        }
        table.rekap th,
        table.rekap td {
            border: 1px solid #000;
            padding: 0.35rem 0.45rem;
            vertical-align: top;
        }
        table.rekap thead th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        table.rekap td:nth-child(1) { text-align: center; width: 2rem; }
        .ttd {
            margin-top: 2.25rem;
            text-align: right;
            font-size: 10.5pt;
            line-height: 1.65;
            page-break-inside: avoid;
        }
        @media print {
            body { padding: 0.5cm; }
            @page { margin: 12mm; }
        }
    </style>
</head>
<body>

<div class="kop">
    <h3>PEMERINTAH PROVINSI PAPUA PEGUNUNGAN</h3>
    <h4>DINAS KOMUNIKASI DAN INFORMATIKA, STATISTIK DAN PERSANDIAN</h4>
    <div class="alamat">
        Jalan Yos Sudarso, Kelurahan Wamena Kota, Distrik Wamena, Kabupaten Jayawijaya, Provinsi Papua Pegunungan<br>
        Indonesia | Kode Pos 99511 - Website https://diskominfosatik.papuapegunungan.go.id/
    </div>
</div>
<hr style="border: 2px solid black; margin: 0.75rem 0 1rem;">

<div class="judul-dokumen">
    <h2>Laporan Rekapitulasi Penanganan Tiket Jaringan e-Honai Connect</h2>
</div>
<p class="doc-meta">Periode: <?= esc($periodeLabel) ?> &nbsp;|&nbsp; Dicetak: <?= esc(date('d/m/Y H:i')) ?></p>

<?php if ($tickets === []) : ?>
    <p>Tidak ada data tiket pada filter ini.</p>
<?php else : ?>
    <table class="rekap">
        <thead>
        <tr>
            <th>No</th>
            <th>No. Tiket</th>
            <th>Tgl. Buat</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Prioritas</th>
            <th>Status</th>
            <th>Pelapor</th>
            <th>Instansi OPD</th>
        </tr>
        </thead>
        <tbody>
        <?php $no = 1;
        foreach ($tickets as $t) : ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($t->nomor_tiket) ?></td>
                <td><?= esc($t->created_at ?? '—') ?></td>
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
<?php endif; ?>

<div class="ttd">
    Wamena, <?= esc($tanggalCetak) ?><br><br>
    Mengetahui,<br>
    Admin e-Honai Connect<br><br><br>
    ( Samuel )
</div>

<script>window.print();</script>
</body>
</html>
