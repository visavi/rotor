<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Search;

trait SearchableTrait
{
    /**
     * Поля модели, которые должны быть включены в поиск
     */
    protected function searchableFields(): array
    {
        return [];
    }

    /**
     * Boot the trait
     */
    public static function bootSearchableTrait(): void
    {
        static::created(static function ($model) {
            if ($model->shouldBeSearchable()) {
                $model->updateSearchIndex();
            }
        });

        static::updated(static function ($model) {
            if ($model->shouldBeSearchable()) {
                $model->updateSearchIndex();
            } else {
                $model->removeFromSearchIndex();
            }
        });

        static::deleted(static function ($model) {
            $model->removeFromSearchIndex();
        });
    }

    /**
     * Проверяет, нужно ли добавлять запись в поисковый индекс
     */
    public function shouldBeSearchable(): bool
    {
        return (bool) ($this->active ?? true);
    }

    /**
     * Обновляет запись в поисковом индексе
     */
    public function updateSearchIndex(): void
    {
        if (! $this->searchableFields()) {
            return;
        }

        $text = $this->buildSearchText();

        Search::query()->updateOrCreate(
            [
                'relate_type' => $this->getMorphClass(),
                'relate_id'   => $this->getKey(),
            ],
            [
                'text'       => $text,
                'created_at' => $this->created_at,
            ]
        );
    }

    /**
     * Удаляет запись из поискового индекса
     */
    public function removeFromSearchIndex(): void
    {
        Search::query()
            ->where('relate_type', $this->getMorphClass())
            ->where('relate_id', $this->getKey())
            ->delete();
    }

    /**
     * Подготавливает текст для поиска из указанных полей
     */
    public function buildSearchText(): string
    {
        $values = [];
        $fields = $this->searchableFields();

        foreach ($fields as $field) {
            if (isset($this->$field)) {
                $values[] = $this->$field;
            }
        }

        return preg_replace('/\[(.*?)]/', '', implode(' ', $values));
    }
}
