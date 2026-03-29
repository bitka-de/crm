<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CompanyProfileStore;
use PHPUnit\Framework\TestCase;

final class CompanyProfileStoreTest extends TestCase
{
    public function testReturnsDefaultsWhenFileDoesNotExist(): void
    {
        $file = sys_get_temp_dir() . '/company_profile_missing_' . uniqid('', true) . '.json';
        $store = new CompanyProfileStore($file);

        $profile = $store->get();

        self::assertArrayHasKey('company_name', $profile);
        self::assertArrayHasKey('tax_number', $profile);
        self::assertArrayHasKey('iban', $profile);
        self::assertSame([], $profile['extra_fields']);
    }

    public function testPersistsAndLoadsProfileData(): void
    {
        $file = sys_get_temp_dir() . '/company_profile_test_' . uniqid('', true) . '.json';
        $store = new CompanyProfileStore($file);

        $store->save([
            'company_name' => 'Beispiel AG',
            'tax_number' => '99/123/45678',
            'bank_name' => 'Beispielbank',
            'account_holder' => 'Beispiel AG',
            'iban' => 'DE89370400440532013000',
            'bic' => 'COBADEFFXXX',
            'extra_fields' => [
                'erp' => [
                    'type' => 'text',
                    'value' => 'intern',
                ],
            ],
        ]);

        $loaded = $store->get();

        self::assertSame('Beispiel AG', $loaded['company_name']);
        self::assertSame('99/123/45678', $loaded['tax_number']);
        self::assertSame('Beispielbank', $loaded['bank_name']);
        self::assertSame('Beispiel AG', $loaded['account_holder']);
        self::assertSame('DE89370400440532013000', $loaded['iban']);
        self::assertSame('COBADEFFXXX', $loaded['bic']);
        self::assertSame('text', $loaded['extra_fields']['erp']['type']);
        self::assertSame('intern', $loaded['extra_fields']['erp']['value']);

        if (is_file($file)) {
            unlink($file);
        }
    }
}
