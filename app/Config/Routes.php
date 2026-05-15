<?php
// Coded by DskyMC

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('login', 'AuthController::index');
$routes->post('login', 'AuthController::process');
$routes->get('register', 'RegisterController::index');
$routes->post('register', 'RegisterController::process');
$routes->get('logout', 'AuthController::logout');

$routes->get('dashboard/notifications-feed', 'Dashboard::notificationsFeed', ['filter' => 'auth']);
$routes->get('dashboard/checkPing', 'Dashboard::checkPing', ['filter' => 'auth']);
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);

/*
|--------------------------------------------------------------------------
| Profil pengguna (nama & password)
|--------------------------------------------------------------------------
| GET  profile        — halaman profil
| POST profile/update — simpan perubahan
*/
$routes->get('profile', 'ProfileController::index', ['filter' => 'auth']);
$routes->post('profile/update', 'ProfileController::update', ['filter' => 'auth']);

/*
|--------------------------------------------------------------------------
| Manajemen pengguna (hak akses Admin dicek di UserController::ensureAdmin)
|--------------------------------------------------------------------------
| GET  user                      — daftar pengguna
| POST user/store               — tambah pengguna
| POST user/update-password/(:num) — reset password
| POST user/delete/(:num)      — hapus permanen
| GET  user/toggle-status/(:num) — aktifkan / nonaktifkan OPD
*/
$routes->group('user', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'UserController::index');
    $routes->post('store', 'UserController::store');
    $routes->post('update-password/(:num)', 'UserController::updatePassword/$1');
    $routes->post('delete/(:num)', 'UserController::delete/$1');
    $routes->get('toggle-status/(:num)', 'UserController::toggleStatus/$1');
});

$routes->group('ticket', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'TicketController::index');
    $routes->get('create', 'TicketController::create');
    $routes->post('store', 'TicketController::store');
    $routes->get('show/(:num)', 'TicketController::show/$1');
    $routes->post('reply', 'TicketController::reply');
    $routes->post('update-status/(:num)', 'TicketController::updateStatus/$1');
});

/*
|--------------------------------------------------------------------------
| Laporan bulanan rekap tiket (Admin)
|--------------------------------------------------------------------------
| GET report       — filter & tabel rekap
| GET report/print — halaman cetak HTML (tab baru)
*/
$routes->group('report', ['filter' => 'admin'], static function ($routes) {
    $routes->get('/', 'ReportController::index');
    $routes->get('print', 'ReportController::print');
});

$routes->get('backup', 'BackupController::index', ['filter' => 'admin']);
