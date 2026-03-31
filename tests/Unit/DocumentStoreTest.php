<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\DocumentStore;
use PHPUnit\Framework\TestCase;

final class DocumentStoreTest extends TestCase
{
    public function testStoresAndDeletesDocuments(): void
    {
        $file = sys_get_temp_dir() . '/documents_test_' . uniqid('', true) . '.json';
        $store = new DocumentStore($file);

        $store->addOffer([
            'id' => 'offer-1',
            'number' => 'ANG-1',
            'customer_name' => 'Musterkunde',
            'amount_eur' => '1200.00',
        ]);

        $store->addInvoice([
            'id' => 'invoice-1',
            'number' => 'RE-1',
            'customer_name' => 'Musterkunde',
            'line_items' => [
                [
                    'description' => 'Leistung 1',
                    'quantity' => '2.00',
                    'unit_price_eur' => '500.00',
                    'line_total' => '1000.00',
                ],
            ],
            'gross_total_eur' => '1190.00',
        ]);

        $store->addReminder([
            'id' => 'reminder-1',
            'number' => 'MAH-1',
            'customer_name' => 'Musterkunde',
            'invoice_number' => 'RE-1',
            'amount_eur' => '1200.00',
            'level' => '1',
        ]);

        $all = $store->getAll();
        self::assertCount(1, $all['offers']);
        self::assertCount(1, $all['invoices']);
        self::assertCount(1, $all['reminders']);
        self::assertIsArray($all['invoices'][0]['line_items']);
        self::assertSame('Leistung 1', $all['invoices'][0]['line_items'][0]['description']);

        $store->deleteEntry('offers', 'offer-1');
        $allAfterDelete = $store->getAll();
        self::assertCount(0, $allAfterDelete['offers']);

        if (is_file($file)) {
            unlink($file);
        }
    }
}
