<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Core\Router;

require_once __DIR__ . '/../vendor/autoload.php';

$router = new Router();

$router->get('/', [HomeController::class, 'index']);

return $router;