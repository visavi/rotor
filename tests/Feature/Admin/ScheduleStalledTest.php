<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\ScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleStalledTest extends TestCase
{
    use RefreshDatabase;

    private User $boss;

    private string $path;

    private string $dismissedPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->boss = User::factory()->boss()->create(['login' => 'boss_schedule']);
        $this->path = storage_path('framework/schedule-run');
        $this->dismissedPath = storage_path('framework/schedule-dismissed');

        // Метка живёт файлом и переживает тесты, поэтому убираем её заранее
        @unlink($this->path);
        @unlink($this->dismissedPath);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
        @unlink($this->dismissedPath);

        parent::tearDown();
    }

    public function testWarningShownWhenSchedulerNeverRan(): void
    {
        $this->actingAs($this->boss)
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('index.schedule_stalled'))
            ->assertSee(__('index.schedule_never'));
    }

    public function testWarningHiddenAfterRun(): void
    {
        app(ScheduleService::class)->markRun();

        $this->actingAs($this->boss)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee(__('index.schedule_stalled'));
    }

    public function testWarningShownWhenMarkIsStale(): void
    {
        app(ScheduleService::class)->markRun();
        touch($this->path, time() - ScheduleService::STALLED - 60);
        clearstatcache();

        $this->actingAs($this->boss)
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('index.schedule_stalled'))
            // У протухшей метки показывается время последнего запуска
            ->assertDontSee(__('index.schedule_never'));
    }

    public function testWarningHiddenFromAdmin(): void
    {
        $admin = User::factory()->admin()->create(['login' => 'admin_schedule']);

        // Крон чинит владелец сайта, остальным сообщение бесполезно
        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee(__('index.schedule_stalled'));
    }

    public function testDismissHidesWarning(): void
    {
        $this->actingAs($this->boss)
            ->postJson(route('admin.alerts.dismiss', ['type' => 'schedule']))
            ->assertJson(['success' => true]);

        $this->get('/admin')
            ->assertOk()
            ->assertDontSee(__('index.schedule_stalled'));
    }

    public function testWarningReturnsAfterNewStall(): void
    {
        app(ScheduleService::class)->markRun();
        touch($this->path, time() - ScheduleService::STALLED - 60);
        clearstatcache();

        $this->actingAs($this->boss)->postJson(route('admin.alerts.dismiss', ['type' => 'schedule']));

        // Крон заработал и снова встал — это уже другая остановка
        touch($this->path, time() - ScheduleService::STALLED - 30);
        clearstatcache();

        $this->get('/admin')
            ->assertOk()
            ->assertSee(__('index.schedule_stalled'));
    }

    public function testDismissForbiddenForAdmin(): void
    {
        $admin = User::factory()->admin()->create(['login' => 'admin_schedule']);

        $this->actingAs($admin)
            ->post(route('admin.alerts.dismiss', ['type' => 'schedule']))
            ->assertForbidden();

        $this->assertFileDoesNotExist($this->dismissedPath);
    }
}
