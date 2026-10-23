<?php

namespace Tests\Feature;

use App\Models\Ban;
use App\Models\Sticker;
use App\Models\StickersCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClearsCacheTest extends TestCase
{
    use RefreshDatabase;

    public function testSaveAndDeleteClearModelCache(): void
    {
        // #[ClearsCache('ipBan')]: ручной clearCache() после create/update/delete больше не нужен
        Cache::put('ipBan', ['stale'], 3600);
        $ban = Ban::factory()->create();
        $this->assertFalse(Cache::has('ipBan'));

        Cache::put('ipBan', ['stale'], 3600);
        $ban->update(['ip' => '127.0.0.2']);
        $this->assertFalse(Cache::has('ipBan'));

        Cache::put('ipBan', ['stale'], 3600);
        $ban->delete();
        $this->assertFalse(Cache::has('ipBan'));
    }

    public function testUnchangedSaveKeepsCache(): void
    {
        $ban = Ban::factory()->create();

        Cache::put('ipBan', ['fresh'], 3600);
        $ban->fresh()?->save();

        $this->assertTrue(Cache::has('ipBan'));
    }

    public function testCacheIsClearedAfterCommit(): void
    {
        // До коммита соседний запрос закэшировал бы ещё старые данные
        DB::transaction(function () {
            Cache::put('stickers_map', ['stale'], 3600);

            Sticker::query()->create([
                'category_id' => StickersCategory::query()->create(['name' => 'Тест'])->id,
                'name'        => '/uploads/stickers/test.gif',
                'code'        => ':test',
            ]);

            $this->assertTrue(Cache::has('stickers_map'));
        });

        $this->assertFalse(Cache::has('stickers_map'));
    }

    public function testUpdatedEventAloneKeepsCache(): void
    {
        // increment() вызывает только updated: счётчики не должны сбрасывать кэш раздела
        $ban = Ban::factory()->create();

        Cache::put('ipBan', ['fresh'], 3600);
        event('eloquent.updated: ' . Ban::class, $ban);

        $this->assertTrue(Cache::has('ipBan'));
    }

    public function testModelWithoutAttributeKeepsCache(): void
    {
        Cache::put('stickers_map', ['fresh'], 3600);

        StickersCategory::query()->create(['name' => 'Тест']);

        $this->assertTrue(Cache::has('stickers_map'));
    }
}
