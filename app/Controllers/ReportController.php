<?php
// Coded by DskyMC

namespace App\Controllers;

use App\Models\TicketModel;

class ReportController extends BaseController
{
    protected $helpers = ['form', 'url'];

    /**
     * @return array{0: int, 1: int, 2: string|null}
     */
    private function parseFilter(): array
    {
        $y = (int) $this->request->getGet('tahun');
        $m = (int) $this->request->getGet('bulan');
        $k = trim((string) $this->request->getGet('kategori'));

        if ($y < 2000 || $y > 2100) {
            $y = (int) date('Y');
        }
        if ($m < 1 || $m > 12) {
            $m = (int) date('n');
        }

        $kategori = $k === '' ? null : $k;

        return [$y, $m, $kategori];
    }

    private function namaBulan(int $bulan): string
    {
        $nama = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $nama[$bulan] ?? (string) $bulan;
    }

    private function buildPeriodeLabel(int $year, int $month, ?string $kategori): string
    {
        $label = $this->namaBulan($month) . ' ' . $year;
        if ($kategori !== null && $kategori !== '') {
            $label .= ' · Kategori: ' . $kategori;
        } else {
            $label .= ' · Semua kategori';
        }

        return $label;
    }

    public function index(): string
    {
        [$year, $month, $kategori] = $this->parseFilter();

        /** @var TicketModel $ticketModel */
        $ticketModel    = model(TicketModel::class);
        $tickets        = $ticketModel->getTicketsForAdminReport($year, $month, $kategori);
        $kategoriList   = $ticketModel->getDistinctKategori();
        $periodeLabel   = $this->buildPeriodeLabel($year, $month, $kategori);

        return view('report/index', [
            'tickets'      => $tickets,
            'bulan'        => $month,
            'tahun'        => $year,
            'kategori'     => $kategori ?? '',
            'kategoriList' => $kategoriList,
            'periodeLabel' => $periodeLabel,
        ]);
    }

    public function print(): string
    {
        [$year, $month, $kategori] = $this->parseFilter();

        /** @var TicketModel $ticketModel */
        $ticketModel  = model(TicketModel::class);
        $tickets      = $ticketModel->getTicketsForAdminReport($year, $month, $kategori);
        $periodeLabel = $this->buildPeriodeLabel($year, $month, $kategori);

        return view('report/print', [
            'tickets'        => $tickets,
            'bulan'          => $month,
            'tahun'          => $year,
            'kategori'       => $kategori ?? '',
            'periodeLabel'   => $periodeLabel,
            'tanggalCetak'   => (int) date('j') . ' ' . $this->namaBulan((int) date('n')) . ' ' . date('Y'),
        ]);
    }
}
