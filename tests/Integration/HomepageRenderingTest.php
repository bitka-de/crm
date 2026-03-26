<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

final class HomepageRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Nutzer einloggen, damit AuthMiddleware passiert wird
        $_SESSION['auth_user'] = 'admin';
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testHomepageRendersLayoutAndComponents(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        ob_start();
        require __DIR__ . '/../../public/index.php';
        $output = (string) ob_get_clean();

        self::assertStringContainsString('<!DOCTYPE html>', $output);
        self::assertStringContainsString('Willkommen im CRM', $output);
        self::assertStringContainsString('/assets/css/app.css', $output);
        self::assertStringContainsString('/assets/js/app.js', $output);
    }
}