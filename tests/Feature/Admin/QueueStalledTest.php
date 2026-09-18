<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\QueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueueStalledTest extends TestCase
{
    use RefreshDatabase;

    private User $boss;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->boss = User::factory()->boss()->create(['login' => 'boss_queue']);

        config(['queue.default' => 'database']);
    }

    public function testWarningHiddenWhenQueueEmpty(): void
    {
        $this->actingAs($this->boss)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee(__('index.queue_stalled'));
    }

    public function testWarningShownWhenJobIsOld(): void
    {
        $this->pushJob(now()->timestamp - QueueService::STALLED - 60);

        $this->actingAs($this->boss)
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('index.queue_stalled'));
    }

    public function testFreshJobDoesNotWarn(): void
    {
        $this->pushJob(now()->timestamp);

        $this->actingAs($this->boss)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee(__('index.queue_stalled'));
    }

    public function testSyncDriverNeverWarns(): void
    {
        config(['queue.default' => 'sync']);

        $this->pushJob(now()->timestamp - QueueService::STALLED - 60);

        $this->actingAs($this->boss)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee(__('index.queue_stalled'));
    }

    public function testWarningHiddenFromAdmin(): void
    {
        $admin = User::factory()->admin()->create(['login' => 'admin_queue']);

        $this->pushJob(now()->timestamp - QueueService::STALLED - 60);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee(__('index.queue_stalled'));
    }

    private function pushJob(int $createdAt): void
    {
        DB::table('jobs')->insert([
            'queue'        => 'default',
            'payload'      => '{}',
            'attempts'     => 0,
            'reserved_at'  => null,
            'available_at' => $createdAt,
            'created_at'   => $createdAt,
        ]);
    }
}
