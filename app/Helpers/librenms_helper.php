<?php
// Coded by DskyMC

use App\Models\AuditLogModel;
use App\Models\MonitoringEventModel;
use App\Models\TicketModel;
use App\Models\UserModel;
use Config\Services;

if (! function_exists('ehonai_librenms_webhook_token_valid')) {
    function ehonai_librenms_webhook_token_valid(\CodeIgniter\HTTP\IncomingRequest $request): bool
    {
        $secret = trim((string) env('LIBRENMS_WEBHOOK_SECRET', ''));
        if ($secret === '') {
            return false;
        }

        $token = trim((string) $request->getHeaderLine('X-Webhook-Token'));
        if ($token === '') {
            $token = trim((string) $request->getGet('token'));
        }

        return $token !== '' && hash_equals($secret, $token);
    }
}

if (! function_exists('ehonai_librenms_webhook_ip_allowed')) {
    function ehonai_librenms_webhook_ip_allowed(string $ip): bool
    {
        $raw = trim((string) env('librenms.webhookAllowIps', ''));
        if ($raw === '') {
            return true;
        }

        $allowed = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        if ($allowed === false) {
            return true;
        }

        return in_array($ip, $allowed, true);
    }
}

if (! function_exists('ehonai_librenms_parse_alert_state')) {
    /**
     * @param array<string, mixed> $payload
     */
    function ehonai_librenms_parse_alert_state(array $payload): string
    {
        $state = $payload['state'] ?? $payload['alert_state'] ?? '';
        if (is_numeric($state)) {
            return (int) $state === 0 ? 'ok' : 'alert';
        }

        $state = strtolower(trim((string) $state));
        if (in_array($state, ['ok', 'recovered', 'recovery', 'clear', 'cleared'], true)) {
            return 'ok';
        }

        return 'alert';
    }
}

if (! function_exists('ehonai_librenms_whatsapp_alert_message')) {
    function ehonai_librenms_whatsapp_alert_message(
        string $hostname,
        string $title,
        string $severity,
        ?string $nomorTiket = null
    ): string {
        $header = ehonai_whatsapp_message_header('⚠️ *Alert LibreNMS*');
        $body   = 'Perangkat : *' . $hostname . '*' . "\n"
            . 'Alert     : ' . $title . "\n"
            . 'Severity  : *' . $severity . '*';
        if ($nomorTiket !== null && $nomorTiket !== '') {
            $body .= "\n" . 'Tiket     : *' . $nomorTiket . '*';
        }

        return $header . $body . "\n\n"
            . 'Silakan tinjau di panel Monitoring / LibreNMS.' . "\n\n"
            . ehonai_whatsapp_message_footer();
    }
}

