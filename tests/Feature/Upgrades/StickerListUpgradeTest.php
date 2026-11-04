<?php

declare(strict_types=1);

namespace Tests\Feature\Upgrades;

use App\Models\Setting;
use Tests\TestCase;

class StickerListUpgradeTest extends TestCase
{
    private const string UPGRADE = 'database/upgrades/2026_11_04_000001_change_stickerlist_default_to_24.php';

    public function testDefaultTwentyBecomesTwentyFour(): void
    {
        // 20 на 12 не делится: ряд стикеров обрывался бы. Колонок 3, 4 или 6 — нужно кратное 12
        $this->overrideSetting('stickerlist', 20);

        (require base_path(self::UPGRADE))->up();

        $this->assertSame('24', Setting::query()->where('name', 'stickerlist')->value('value'));
    }

    public function testOwnValueIsKept(): void
    {
        $this->overrideSetting('stickerlist', 30);

        (require base_path(self::UPGRADE))->up();

        $this->assertSame('30', Setting::query()->where('name', 'stickerlist')->value('value'));
    }
}
