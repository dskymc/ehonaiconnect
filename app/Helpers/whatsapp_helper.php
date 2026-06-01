<?php
// Coded by DskyMC

use App\Libraries\WhatsAppService;
use App\Models\UserModel;
use Config\Services;

if (! function_exists('ehonai_whatsapp_message_header')) {
    function ehonai_whatsapp_message_header(string $sectionTitle): string
    {
        return '*e-Honai Connect*' . "\n"
            . 'Sistem Ticketing & Monitoring Jaringan OPD' . "\n"
            . '━━━━━━━━━━━━━━━━━━━━' . "\n\n"
            . $sectionTitle . "\n\n";
    }
}

if (! function_exists('ehonai_whatsapp_message_footer')) {
    function ehonai_whatsapp_message_footer(): string
    {
        return '_Pesan otomatis — mohon tidak membalas langsung._' . "\n"
            . '© Diskominfosatik Provinsi Papua Pegunungan';
    }
}

if (! function_exists('ehonai_admin_notify_whatsapp_numbers')) {
    /**
     * @return list<string>
     */
    function ehonai_admin_notify_whatsapp_numbers(): array
    {
        $raw   = (string) env('whatsapp.adminNotify', '');
        $parts = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $out   = [];

        $service = Services::whatsapp(false);
        foreach ($parts as $part) {
            $normalized = $service->normalizePhoneNumber($part);
            if ($normalized !== null) {
                $out[] = $normalized;
            }
        }

        return array_values(array_unique($out));
    }
}

if (! function_exists('ehonai_send_whatsapp_to_admins')) {
    function ehonai_send_whatsapp_to_admins(string $message): void
    {
        foreach (ehonai_admin_notify_whatsapp_numbers() as $adminPhone) {
            sendWhatsAppNotification($adminPhone, $message);
        }
    }
}

if (! function_exists('ehonai_resolve_ticket_pelapor_phone')) {
    function ehonai_resolve_ticket_pelapor_phone(object $ticket): ?string
    {
        $phone = trim((string) ($ticket->no_hp_pelapor ?? ''));
        if ($phone !== '') {
            return $phone;
        }

        $phone = trim((string) ($ticket->pelapor_user_no_hp ?? ''));
        if ($phone !== '') {
            return $phone;
        }

        $userId = (int) ($ticket->user_id ?? 0);
        if ($userId > 0) {
            $user = model(UserModel::class)->find($userId);
            if ($user !== null) {
                $phone = trim((string) ($user->no_hp ?? ''));
                if ($phone !== '') {
                    return $phone;
                }
            }
        }

        return null;
    }
}

if (! function_exists('ehonai_whatsapp_new_ticket_message')) {
    /**
     * Template WhatsApp untuk admin — tiket baru masuk.
     *
     * @param object $ticket Objek tiket (disarankan dari TicketModel::getTicketWithPelapor)
     */
    function ehonai_whatsapp_new_ticket_message(object $ticket): string
    {
        $nomorTiket = (string) ($ticket->nomor_tiket ?? '-');
        $namaOpd    = trim((string) ($ticket->pelapor_instansi_opd ?? $ticket->pelapor_instansi ?? '-'));
        if ($namaOpd === '') {
            $namaOpd = '-';
        }
        $status    = (string) ($ticket->status ?? 'Baru');
        $judul     = (string) ($ticket->judul_laporan ?? '-');
        $kategori  = (string) ($ticket->kategori ?? '-');
        $prioritas = (string) ($ticket->prioritas ?? '-');

        return ehonai_whatsapp_message_header('📋 *Notifikasi Tiket Baru*')
            . 'ID Tiket   : *' . $nomorTiket . '*' . "\n"
            . 'Nama OPD   : ' . $namaOpd . "\n"
            . 'Status     : *' . $status . '*' . "\n\n"
            . 'Judul      : ' . $judul . "\n"
            . 'Kategori   : ' . $kategori . "\n"
            . 'Prioritas  : ' . $prioritas . "\n\n"
            . 'Silakan tinjau tiket di panel Admin.' . "\n\n"
            . ehonai_whatsapp_message_footer();
    }
}

if (! function_exists('ehonai_whatsapp_new_ticket_pelapor_message')) {
    /**
     * Template WhatsApp untuk pelapor — konfirmasi tiket berhasil dibuat.
     */
    function ehonai_whatsapp_new_ticket_pelapor_message(object $ticket): string
    {
        $nomorTiket = (string) ($ticket->nomor_tiket ?? '-');
        $namaOpd    = trim((string) ($ticket->pelapor_instansi_opd ?? $ticket->pelapor_instansi ?? '-'));
        if ($namaOpd === '') {
            $namaOpd = '-';
        }
        $status = (string) ($ticket->status ?? 'Baru');
        $judul  = (string) ($ticket->judul_laporan ?? '-');

        return ehonai_whatsapp_message_header('✅ *Laporan Anda Telah Diterima*')
            . 'Terima kasih, laporan Anda telah kami terima.' . "\n\n"
            . 'ID Tiket   : *' . $nomorTiket . '*' . "\n"
            . 'Nama OPD   : ' . $namaOpd . "\n"
            . 'Status     : *' . $status . '*' . "\n\n"
            . 'Judul      : ' . $judul . "\n\n"
            . 'Tim kami akan segera menindaklanjuti laporan Anda. '
            . 'Silakan buka aplikasi e-Honai Connect untuk memantau perkembangan tiket.' . "\n\n"
            . ehonai_whatsapp_message_footer();
    }
}

