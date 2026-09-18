<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;

class ScheduleService
{
    /**
     * Через сколько секунд молчания планировщик считается вставшим
     *
     * Задачи запускаются ежеминутно, но на нагруженном хостинге запуск
     * случается пропустить, поэтому порог с большим запасом
     */
    public const int STALLED = 3600;

    /**
     * Отмечает запуск планировщика
     *
     * Метка лежит файлом, а не в кэше: кэш админ чистит кнопкой в панели,
     * и после каждой чистки движок сообщал бы о неработающем кроне
     */
    public function markRun(): void
    {
        $path = $this->path();

        if (! is_dir(dirname($path))) {
            return;
        }

        touch($path);
    }

    /**
     * Время последнего запуска планировщика
     */
    public function lastRun(): ?CarbonImmutable
    {
        $path = $this->path();

        if (! is_file($path)) {
            return null;
        }

        $time = filemtime($path);

        return $time ? CarbonImmutable::createFromTimestamp($time) : null;
    }

    /**
     * Молчит ли планировщик дольше порога
     *
     * Метки нет вовсе — крон не настроен либо не отработал ни разу
     */
    public function isStalled(): bool
    {
        $lastRun = $this->lastRun();

        return ! $lastRun || $lastRun->timestamp < now()->timestamp - self::STALLED;
    }

    /**
     * Путь к метке запуска
     */
    private function path(): string
    {
        return storage_path('framework/schedule-run');
    }
}
