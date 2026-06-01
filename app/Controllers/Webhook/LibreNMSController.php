<?php
// Coded by DskyMC

namespace App\Controllers\Webhook;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class LibreNMSController extends BaseController
{
    public function receive(): ResponseInterface
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Method not allowed']);
        }

        helper(['librenms', 'whatsapp']);

        $ip = $this->request->getIPAddress();
        if (! ehonai_librenms_webhook_ip_allowed($ip)) {
            log_message('warning', 'LibreNMS webhook ditolak dari IP: ' . $ip);

            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Forbidden']);
        }

        if (! ehonai_librenms_webhook_token_valid($this->request)) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'message' => 'Unauthorized']);
        }

        $payload = $this->request->getJSON(true);
        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }
        if (! is_array($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Invalid payload']);
        }

        $result = ehonai_librenms_process_webhook_payload($payload);

        $status = $result['ok'] ? 200 : 422;

        return $this->response->setStatusCode($status)->setJSON($result);
    }
}
