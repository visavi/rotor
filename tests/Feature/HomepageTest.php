<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    private array $homepages;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        // Реестр живёт в статике весь прогон: главные модулей возвращаются после теста
        $this->homepages = Registry::$homepages;
        Registry::$homepages = [];

        $file = sys_get_temp_dir() . '/rotor-test-homepage.blade.php';
        file_put_contents($file, '<div id="test-homepage"></div>');
        Registry::homepage('test', 'Тестовая главная', static fn () => view()->file($file));
    }

    protected function tearDown(): void
    {
        Registry::$homepages = $this->homepages;

        parent::tearDown();
    }

    public function testFeedIsShownByDefault(): void
    {
        $this->overrideSetting('homepage', 'feed');

        $this->get('/')
            ->assertOk()
            ->assertSee('feed-container', false)
            ->assertDontSee('test-homepage', false);
    }

    public function testSelectedModuleHomepageIsShown(): void
    {
        $this->overrideSetting('homepage', 'test');

        $this->get('/')
            ->assertOk()
            ->assertSee('<div id="test-homepage"></div>', false)
            ->assertDontSee('feed-container', false);
    }

    public function testFeedIsShownWhenModuleIsDisabled(): void
    {
        // Модуль выключен — его главная не зарегистрирована
        $this->overrideSetting('homepage', 'missing');

        $this->get('/')
            ->assertOk()
            ->assertSee('feed-container', false);
    }

    public function testSettingsListModuleHomepages(): void
    {
        $this->overrideSetting('homepage', 'feed');

        $this->actingAs(User::factory()->boss()->create())
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('<option value="feed" selected>', false)
            ->assertSee('<option value="test">Тестовая главная</option>', false);
    }

    public function testSettingsKeepHomepageOfDisabledModule(): void
    {
        // Иначе сохранение других настроек молча вернёт ленту
        $this->overrideSetting('homepage', 'missing');

        $this->actingAs(User::factory()->boss()->create())
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('<option value="missing" selected>', false)
            ->assertSee(__('settings.homepage_unavailable', ['name' => 'missing']));
    }
}
