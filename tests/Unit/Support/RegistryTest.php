<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(Registry::class)]
class RegistryTest extends TestCase
{
    private array $textFilters;

    protected function setUp(): void
    {
        parent::setUp();

        // Фильтры модулей живут в статике весь прогон: подменяем только их и возвращаем как было
        $this->textFilters = Registry::$textFilters;
        Registry::$textFilters = [];
    }

    protected function tearDown(): void
    {
        Registry::$textFilters = $this->textFilters;

        parent::tearDown();
    }

    public function testFilterTextWithoutFiltersReturnsText(): void
    {
        $this->assertSame('text', Registry::filterText('text'));
    }

    public function testFilterTextAppliesFiltersInOrder(): void
    {
        Registry::textFilter(static fn (string $text): string => str_replace('x', '*', $text));
        Registry::textFilter(static fn (string $text): string => strtoupper($text));

        $this->assertSame('A*B', Registry::filterText('axb'));
    }

    public function testFilterTextSkipsEmptyString(): void
    {
        Registry::textFilter(static fn (string $text): string => 'filtered');

        $this->assertSame('', Registry::filterText(''));
    }
}