if (! function_exists('ehonai_librenms_process_webhook_payload')) {
    /**
     * Proses payload webhook LibreNMS: simpan event, buat tiket jika perlu.
     *
     * @param array<string, mixed> $payload
     * @return array{ok: bool, message: string, ticket_id?: int, event_id?: int}
     */
    function ehonai_librenms_process_webhook_payload(array $payload): array
    {
        $hostname = trim((string) ($payload['hostname'] ?? $payload['host'] ?? ''));
        $deviceId = (int) ($payload['device_id'] ?? 0);
        $alertId  = (int) ($payload['alert_id'] ?? $payload['id'] ?? 0);
        $title    = trim((string) ($payload['title'] ?? ''));
        $message  = trim((string) ($payload['msg'] ?? $payload['message'] ?? ''));
        $severity = strtolower(trim((string) ($payload['severity'] ?? 'warning')));
        $state    = ehonai_librenms_parse_alert_state($payload);

        if ($hostname === '' && $deviceId <= 0) {
            return ['ok' => false, 'message' => 'hostname atau device_id wajib'];
        }

        $dedupeKey = 'lnms:' . $deviceId . ':' . $alertId . ':' . $state . ':' . md5($title . $message);

        /** @var MonitoringEventModel $eventModel */
        $eventModel = model(MonitoringEventModel::class);
        if ($eventModel->existsByDedupeKey($dedupeKey)) {
            return ['ok' => true, 'message' => 'Event duplikat dilewati'];
        }

        /** @var TicketModel $ticketModel */
        $ticketModel = model(TicketModel::class);
        $ticketId    = null;

        $severityNorm = $severity;
        if (is_numeric($severity) && (int) $severity >= 3) {
            $severityNorm = 'critical';
        } elseif (is_numeric($severity) && (int) $severity >= 2) {
            $severityNorm = 'warning';
        }
        if ($state === 'alert' && $severityNorm === '') {
            $severityNorm = 'warning';
        }

        if ($state === 'alert' && in_array($severityNorm, ['warning', 'critical', 'critical alert', 'warn'], true) && $deviceId > 0) {
            $existing = $ticketModel->findOpenByLibrenmsDeviceId($deviceId);
            if ($existing === null) {
                $prioritas = in_array($severityNorm, ['critical', 'critical alert'], true) ? 'High' : 'Medium';
                $judul     = '[NMS] ' . ($hostname !== '' ? $hostname : 'Device #' . $deviceId);
                if ($title !== '') {
                    $judul .= ' — ' . $title;
                }
                $deskripsi = "Alert otomatis dari LibreNMS.\n\n"
                    . 'Hostname: ' . $hostname . "\n"
                    . 'Device ID: ' . $deviceId . "\n"
                    . 'Severity: ' . $severityNorm . "\n"
                    . 'Judul alert: ' . $title . "\n\n"
                    . $message;

                $ticketId = $ticketModel->createFromLibrenmsAlert([
                    'librenms_device_id' => $deviceId,
                    'librenms_alert_id'  => $alertId > 0 ? $alertId : null,
                    'librenms_hostname'  => $hostname !== '' ? $hostname : null,
                    'judul_laporan'      => mb_substr($judul, 0, 255),
                    'deskripsi'          => $deskripsi,
                    'prioritas'          => $prioritas,
                    'kategori'           => 'Monitoring Jaringan',
                ]);

                if ($ticketId !== null) {
                    helper(['email', 'whatsapp']);
                    $ticketRow = $ticketModel->find($ticketId);
                    $nomor     = $ticketRow !== null ? (string) $ticketRow->nomor_tiket : '';

                    $bodyAdmin = 'Tiket otomatis dari alert LibreNMS.' . "\n\n"
                        . 'Nomor tiket: ' . $nomor . "\n"
                        . 'Perangkat: ' . $hostname . "\n"
                        . 'Severity: ' . $severityNorm . "\n"
                        . 'Alert: ' . $title . "\n\n"
                        . $message;

                    foreach (ehonai_admin_notify_recipients() as $adminEmail) {
                        sendNotification(
                            $adminEmail,
                            '[e-Honai Connect] Alert NMS — tiket ' . $nomor,
                            $bodyAdmin
                        );
                    }

                    ehonai_send_whatsapp_to_admins(
                        ehonai_librenms_whatsapp_alert_message($hostname, $title !== '' ? $title : 'Alert perangkat', $severityNorm, $nomor)
                    );
                }
            } else {
                $ticketId = (int) $existing->id;
            }
        }

        if ($state === 'ok' && $deviceId > 0) {
            $open = $ticketModel->findOpenByLibrenmsDeviceId($deviceId);
            if ($open !== null) {
                $ticketModel->update((int) $open->id, [
                    'status' => 'Tertunda',
                ]);
                $ticketId = (int) $open->id;
            }
        }

        $eventModel->insert([
            'librenms_device_id' => $deviceId > 0 ? $deviceId : null,
            'librenms_alert_id'  => $alertId > 0 ? $alertId : null,
            'hostname'           => $hostname !== '' ? $hostname : null,
            'severity'           => $severity,
            'state'              => $state,
            'title'              => $title !== '' ? $title : null,
            'message'            => $message !== '' ? $message : null,
            'dedupe_key'         => $dedupeKey,
            'payload'            => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'ticket_id'          => $ticketId,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        $eventId = (int) $eventModel->getInsertID();

        $adminUser = model(UserModel::class)->where('role', 'admin')->orderBy('id', 'ASC')->first();
        $auditUserId = $adminUser !== null ? (int) $adminUser->id : 1;

        /** @var AuditLogModel $audit */
        $audit = model(AuditLogModel::class);
        $audit->insert([
            'user_id'    => $auditUserId,
            'aksi'       => 'WEBHOOK_LIBRENMS_ALERT',
            'deskripsi'  => 'Webhook LibreNMS: ' . $hostname . ' state=' . $state . ' severity=' . $severity
                . ($ticketId !== null ? ' ticket_id=' . $ticketId : ''),
            'ip_address' => mb_substr(service('request')->getIPAddress(), 0, 45),
        ]);

        return [
            'ok'        => true,
            'message'   => 'Event diproses',
            'ticket_id' => $ticketId,
            'event_id'  => $eventId,
        ];
    }
}
