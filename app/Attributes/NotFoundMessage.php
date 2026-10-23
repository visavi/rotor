<?php

declare(strict_types=1);

namespace App\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use ReflectionClass;

/**
 * Текст 404, когда findOrFail() или firstOrFail() не нашли запись модели
 *
 * Без атрибута — общее «Запись не найдена». Наследник модели берёт атрибут родителя
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class NotFoundMessage
{
    public function __construct(public string $key)
    {
    }

    /**
     * Сообщение для исключения: ключ перевода из атрибута модели
     */
    public static function for(ModelNotFoundException $exception): string
    {
        // Collection::findOrFail() бросает исключение без модели: getModel() тогда null
        $model = (string) $exception->getModel();
        $class = class_exists($model) ? new ReflectionClass($model) : null;

        while ($class) {
            $attributes = $class->getAttributes(self::class);

            if ($attributes) {
                return (string) __($attributes[0]->newInstance()->key);
            }

            $class = $class->getParentClass() ?: null;
        }

        return (string) __('main.record_not_found');
    }
}
