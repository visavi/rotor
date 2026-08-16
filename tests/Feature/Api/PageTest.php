<?php

namespace Tests\Feature\Api;

use App\Models\Rule;
use App\Models\Status;
use App\Models\Sticker;
use App\Models\StickersCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
    }

    public function testPageReturnsRenderedHtml(): void
    {
        $this->getJson('/api/pages')
            ->assertOk()
            ->assertJsonPath('slug', 'index')
            ->assertJsonStructure(['slug', 'html']);
    }

    public function testUnknownPageIsNotFound(): void
    {
        $this->getJson('/api/pages/nosuchpage')->assertStatus(404);
    }

    public function testRulesReturnText(): void
    {
        Rule::query()->delete();
        Rule::query()->create(['text' => 'Правила сайта %SITENAME%', 'created_at' => now()]);

        // Название сайта подставляется, как и на странице правил
        $this->getJson('/api/rules')
            ->assertOk()
            ->assertJsonPath('text', 'Правила сайта ' . setting('title'));
    }

    public function testRulesAreEmptyWithoutRecord(): void
    {
        Rule::query()->delete();

        $this->getJson('/api/rules')
            ->assertOk()
            ->assertJsonPath('text', null);
    }

    public function testStickersAreGroupedByCategory(): void
    {
        Sticker::query()->delete();
        StickersCategory::query()->delete();

        $category = StickersCategory::query()->create(['name' => 'Смайлы']);

        Sticker::query()->create([
            'category_id' => $category->id,
            'code'        => ':)',
            'name'        => '/uploads/stickers/smile.gif',
        ]);

        $response = $this->getJson('/api/stickers')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Смайлы')
            ->assertJsonPath('data.0.stickers.0.code', ':)');

        $this->assertSame(url('/uploads/stickers/smile.gif'), $response->json('data.0.stickers.0.url'));
    }

    public function testStatusesAreSortedByPoints(): void
    {
        Status::query()->delete();

        Status::query()->create(['name' => 'Новичок', 'point' => 0, 'topoint' => 10, 'color' => '#000000']);
        Status::query()->create(['name' => 'Мастер', 'point' => 100, 'topoint' => 1000, 'color' => '#ff0000']);

        $this->getJson('/api/statuses')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Мастер')
            ->assertJsonPath('data.1.name', 'Новичок');
    }
}
