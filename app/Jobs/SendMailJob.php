<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\MailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendMailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Сколько раз пробовать отправить
     */
    public int $tries = 3;

    public function __construct(
        public string $view,
        public array $data,
    ) {
    }

    /**
     * Паузы между попытками
     *
     * Первая через минуту — переживает мигание сети, последняя через
     * четверть часа — переживает перезапуск почтового сервера
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    /**
     * Отправляет письмо
     *
     * Исключение пробрасывается наружу: очередь должна увидеть провал
     * и назначить повтор, а не считать задачу выполненной
     */
    public function handle(MailService $mail): void
    {
        $mail->sendOrFail($this->view, $this->data);
    }

    /**
     * Вызывается, когда исчерпаны все попытки
     */
    public function failed(Throwable $e): void
    {
        app(MailService::class)->markFailure($e, $this->data);
    }
}
