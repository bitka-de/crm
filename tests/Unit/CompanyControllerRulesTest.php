<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\CompanyController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class CompanyControllerRulesTest extends TestCase
{
    public function testNormalizeByLegalFormClearsFieldsForFreelancer(): void
    {
        $controller = new CompanyController();

        $method = new ReflectionMethod(CompanyController::class, 'normalizeByLegalForm');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [
            'legal_form' => 'Freelancer',
            'registration_number' => 'HRB 1',
            'registration_court' => 'Amtsgericht',
            'share_capital_eur' => '25000',
            'managing_director' => 'Name',
        ]);

        self::assertSame('', $result['registration_number']);
        self::assertSame('', $result['registration_court']);
        self::assertSame('', $result['share_capital_eur']);
        self::assertSame('', $result['managing_director']);
    }

    public function testValidateByLegalFormRequiresFieldsForGmbhAndUg(): void
    {
        $controller = new CompanyController();

        $method = new ReflectionMethod(CompanyController::class, 'validateByLegalForm');
        $method->setAccessible(true);

        $error = $method->invoke($controller, [
            'company_name' => 'Beispiel GmbH',
            'legal_form' => 'GmbH',
            'tax_number' => '12/123/12345',
            'managing_director' => '',
            'registration_number' => '',
            'registration_court' => '',
            'share_capital_eur' => '',
        ]);

        self::assertIsString($error);
        self::assertStringContainsString('UG und GmbH', $error);
    }

    public function testValidateByLegalFormRequiresOwnerForGbr(): void
    {
        $controller = new CompanyController();

        $method = new ReflectionMethod(CompanyController::class, 'validateByLegalForm');
        $method->setAccessible(true);

        $error = $method->invoke($controller, [
            'company_name' => 'Muster GbR',
            'legal_form' => 'GbR',
            'tax_number' => '12/123/12345',
            'owner_name' => '',
        ]);

        self::assertIsString($error);
        self::assertStringContainsString('GbR', $error);
    }

    public function testBuildChangeEntriesDetectsFieldAndExtraFieldChanges(): void
    {
        $controller = new CompanyController();

        $method = new ReflectionMethod(CompanyController::class, 'buildChangeEntries');
        $method->setAccessible(true);

        $changes = $method->invoke(
            $controller,
            [
                'company_name' => 'Alt GmbH',
                'tax_number' => '11/111/11111',
                'extra_fields' => [
                    'erp' => [
                        'type' => 'text',
                        'value' => 'alt',
                    ],
                ],
            ],
            [
                'company_name' => 'Neu GmbH',
                'tax_number' => '11/111/11111',
                'extra_fields' => [
                    'erp' => [
                        'type' => 'text',
                        'value' => 'neu',
                    ],
                    'is_client' => [
                        'type' => 'boolean',
                        'value' => true,
                    ],
                ],
            ]
        );

        self::assertIsArray($changes);
        self::assertNotEmpty($changes);

        $fields = array_column($changes, 'field');
        self::assertContains('Unternehmensname', $fields);
        self::assertContains('Zusatzfeld: erp', $fields);
        self::assertContains('Zusatzfeld: is_client', $fields);
    }
}
