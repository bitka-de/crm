<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Middleware\AuthMiddleware;
use PHPUnit\Framework\TestCase;

final class AuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testAuthenticatedUserPassesThrough(): void
    {
        $_SESSION['auth_user'] = 'admin';

        $result = (new AuthMiddleware())->handle();

        self::assertNull($result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testUnauthenticatedUserIsRedirected(): void
    {
        $result = (new AuthMiddleware())->handle();

        self::assertSame('', $result);
        self::assertSame(302, http_response_code());
    }
}
