<?php

declare(strict_types=1);

namespace App\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use ReflectionClass;

/**
 * Кэш, который устаревает при сохранении или удалении записи модели
 *
 * Сбрасывается по событиям saved и deleted, после коммита транзакции: иначе соседний
 * запрос успел бы закэшировать ещё не изменённые данные. save() без изменений кэш не трогает.
 * Массовые query()->update() и delete() событий не вызывают — после них clearCache()
 * по-прежнему нужен вручную. increment() вызывает только updated, поэтому счётчики
 * просмотров кэш не трогают; кэш, собранный из счётчиков, сбрасывают после них вручную
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ClearsCache
{
    /**
     * @var list<string>
     */
    public array $keys;

    /**
     * Ключи по классу модели: рефлексия — один раз на класс
     *
     * @var array<class-string, list<string>>
     */
    private static array $memo = [];

    public function __construct(string ...$keys)
    {
        $this->keys = array_values($keys);
    }

    /**
     * Слушатель для всех моделей: модели без атрибута пропускаются
     */
    public static function listen(): void
    {
        Event::listen(['eloquent.saved: *', 'eloquent.deleted: *'], static function (string $event, array $payload): void {
            $model = $payload[0] ?? null;

            if (! $model instanceof Model) {
                return;
            }

            $keys = self::keysFor($model);
            $unchanged = str_starts_with($event, 'eloquent.saved') && ! $model->wasRecentlyCreated && ! $model->wasChanged();

            if ($keys && ! $unchanged) {
                // Без открытой транзакции afterCommit() выполняет сразу
                $model->getConnection()->afterCommit(static fn () => clearCache($keys));
            }
        });
    }

    /**
     * Ключи из атрибута модели, у наследника — из атрибута родителя
     *
     * @return list<string>
     */
    public static function keysFor(Model $model): array
    {
        $class = $model::class;

        if (! isset(self::$memo[$class])) {
            $reflection = new ReflectionClass($class);
            $keys = [];

            while ($reflection) {
                $attributes = $reflection->getAttributes(self::class);

                if ($attributes) {
                    $keys = $attributes[0]->newInstance()->keys;

                    break;
                }

                $reflection = $reflection->getParentClass() ?: null;
            }

            self::$memo[$class] = $keys;
        }

        return self::$memo[$class];
    }
}
