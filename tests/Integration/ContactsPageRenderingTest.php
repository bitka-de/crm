<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

final class ContactsPageRenderingTest extends TestCase
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

    public function testContactsPageRendersTabsAndForms(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/contacts';

        ob_start();
        require __DIR__ . '/../../public/index.php';
        $output = (string) ob_get_clean();

        self::assertStringContainsString('Kontakte, Status und Firmen', $output);
        self::assertStringContainsString('data-contacts-switch="contacts"', $output);
        self::assertStringContainsString('data-contacts-switch="statuses"', $output);
        self::assertStringContainsString('data-contacts-switch="companies"', $output);
        // Contact dialog fields
        self::assertStringContainsString('name="first_name"', $output);
        self::assertStringContainsString('name="last_name"', $output);
        self::assertStringContainsString('name="company"', $output);
        self::assertStringContainsString('name="extra_field_key[]"', $output);
        // Status dialog
        self::assertStringContainsString('name="status_name"', $output);
        self::assertStringContainsString('data-open-status-dialog', $output);
        // Company dialog fields (single, not array)
        self::assertStringContainsString('name="company_name"', $output);
        self::assertStringContainsString('name="legal_form"', $output);
        self::assertStringContainsString('name="street"', $output);
        self::assertStringContainsString('name="zip_code"', $output);
        self::assertStringContainsString('name="city"', $output);
        self::assertStringContainsString('name="country"', $output);
        self::assertStringContainsString('name="vat_id"', $output);
        self::assertStringContainsString('name="tax_number"', $output);
        self::assertStringContainsString('data-open-company-dialog', $output);
        // Sample data
        self::assertStringContainsString('Musterfirma GmbH', $output);
        self::assertStringContainsString('Kontakt', $output);
        self::assertStringContainsString('Lead', $output);
        self::assertStringContainsString('Kunde', $output);
        self::assertStringContainsString('Partner', $output);
        self::assertStringContainsString('Mitarbeiter', $output);
    }
}
