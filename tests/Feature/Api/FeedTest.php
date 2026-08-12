<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('feed_comments_show', 1);
        $this->overrideSetting('feed_per_page', 20);
        $this->overrideSetting('feed_cache_time', 0);
    }

    public function testFeedIsAvailableForGuests(): void
    {
        $this->get('/api/feed')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    // Наполнение элементов ленты проверяется в тестах модулей (Forum\FeedApiTest),
    // ядро без модулей не имеет зарегистрированных типов записей
}
