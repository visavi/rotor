<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Throwable;

class QueueDrainer
{
    /**
     * Сколько задач разбирать за один запрос
     */
    public const int MAX_JOBS = 3;

    /**
     * Сколько секунд на это тратить
     *
     * Держится заметно ниже retry_after (90 секунд), иначе очередь
     * сочла бы задачу зависшей и выполнила её повторно
     */
    public const int MAX_SECONDS = 5;

    public function __construct(private readonly ScheduleService $schedule)
    {
    }

    /**
     * Нужно ли разгребать очередь силами веб-запроса
     *
     * Нужно только там, где крон не настроен: при живом планировщике
     * очередь разбирает queue:work, и мешать ему незачем
     */
    public function shouldRun(): bool
    {
        // Встроенный сервер PHP не умеет fastcgi_finish_request: разгребание
        // задержало бы отдачу страницы вместо того, чтобы идти после неё
        if ($this->sapi() === 'cli-server') {
            return false;
        }

        if (! $this->schedule->isStalled()) {
            return false;
        }

        return $this->pending() > 0;
    }

    /**
     * Разбирает несколько задач
     *
     * Ошибки глушатся намеренно: разгребание идёт после отдачи ответа,
     * ронять его падением одной задачи бессмысленно — очередь
     * назначит повтор сама
     */
    public function drain(): void
    {
        try {
            Artisan::call('queue:work', [
                'connection'        => app(MailService::class)->connection(),
                '--stop-when-empty' => true,
                '--max-jobs'        => self::MAX_JOBS,
                '--max-time'        => self::MAX_SECONDS,
            ]);
        } catch (Throwable) {
            // очередь разберётся при следующем запросе
        }
    }

    /**
     * Сколько задач ждёт
     *
     * Через фасад, а не запросом к jobs: размер так спрашивается
     * у любого драйвера, включая redis
     */
    private function pending(): int
    {
        try {
            return Queue::connection(app(MailService::class)->connection())->size();
        } catch (Throwable) {
            // хранилища может не быть: установка ещё не закончена
            return 0;
        }
    }

    /**
     * Интерфейс, под которым работает PHP
     *
     * Вынесен в метод, чтобы тест мог подменить его подклассом
     */
    protected function sapi(): string
    {
        return PHP_SAPI;
    }
}
