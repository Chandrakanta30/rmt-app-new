<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Dashboard;
// use CodeIgniter\Controllers\Dashboard
/**
 * @var RouteCollection $routes
 */

$routes->get('/', [Dashboard::class, 'index']);
$routes->get('dashboard', [Dashboard::class, 'index']);
$routes->get('page/(:any)', [Dashboard::class, 'show/$1']);

// $routes->get('/', 'Home::index');
$routes->get('/forms', 'Form::listing');
$routes->get('/form', 'Form::index');
$routes->get('/form/(:any)', 'Form::index/$1');
$routes->post('/form/submit', 'Form::submit');
