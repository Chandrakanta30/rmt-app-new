<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Dashboard;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\RoleController;
use App\Controllers\PermissionController;
use App\Controllers\AsrController;

/**
 * @var RouteCollection $routes
 */

// Guest routes
$routes->get('login', [AuthController::class, 'login']);
$routes->post('login', [AuthController::class, 'loginSubmit']);
$routes->get('logout', [AuthController::class, 'logout']);

// Dashboard & Base routes
$routes->get('/', [Dashboard::class, 'index']);
$routes->get('dashboard', [Dashboard::class, 'index']);
$routes->get('page/(:any)', [Dashboard::class, 'show/$1']);

// Form routes with granular permission checks
$routes->group('', ['filter' => 'permission:view_forms'], function($routes) {
    $routes->get('forms', 'Form::listing');
    $routes->get('form', 'Form::index');
    $routes->get('form/(:any)', 'Form::index/$1');
});

$routes->post('form/status', 'Form::updateStatus', ['filter' => 'permission:view_forms']);
$routes->post('form/submit', 'Form::submit', ['filter' => 'permission:submit_data']);

// ASR No. routes
$routes->group('asr-mapping', ['filter' => 'permission:create_asrno'], function($routes) {
    $routes->get('/', [AsrController::class, 'index']);
    $routes->post('store', [AsrController::class, 'store']);
});

$routes->post('asr-mapping/delete/(:num)', [AsrController::class, 'delete/$1'], ['filter' => 'permission:delete_asrno']);
$routes->post('asr-mapping/update/(:num)', [AsrController::class, 'update/$1'], ['filter' => 'permission:update_asrno']);

// Admin management routes (protected by specific permission checks)
$routes->group('users', ['filter' => 'permission:manage_users'], function($routes) {
    $routes->get('/', [UserController::class, 'index']);
    $routes->get('create', [UserController::class, 'create']);
    $routes->post('store', [UserController::class, 'store']);
    $routes->get('edit/(:num)', [UserController::class, 'edit/$1']);
    $routes->post('update/(:num)', [UserController::class, 'update/$1']);
    $routes->get('delete/(:num)', [UserController::class, 'delete/$1']);
});

$routes->group('roles', ['filter' => 'permission:manage_roles'], function($routes) {
    $routes->get('/', [RoleController::class, 'index']);
    $routes->get('create', [RoleController::class, 'create']);
    $routes->post('store', [RoleController::class, 'store']);
    $routes->get('edit/(:num)', [RoleController::class, 'edit/$1']);
    $routes->post('update/(:num)', [RoleController::class, 'update/$1']);
    $routes->get('delete/(:num)', [RoleController::class, 'delete/$1']);
});

$routes->group('permissions', ['filter' => 'permission:manage_permissions'], function($routes) {
    $routes->get('/', [PermissionController::class, 'index']);
    $routes->post('store', [PermissionController::class, 'store']);
    $routes->get('delete/(:num)', [PermissionController::class, 'delete/$1']);
});
$routes->post('form/update_status/(:num)', 'Form::updateStatus/$1');