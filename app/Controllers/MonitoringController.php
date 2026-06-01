<?php
// Coded by DskyMC

namespace App\Controllers;

use App\Models\MonitoringEventModel;
use App\Models\MonitoredDeviceModel;
use App\Models\TicketModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use Config\Services;

class MonitoringController extends BaseController
{
    protected function ensureAdmin(): ?RedirectResponse
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya Admin yang dapat mengakses monitoring perangkat.');
        }

        return null;
    }

    public function index(): RedirectResponse|string
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $filter = (string) ($this->request->getGet('status') ?? 'all');
        if (! in_array($filter, ['all', 'up', 'down'], true)) {
            $filter = 'all';
        }
        $search = trim((string) ($this->request->getGet('q') ?? ''));

        /** @var MonitoredDeviceModel $deviceModel */
        $deviceModel = model(MonitoredDeviceModel::class);
        $devices     = $deviceModel->getAllOrdered($filter, $search !== '' ? $search : null);

        $librenms = Services::librenms();
        $useApi   = $devices === [] && $librenms->isConfigured();
        $apiList  = ['devices' => [], 'count' => 0];
        if ($useApi) {
            $type = $filter === 'down' ? 'down' : 'active';
            $apiList = $librenms->listDevices($type, $search !== '' ? $search : null);
        }

        return view('monitoring/index', [
            'devices'    => $devices,
            'apiDevices' => $apiList['devices'],
            'useApi'     => $useApi,
            'filter'     => $filter,
            'search'     => $search,
            'librenms'   => $librenms,
        ]);
    }

    public function device(string $hostname): RedirectResponse|string
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $hostname = rawurldecode($hostname);
        $librenms = Services::librenms();

        /** @var MonitoredDeviceModel $deviceModel */
        $deviceModel = model(MonitoredDeviceModel::class);
        $cached      = $deviceModel->findByHostname($hostname);

        $detail = $librenms->isConfigured() ? $librenms->getDevice($hostname) : null;

        if ($cached === null && $detail === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        /** @var TicketModel $ticketModel */
        $ticketModel = model(TicketModel::class);
        $openTicket  = null;
        if ($cached !== null) {
            $openTicket = $ticketModel->findOpenByLibrenmsDeviceId((int) $cached->librenms_device_id);
        }

        return view('monitoring/device', [
            'hostname'   => $hostname,
            'cached'     => $cached,
            'detail'     => $detail,
            'librenms'   => $librenms,
            'openTicket' => $openTicket,
        ]);
    }

    public function alerts(): RedirectResponse|string
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        /** @var MonitoringEventModel $eventModel */
        $eventModel = model(MonitoringEventModel::class);
        $events     = $eventModel->getRecent(100);

        $librenms = Services::librenms();
        $liveAlerts = $librenms->isConfigured() ? $librenms->listAlerts(1, 30) : [];

        return view('monitoring/alerts', [
            'events'     => $events,
            'liveAlerts' => $liveAlerts,
            'librenms'   => $librenms,
        ]);
    }
}
