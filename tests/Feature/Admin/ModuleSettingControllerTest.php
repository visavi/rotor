<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ModuleSettingController;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ModuleSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        Route::middleware(['web', 'check.admin'])->group(static function () {
            Route::get('/admin/fixture-settings', static fn () => '')->name('fixture.settings');
            Route::post('/admin/fixture-settings', [FixtureSettingController::class, 'update']);
        });

        // Имена маршрутов, добавленных после загрузки приложения, нужно пересобрать
        Route::getRoutes()->refreshNameLookups();
    }

    public function testModuleRuleKeepsLengthLimit(): void
    {
        // Своё правило модуля заменяет общее sets.*: без добавленной длины строка
        // длиннее колонки value прошла бы проверку и уронила сохранение в строгом MySQL
        $this->actingAs(User::factory()->boss()->create())
            ->post('/admin/fixture-settings', ['sets' => ['fixture_text' => str_repeat('a', 256)]])
            ->assertRedirect(route('fixture.settings'))
            ->assertSessionHasErrors('sets.fixture_text');

        $this->assertNull(Setting::query()->where('name', 'fixture_text')->value('value'));
    }

    public function testNumberIsNotComparedAsLength(): void
    {
        $this->actingAs(User::factory()->boss()->create())
            ->post('/admin/fixture-settings', ['sets' => ['fixture_text' => 'текст', 'fixture_number' => 100000]])
            ->assertSessionHasNoErrors();

        $this->assertSame('100000', Setting::query()->where('name', 'fixture_number')->value('value'));
    }

    public function testOnlyDeclaredKeysAreSaved(): void
    {
        $this->actingAs(User::factory()->boss()->create())
            ->post('/admin/fixture-settings', ['sets' => ['fixture_text' => 'текст', 'title' => 'чужой ключ']])
            ->assertSessionHasNoErrors();

        $this->assertSame('текст', Setting::query()->where('name', 'fixture_text')->value('value'));
        $this->assertNotSame('чужой ключ', Setting::query()->where('name', 'title')->value('value'));
    }
}

/**
 * Контроллер настроек модуля со своими правилами
 */
class FixtureSettingController extends ModuleSettingController
{
    protected string $view = 'admin/settings/layout';

    protected string $route = 'fixture.settings';

    protected function rules(): array
    {
        return [
            'sets.fixture_text'   => ['nullable', 'string'],
            'sets.fixture_number' => 'nullable|integer|min:1',
        ];
    }
}
