<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\HomePage;
use PHPUnit\Framework\TestCase;

final class HomePageTest extends TestCase
{
    public function testReturnsGivenValues(): void
    {
        $page = new HomePage('Titel', 'Headline', 'Beschreibung');

        self::assertSame('Titel', $page->title());
        self::assertSame('Headline', $page->headline());
        self::assertSame('Beschreibung', $page->description());
    }
}