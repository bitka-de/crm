<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

final class DocumentsPageRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        $_POST = [];
        $_SESSION['auth_user'] = 'admin';
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    public function testDocumentsPageRendersAllCreationForms(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/documents';

        ob_start();
        require __DIR__ . '/../../public/index.php';
        $output = (string) ob_get_clean();

        self::assertStringContainsString('Angebote, Rechnungen und Mahnungen', $output);
        self::assertStringContainsString('name="customer_name"', $output);
        self::assertStringContainsString('name="discount_percent"', $output);
        self::assertStringContainsString('name="vat_percent"', $output);
        self::assertStringContainsString('name="item_description[]"', $output);
        self::assertStringContainsString('name="item_quantity[]"', $output);
        self::assertStringContainsString('name="item_unit_price[]"', $output);
        self::assertStringContainsString('name="invoice_number"', $output);
        self::assertStringContainsString('name="level"', $output);
        self::assertStringContainsString('Bitte auswaehlen', $output);
        self::assertStringContainsString('Angebot erstellen', $output);
        self::assertStringContainsString('Rechnung erstellen', $output);
        self::assertStringContainsString('Mahnung erstellen', $output);
    }
}
