<?php
// Coded by DskyMC

namespace App\Controllers;

use App\Models\AuditLogModel;
use CodeIgniter\HTTP\RedirectResponse;

class AuditController extends BaseController
{
    public function index(): RedirectResponse|string
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Log sistem hanya dapat diakses oleh Admin.');
        }

        /** @var AuditLogModel $auditLogModel */
        $auditLogModel = model(AuditLogModel::class);
        $logs          = $auditLogModel->getLogsWithUser(500);

        return view('audit/index', ['logs' => $logs]);
    }
}
