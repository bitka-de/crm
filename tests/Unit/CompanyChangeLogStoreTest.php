<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CompanyChangeLogStore;
use PHPUnit\Framework\TestCase;

final class CompanyChangeLogStoreTest extends TestCase
{
    public function testReturnsEmptyListWhenFileDoesNotExist(): void
    {
        $file = sys_get_temp_dir() . '/company_change_log_missing_' . uniqid('', true) . '.json';
        $store = new CompanyChangeLogStore($file);

        self::assertSame([], $store->latest());
    }

    public function testAppendAddsNewestEntryFirst(): void
    {
        $file = sys_get_temp_dir() . '/company_change_log_test_' . uniqid('', true) . '.json';
        $store = new CompanyChangeLogStore($file);

        $store->append([
            'changed_at' => '2026-03-26T10:00:00+00:00',
            'changed_by' => 'admin',
            'changes' => [
                [
                    'field' => 'Unternehmensname',
                    'from' => 'Alt',
                    'to' => 'Neu',
                ],
            ],
        ]);

        $store->append([
            'changed_at' => '2026-03-26T11:00:00+00:00',
            'changed_by' => 'admin',
            'changes' => [
                [
                    'field' => 'Stadt',
                    'from' => 'Hamburg',
                    'to' => 'Berlin',
                ],
            ],
        ]);

        $entries = $store->latest();

        self::assertCount(2, $entries);
        self::assertSame('2026-03-26T11:00:00+00:00', $entries[0]['changed_at']);
        self::assertSame('2026-03-26T10:00:00+00:00', $entries[1]['changed_at']);

        if (is_file($file)) {
            unlink($file);
        }
    }
}
