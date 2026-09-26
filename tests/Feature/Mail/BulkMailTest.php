<?php

namespace Tests\Feature\Mail;

use App\Jobs\SendMailJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class BulkMailTest extends TestCase
{
    use RefreshDatabase;

    public function testBirthdaysAreQueued(): void
    {
        Queue::fake();

        $this->makeBirthdayUser(1);

        $this->artisan('add:birthdays')->assertSuccessful();

        Queue::assertPushed(SendMailJob::class, static function (SendMailJob $job) {
            return $job->data['to'] === 'birthday1@example.com'
                && $job->data['unsubscribe'] === 'key-1';
        });
    }

    public function testPacketSizeStaggersDelivery(): void
    {
        Queue::fake();

        $this->overrideSetting('sendmailpacket', 2);

        foreach (range(1, 5) as $index) {
            $this->makeBirthdayUser($index);
        }

        $this->artisan('add:birthdays')->assertSuccessful();

        Queue::assertPushed(SendMailJob::class, 5);

        Queue::assertPushed(SendMailJob::class, static function (SendMailJob $job) {
            return $job->delay !== null;
        });
    }

    public function testSyncFailureDoesNotStopBirthdays(): void
    {
        // На sync письмо уходит прямо в команде: сбой на одном адресе
        // не должен оставлять без поздравления остальных
        $sent = [];

        Mail::shouldReceive('send')->andReturnUsing(static function ($views, array $data) use (&$sent) {
            if ($data['to'] === 'birthday1@example.com') {
                throw new RuntimeException('Connection could not be established');
            }

            $sent[] = $data['to'];
        });

        foreach (range(1, 3) as $index) {
            $this->makeBirthdayUser($index);
        }

        try {
            $this->artisan('add:birthdays')->assertSuccessful();
        } finally {
            // Метка сбоя живёт файлом и пережила бы тест
            @unlink(storage_path('framework/mail-failure'));
        }

        sort($sent);

        $this->assertSame(['birthday2@example.com', 'birthday3@example.com'], $sent);
    }

    private function makeBirthdayUser(int $index): void
    {
        User::factory()->create([
            'login'     => 'birthday_' . $index,
            'email'     => 'birthday' . $index . '@example.com',
            'birthday'  => now()->format('d.m.Y'),
            'point'     => 10,
            'subscribe' => 'key-' . $index,
        ]);
    }
}
