<?php

declare(strict_types=1);

use App\Core\Router;

/** @var Router $router */
$router = require __DIR__ . '/../app/bootstrap.php';

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);