<?php
// Coded by DskyMC

use Config\Email as EmailConfig;
use Config\Services;

if (! function_exists('ehonai_admin_notify_recipients')) {
    /**
     * @return list<string>
     */
    function ehonai_admin_notify_recipients(): array
    {
        $raw = (string) env('email.adminNotify', '');
        $parts = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $out   = [];
        foreach ($parts as $p) {
            if (filter_var($p, FILTER_VALIDATE_EMAIL)) {
                $out[] = $p;
            }
        }

        return array_values(array_unique($out));
    }
}

if (! function_exists('ehona_email_wrap_html')) {
    /**
     * Bungkus konten email dengan header resmi, logo Pemprov, dan gaya profesional (HTML + inline CSS).
     */
    function ehona_email_wrap_html(string $innerBodyHtml): string
    {
        $logoUrl = 'https://bppkad.papuapegunungan.go.id/wp-content/uploads/2023/05/logo-papua-pegunungan.png';
        $title   = 'e-Honai Connect';
        $sub     = 'Dinas Komunikasi, Informatika, Persandian dan Statistik';
        $sub2    = 'Pemerintah Provinsi Papua Pegunungan';

        // Jangan gunakan esc(..., 'url') di sini: rawurlencode seluruh URL membuat src="https%3A%2F%2F..."
        // dan banyak klien email membuang atribut src yang tidak valid, sehingga hanya tersisa tag <img> kosong.
        $logoUrlEsc = esc($logoUrl, 'attr');

        return '<!DOCTYPE html>'
            . '<html lang="id" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">'
            . '<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<meta http-equiv="X-UA-Compatible" content="IE=edge">'
            . '<title>' . esc($title) . '</title>'
            . '<!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->'
            . '</head>'
            . '<body style="margin:0;padding:0;background-color:#e8eef5;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">'
            . '<div style="display:none;font-size:1px;color:#e8eef5;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">'
            . esc($title) . ' — ' . esc($sub)
            . '</div>'
            . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#e8eef5;">'
            . '<tr><td align="center" style="padding:32px 16px;">'
            . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 8px 32px rgba(12,53,105,0.14);border:1px solid #d7e0ea;">'

            . '<tr><td style="padding:28px 32px 20px;text-align:center;background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);">'
            . '<img src="' . $logoUrlEsc . '" width="112" alt="Logo Provinsi Papua Pegunungan" border="0" '
            . 'style="display:block;margin:0 auto;max-width:140px;width:112px;height:auto;border:0;outline:none;text-decoration:none;">'
            . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:20px;">'
            . '<tr><td style="height:3px;background:linear-gradient(90deg,#0c3569 0%,#0d6efd 50%,#0d9488 100%);border-radius:2px;font-size:0;line-height:0;">&nbsp;</td></tr>'
            . '</table>'
            . '<p style="margin:18px 0 0;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:22px;font-weight:700;color:#0c3569;letter-spacing:-0.03em;line-height:1.25;">'
            . esc($title) . '</p>'
            . '<p style="margin:8px 0 0;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.06em;line-height:1.4;">'
            . esc($sub) . '</p>'
            . '<p style="margin:4px 0 0;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:12px;color:#64748b;line-height:1.45;">'
            . esc($sub2) . '</p>'
            . '</td></tr>'

            . '<tr><td style="padding:0 32px 8px;">'
            . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-top:1px solid #e2e8f0;">'
            . '<tr><td style="font-size:0;line-height:0;height:1px;">&nbsp;</td></tr></table>'
            . '</td></tr>'

            . '<tr><td style="padding:8px 32px 36px;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.65;color:#334155;">'
            . $innerBodyHtml
            . '</td></tr>'

            . '<tr><td style="padding:20px 32px;background:linear-gradient(180deg,#f1f5f9 0%,#e2e8f0 100%);border-top:1px solid #cbd5e1;">'
            . '<p style="margin:0;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:11px;line-height:1.5;color:#64748b;text-align:center;">'
            . 'Email ini dikirim otomatis oleh sistem <strong style="color:#475569;">e-Honai Connect</strong>. '
            . 'Mohon tidak membalas langsung ke alamat pengirim jika layanan balasan tidak tersedia.'
            . '</p>'
            . '<p style="margin:10px 0 0;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:11px;color:#94a3b8;text-align:center;">'
            . '&copy; ' . date('Y') . ' Diskominfosatik Provinsi Papua Pegunungan'
            . '</p>'
            . '</td></tr>'

            . '</table></td></tr></table></body></html>';
    }
}

if (! function_exists('sendNotification')) {
    /**
     * Kirim email notifikasi SMTP. Jika $to kosong/null, tidak mengirim apa pun.
     *
     * @param string|null $to      Alamat tujuan
     * @param string      $subject Judul email
     * @param string      $message Isi (teks biasa; akan di-escape dan diberi nl2br)
     */
    function sendNotification(?string $to, string $subject, string $message): bool
    {
        if ($to === null) {
            return false;
        }

        $to = trim($to);
        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        /** @var EmailConfig $cfg */
        $cfg = config(EmailConfig::class);

        if ($cfg->protocol !== 'smtp' || trim($cfg->SMTPHost) === '' || trim($cfg->fromEmail) === '') {
            log_message('notice', 'Email SMTP tidak dikonfigurasi lengkap; notifikasi dilewati.');

            return false;
        }

        $inner = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0;">'
            . '<tr><td style="background-color:#f8fafc;border-left:4px solid #0d6efd;border-radius:0 8px 8px 0;padding:18px 20px;">'
            . '<p style="margin:0;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.65;color:#334155;">'
            . nl2br(esc($message)) . '</p></td></tr></table>';
        $html  = ehona_email_wrap_html($inner);

        /** @var \CodeIgniter\Email\Email $email */
        $email = Services::email(null, false);
        $email->clear(true);
        $email->setFrom($cfg->fromEmail, $cfg->fromName !== '' ? $cfg->fromName : 'e-Honai Connect');
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($html);
        $email->setMailType('html');

        if (! $email->send()) {
            log_message('error', 'Gagal mengirim email: ' . $email->printDebugger(['headers']));

            return false;
        }

        return true;
    }
}
