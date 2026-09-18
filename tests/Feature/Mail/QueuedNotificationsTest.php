<?php

namespace Tests\Feature\Mail;

use App\Jobs\SendMailJob;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueuedNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function testRecoveryMailIsQueued(): void
    {
        Queue::fake();

        $this->overrideSetting('app_installed', 1);

        $user = User::factory()->create([
            'login' => 'queued_user',
            'email' => 'queued@example.com',
        ]);

        app(UserService::class)->requestRecovery($user);

        Queue::assertPushed(SendMailJob::class);
    }
}
