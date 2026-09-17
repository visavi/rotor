<?php

namespace Tests\Feature\Admin;

use App\Models\Comment;
use App\Models\Setting;
use App\Models\User;
use App\Services\DashboardService;
use App\Support\Registry;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DashboardWidgetTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    /** @var array<string, mixed> */
    private array $widgets;

    protected function setUp(): void
    {
        parent::setUp();

        // Реестр живёт в статике весь процесс: виджеты теста иначе протекают в соседние
        $this->widgets = Registry::$widgets;

        $this->overrideSetting('app_installed', 1);

        // Виджеты фильтруются по уровню, поэтому проверяем на владельце
        $this->admin = User::factory()->boss()->create(['login' => 'boss_widgets']);

        $this->actingAs($this->admin);
    }

    protected function tearDown(): void
    {
        Registry::$widgets = $this->widgets;

        parent::tearDown();
    }

    public function testDashboardShowsCoreWidgets(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('index.widget_registrations'))
            ->assertSee(__('index.widget_comments'));
    }

    public function testRegistrationsWidgetCountsOnlyPeriod(): void
    {
        User::factory()->create(['created_at' => now()->subDay()]);
        User::factory()->create(['created_at' => now()->subDays(DashboardService::days() + 5)]);

        $widget = $this->widget(__('index.widget_registrations'));

        // Админ из setUp плюс один свежий пользователь, старый за период не попадает
        $this->assertSame(2, $widget['value']);
        $this->assertCount(DashboardService::days(), $widget['series']);
    }

    public function testCommentsWidgetCountsPeriod(): void
    {
        $this->makeComment(now());
        $this->makeComment(now()->subDays(DashboardService::days() + 2));

        $widget = $this->widget(__('index.widget_comments'));

        // Свежий комментарий попадает в период, старый уходит в прошлый
        $this->assertSame(1, $widget['value']);
        $this->assertSame(1, $widget['previous']);
    }

    public function testDiffComparesWithPreviousPeriod(): void
    {
        // Прошлый период: 2 комментария, текущий: 3 — рост в полтора раза
        $this->makeComment(now()->subDays(DashboardService::days() + 1));
        $this->makeComment(now()->subDays(DashboardService::days() + 2));

        $this->makeComment(now());
        $this->makeComment(now()->subDay());
        $this->makeComment(now()->subDays(2));

        $widget = $this->widget(__('index.widget_comments'));

        $this->assertSame(3, $widget['value']);
        $this->assertSame(2, $widget['previous']);
        $this->assertSame(50.0, $widget['diff']);
    }

    public function testDiffIsEmptyOnEmptyPreviousPeriod(): void
    {
        $this->makeComment(now());

        // Рост от нуля бесконечен, поэтому процент не считается
        $this->assertNull($this->widget(__('index.widget_comments'))['diff']);
    }

    public function testModuleWidgetIsRendered(): void
    {
        Registry::widget('test', static fn (): array => [
            'label'  => 'Тестовый виджет',
            'value'  => 42,
            'series' => array_fill(0, DashboardService::days(), 1),
            'icon'   => 'fas fa-star',
            'color'  => '#123456',
            'type'   => 'line',
            'url'    => '/admin',
        ]);

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Тестовый виджет')
            ->assertSee('42');
    }

    public function testBrokenWidgetDoesNotBreakDashboard(): void
    {
        Registry::widget('broken', static fn () => throw new RuntimeException('Виджет сломан'));

        // Панель обязана открыться: битый модуль теряет свою плитку, остальные на месте
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('index.widget_comments'));
    }

    public function testIncompleteWidgetIsSkipped(): void
    {
        Registry::widget('partial', static fn (): array => ['label' => 'Без ряда']);

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee('Без ряда');
    }

    public function testOptionalKeysAreFilled(): void
    {
        Registry::widget('minimal', static fn (): array => [
            'label'  => 'Минимальный виджет',
            'value'  => 7,
            'series' => array_fill(0, DashboardService::days(), 1),
        ]);

        $widget = $this->widget('Минимальный виджет');

        $this->assertSame(User::EDITOR, $widget['level']);
        $this->assertSame('line', $widget['type']);
        $this->assertNull($widget['url']);
    }

    public function testWidgetIsHiddenFromLowerLevel(): void
    {
        Registry::widget('secret', static fn (): array => [
            'label'  => 'Виджет владельца',
            'value'  => 1,
            'series' => array_fill(0, DashboardService::days(), 1),
            'level'  => User::BOSS,
        ]);

        $editor = User::factory()->create(['login' => 'editor_widgets', 'level' => User::EDITOR]);

        $this->actingAs($editor)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee('Виджет владельца');

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Виджет владельца');
    }

    public function testSettingLimitsAndOrdersWidgets(): void
    {
        $this->overrideSetting(DashboardService::SETTING, 'comments,registrations,-counter');

        $labels = array_column(app(DashboardService::class)->widgets(), 'label');

        // Настройка задаёт и состав, и порядок
        $this->assertSame([__('index.widget_comments'), __('index.widget_registrations')], $labels);
    }

    public function testNewWidgetGoesAfterConfigured(): void
    {
        $this->overrideSetting(DashboardService::SETTING, 'comments,-registrations');

        Registry::widget('fresh', static fn (): array => [
            'label'  => 'Новый виджет',
            'value'  => 1,
            'series' => array_fill(0, DashboardService::days(), 1),
        ]);

        // Модуль поставили, до настроек не дошли — виджет виден, но последним
        $labels = array_column(app(DashboardService::class)->widgets(), 'label');

        $this->assertSame(__('index.widget_comments'), $labels[0]);
        $this->assertContains('Новый виджет', $labels);
    }

    public function testWidgetSettingsPageSavesOrder(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.widgets.update'), [
                'widgets' => ['comments', 'registrations'],
                'order'   => 'registrations,comments',
            ])
            ->assertRedirect(route('admin.widgets.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'  => DashboardService::SETTING,
            'value' => 'registrations,comments',
        ]);
    }

    public function testWidgetSettingsPageIgnoresUnknownKeys(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.widgets.update'), ['widgets' => ['comments', 'hacked']])
            ->assertRedirect(route('admin.widgets.index'));

        // Чужой ключ в настройку не попал, известные сохранились со своим состоянием
        $value = (string) Setting::query()->where('name', DashboardService::SETTING)->value('value');

        $this->assertStringNotContainsString('hacked', $value);
        $this->assertStringContainsString('comments', $value);
        $this->assertStringContainsString('-registrations', $value);
    }

    public function testWidgetReceivesPeriod(): void
    {
        $received = null;

        Registry::widget('period', static function (int $days) use (&$received): array {
            $received = $days;

            return ['label' => 'Период', 'value' => 1, 'series' => [1]];
        });

        app(DashboardService::class)->widgets();

        // Период приходит аргументом, иначе смена глубины графиков потребует правки модулей
        $this->assertSame(DashboardService::days(), $received);
    }

    public function testWidgetWithoutArgumentsStillWorks(): void
    {
        // Старый колбэк без аргумента не должен ломаться от переданного периода
        Registry::widget('legacy', static fn (): array => [
            'label'  => 'Без аргумента',
            'value'  => 3,
            'series' => [1, 2, 3],
        ]);

        $this->assertSame(3, $this->widget('Без аргумента')['value']);
    }

    public function testWidgetKeepsOwnPeriod(): void
    {
        Registry::widget('monthly', static fn (): array => [
            'label'  => 'Месячный виджет',
            'value'  => 1,
            'series' => [1],
            'days'   => 30,
        ]);

        // Свой период не затирается общим и попадает в подпись плитки
        $this->assertSame(30, $this->widget('Месячный виджет')['days']);

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('index.widget_period', ['days' => 30]));
    }

    /**
     * Возвращает виджет по его названию
     *
     * @return array<string, mixed>
     */
    private function widget(string $label): array
    {
        return collect(app(DashboardService::class)->widgets())
            ->keyBy('label')[$label];
    }

    private function makeComment(CarbonImmutable|Carbon $date): void
    {
        Comment::query()->create([
            'user_id'     => $this->admin->id,
            'relate_type' => 'test',
            'relate_id'   => 1,
            'text'        => 'Тестовый комментарий',
            'ip'          => '127.0.0.1',
            'brow'        => 'Test',
            'created_at'  => $date,
        ]);
    }
}
