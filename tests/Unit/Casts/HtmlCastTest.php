<?php

declare(strict_types=1);

namespace Tests\Unit\Casts;

use App\Casts\HtmlCast;
use App\Models\Comment;
use App\Models\User;
use App\Support\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(HtmlCast::class)]
class HtmlCastTest extends TestCase
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

    public function testNullBecomesEmptyString(): void
    {
        // Пустое поле формы приходит null, а колонка text NOT NULL
        $comment = new Comment(['text' => null]);

        $this->assertSame('', $comment->getAttributes()['text']);
    }

    public function testNullableKeepsNull(): void
    {
        $user = new User(['info' => null]);

        $this->assertNull($user->getAttributes()['info']);
        $this->assertNull($user->info);
    }

    public function testHtmlIsSanitized(): void
    {
        $comment = new Comment(['text' => '<p>текст</p><script>alert(1)</script>']);

        $this->assertStringNotContainsString('<script', $comment->getAttributes()['text']);
        $this->assertStringContainsString('текст', $comment->getAttributes()['text']);
    }

    public function testFiltersApplyOnReadAndKeepOriginal(): void
    {
        Registry::textFilter(static fn (string $text): string => str_replace('плохо', '***', $text));

        $comment = new Comment(['text' => '<p>плохо</p>']);

        $this->assertSame('<p>***</p>', $comment->text);
        $this->assertSame('<p>плохо</p>', $comment->getAttributes()['text']);
    }

    public function testFiltersSkipMarkup(): void
    {
        Registry::textFilter(static fn (string $text): string => str_replace('ass', '***', $text));

        $comment = new Comment(['text' => '<p><a href="/pass">class ass</a></p>']);

        $this->assertSame('<p><a href="/pass">cl*** ***</a></p>', $comment->text);
    }
}
