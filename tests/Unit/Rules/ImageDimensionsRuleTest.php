<?php

namespace Tests\Unit\Rules;

use App\Rules\ImageDimensionsRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(ImageDimensionsRule::class)]
class ImageDimensionsRuleTest extends TestCase
{
    private function errors(UploadedFile $file, ?int $min, ?int $max): array
    {
        return Validator::make(['file' => $file], ['file' => new ImageDimensionsRule($min, $max)])
            ->errors()
            ->get('file');
    }

    public function testPassesWithinRange(): void
    {
        self::assertSame([], $this->errors(UploadedFile::fake()->image('a.png', 50, 50), 16, 500));
    }

    public function testFailsTooBig(): void
    {
        self::assertSame(
            [__('validator.weight_max', ['weight' => 100])],
            $this->errors(UploadedFile::fake()->image('a.png', 150, 50), 16, 100)
        );
    }

    public function testFailsTooSmall(): void
    {
        self::assertSame(
            [__('validator.weight_min', ['weight' => 16])],
            $this->errors(UploadedFile::fake()->image('a.png', 50, 10), 16, 100)
        );
    }

    public function testSkipsNotImages(): void
    {
        self::assertSame([], $this->errors(UploadedFile::fake()->create('a.mp4', 10, 'video/mp4'), 16, 100));
    }

    public function testFailsBrokenImageWithoutMin(): void
    {
        $file = UploadedFile::fake()->createWithContent('a.png', "\x89PNG\r\n\x1a\nbroken");

        self::assertSame([__('validator.weight_empty')], $this->errors($file, null, 100));
    }
}
