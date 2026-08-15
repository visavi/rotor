<?php

declare(strict_types=1);

namespace App\Http\Resources\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Вложения записи для API: медиа отдельно от остальных файлов, как в вёрстке сайта
 */
trait ResolvesAttachments
{
    /**
     * Картинки и видео, кроме вставленных в текст — их клиент покажет прямо в тексте
     */
    protected function resolveMedia(?Model $model): Collection
    {
        if ($model && $model->relationLoaded('files') && method_exists($model, 'getDetachedMedia')) {
            return $model->getDetachedMedia();
        }

        return collect();
    }

    /**
     * Вложения без картинок и видео
     */
    protected function resolveFiles(?Model $model): Collection
    {
        if ($model && $model->relationLoaded('files') && method_exists($model, 'getFiles')) {
            return $model->getFiles();
        }

        return collect();
    }
}
