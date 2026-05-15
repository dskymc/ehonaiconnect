<?php
// Coded by DskyMC

namespace App\Controllers;

use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\ResponseInterface;

class BackupController extends BaseController
{
    public function index(): ResponseInterface
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Fitur backup database hanya dapat diakses oleh Admin.');
        }

        $db = config('Database')->default;
        if (($db['DBDriver'] ?? '') !== 'MySQLi') {
            return redirect()->to('/dashboard')->with('error', 'Backup hanya didukung untuk MySQL/MariaDB (MySQLi).');
        }

        $database = (string) ($db['database'] ?? '');
        if ($database === '') {
            return redirect()->to('/dashboard')->with('error', 'Nama database tidak terkonfigurasi.');
        }

        $binary = $this->resolveMysqldumpBinary();
        $tmpBase = tempnam(sys_get_temp_dir(), 'ehonai_bak_');
        if ($tmpBase === false) {
            return redirect()->to('/dashboard')->with('error', 'Gagal membuat berkas sementara.');
        }

        unlink($tmpBase);
        $sqlPath = $tmpBase . '.sql';
        $errPath = $sqlPath . '.err';

        $exitCode = $this->runMysqldump($binary, $db, $sqlPath, $errPath);

        if ($exitCode !== 0 || ! is_readable($sqlPath)) {
            @unlink($sqlPath);
            @unlink($errPath);

            return redirect()->to('/dashboard')->with(
                'error',
                'Gagal membuat cadangan database. Pastikan utilitas mysqldump dapat dijalankan (XAMPP: biasanya di C:\\xampp\\mysql\\bin\\mysqldump.exe) atau setel backup.mysqldumpPath di file .env.'
            );
        }

        $downloadName = 'Backup_eHonaiConnect_' . date('Y-m-d_His') . '.sql';

        register_shutdown_function(static function () use ($sqlPath, $errPath): void {
            if (is_file($sqlPath)) {
                @unlink($sqlPath);
            }
            if (is_file($errPath)) {
                @unlink($errPath);
            }
        });

        $download = $this->response->download($sqlPath, null, false);
        if (! $download instanceof DownloadResponse) {
            @unlink($sqlPath);
            @unlink($errPath);

            return redirect()->to('/dashboard')->with('error', 'Gagal menyiapkan unduhan cadangan.');
        }

        $download->setFileName($downloadName);

        return $download;
    }

    private function resolveMysqldumpBinary(): string
    {
        $fromEnv = env('backup.mysqldumpPath', '');
        if (is_string($fromEnv) && $fromEnv !== '' && is_file($fromEnv)) {
            return $fromEnv;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $candidates = [
                'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            ];
            foreach ($candidates as $path) {
                if (is_file($path)) {
                    return $path;
                }
            }
        }

        return 'mysqldump';
    }

    /**
     * @param array<string, mixed> $db
     */
    private function runMysqldump(string $binary, array $db, string $outSql, string $errFile): int
    {
        $hostname = (string) ($db['hostname'] ?? '127.0.0.1');
        $username = (string) ($db['username'] ?? 'root');
        $password = (string) ($db['password'] ?? '');
        $database = (string) ($db['database'] ?? '');
        $port      = (int) ($db['port'] ?? 3306);

        $cmd = [
            $binary,
            '--single-transaction',
            '--skip-lock-tables',
            '--routines',
            '--triggers',
            '--default-character-set=utf8mb4',
            '--host=' . $hostname,
            '--port=' . (string) $port,
            '--user=' . $username,
            '--password=' . $password,
            $database,
        ];

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['file', $outSql, 'wb'],
            2 => ['file', $errFile, 'wb'],
        ];

        $options = is_file($binary) ? ['bypass_shell' => true] : [];

        $process = @proc_open($cmd, $descriptorspec, $pipes, null, null, $options);
        if (! is_resource($process)) {
            return -1;
        }

        if (isset($pipes[0]) && is_resource($pipes[0])) {
            fclose($pipes[0]);
        }

        return proc_close($process);
    }
}
