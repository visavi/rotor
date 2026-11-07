<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File as FileSystem;
use Tests\TestCase;

/**
 * Соцсети в футере: общий шаблон во всех темах,
 * владелец сайта перебивает его своей копией в resources/custom
 */
class FooterSocialTest extends TestCase
{
    use RefreshDatabase;

    private string $customPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->customPath = storage_path('framework/testing/custom-' . uniqid());
    }

    protected function tearDown(): void
    {
        FileSystem::deleteDirectory($this->customPath);

        parent::tearDown();
    }

    public function testEveryThemeShowsSocialLinksAndOwnerCanReplaceThem(): void
    {
        $themes = array_map('basename', glob(resource_path('views/themes/*'), GLOB_ONLYDIR) ?: []);

        foreach ($themes as $theme) {
            $this->overrideSetting('themes', $theme);

            $this->get('/search')->assertOk()->assertSee('https://telegram.me/visavinet', false);
        }

        // Копия шаблона в custom перебивает поставку в любой теме
        FileSystem::ensureDirectoryExists($this->customPath . '/app');
        FileSystem::put($this->customPath . '/app/_social.blade.php', '<a href="https://t.me/owner" aria-label="Telegram">tg</a>');
        config(['view.paths' => [$this->customPath, ...config('view.paths')]]);

        foreach ($themes as $theme) {
            $this->overrideSetting('themes', $theme);

            $this->get('/search')
                ->assertSee('https://t.me/owner', false)
                ->assertDontSee('visavinet');
        }
    }
}
