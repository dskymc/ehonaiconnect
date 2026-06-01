<?php
// Coded by DskyMC

namespace App\Controllers;

use App\Models\NotificationModel;
use App\Models\TicketModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $uid  = (int) session()->get('id');
        $role = (string) session()->get('role');

        /** @var TicketModel $ticketModel */
        $ticketModel = model(TicketModel::class);
        $ticketStats = $ticketModel->getDashboardTicketStats($role, $uid);

        $nmsSummary = Services::librenms()->getDashboardSummary();

        return view('dashboard/index', [
            'ticketStats' => $ticketStats,
            'nmsSummary'  => $nmsSummary,
            'showNms'     => session()->get('role') === 'admin',
        ]);
    }

    public function notificationsFeed(): ResponseInterface
    {
        $uid = (int) session()->get('id');

        /** @var NotificationModel $notificationModel */
        $notificationModel = model(NotificationModel::class);
        $items             = $notificationModel->getUnreadForUser($uid, 20);

        $payload = array_map(static function ($n) {
            return [
                'id'         => (int) $n->id,
                'message'    => $n->message,
                'ticket_id'  => (int) ($n->ticket_id ?? 0),
                'created_at' => $n->created_at,
            ];
        }, $items);

        return $this->response->setJSON(['items' => $payload]);
    }

    public function checkPing(): ResponseInterface
    {
        $host    = '8.8.8.8';
        $port    = 53;
        $timeout = 2.0;

        $t0 = microtime(true);
        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        $t1 = microtime(true);

        if ($fp === false) {
            $ping        = 0;
            $status_text = 'Jaringan Terputus / Down';
            $color       = 'danger';
        } else {
            fclose($fp);
            $ping = (int) round(($t1 - $t0) * 1000);

            if ($ping < 100) {
                $status_text = 'Kapasitas Jaringan Normal';
                $color       = 'success';
            } elseif ($ping >= 100 && $ping <= 250) {
                $status_text = 'Kapasitas Jaringan Medium';
                $color       = 'warning';
            } else {
                $status_text = 'Kapasitas Jaringan Berat';
                $color       = 'danger';
            }
        }

        return $this->response->setJSON([
            'ping'        => $ping,
            'status_text' => $status_text,
            'color'       => $color,
        ]);
    }
}
