<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ContactCompanyStore;
use PHPUnit\Framework\TestCase;

final class ContactCompanyStoreTest extends TestCase
{
    public function testReturnsDefaultCompanyWhenFileMissing(): void
    {
        $file = sys_get_temp_dir() . '/contact_companies_missing_' . uniqid('', true) . '.json';
        $store = new ContactCompanyStore($file);

        $companies = $store->getAll();

        self::assertSame('Musterfirma GmbH', $companies[0]['company_name']);
        self::assertSame('https://musterfirma.de', $companies[0]['website']);
    }

    public function testSaveAllNormalizesDuplicates(): void
    {
        $file = sys_get_temp_dir() . '/contact_companies_test_' . uniqid('', true) . '.json';
        $store = new ContactCompanyStore($file);

        $store->saveAll([
            [
                'company_name' => 'Alpha AG',
                'website' => 'https://alpha.example',
            ],
            [
                'company_name' => 'alpha ag',
                'website' => 'https://alpha-duplicate.example',
            ],
            [
                'company_name' => 'Beta GmbH',
                'website' => 'https://beta.example',
            ],
        ]);
        $companies = $store->getAll();

        self::assertCount(2, $companies);
        self::assertSame('Alpha AG', $companies[0]['company_name']);
        self::assertSame('Beta GmbH', $companies[1]['company_name']);

        if (is_file($file)) {
            unlink($file);
        }
    }
}
