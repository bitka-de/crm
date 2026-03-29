<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ContactStatusStore;
use PHPUnit\Framework\TestCase;

final class ContactStatusStoreTest extends TestCase
{
    public function testReturnsDefaultStatusesWhenFileMissing(): void
    {
        $file = sys_get_temp_dir() . '/contact_status_missing_' . uniqid('', true) . '.json';
        $store = new ContactStatusStore($file);

        $statuses = $store->getAll();

        self::assertContains('Kontakt', $statuses);
        self::assertContains('Lead', $statuses);
        self::assertContains('Kunde', $statuses);
    }

    public function testSaveAllNormalizesDuplicatesAndKeepsKontakt(): void
    {
        $file = sys_get_temp_dir() . '/contact_status_test_' . uniqid('', true) . '.json';
        $store = new ContactStatusStore($file);

        $store->saveAll(['kunde', 'Kunde', 'Partner']);

        $statuses = $store->getAll();

        self::assertSame('Kontakt', $statuses[0]);
        self::assertContains('kunde', $statuses);
        self::assertContains('Partner', $statuses);

        if (is_file($file)) {
            unlink($file);
        }
    }
}
