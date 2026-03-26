<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testDispatchesClosureRoute(): void
    {
        $router = new Router();
        $router->get('/health', static fn (): string => 'ok');

        ob_start();
        $router->dispatch('GET', '/health');
        $output = (string) ob_get_clean();

        self::assertSame('ok', $output);
    }

    public function testDispatchesControllerRoute(): void
    {
        $router = new Router();
        $router->get('/controller', [RouterTestController::class, 'index']);

        ob_start();
        $router->dispatch('GET', '/controller');
        $output = (string) ob_get_clean();

        self::assertSame('controller-ok', $output);
    }

    public function testReturns404ForUnknownRoute(): void
    {
        $router = new Router();

        ob_start();
        $router->dispatch('GET', '/missing');
        $output = (string) ob_get_clean();

        self::assertSame('404 Not Found', $output);
    }
}

final class RouterTestController
{
    public function index(): string
    {
        return 'controller-ok';
    }
}