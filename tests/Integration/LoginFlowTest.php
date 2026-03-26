<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

final class LoginFlowTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        $_POST = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    public function testLoginPageRendersForm(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/login';

        ob_start();
        require __DIR__ . '/../../public/index.php';
        $output = (string) ob_get_clean();

        self::assertStringContainsString('<form', $output);
        self::assertStringContainsString('name="username"', $output);
        self::assertStringContainsString('name="password"', $output);
        self::assertStringContainsString('Anmelden', $output);
    }

    public function testLoginPageUsesLayout(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/login';

        ob_start();
        require __DIR__ . '/../../public/index.php';
        $output = (string) ob_get_clean();

        self::assertStringContainsString('<!DOCTYPE html>', $output);
        self::assertStringContainsString('/assets/css/app.css', $output);
        self::assertStringContainsString('/assets/js/app.js', $output);
    }

    public function testLoginPageShowsErrorFromSession(): void
    {
        $_SESSION['auth_error'] = 'Ungueltige Zugangsdaten. Bitte erneut versuchen.';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/login';

        ob_start();
        require __DIR__ . '/../../public/index.php';
        $output = (string) ob_get_clean();

        self::assertStringContainsString('Ungueltige Zugangsdaten', $output);
        self::assertStringContainsString('auth-error', $output);
    }

    public function testProtectedRouteRedirectsWhenNotLoggedIn(): void
    {
        $_SESSION = [];

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        ob_start();
        require __DIR__ . '/../../public/index.php';
        $output = (string) ob_get_clean();

        // AuthMiddleware gibt leeren String zurueck, kein HTML
        self::assertStringNotContainsString('<!DOCTYPE html>', $output);
        self::assertSame(302, http_response_code());
    }
}
