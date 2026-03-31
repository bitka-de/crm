<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ContactStore;
use PHPUnit\Framework\TestCase;

final class ContactStoreTest extends TestCase
{
    public function testReturnsEmptyListWhenFileDoesNotExist(): void
    {
        $file = sys_get_temp_dir() . '/contacts_missing_' . uniqid('', true) . '.json';
        $store = new ContactStore($file);

        self::assertSame([], $store->getAll());
    }

    public function testPersistsAndLoadsContacts(): void
    {
        $file = sys_get_temp_dir() . '/contacts_test_' . uniqid('', true) . '.json';
        $store = new ContactStore($file);

        $store->upsert([
            'id' => 'abc123',
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
            'status' => 'Lead',
            'street' => 'Musterstrasse 10',
            'zip_code' => '76133',
            'city' => 'Karlsruhe',
            'country' => 'Deutschland',
            'extra_fields' => [
                'linkedin' => [
                    'type' => 'text',
                    'value' => 'max-mustermann',
                ],
            ],
        ]);

        $contacts = $store->getAll();

        self::assertCount(1, $contacts);
        self::assertSame('abc123', $contacts[0]['id']);
        self::assertSame('Max', $contacts[0]['first_name']);
        self::assertSame('Lead', $contacts[0]['status']);
        self::assertSame('Musterstrasse 10', $contacts[0]['street']);
        self::assertSame('76133', $contacts[0]['zip_code']);
        self::assertSame('Karlsruhe', $contacts[0]['city']);
        self::assertSame('Deutschland', $contacts[0]['country']);
        self::assertSame('text', $contacts[0]['extra_fields']['linkedin']['type']);

        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testDeleteByIdRemovesMatchingContact(): void
    {
        $file = sys_get_temp_dir() . '/contacts_delete_test_' . uniqid('', true) . '.json';
        $store = new ContactStore($file);

        $store->saveAll([
            [
                'id' => 'one',
                'first_name' => 'A',
                'last_name' => 'One',
                'status' => 'Kontakt',
                'extra_fields' => [],
            ],
            [
                'id' => 'two',
                'first_name' => 'B',
                'last_name' => 'Two',
                'status' => 'Kunde',
                'extra_fields' => [],
            ],
        ]);

        $store->deleteById('one');
        $contacts = $store->getAll();

        self::assertCount(1, $contacts);
        self::assertSame('two', $contacts[0]['id']);

        if (is_file($file)) {
            unlink($file);
        }
    }
}
