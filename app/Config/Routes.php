<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->get('/form', 'Form::index');
$routes->get('/form/(:any)', 'Form::index/$1');
$routes->post('/form/submit', 'Form::submit');
use App\Controllers\Dashboard;

$routes->get('/', [Dashboard::class, 'index']);
$routes->get('dashboard', [Dashboard::class, 'index']);
$routes->get('page/(:any)', [Dashboard::class, 'show/$1']);