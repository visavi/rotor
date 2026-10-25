<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\HtmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(HtmlRenderer::class)]
class HtmlRendererTest extends TestCase
{
    public function testHiddenBlockWithNestedDivIsHiddenFromGuests(): void
    {
        $html = (string) HtmlRenderer::html('<div class="hidden"><div class="video">видео</div>секрет</div><p>после</p>');

        // Скрытие обрывалось на первом </div>: всё после вложенного блока видел гость
        $this->assertStringNotContainsString('секрет', $html);
        $this->assertStringNotContainsString('видео', $html);
        $this->assertStringContainsString('<p>после</p>', $html);
    }

    public function testHiddenBlockIsShownToUsers(): void
    {
        $this->actingAs(new User(['id' => 1]));

        $html = (string) HtmlRenderer::html('<div class="hidden"><div class="video">видео</div>секрет</div>');

        $this->assertStringContainsString('секрет', $html);
    }
}
