<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class QueueService
{
    /**
     * Через сколько секунд ожидания очередь считается вставшей
     *
     * Воркер поднимается ежеминутно; четверть часа — запас на случай
     * долгой задачи и пропущенных запусков крона
     */
    public const int STALLED = 900;

    /**
     * Сколько задач ждёт обработки
     */
    public function pendingCount(): int
    {
        if (! $this->isDatabaseDriver()) {
            return 0;
        }

        return DB::table('jobs')->count();
    }

    /**
     * Стоит ли очередь
     *
     * Признак — возраст самой старой задачи: при живом воркере
     * очередь не накапливается
     */
    public function isStalled(): bool
    {
        if (! $this->isDatabaseDriver()) {
            return false;
        }

        $oldest = DB::table('jobs')->min('created_at');

        return $oldest !== null && (int) $oldest < now()->timestamp - self::STALLED;
    }

    /**
     * Скрывает предупреждение до следующего затора
     *
     * Запоминается самая старая задача: когда её разберут и очередь встанет
     * снова, первой будет другая задача, и предупреждение вернётся
     */
    public function dismiss(): void
    {
        file_put_contents($this->dismissedPath(), (string) DB::table('jobs')->min('id'));
    }

    /**
     * Скрыто ли предупреждение о текущем заторе
     */
    public function isDismissed(): bool
    {
        $path = $this->dismissedPath();

        return is_file($path) && (int) file_get_contents($path) === (int) DB::table('jobs')->min('id');
    }

    private function dismissedPath(): string
    {
        return storage_path('framework/queue-dismissed');
    }

    /**
     * Работает ли очередь через таблицу
     *
     * При sync задач не бывает вовсе, при redis и прочих драйверах
     * таблицы jobs нет — предупреждать не о чем
     */
    private function isDatabaseDriver(): bool
    {
        return config('queue.default') === 'database';
    }
}
