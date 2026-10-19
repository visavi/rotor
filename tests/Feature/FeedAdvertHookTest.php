<?php

namespace Tests\Feature;

use App\Support\Hook;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\HtmlString;
use Tests\TestCase;

class FeedAdvertHookTest extends TestCase
{
    private array $hooks;

    protected function setUp(): void
    {
        parent::setUp();

        // Хуки живут в статике весь прогон: хуки модулей возвращаются после теста
        $this->hooks = Hook::getHooks();
        Hook::flush();
        Hook::add('feedAdvert', static fn () => '<i>AD</i>');
    }

    protected function tearDown(): void
    {
        Hook::flush();

        foreach ($this->hooks as $name => $hooks) {
            foreach ($hooks as $hook) {
                Hook::add($name, $hook['value'], $hook['priority']);
            }
        }

        parent::tearDown();
    }

    /**
     * Рендерит порцию ленты из $count записей
     */
    private function renderFeed(int $count): string
    {
        $posts = collect(range(1, $count))->map(static fn ($i) => new HtmlString("<p>post$i</p>"));

        return view('feeds/_feed', ['posts' => new Paginator($posts, 10)])->render();
    }

    public function testHookRendersOnceAfterFifthPost(): void
    {
        $html = $this->renderFeed(7);

        $this->assertSame(1, substr_count($html, '<i>AD</i>'));
        $this->assertGreaterThan(strpos($html, 'post5'), strpos($html, '<i>AD</i>'));
        $this->assertLessThan(strpos($html, 'post6'), strpos($html, '<i>AD</i>'));
    }

    public function testHookSkippedForShortPortion(): void
    {
        $this->assertStringNotContainsString('<i>AD</i>', $this->renderFeed(4));
    }
}
