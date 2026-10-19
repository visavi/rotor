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
        Hook::add('feedAdvert', static fn (string $point) => '<i>AD-' . $point . '</i>');
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
    private function renderFeed(int $count, int $page = 1): string
    {
        $posts = collect($count ? range(1, $count) : [])->map(static fn ($i) => new HtmlString("<p>post$i</p>"));

        return view('feeds/_feed', ['posts' => new Paginator($posts, 20, $page)])->render();
    }

    public function testHookPointsOnFirstPage(): void
    {
        $html = $this->renderFeed(20);

        $this->assertSame(3, substr_count($html, '<i>AD'));
        $this->assertLessThan(strpos($html, 'post1<'), strpos($html, '<i>AD-start</i>'));
        $this->assertGreaterThan(strpos($html, 'post10<'), strpos($html, '<i>AD-middle</i>'));
        $this->assertLessThan(strpos($html, 'post11<'), strpos($html, '<i>AD-middle</i>'));
        $this->assertGreaterThan(strpos($html, 'post20<'), strpos($html, '<i>AD-end</i>'));
    }

    public function testMiddleFollowsPortionSize(): void
    {
        $html = $this->renderFeed(7);

        $this->assertGreaterThan(strpos($html, 'post3<'), strpos($html, '<i>AD-middle</i>'));
        $this->assertLessThan(strpos($html, 'post4<'), strpos($html, '<i>AD-middle</i>'));
        $this->assertGreaterThan(strpos($html, 'post7<'), strpos($html, '<i>AD-end</i>'));
    }

    public function testHookSkippedOnNextPages(): void
    {
        $this->assertStringNotContainsString('<i>AD', $this->renderFeed(20, 2));
    }

    public function testShortPortionSkipsMiddle(): void
    {
        $html = $this->renderFeed(4);

        $this->assertSame(2, substr_count($html, '<i>AD'));
        $this->assertStringContainsString('<i>AD-start</i>', $html);
        $this->assertStringContainsString('<i>AD-end</i>', $html);
    }

    public function testTinyPortionHasOnlyStart(): void
    {
        $html = $this->renderFeed(2);

        $this->assertSame(1, substr_count($html, '<i>AD'));
        $this->assertStringContainsString('<i>AD-start</i>', $html);
    }

    public function testEmptyFeedHasNoAdverts(): void
    {
        $this->assertStringNotContainsString('<i>AD', $this->renderFeed(0));
    }
}