if (! function_exists('ehonai_whatsapp_ticket_status_message')) {
    /**
     * Template WhatsApp untuk pelapor — pembaruan status tiket.
     */
    function ehonai_whatsapp_ticket_status_message(object $ticket, string $oldStatus, string $newStatus): string
    {
        $nomorTiket = (string) ($ticket->nomor_tiket ?? '-');
        $namaOpd    = trim((string) ($ticket->pelapor_instansi ?? '-'));
        if ($namaOpd === '' && (int) ($ticket->user_id ?? 0) > 0) {
            $user = model(UserModel::class)->find((int) $ticket->user_id);
            if ($user !== null) {
                $namaOpd = trim((string) ($user->instansi_opd ?? ''));
            }
        }
        if ($namaOpd === '') {
            $namaOpd = '-';
        }
        $judul = (string) ($ticket->judul_laporan ?? '-');

        return ehonai_whatsapp_message_header('🔄 *Pembaruan Status Tiket*')
            . 'Status tiket laporan Anda telah diperbarui.' . "\n\n"
            . 'ID Tiket   : *' . $nomorTiket . '*' . "\n"
            . 'Nama OPD   : ' . $namaOpd . "\n"
            . 'Judul      : ' . $judul . "\n\n"
            . 'Status lama : ' . $oldStatus . "\n"
            . 'Status baru : *' . $newStatus . '*' . "\n\n"
            . 'Silakan buka aplikasi e-Honai Connect untuk melihat detail tiket.' . "\n\n"
            . ehonai_whatsapp_message_footer();
    }
}

if (! function_exists('ehonai_whatsapp_opd_registration_admin_message')) {
    /**
     * Template WhatsApp untuk admin — registrasi OPD baru menunggu verifikasi.
     */
    function ehonai_whatsapp_opd_registration_admin_message(
        string $namaLengkap,
        string $username,
        string $instansiOpd,
        string $noHp,
        string $email
    ): string {
        return ehonai_whatsapp_message_header('👤 *Registrasi OPD Baru*')
            . 'OPD baru mendaftar melalui formulir registrasi e-Honai Connect.' . "\n\n"
            . 'Nama Lengkap : ' . $namaLengkap . "\n"
            . 'Username     : ' . $username . "\n"
            . 'Instansi OPD : ' . $instansiOpd . "\n"
            . 'WhatsApp     : ' . $noHp . "\n"
            . 'Email        : ' . $email . "\n\n"
            . 'Silakan lakukan verifikasi akun di menu Manajemen OPD (Admin).' . "\n\n"
            . ehonai_whatsapp_message_footer();
    }
}

if (! function_exists('ehonai_whatsapp_opd_activated_message')) {
    /**
     * Template WhatsApp untuk OPD — akun telah diaktifkan.
     */
    function ehonai_whatsapp_opd_activated_message(object $user): string
    {
        $nama     = (string) ($user->nama_lengkap ?? 'Bapak/Ibu');
        $username = (string) ($user->username ?? '-');

        return ehonai_whatsapp_message_header('🎉 *Akun OPD Diaktifkan*')
            . 'Yth. ' . $nama . ',' . "\n\n"
            . 'Akun OPD Anda pada aplikasi e-Honai Connect telah *diaktifkan* '
            . 'oleh Admin Diskominfosatik Provinsi Papua Pegunungan.' . "\n\n"
            . 'Username : *' . $username . '*' . "\n\n"
            . 'Anda sekarang dapat masuk (login) ke aplikasi e-Honai Connect.' . "\n\n"
            . 'Terima kasih.' . "\n\n"
            . ehonai_whatsapp_message_footer();
    }
}

if (! function_exists('sendWhatsAppNotification')) {
    /**
     * Kirim notifikasi WhatsApp via Fonnte. Jika $target kosong/null, tidak mengirim apa pun.
     *
     * @param string|null $target  Nomor WhatsApp tujuan
     * @param string      $message Isi pesan (teks biasa, mendukung format *bold* WhatsApp)
     */
    function sendWhatsAppNotification(?string $target, string $message): bool
    {
        if ($target === null) {
            return false;
        }

        $target = trim($target);
        if ($target === '') {
            return false;
        }

        /** @var WhatsAppService $service */
        $service = Services::whatsapp();

        return $service->send($target, $message);
    }
}
