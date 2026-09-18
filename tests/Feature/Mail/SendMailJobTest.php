<?php

namespace Tests\Feature\Mail;

use App\Jobs\SendMailJob;
use App\Services\MailService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendMailJobTest extends TestCase
{
    public function testQueuePushesJob(): void
    {
        Queue::fake();

        app(MailService::class)->queue('mailer.default', [
            'to'      => 'user@example.com',
            'subject' => 'Тема',
            'text'    => 'Текст',
        ]);

        Queue::assertPushed(SendMailJob::class, static function (SendMailJob $job) {
            return $job->view === 'mailer.default'
                && $job->data['to'] === 'user@example.com';
        });
    }

    public function testJobSendsMail(): void
    {
        $data = [
            'to'      => 'user@example.com',
            'subject' => 'Тема',
            'text'    => 'Текст',
        ];

        $mail = $this->createMock(MailService::class);
        $mail->expects(self::once())
            ->method('sendOrFail')
            ->with('mailer.default', $data);

        (new SendMailJob('mailer.default', $data))->handle($mail);
    }

    public function testMailAlwaysGoesToDatabaseConnection(): void
    {
        config(['queue.default' => 'sync']);

        Queue::fake();

        app(MailService::class)->queue('mailer.default', ['to' => 'user@example.com']);

        Queue::assertPushed(SendMailJob::class, static function (SendMailJob $job) {
            return $job->connection === 'database';
        });
    }

    public function testCustomDriverIsRespected(): void
    {
        config(['queue.default' => 'redis']);

        self::assertSame('redis', app(MailService::class)->connection());
    }

    public function testRetryPolicy(): void
    {
        $job = new SendMailJob('mailer.default', ['to' => 'user@example.com']);

        self::assertSame(3, $job->tries);
        self::assertSame([60, 300, 900], $job->backoff());
    }
}
