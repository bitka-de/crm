<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CompanyController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Core\Router;
use App\Core\Session;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

Session::start();

$router = new Router();

// Oeffentliche Routen
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Geschuetzte Routen
$router->get('/', [HomeController::class, 'index'], [AuthMiddleware::class]);
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/company', [CompanyController::class, 'index'], [AuthMiddleware::class]);
$router->post('/company', [CompanyController::class, 'update'], [AuthMiddleware::class]);

return $router;