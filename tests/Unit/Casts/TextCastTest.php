<?php

declare(strict_types=1);

namespace Tests\Unit\Casts;

use App\Casts\TextCast;
use App\Support\Registry;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(TextCast::class)]
class TextCastTest extends TestCase
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
        $model = $this->model(['title' => null]);

        $this->assertSame('', $model->getAttributes()['title']);
    }

    public function testNullableKeepsNull(): void
    {
        $model = $this->model(['note' => null]);

        $this->assertNull($model->getAttributes()['note']);
        $this->assertNull($model->note);
    }

    public function testTextIsNotEscapedOnWrite(): void
    {
        $model = $this->model(['title' => '<b>Заголовок</b>']);

        $this->assertSame('<b>Заголовок</b>', $model->getAttributes()['title']);
    }

    public function testFiltersApplyOnReadAndKeepOriginal(): void
    {
        Registry::textFilter(static fn (string $text): string => str_replace('плохо', '***', $text));

        $model = $this->model(['title' => 'плохо']);

        $this->assertSame('***', $model->title);
        $this->assertSame('плохо', $model->getAttributes()['title']);
    }

    private function model(array $attributes): Model
    {
        $model = new class extends Model {
            protected $guarded = [];

            protected function casts(): array
            {
                return [
                    'title' => TextCast::class,
                    'note'  => TextCast::class . ':nullable',
                ];
            }
        };

        return $model->fill($attributes);
    }
}
