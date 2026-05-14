<?php
// Coded by DskyMC

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('login', 'AuthController::index');
$routes->post('login', 'AuthController::process');
$routes->get('logout', 'AuthController::logout');
