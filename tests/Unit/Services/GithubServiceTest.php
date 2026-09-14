<?php

namespace Tests\Unit\Services;

use App\Services\GithubService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(GithubService::class)]
class GithubServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function testSourceFailureKeepsCachedReleases(): void
    {
        $this->fakeThenFail();

        $github = new GithubService();
        $this->assertSame('v14.6.0', $github->getLatestVersion());

        // Кэш протух — обновление уходит в afterResponse, в тесте гоняем его вручную
        $this->travel(2)->hours();
        (new GithubService())->getLatestReleases();
        $this->app->terminate();

        // Накопленное осталось в кэше, а не затёрлось пустым ответом
        $this->assertSame([['tag_name' => 'v14.6.0']], Cache::get('releases')['data']);
        $this->assertSame('v14.6.0', (new GithubService())->getLatestVersion());
    }

    public function testFailedRefreshPostponesNextAttempt(): void
    {
        $this->fakeThenFail();

        (new GithubService())->getLatestReleases();

        $this->travel(2)->hours();
        (new GithubService())->getLatestReleases();
        $this->app->terminate();

        // Метка времени сдвинута: следующий запрос не пойдёт в недоступный источник
        $cached = Cache::get('releases');
        $this->assertSame(now()->getTimestamp(), $cached['cached_at']);
        $this->assertNotEmpty($cached['data']);
    }

    public function testSourceFailureOnColdCacheStoresNothing(): void
    {
        Http::fake(['*' => Http::response('', 500)]);

        $this->assertSame([], (new GithubService())->getLatestReleases());
        $this->assertNull(Cache::get('releases'));
    }

    /**
     * Первый запрос отвечает списком релизов, все последующие — ошибкой
     */
    private function fakeThenFail(): void
    {
        Http::fake([
            '*' => Http::sequence()
                ->push([['tag_name' => 'v14.6.0']])
                ->whenEmpty(Http::response('', 500)),
        ]);
    }
}
