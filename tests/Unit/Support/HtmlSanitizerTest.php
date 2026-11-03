<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(HtmlSanitizer::class)]
class HtmlSanitizerTest extends TestCase
{
    public function testLongCyrillicTextIsKept(): void
    {
        // Symfony по умолчанию режет вход на 20 000 байт посреди буквы, и от текста оставалась пустая строка
        $text = '<p>' . str_repeat('согласие ', 6000) . '</p>';

        $this->assertSame($text, HtmlSanitizer::sanitize($text));
    }

    public function testScriptIsDroppedAndUnknownTagKeepsText(): void
    {
        $this->assertSame('<p>до слово после</p>', HtmlSanitizer::sanitize('<p>до <b>слово</b> после</p><script>alert(1)</script>'));
    }
}
