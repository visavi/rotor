<?php

namespace Tests\Feature\Mail;

use App\Services\QueueDrainer;
use App\Services\ScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueueDrainerTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = storage_path('framework/schedule-run');

        @unlink($this->path);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);

        parent::tearDown();
    }

    public function testRunsWhenSchedulerIsStalled(): void
    {
        $this->pushJob();

        self::assertTrue(app(QueueDrainer::class)->shouldRun());
    }

    public function testSkippedWhenSchedulerIsAlive(): void
    {
        app(ScheduleService::class)->markRun();

        $this->pushJob();

        self::assertFalse(app(QueueDrainer::class)->shouldRun());
    }

    public function testSkippedWhenQueueIsEmpty(): void
    {
        self::assertFalse(app(QueueDrainer::class)->shouldRun());
    }

    public function testSkippedUnderBuiltInServer(): void
    {
        $this->pushJob();

        $drainer = new class (app(ScheduleService::class)) extends QueueDrainer {
            protected function sapi(): string
            {
                return 'cli-server';
            }
        };

        self::assertFalse($drainer->shouldRun());
    }

    private function pushJob(): void
    {
        DB::table('jobs')->insert([
            'queue'        => 'default',
            'payload'      => '{}',
            'attempts'     => 0,
            'reserved_at'  => null,
            'available_at' => now()->timestamp,
            'created_at'   => now()->timestamp,
        ]);
    }
}
