<?php
// Coded by DskyMC

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\NotificationModel;
use App\Models\TicketModel;
use App\Models\TicketReplyModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class TicketController extends BaseController
{
    protected $helpers = ['form', 'url'];

    protected function canCreateTicket(): bool
    {
        $role = session()->get('role');

        return $role === 'admin' || $role === 'opd';
    }

    public function index(): RedirectResponse|string
    {
        $role = (string) session()->get('role');
        $uid  = (int) session()->get('id');

        /** @var TicketModel $ticketModel */
        $ticketModel = model(TicketModel::class);
        $tickets     = $ticketModel->getTicketsWithUsers($role, $uid);

        return view('ticket/index', ['tickets' => $tickets]);
    }

    public function create(): RedirectResponse|string
    {
        if (! $this->canCreateTicket()) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses untuk membuat tiket.');
        }

        $opdUsers = [];
        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);
        if (session()->get('role') === 'admin') {
            $opdUsers = $userModel->where('role', 'opd')->orderBy('nama_lengkap', 'ASC')->findAll();
        }

        $currentUser  = $userModel->find((int) session()->get('id'));
        $defaultNoHp  = (string) ($currentUser->no_hp ?? '');
        $defaultEmail = (string) ($currentUser->email ?? '');

        return view('ticket/create', [
            'opdUsers'     => $opdUsers,
            'defaultNoHp'  => $defaultNoHp,
            'defaultEmail' => $defaultEmail,
        ]);
    }

    public function store(): RedirectResponse
    {
        if (! $this->canCreateTicket()) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses untuk membuat tiket.');
        }

        $isAdmin = session()->get('role') === 'admin';

        $rules = [
            'judul_laporan'   => 'required|max_length[255]',
            'kategori'        => 'required|max_length[100]',
            'prioritas'       => 'required|in_list[Low,Medium,High]',
            'deskripsi'       => 'required',
            'no_hp_pelapor'   => 'required|max_length[20]',
            'email_pelapor'   => 'permit_empty|max_length[100]|valid_email',
        ];

        if ($isAdmin) {
            $rules['pelapor_mode'] = 'required|in_list[opd_terdaftar,input_manual]';
            $mode                  = (string) $this->request->getPost('pelapor_mode');
            if ($mode === 'input_manual') {
                $rules['pelapor_nama']      = 'required|max_length[150]';
                $rules['pelapor_instansi']  = 'required|max_length[200]';
            } else {
                $rules['user_id'] = 'required|is_natural_no_zero';
            }
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $userId          = (int) session()->get('id');
        $pelaporNama     = null;
        $pelaporInstansi = null;

        if ($isAdmin) {
            $mode = (string) $this->request->getPost('pelapor_mode');
            if ($mode === 'input_manual') {
                $userId          = null;
                $pelaporNama     = (string) $this->request->getPost('pelapor_nama');
                $pelaporInstansi = (string) $this->request->getPost('pelapor_instansi');
            } else {
                $userId = (int) $this->request->getPost('user_id');
                /** @var UserModel $userModel */
                $userModel = model(UserModel::class);
                $target    = $userModel->where('id', $userId)->where('role', 'opd')->first();
                if ($target === null) {
                    return redirect()->back()->withInput()->with('error', 'Pelapor OPD yang dipilih tidak valid.');
                }
            }
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'tickets' . DIRECTORY_SEPARATOR;
        if (! is_dir($uploadDir) && ! mkdir($uploadDir, 0755, true) && ! is_dir($uploadDir)) {
            return redirect()->back()->withInput()->with('error', 'Folder upload tidak dapat dibuat.');
        }

        $buktiNama = null;
        $file      = $this->request->getFile('bukti_foto');
        if ($file !== null && $file->isValid() && ! $file->hasMoved()) {
            if (! in_array($file->getMimeType(), [
                'image/jpeg',
                'image/png',
                'image/webp',
                'application/pdf',
            ], true)) {
                return redirect()->back()->withInput()->with('error', 'Format lampiran harus JPG, PNG, WEBP, atau PDF.');
            }
            if ($file->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()->withInput()->with('error', 'Ukuran lampiran maksimal 2 MB.');
            }
            $newName   = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $buktiNama = $newName;
        }

        /** @var TicketModel $ticketModel */
        $ticketModel = model(TicketModel::class);

        $emailPelapor = trim((string) $this->request->getPost('email_pelapor'));

        $inserted = $ticketModel->insert([
            'nomor_tiket'       => $ticketModel->generateNomorTiket(),
            'user_id'           => $userId,
            'pelapor_nama'      => $pelaporNama,
            'pelapor_instansi'  => $pelaporInstansi,
            'no_hp_pelapor'     => (string) $this->request->getPost('no_hp_pelapor'),
            'email_pelapor'     => $emailPelapor === '' ? null : $emailPelapor,
            'kategori'          => (string) $this->request->getPost('kategori'),
            'prioritas'         => (string) $this->request->getPost('prioritas'),
            'status'            => 'Baru',
            'judul_laporan'     => (string) $this->request->getPost('judul_laporan'),
            'deskripsi'         => (string) $this->request->getPost('deskripsi'),
            'bukti_foto'        => $buktiNama,
            'teknisi_id'        => null,
        ]);

        if ($inserted === false) {
            return redirect()->back()->withInput()->with('error', implode(' ', $ticketModel->errors()));
        }

        $newTicketId = (int) $inserted;
        $createdRow  = $ticketModel->find($newTicketId);
        if ($createdRow !== null) {
            /** @var AuditLogModel $auditLogModel */
            $auditLogModel = model(AuditLogModel::class);
            $auditLogModel->insert([
                'user_id'    => (int) session()->get('id'),
                'aksi'       => 'BUAT_TIKET',
                'deskripsi'  => 'Membuat tiket ' . $createdRow->nomor_tiket . ': ' . $createdRow->judul_laporan,
                'ip_address' => mb_substr($this->request->getIPAddress(), 0, 45),
            ]);

            helper(['email', 'whatsapp']);

            $bodyAdmin = 'Tiket laporan baru masuk ke sistem e-Honai Connect.' . "\n\n"
                . 'Nomor tiket: ' . $createdRow->nomor_tiket . "\n"
                . 'Judul: ' . $createdRow->judul_laporan . "\n"
                . 'Kategori: ' . $createdRow->kategori . "\n"
                . 'Prioritas: ' . $createdRow->prioritas . "\n"
                . 'Status: ' . $createdRow->status . "\n\n"
                . 'Silakan tinjau daftar tiket di panel Admin.';

            foreach (ehonai_admin_notify_recipients() as $adminEmail) {
                sendNotification(
                    $adminEmail,
                    '[e-Honai Connect] Tiket baru: ' . $createdRow->nomor_tiket,
                    $bodyAdmin
                );
            }

            $ticketDetail = $ticketModel->getTicketWithPelapor($newTicketId);
            if ($ticketDetail !== null) {
                ehonai_send_whatsapp_to_admins(ehonai_whatsapp_new_ticket_message($ticketDetail));

                $pelaporPhone = ehonai_resolve_ticket_pelapor_phone($ticketDetail);
                if ($pelaporPhone !== null) {
                    sendWhatsAppNotification(
                        $pelaporPhone,
                        ehonai_whatsapp_new_ticket_pelapor_message($ticketDetail)
                    );
                }
            }
        }

        return redirect()->to('/ticket')->with('success', 'Tiket berhasil dibuat.');
    }

    public function show(int $id): RedirectResponse|string
    {
        /** @var TicketModel $ticketModel */
        $ticketModel = model(TicketModel::class);
        $ticket      = $ticketModel->getTicketWithPelapor($id);

        if ($ticket === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $role = (string) session()->get('role');
        $uid  = (int) session()->get('id');

        if ($role === 'opd' && (int) $ticket->user_id !== $uid) {
            return redirect()->to('/ticket')->with('error', 'Anda tidak memiliki akses ke tiket ini.');
        }

        if ((int) $ticket->user_id === $uid && $ticket->user_id !== null) {
            model(NotificationModel::class)->markReadForTicketAndUser($id, $uid);
        }

        /** @var TicketReplyModel $replyModel */
        $replyModel = model(TicketReplyModel::class);
        $replies    = $replyModel->getRepliesByTicket($id);

        return view('ticket/show', [
            'ticket'  => $ticket,
            'replies' => $replies,
        ]);
    }

    public function updateStatus(int $id): RedirectResponse
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/ticket')->with('error', 'Hanya Admin yang dapat mengubah status tiket.');
        }

        if (! $this->request->is('post')) {
            return redirect()->to('/ticket/show/' . $id)->with('error', 'Metode tidak diizinkan.');
        }

        $rules = [
            'status' => 'required|in_list[Baru,Diproses,Tertunda,Selesai,Ditutup]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/ticket/show/' . $id)->with('error', implode(' ', $this->validator->getErrors()));
        }

        /** @var TicketModel $ticketModel */
        $ticketModel = model(TicketModel::class);
        $ticket      = $ticketModel->find($id);

        if ($ticket === null) {
            return redirect()->to('/ticket')->with('error', 'Tiket tidak ditemukan.');
        }

        $newStatus = (string) $this->request->getPost('status');
        $oldStatus = (string) $ticket->status;

        if ($oldStatus === $newStatus) {
            return redirect()->to('/ticket/show/' . $id)->with('error', 'Status tidak berubah.');
        }

        $ticketModel->update($id, [
            'status' => $newStatus,
        ]);

        /** @var AuditLogModel $auditLogModel */
        $auditLogModel = model(AuditLogModel::class);
        $auditLogModel->insert([
            'user_id'    => (int) session()->get('id'),
            'aksi'       => 'UPDATE_STATUS_TIKET',
            'deskripsi'  => 'Mengubah status tiket ' . $ticket->nomor_tiket . ' (ID #' . $id . ') dari "' . $oldStatus . '" menjadi "' . $newStatus . '".',
            'ip_address' => mb_substr($this->request->getIPAddress(), 0, 45),
        ]);

        $uidPelapor = (int) $ticket->user_id;
        if ($uidPelapor > 0) {
            /** @var NotificationModel $notificationModel */
            $notificationModel = model(NotificationModel::class);
            $notificationModel->insert([
                'user_id'   => $uidPelapor,
                'ticket_id' => $id,
                'message'   => 'Status laporan tiket ' . $ticket->nomor_tiket . ' diubah dari ' . $oldStatus . ' menjadi ' . $newStatus . '.',
                'is_read'   => 0,
            ]);
        }

        helper(['email', 'whatsapp']);
        $pelaporEmail = trim((string) ($ticket->email_pelapor ?? ''));
        if ($pelaporEmail === '' && (int) $ticket->user_id > 0) {
            $userPelapor = model(UserModel::class)->find((int) $ticket->user_id);
            if ($userPelapor !== null) {
                $pelaporEmail = trim((string) ($userPelapor->email ?? ''));
            }
        }

        $bodyPel = 'Status tiket laporan Anda pada e-Honai Connect telah diperbarui.' . "\n\n"
            . 'Nomor tiket: ' . $ticket->nomor_tiket . "\n"
            . 'Judul: ' . $ticket->judul_laporan . "\n"
            . 'Status sebelumnya: ' . $oldStatus . "\n"
            . 'Status saat ini: ' . $newStatus . "\n\n"
            . 'Silakan buka aplikasi untuk melihat detail tiket.';

        sendNotification(
            $pelaporEmail !== '' ? $pelaporEmail : null,
            '[e-Honai Connect] Pembaruan status tiket ' . $ticket->nomor_tiket,
            $bodyPel
        );

        $pelaporPhone = ehonai_resolve_ticket_pelapor_phone($ticket);
        if ($pelaporPhone !== null) {
            sendWhatsAppNotification(
                $pelaporPhone,
                ehonai_whatsapp_ticket_status_message($ticket, $oldStatus, $newStatus)
            );
        }

        return redirect()->to('/ticket/show/' . $id)->with('success', 'Status tiket diperbarui.');
    }

    public function reply(): RedirectResponse
    {
        if (! $this->request->is('post')) {
            return redirect()->to('/ticket')->with('error', 'Metode tidak diizinkan.');
        }

        $rules = [
            'ticket_id' => 'required|is_natural_no_zero',
            'pesan'     => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $ticketId = (int) $this->request->getPost('ticket_id');

        /** @var TicketModel $ticketModel */
        $ticketModel = model(TicketModel::class);
        $ticket      = $ticketModel->find($ticketId);

        if ($ticket === null) {
            return redirect()->to('/ticket')->with('error', 'Tiket tidak ditemukan.');
        }

        $role = (string) session()->get('role');
        $uid  = (int) session()->get('id');

        if ($role === 'opd' && (int) $ticket->user_id !== $uid) {
            return redirect()->to('/ticket')->with('error', 'Anda tidak dapat membalas tiket ini.');
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'replies' . DIRECTORY_SEPARATOR;
        if (! is_dir($uploadDir) && ! mkdir($uploadDir, 0755, true) && ! is_dir($uploadDir)) {
            return redirect()->back()->with('error', 'Folder upload balasan tidak dapat dibuat.');
        }

        $lampiranNama = null;
        $file         = $this->request->getFile('lampiran');
        if ($file !== null && $file->isValid() && ! $file->hasMoved()) {
            if (! in_array($file->getMimeType(), [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
            ], true)) {
                return redirect()->back()->with('error', 'Lampiran balasan harus berupa gambar (JPG, PNG, WEBP, atau GIF).');
            }
            if ($file->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()->with('error', 'Ukuran gambar maksimal 2 MB.');
            }
            $newName      = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $lampiranNama = $newName;
        }

        /** @var TicketReplyModel $replyModel */
        $replyModel = model(TicketReplyModel::class);
        $inserted   = $replyModel->insert([
            'ticket_id' => $ticketId,
            'user_id'   => $uid,
            'pesan'     => (string) $this->request->getPost('pesan'),
            'lampiran'  => $lampiranNama,
        ]);

        if ($inserted === false) {
            return redirect()->back()->withInput()->with('error', implode(' ', $replyModel->errors()));
        }

        return redirect()->to('/ticket/show/' . $ticketId)->with('success', 'Balasan berhasil dikirim.');
    }
}
