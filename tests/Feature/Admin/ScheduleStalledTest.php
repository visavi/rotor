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

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->boss = User::factory()->boss()->create(['login' => 'boss_schedule']);
        $this->path = storage_path('framework/schedule-run');

        // Метка живёт файлом и переживает тесты, поэтому убираем её заранее
        @unlink($this->path);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);

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
}
