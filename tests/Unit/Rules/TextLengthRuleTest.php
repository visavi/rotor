<?php

namespace Tests\Unit\Rules;

use App\Rules\TextLengthRule;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(TextLengthRule::class)]
class TextLengthRuleTest extends TestCase
{
    private function errors(mixed $value, int $min, int $max): array
    {
        return Validator::make(['text' => $value], ['text' => new TextLengthRule($min, $max)])
            ->errors()
            ->get('text');
    }

    public function testPassesWithinRange(): void
    {
        self::assertSame([], $this->errors('test', 0, 10));
        self::assertSame([], $this->errors('тест', 4, 4));
    }

    public function testFailsTooShort(): void
    {
        self::assertSame([__('validator.length_min', ['length' => 5])], $this->errors('test', 5, 10));
    }

    public function testFailsTooLong(): void
    {
        self::assertSame([__('validator.length_max', ['length' => 10])], $this->errors('testtesttest', 0, 10));
    }

    public function testIgnoresHtmlTags(): void
    {
        self::assertSame([], $this->errors('<p><strong>test</strong></p>', 0, 4));
        self::assertNotSame([], $this->errors('<p></p>', 1, 10));
    }

    public function testCountsNumbers(): void
    {
        self::assertSame([], $this->errors(12345, 5, 5));
    }
}
