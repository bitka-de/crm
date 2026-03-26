<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Auth;
use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase
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

    public function testSuccessfulLoginReturnsTrueAndSetsSession(): void
    {
        $result = Auth::attempt('admin', '1234');

        self::assertTrue($result);
        self::assertTrue(Auth::check());
        self::assertSame('admin', Auth::user());
    }

    public function testWrongPasswordReturnsFalse(): void
    {
        $result = Auth::attempt('admin', 'falsch');

        self::assertFalse($result);
        self::assertFalse(Auth::check());
        self::assertNull(Auth::user());
    }

    public function testUnknownUserReturnsFalse(): void
    {
        $result = Auth::attempt('unbekannt', '1234');

        self::assertFalse($result);
        self::assertFalse(Auth::check());
    }

    public function testCheckReturnsFalseWhenNotLoggedIn(): void
    {
        self::assertFalse(Auth::check());
        self::assertNull(Auth::user());
    }

    public function testLogoutClearsSession(): void
    {
        Auth::attempt('admin', '1234');
        self::assertTrue(Auth::check());

        Auth::logout();

        self::assertFalse(Auth::check());
        self::assertNull(Auth::user());
    }

    public function testEmptyCredentialsFail(): void
    {
        self::assertFalse(Auth::attempt('', ''));
        self::assertFalse(Auth::attempt('admin', ''));
        self::assertFalse(Auth::attempt('', '1234'));
    }
}
