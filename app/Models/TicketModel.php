<?php
// Coded by DskyMC

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table         = 'tickets';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'nomor_tiket',
        'user_id',
        'pelapor_nama',
        'pelapor_instansi',
        'no_hp_pelapor',
        'email_pelapor',
        'kategori',
        'prioritas',
        'status',
        'judul_laporan',
        'deskripsi',
        'bukti_foto',
        'teknisi_id',
        'librenms_device_id',
        'librenms_alert_id',
        'librenms_hostname',
        'source',
    ];

    public function findOpenByLibrenmsDeviceId(int $deviceId): ?object
    {
        if ($deviceId <= 0) {
            return null;
        }

        return $this->where('librenms_device_id', $deviceId)
            ->whereIn('status', ['Baru', 'Diproses', 'Tertunda'])
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * @param array{
     *   librenms_device_id: int,
     *   librenms_alert_id?: int|null,
     *   librenms_hostname?: string|null,
     *   judul_laporan: string,
     *   deskripsi: string,
     *   prioritas: string,
     *   kategori: string
     * } $data
     */
    public function createFromLibrenmsAlert(array $data): ?int
    {
        $inserted = $this->insert([
            'nomor_tiket'        => $this->generateNomorTiket(),
            'user_id'            => null,
            'pelapor_nama'       => 'Sistem LibreNMS',
            'pelapor_instansi'   => 'Monitoring Otomatis',
            'no_hp_pelapor'      => '-',
            'email_pelapor'      => null,
            'kategori'           => $data['kategori'],
            'prioritas'          => $data['prioritas'],
            'status'             => 'Baru',
            'judul_laporan'      => $data['judul_laporan'],
            'deskripsi'          => $data['deskripsi'],
            'bukti_foto'         => null,
            'teknisi_id'         => null,
            'librenms_device_id' => $data['librenms_device_id'],
            'librenms_alert_id'  => $data['librenms_alert_id'] ?? null,
            'librenms_hostname'  => $data['librenms_hostname'] ?? null,
            'source'             => 'librenms',
        ]);

        if ($inserted === false) {
            log_message('error', 'Gagal membuat tiket dari LibreNMS: ' . json_encode($this->errors()));

            return null;
        }

        return (int) $inserted;
    }

    public function generateNomorTiket(): string
    {
        $ym     = date('Ym');
        $prefix = 'HNC-' . $ym . '-';

        $last = $this->like('nomor_tiket', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        $next = 1;
        if ($last !== null) {
            $parts = explode('-', $last->nomor_tiket);
            $next  = (int) end($parts) + 1;
        }

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @return list<object>
     */
    public function getTicketsWithUsers(string $role, int $user_id): array
    {
        $builder = $this->builder();
        $builder->select(
            $this->table . '.*, COALESCE(users.nama_lengkap, ' . $this->table . '.pelapor_nama) AS nama_pelapor, '
            . 'users.no_hp AS pelapor_user_no_hp, users.email AS pelapor_user_email',
            false
        );
        $builder->join('users', 'users.id = ' . $this->table . '.user_id', 'left');

        if ($role === 'opd') {
            $builder->where($this->table . '.user_id', $user_id);
        }

        $builder->orderBy($this->table . '.created_at', 'DESC');

        return $builder->get()->getResult();
    }

    public function getTicketWithPelapor(int $id): ?object
    {
        return $this->builder()
            ->select(
                $this->table . '.*, '
                . 'COALESCE(users.nama_lengkap, ' . $this->table . '.pelapor_nama) AS pelapor_nama_lengkap, '
                . 'COALESCE(users.username, \'(tanpa akun)\') AS pelapor_username, '
                . 'COALESCE(users.instansi_opd, ' . $this->table . '.pelapor_instansi) AS pelapor_instansi_opd, '
                . 'users.no_hp AS pelapor_user_no_hp, users.email AS pelapor_user_email',
                false
            )
            ->join('users', 'users.id = ' . $this->table . '.user_id', 'left')
            ->where($this->table . '.id', $id)
            ->get()
            ->getRow();
    }

    /**
     * Statistik tiket untuk dashboard (scope OPD = user sendiri; admin/teknisi = seluruh tiket).
     *
     * @return array{total_bulan_ini: int, menunggu: int, sedang_dikerjakan: int, selesai_bulan_ini: int}
     */
    public function getDashboardTicketStats(string $role, int $userId): array
    {
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd   = date('Y-m-t 23:59:59');

        $b = $this->builder();
        if ($role === 'opd') {
            $b->where('user_id', $userId);
        }
        $b->where('created_at >=', $monthStart)->where('created_at <=', $monthEnd);
        $totalBulanIni = (int) $b->countAllResults();

        $b = $this->builder();
        if ($role === 'opd') {
            $b->where('user_id', $userId);
        }
        $b->whereIn('status', ['Baru', 'Tertunda']);
        $menunggu = (int) $b->countAllResults();

        $b = $this->builder();
        if ($role === 'opd') {
            $b->where('user_id', $userId);
        }
        $b->where('status', 'Diproses');
        $sedangDikerjakan = (int) $b->countAllResults();

        $b = $this->builder();
        if ($role === 'opd') {
            $b->where('user_id', $userId);
        }
        $b->whereIn('status', ['Selesai', 'Ditutup']);
        $b->where('updated_at >=', $monthStart)->where('updated_at <=', $monthEnd);
        $selesaiBulanIni = (int) $b->countAllResults();

        return [
            'total_bulan_ini'   => $totalBulanIni,
            'menunggu'          => $menunggu,
            'sedang_dikerjakan' => $sedangDikerjakan,
            'selesai_bulan_ini' => $selesaiBulanIni,
        ];
    }

    /**
     * Daftar tiket untuk laporan admin (filter tanggal buat + opsional kategori).
     *
     * @return list<object>
     */
    public function getTicketsForAdminReport(int $year, int $month, ?string $kategori = null): array
    {
        $month      = max(1, min(12, $month));
        $monthStart = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $monthEnd   = date('Y-m-t 23:59:59', strtotime($monthStart));

        $builder = $this->builder();
        $builder->select(
            $this->table . '.*, '
            . 'COALESCE(users.nama_lengkap, ' . $this->table . '.pelapor_nama) AS nama_pelapor, '
            . 'COALESCE(users.instansi_opd, ' . $this->table . '.pelapor_instansi) AS instansi_pelapor',
            false
        );
        $builder->join('users', 'users.id = ' . $this->table . '.user_id', 'left');
        $builder->where($this->table . '.created_at >=', $monthStart);
        $builder->where($this->table . '.created_at <=', $monthEnd);

        if ($kategori !== null && $kategori !== '') {
            $builder->where($this->table . '.kategori', $kategori);
        }

        $builder->orderBy($this->table . '.created_at', 'ASC');

        return $builder->get()->getResult();
    }

    /**
     * @return list<string>
     */
    public function getDistinctKategori(): array
    {
        $rows = $this->builder()
            ->distinct()
            ->select('kategori')
            ->where('kategori !=', '')
            ->orderBy('kategori', 'ASC')
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $row) {
            $k = isset($row['kategori']) ? (string) $row['kategori'] : '';
            if ($k !== '') {
                $out[] = $k;
            }
        }

        return $out;
    }
}
