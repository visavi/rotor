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

    public function testSyncConnectionSendsImmediately(): void
    {
        config(['queue.default' => 'sync']);

        // Настоящий queue(), подменена только сама отправка
        $mail = $this->getMockBuilder(MailService::class)
            ->onlyMethods(['sendOrFail'])
            ->getMock();

        $mail->expects(self::once())->method('sendOrFail');

        $this->app->instance(MailService::class, $mail);

        // sync выполняет задачу на месте: у кого крон раз в час, тот выбирает
        // мгновенную отправку сознательно
        $mail->queue('mailer.default', ['to' => 'user@example.com']);
    }

    public function testTextPartIsNotHtmlEscaped(): void
    {
        config(['mail.default' => 'array']);

        app(MailService::class)->sendOrFail('mailer.restore', [
            'to'       => 'user@example.com',
            'subject'  => 'Тема',
            'username' => 'Tom & <Jerry>',
            'login'    => 'tom',
            'password' => 'a&b"c',
        ]);

        $message = app('mailer')->getSymfonyTransport()->messages()->last()->getOriginalMessage();

        // В простом тексте экранировать нечего, в HTML — обязательно
        self::assertStringContainsString('Tom & <Jerry>', $message->getTextBody());
        self::assertStringContainsString('a&b"c', $message->getTextBody());
        self::assertStringContainsString('Tom &amp; &lt;Jerry&gt;', $message->getHtmlBody());
    }

    public function testRetryPolicy(): void
    {
        $job = new SendMailJob('mailer.default', ['to' => 'user@example.com']);

        self::assertSame(3, $job->tries);
        self::assertSame([60, 300, 900], $job->backoff());
    }
}
