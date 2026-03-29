<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

final class CompanyPageRenderingTest extends TestCase
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

    public function testCompanyPageRendersFormAndSampleData(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/company';

        ob_start();
        require __DIR__ . '/../../public/index.php';
        $output = (string) ob_get_clean();

        self::assertStringContainsString('Unternehmensdaten (Deutschland)', $output);
        self::assertStringContainsString('name="company_name"', $output);
        self::assertStringContainsString('name="tax_number"', $output);
        self::assertStringContainsString('Gespeicherte Unternehmensdaten', $output);
        self::assertStringContainsString('name="legal_form"', $output);
        self::assertStringContainsString('Freelancer', $output);
        self::assertStringContainsString('Einzelunternehmen', $output);
        self::assertStringContainsString('GbR', $output);
        self::assertStringContainsString('UG (haftungsbeschraenkt)', $output);
        self::assertStringContainsString('GmbH', $output);
        self::assertStringContainsString('name="managing_director"', $output);
        self::assertStringContainsString('name="share_capital_eur"', $output);
        self::assertStringContainsString('name="bank_name"', $output);
        self::assertStringContainsString('name="account_holder"', $output);
        self::assertStringContainsString('name="iban"', $output);
        self::assertStringContainsString('name="bic"', $output);
        self::assertStringContainsString('Bankdaten', $output);
        self::assertStringContainsString('name="extra_field_key[]"', $output);
        self::assertStringContainsString('name="extra_field_value[]"', $output);
        self::assertStringContainsString('name="extra_field_type[]"', $output);
        self::assertStringContainsString('Aenderungsprotokoll', $output);
        self::assertStringNotContainsString('name="extra_fields"', $output);
    }
}
